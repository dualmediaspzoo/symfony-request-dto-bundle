<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Resolve;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use DualMedia\DtoRequestBundle\Metadata\Enum\BagEnum;
use DualMedia\DtoRequestBundle\Metadata\Model\ValidateWithGroups;
use DualMedia\DtoRequestBundle\MetadataUtils;
use DualMedia\DtoRequestBundle\Provider\Interface\GroupProviderInterface;
use DualMedia\DtoRequestBundle\Reflection\CacheReflector;
use DualMedia\DtoRequestBundle\Resolve\Model\PendingEntityValue;
use DualMedia\DtoRequestBundle\Resolve\Model\PendingValue;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\HttpFoundation\Request;
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
        private readonly ServiceLocator $groupProviderLocator,
        private readonly ViolationMapper $violationMapper
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
            $dto->addConstraintViolation(
                $this->violationMapper->remap($violation, $mainDto)
            );
        }

        return $dto;
    }
}
