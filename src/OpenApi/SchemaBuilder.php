<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\OpenApi;

use DualMedia\DtoRequestBundle\Metadata\Enum\BagEnum;
use DualMedia\DtoRequestBundle\Metadata\Model\Action;
use DualMedia\DtoRequestBundle\Metadata\Model\Format;
use DualMedia\DtoRequestBundle\MetadataUtils;
use DualMedia\DtoRequestBundle\OpenApi\Model\DescribedDto;
use DualMedia\DtoRequestBundle\OpenApi\Model\DescribedField;
use OpenApi\Annotations as OA;
use OpenApi\Context;
use OpenApi\Generator;
use Symfony\Component\Validator\Constraints as Assert;

class SchemaBuilder
{
    private const string FILE_EXAMPLE_BASE64 = 'data:image/jpeg;base64,aHR0cHM6Ly93d3cueW91dHViZS5jb20vd2F0Y2g/dj1kUXc0dzlXZ1hjUQ==';

    private const array PARAMETER_BAG_MAP = [
        'query' => 'query',
        'headers' => 'header',
        'cookies' => 'cookie',
        'attributes' => 'path',
    ];

    /**
     * @return list<OA\Parameter>
     */
    public function buildParameters(
        DescribedDto $dto,
        string $routePath,
        Context $context
    ): array {
        $out = [];

        foreach ($dto->fields as $field) {
            $in = self::PARAMETER_BAG_MAP[$field->bag->value] ?? null;

            if (null === $in) {
                continue;
            }

            if ('path' === $in && !str_contains($routePath, '{'.$field->path.'}')) {
                continue;
            }

            $out[] = $this->buildParameter($field, $in, $context);
        }

        return $out;
    }

    public function buildRequestBody(
        DescribedDto $dto,
        Context $context
    ): OA\RequestBody|null {
        $bodyFields = array_values(array_filter(
            $dto->fields,
            static fn (DescribedField $f): bool => BagEnum::Request === $f->bag || BagEnum::Files === $f->bag
        ));

        if ([] === $bodyFields) {
            return null;
        }

        $schema = new OA\Schema([
            'type' => 'object',
            'properties' => $this->buildPropertyList($bodyFields, $context),
            'required' => $this->requiredNames($bodyFields),
            '_context' => $context,
        ]);

        return new OA\RequestBody([
            'content' => [
                new OA\MediaType([
                    'mediaType' => 'application/json',
                    'schema' => $schema,
                    '_context' => $context,
                ]),
            ],
            '_context' => $context,
        ]);
    }

    /**
     * @return list<OA\Response>
     */
    public function buildResponses(
        DescribedDto $dto,
        Context $context
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
            $out[] = new OA\Response([
                'response' => (string)$action->statusCode,
                'description' => $action->message ?? 'No description set',
                '_context' => $context,
            ]);
        }

        return $out;
    }

    private function buildParameter(
        DescribedField $field,
        string $in,
        Context $context
    ): OA\Parameter {
        $schema = new OA\Schema([
            'type' => $field->isCollection ? 'array' : $field->oaType,
            '_context' => $context,
        ]);

        if ($field->isCollection) {
            $schema->items = new OA\Items([
                'type' => $field->oaType,
                '_context' => $context,
            ]);
            $this->applyConstraints($schema->items, $field);
        } else {
            $this->applyConstraints($schema, $field);
        }

        return new OA\Parameter([
            'name' => $field->path.('query' === $in && $field->isCollection ? '[]' : ''),
            'in' => $in,
            'required' => $field->required || 'path' === $in,
            'schema' => $schema,
            '_context' => $context,
        ]);
    }

    /**
     * @param list<DescribedField> $fields
     *
     * @return list<OA\Property>
     */
    private function buildPropertyList(
        array $fields,
        Context $context
    ): array {
        $out = [];

        foreach ($fields as $field) {
            $out[] = $this->buildProperty($field, $context);
        }

        return $out;
    }

    private function buildProperty(
        DescribedField $field,
        Context $context
    ): OA\Property {
        $property = new OA\Property([
            'property' => $field->path,
            '_context' => $context,
        ]);

        if ('object' === $field->oaType) {
            if ($field->isCollection) {
                $property->type = 'array';
                $property->items = new OA\Items([
                    'type' => 'object',
                    'properties' => $this->buildPropertyList($field->children, $context),
                    'required' => $this->requiredNames($field->children),
                    '_context' => $context,
                ]);
            } else {
                $property->type = 'object';
                $property->properties = $this->buildPropertyList($field->children, $context);
                $property->required = $this->requiredNames($field->children);
            }

            return $property;
        }

        if ($field->isCollection) {
            $property->type = 'array';
            $property->items = new OA\Items([
                'type' => $field->oaType,
                '_context' => $context,
            ]);
            $this->applyConstraints($property->items, $field);
        } else {
            $property->type = $field->oaType;
            $this->applyConstraints($property, $field);
        }

        if (BagEnum::Files === $field->bag) {
            $suffix = 'This field is a file and can be passed as a http upload by using the same path, '
                .'or by encoding as base64 in the body';
            $property->description = $suffix;
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
     * Based on the old DtoOADescriber::applySchemaConstraints().
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
