<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Resolve;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use DualMedia\DtoRequestBundle\Dto\Model\Dynamic;
use DualMedia\DtoRequestBundle\Dto\Model\Literal;
use DualMedia\DtoRequestBundle\Metadata\Enum\BagEnum;
use DualMedia\DtoRequestBundle\Metadata\Model\Dto;
use DualMedia\DtoRequestBundle\Metadata\Model\FindBy;
use DualMedia\DtoRequestBundle\Metadata\Model\MainDto;
use DualMedia\DtoRequestBundle\Metadata\Model\Property;
use DualMedia\DtoRequestBundle\Metadata\Model\ValidateWithGroups;
use DualMedia\DtoRequestBundle\Metadata\Model\WithErrorPath;
use DualMedia\DtoRequestBundle\MetadataUtils;
use DualMedia\DtoRequestBundle\Provider\Interface\GroupProviderInterface;
use DualMedia\DtoRequestBundle\Reflection\CacheReflector;
use DualMedia\DtoRequestBundle\Resolve\Model\PendingEntityValue;
use DualMedia\DtoRequestBundle\Resolve\Model\PendingValue;
use DualMedia\DtoRequestBundle\Type\TypeInfoUtils;
use DualMedia\DtoRequestBundle\Util;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class DtoResolver
{
    /**
     * @param ServiceLocator<GroupProviderInterface> $groupProviderLocator
     */
    public function __construct(
        private readonly Extractor $extractor,
        private readonly CacheReflector $cacheReflector,
        private readonly ValidatorInterface $validator,
        private readonly ServiceLocator $groupProviderLocator
    ) {
    }

    /**
     * @param class-string<T> $class
     *
     * @return T
     *
     * @template T of AbstractDto
     */
    public function resolve(
        string $class,
        Request $request,
        BagEnum $defaultBag = BagEnum::Request
    ): AbstractDto {
        $dto = new $class();
        $mainDto = $this->cacheReflector->get($class);

        if (null === $mainDto) {
            return $dto;
        }

        /** @var list<PendingValue|PendingEntityValue> $pending */
        $pending = [];

        // phase 1: recursively extract and coerce all values across the tree
        $accessor = new BagAccessor($request);
        $this->extractor->extract($mainDto, $dto, $accessor, $defaultBag, [], $pending);

        // phase 2: coerce and validate in sequenced phases per property
        $violated = [];
        $finalValues = [];

        foreach ($pending as $i => $entry) {
            if ($entry instanceof PendingEntityValue) {
                $criteria = [];
                $entryViolated = false;

                foreach ($entry->fields as $target => $fieldPending) {
                    $value = $fieldPending->value;

                    foreach ($fieldPending->phases as [$coerce, $phaseConstraints]) {
                        $value = $coerce($value);

                        $context = $this->validator->startContext();

                        $context->atPath($fieldPending->validationPath)
                            ->validate($value, $phaseConstraints);

                        $phaseViolations = $context->getViolations();

                        if ($phaseViolations->count() > 0) {
                            $entryViolated = true;

                            foreach ($phaseViolations as $violation) {
                                $entry->dto->addConstraintViolation($violation);
                            }

                            break;
                        }
                    }

                    if ($entryViolated) {
                        break;
                    }

                    $criteria[$target] = $value;
                }

                if (!$entryViolated) {
                    $finalValues[$i] = ($entry->load)($criteria);
                } else {
                    $violated[$entry->validationPath] = true;
                }

                continue;
            }

            $value = $entry->value;

            foreach ($entry->phases as [$coerce, $phaseConstraints]) {
                $value = $coerce($value);

                $context = $this->validator->startContext();

                $context->atPath($entry->validationPath)
                    ->validate($value, $phaseConstraints);

                $phaseViolations = $context->getViolations();

                if ($phaseViolations->count() > 0) {
                    $violated[$entry->validationPath] = true;

                    foreach ($phaseViolations as $violation) {
                        $dto->addConstraintViolation($violation);
                    }

                    break;
                }
            }

            if (!isset($violated[$entry->validationPath])) {
                $finalValues[$i] = $value;
            }
        }

        // phase 3: set valid values
        foreach ($pending as $i => $entry) {
            if (isset($violated[$entry->validationPath])) {
                continue;
            }

            $entry->dto->{$entry->name} = $finalValues[$i];
        }

        // phase 4: validate the main object
        $groups = null;

        if (null !== $mainDto->validationGroupsServiceId) {
            $vwg = MetadataUtils::single(ValidateWithGroups::class, $mainDto->meta);
            assert(null !== $vwg);

            $provider = $this->groupProviderLocator->get($mainDto->validationGroupsServiceId);
            $groups = ($vwg->closure)($provider, $dto);
        }

        $violations = $this->validator->startContext()
            ->validate($dto, null, $groups)
            ->getViolations();

        foreach ($violations as $violation) {
            $path = $violation->getPropertyPath();
            $remapped = $this->remapViolationPath($path, $mainDto);

            if ($remapped !== $path) {
                $violation = new ConstraintViolation(
                    $violation->getMessage(),
                    $violation->getMessageTemplate(),
                    $violation->getParameters(),
                    $violation->getRoot(),
                    $remapped,
                    $violation->getInvalidValue(),
                    $violation->getPlural(),
                    $violation->getCode(),
                    $violation->getConstraint(),
                    $violation->getCause()
                );
            }

            $dto->addConstraintViolation($violation);
        }

        return $dto;
    }

    private function remapViolationPath(
        string $path,
        MainDto $mainDto
    ): string {
        if ('' === $path) {
            return $path;
        }

        $elements = $this->parsePropertyPath($path);
        $segments = [];
        $currentFields = $mainDto->fields;

        for ($i = 0, $len = count($elements); $i < $len; ++$i) {
            [$element, $isIndex] = $elements[$i];

            if ($isIndex) {
                $segments[] = $element;

                continue;
            }

            $field = $currentFields[$element] ?? null;

            if (null === $field) {
                for ($j = $i; $j < $len; ++$j) {
                    $segments[] = $elements[$j][0];
                }

                break;
            }

            if ($field instanceof Property && MetadataUtils::exists(FindBy::class, $field->meta)) {
                $errorPath = MetadataUtils::single(WithErrorPath::class, $field->meta);

                $segments[] = null !== $errorPath
                    ? $errorPath->path
                    : ($this->findFirstNonDynamicInput($field->virtual) ?? $field->getRealPath());

                for ($j = $i + 1; $j < $len; ++$j) {
                    $segments[] = $elements[$j][0];
                }

                break;
            }

            if ($field instanceof Dto) {
                $segments[] = $field->getRealPath();
                /** @var class-string<AbstractDto>|null $fqcn */
                $fqcn = TypeInfoUtils::getClassName($field->type)
                    ?? TypeInfoUtils::getCollectionValueClassName($field->type);
                $childMeta = null !== $fqcn ? $this->cacheReflector->get($fqcn) : null;
                $currentFields = null !== $childMeta ? $childMeta->fields : [];

                continue;
            }

            $segments[] = $field->getRealPath();

            for ($j = $i + 1; $j < $len; ++$j) {
                $segments[] = $elements[$j][0];
            }

            break;
        }

        return Util::buildValidationPath($segments);
    }

    /**
     * Parses a Symfony violation property path into elements.
     *
     * E.g. "children[0].entity" → [['children', false], ['0', true], ['entity', false]]
     *
     * @return list<array{string, bool}> pairs of [element, isIndex]
     */
    private function parsePropertyPath(
        string $path
    ): array {
        $elements = [];

        preg_match_all('/(?:\[(?<index>[^\]]+)\])|(?<prop>[^.\[\]]+)/', $path, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $index = $match['index'] ?? '';
            $prop = $match['prop'] ?? '';

            if ('' !== $index) {
                $elements[] = [$index, true];
            } elseif ('' !== $prop) {
                $elements[] = [$prop, false];
            }
        }

        return $elements;
    }

    /**
     * @param array<string, Property|Dynamic|Literal> $virtual
     */
    private function findFirstNonDynamicInput(
        array $virtual
    ): string|null {
        foreach ($virtual as $v) {
            if ($v instanceof Property) {
                return $v->getRealPath();
            }
        }

        return null;
    }
}
