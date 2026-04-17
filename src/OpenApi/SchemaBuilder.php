<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\OpenApi;

use DualMedia\DtoRequestBundle\Metadata\Enum\BagEnum;
use DualMedia\DtoRequestBundle\Metadata\Model\Action;
use DualMedia\DtoRequestBundle\Metadata\Model\Format;
use DualMedia\DtoRequestBundle\MetadataUtils;
use DualMedia\DtoRequestBundle\OpenApi\Model\DescribedDto;
use DualMedia\DtoRequestBundle\OpenApi\Model\DescribedField;
use OpenApi\Attributes as OA;
use OpenApi\Generator;
use Symfony\Component\Validator\Constraints as Assert;

class SchemaBuilder
{
    private const string FILE_EXAMPLE_BASE64 = 'data:image/jpeg;base64,aHR0cHM6Ly93d3cueW91dHViZS5jb20vd2F0Y2g/dj1kUXc0dzlXZ1hjUQ==';

    /**
     * @return list<OA\Parameter>
     */
    public function buildParameters(
        DescribedDto $dto,
        string $routePath
    ): array {
        $out = [];

        $this->collectParameters($dto->fields, $routePath, $out);

        return $out;
    }

    public function buildRequestBody(
        DescribedDto $dto
    ): OA\RequestBody|null {
        $bodyFields = array_values(array_filter(
            $dto->fields,
            static fn (DescribedField $f): bool => BagEnum::Request === $f->bag || BagEnum::Files === $f->bag
        ));

        if ([] === $bodyFields) {
            return null;
        }

        $schema = new OA\Schema(
            required: $this->requiredNames($bodyFields),
            properties: $this->buildPropertyList($bodyFields),
            type: 'object',
        );

        return new OA\RequestBody(
            content: [
                new OA\MediaType(
                    mediaType: 'application/json',
                    schema: $schema,
                ),
            ],
        );
    }

    /**
     * @return list<OA\Response>
     */
    public function buildResponses(
        DescribedDto $dto
    ): array {
        // TODO: this metadata-specific logic (Action → Response) should eventually
        // move out of the describer into dedicated per-metadata handlers. Inlined
        // here for now because we don't care yet.
        $actions = MetadataUtils::list(Action::class, $dto->meta);

        foreach ($dto->fields as $field) {
            foreach (MetadataUtils::list(Action::class, $field->meta) as $action) {
                $actions[] = $action;
            }
        }

        $out = [];
        $seen = [];

        foreach ($actions as $action) {
            if (isset($seen[$action->statusCode])) {
                continue;
            }

            $seen[$action->statusCode] = true;
            $out[] = new OA\Response(
                response: (string)$action->statusCode,
                description: $action->message ?? 'No description set',
            );
        }

        return $out;
    }

    /**
     * Parameter-position bags (query/header/path/cookie) cannot carry nested paths.
     * When a nested DTO lands in one of those bags, its leaf children become
     * individual top-level parameters keyed only by their own path.
     *
     * @param list<DescribedField> $fields
     * @param list<OA\Parameter> $out
     */
    private function collectParameters(
        array $fields,
        string $routePath,
        array &$out
    ): void {
        foreach ($fields as $field) {
            $in = $field->bag->parameterLocation();

            if (null === $in) {
                continue;
            }

            if ('object' === $field->oaType) {
                $this->collectParameters($field->children, $routePath, $out);

                continue;
            }

            if ('path' === $in && !str_contains($routePath, '{'.$field->path.'}')) {
                continue;
            }

            $out[] = $this->buildParameter($field, $in);
        }
    }

    /**
     * @param 'query'|'header'|'path'|'cookie' $in
     */
    private function buildParameter(
        DescribedField $field,
        string $in
    ): OA\Parameter {
        if ($field->isCollection) {
            $items = new OA\Items(type: $field->oaType);
            $this->applyConstraints($items, $field);

            $schema = new OA\Schema(
                type: 'array',
                items: $items,
            );
        } else {
            $schema = new OA\Schema(type: $field->oaType);
            $this->applyConstraints($schema, $field);
        }

        return new OA\Parameter(
            name: $field->path.('query' === $in && $field->isCollection ? '[]' : ''),
            description: null,
            in: $in,
            required: $field->required || 'path' === $in,
            schema: $schema,
        );
    }

    /**
     * @param list<DescribedField> $fields
     *
     * @return list<OA\Property>
     */
    private function buildPropertyList(
        array $fields
    ): array {
        $out = [];

        foreach ($fields as $field) {
            $out[] = $this->buildProperty($field);
        }

        return $out;
    }

    private function buildProperty(
        DescribedField $field
    ): OA\Property {
        if ('object' === $field->oaType) {
            if ($field->isCollection) {
                return new OA\Property(
                    property: $field->path,
                    type: 'array',
                    items: new OA\Items(
                        required: $this->requiredNames($field->children),
                        properties: $this->buildPropertyList($field->children),
                        type: 'object',
                    ),
                );
            }

            return new OA\Property(
                property: $field->path,
                required: $this->requiredNames($field->children),
                properties: $this->buildPropertyList($field->children),
                type: 'object',
            );
        }

        if ($field->isCollection) {
            $items = new OA\Items(type: $field->oaType);
            $this->applyConstraints($items, $field);

            $property = new OA\Property(
                property: $field->path,
                type: 'array',
                items: $items,
            );
        } else {
            $property = new OA\Property(
                property: $field->path,
                type: $field->oaType,
            );
            $this->applyConstraints($property, $field);
        }

        if (BagEnum::Files === $field->bag) {
            $property->description = 'This field is a file and can be passed as a http upload by using the same path, '
                .'or by encoding as base64 in the body';
            $property->example = $field->isCollection ? [self::FILE_EXAMPLE_BASE64] : self::FILE_EXAMPLE_BASE64;
        }

        return $property;
    }

    /**
     * @param list<DescribedField> $fields
     *
     * @return list<string>
     */
    private function requiredNames(
        array $fields
    ): array {
        $out = [];

        foreach ($fields as $field) {
            if ($field->required) {
                $out[] = $field->path;
            }
        }

        return $out;
    }

    /**
     * Map validator constraints + carried metadata onto an OpenAPI schema node.
     */
    private function applyConstraints(
        OA\Schema|OA\Property|OA\Items $schema,
        DescribedField $field
    ): void {
        if ([] !== $field->enumCases) {
            $schema->enum = $field->enumCases;
        }

        // TODO: this metadata-specific logic (Format → schema->format) should
        // eventually move out of the describer into dedicated per-metadata
        // handlers. Inlined here for now because we don't care yet.
        $format = MetadataUtils::single(Format::class, $field->meta);

        if (null !== $format) {
            $schema->format = $format->format;
        }

        foreach ($field->constraints as $constraint) {
            if ($constraint instanceof Assert\Length) {
                if (null !== $constraint->min) {
                    $schema->minLength = $constraint->min;
                }

                if (null !== $constraint->max) {
                    $schema->maxLength = $constraint->max;
                }
            }

            if ($constraint instanceof Assert\Range) {
                if (null !== ($min = $this->toNumeric($constraint->min))) {
                    $schema->minimum = $min;
                }

                if (null !== ($max = $this->toNumeric($constraint->max))) {
                    $schema->maximum = $max;
                }
            }

            if ($constraint instanceof Assert\LessThan && null !== ($value = $this->toNumeric($constraint->value))) {
                $schema->exclusiveMaximum = true;
                $schema->maximum = $value;
            }

            if ($constraint instanceof Assert\LessThanOrEqual && null !== ($value = $this->toNumeric($constraint->value))) {
                $schema->maximum = $value;
            }

            if ($constraint instanceof Assert\GreaterThan && null !== ($value = $this->toNumeric($constraint->value))) {
                $schema->exclusiveMinimum = true;
                $schema->minimum = $value;
            }

            if ($constraint instanceof Assert\GreaterThanOrEqual && null !== ($value = $this->toNumeric($constraint->value))) {
                $schema->minimum = $value;
            }

            if ($constraint instanceof Assert\Regex && null !== ($pattern = $constraint->getHtmlPattern())) {
                $schema->pattern = Generator::UNDEFINED !== $schema->pattern
                    ? sprintf('%s, %s', $schema->pattern, $pattern)
                    : $pattern;
            }
        }
    }

    private function toNumeric(
        mixed $value
    ): int|float|null {
        if (is_int($value) || is_float($value)) {
            return $value;
        }

        if (is_string($value) && is_numeric($value)) {
            return str_contains($value, '.') ? (float)$value : (int)$value;
        }

        return null;
    }
}
