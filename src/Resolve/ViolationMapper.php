<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Resolve;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use DualMedia\DtoRequestBundle\Dto\Model\Dynamic;
use DualMedia\DtoRequestBundle\Dto\Model\Literal;
use DualMedia\DtoRequestBundle\Metadata\Model\Dto;
use DualMedia\DtoRequestBundle\Metadata\Model\FindBy;
use DualMedia\DtoRequestBundle\Metadata\Model\MainDto;
use DualMedia\DtoRequestBundle\Metadata\Model\Property;
use DualMedia\DtoRequestBundle\Metadata\Model\WithErrorPath;
use DualMedia\DtoRequestBundle\MetadataUtils;
use DualMedia\DtoRequestBundle\Reflection\CacheReflector;
use DualMedia\DtoRequestBundle\Type\TypeInfoUtils;
use DualMedia\DtoRequestBundle\Util;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationInterface;

class ViolationMapper
{
    public function __construct(
        private readonly CacheReflector $cacheReflector
    ) {
    }

    public function remap(
        ConstraintViolationInterface $violation,
        MainDto $mainDto
    ): ConstraintViolationInterface {
        $path = $violation->getPropertyPath();
        $remapped = $this->remapPath($path, $mainDto);

        if ($remapped === $path) {
            return $violation;
        }

        return new ConstraintViolation(
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

    private function remapPath(
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
                if ('' !== $field->getRealPath()) {
                    $segments[] = $field->getRealPath();
                }

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
