<?php

namespace DM\DtoRequestBundle\Service\Nelmio;

use DM\DtoRequestBundle\Interfaces\Attribute\HttpActionInterface;
use DM\DtoRequestBundle\Interfaces\DtoInterface;
use DM\DtoRequestBundle\Interfaces\Resolver\DtoTypeExtractorInterface;
use DM\DtoRequestBundle\Model\Type\Dto as DtoTypeModel;
use DM\DtoRequestBundle\Model\Type\Property as PropertyTypeModel;
use DM\DtoRequestBundle\Util as DtoUtil;
use Nelmio\ApiDocBundle\OpenApiPhp\Util as OAUtil;
use Nelmio\ApiDocBundle\RouteDescriber\RouteDescriberInterface;
use OpenApi\Annotations\Items;
use OpenApi\Annotations\MediaType;
use OpenApi\Annotations\OpenApi;
use OpenApi\Annotations\Parameter;
use OpenApi\Annotations\PathItem;
use OpenApi\Annotations\Property;
use OpenApi\Annotations\RequestBody;
use OpenApi\Annotations\Response;
use OpenApi\Annotations\Schema;
use OpenApi\Context;
use OpenApi\Generator;
use Symfony\Component\Routing\Route;

class DtoOADescriber implements RouteDescriberInterface
{
    private const FILE_EXAMPLE_BASE64 = "data:image/jpeg;base64,aHR0cHM6Ly93d3cueW91dHViZS5jb20vd2F0Y2g/dj1kUXc0dzlXZ1hjUQ==";

    private const OA_BAG_MAP = [
        'attributes' => 'path',
        'headers' => 'header',
        'cookies' => 'cookie',
    ];

    /**
     * @var list<HttpActionInterface>
     */
    private array $httpActions = [];

    private DtoTypeExtractorInterface $typeExtractorHelper;

    public function __construct(
        DtoTypeExtractorInterface $typeExtractorHelper
    ) {
        $this->typeExtractorHelper = $typeExtractorHelper;
    }

    public function describe(
        OpenApi $api,
        Route $route,
        \ReflectionMethod $reflectionMethod
    ): void {
        $this->httpActions = [];

        if (empty($parameters = $this->getDtoParameters($reflectionMethod))) {
            return;
        }

        $this->describeDTO($parameters, $api, $route, $reflectionMethod);
    }

    /**
     * @param \ReflectionParameter[] $parameters
     * @param OpenApi $api
     * @param Route $route
     * @param \ReflectionMethod $reflectionMethod
     *
     * @return void
     */
    private function describeDTO(
        array $parameters,
        OpenApi $api,
        Route $route,
        \ReflectionMethod $reflectionMethod
    ): void {
        $bags = [];

        foreach ($parameters as $parameter) {
            $bags = DtoUtil::mergeRecursively($bags, $this->getClassBags(
                $this->typeExtractorHelper->extract($parameter->getClass())
            ));
        }

        $path = OAUtil::getPath($api, $route->getPath());

        foreach ($this->getSupportedHttpMethods($route) as $method) {
            $operation = OAUtil::getOperation($path, $method);
            $context = $this->getContext($path, $reflectionMethod);

            foreach (['query', 'headers', 'attributes', 'cookies'] as $type) {
                foreach ($this->getParametersFromBag($bags[$type] ?? []) as $key => $model) {
                    if ('attributes' === $type) {
                        // so this isn't exactly supported, but we generally should check if this item exists in the path
                        // if it does, we may show it, otherwise no
                        if (str_contains($path->path, '.') || !str_contains($path->path, '{'.$key.'}')) {
                            continue;
                        }
                    }

                    $schema = new Schema([
                        'type' => $model->isCollection() ? 'array' : $model->getOAType(),
                        '_context' => $context,
                    ]);

                    if ($model->isCollection()) {
                        $model->applyCollectionConstraints($schema);
                        $schema->items = new Items([
                            'type' => $model->getOAType(),
                        ]);
                        $model->applySchemaConstraints($schema->items);
                    } else {
                        $model->applySchemaConstraints($schema);
                    }

                    // @phpstan-ignore-next-line
                    if (Generator::UNDEFINED === $operation->parameters) {
                        $operation->parameters = [];
                    }

                    $operation->parameters[] = new Parameter([
                        'name' => explode('.', $key)[0],
                        'in' => self::OA_BAG_MAP[$type] ?? $type,
                        'schema' => $schema,
                        'description' => $model->getDescription() ?? Generator::UNDEFINED,
                        'required' => $model->isRequired(),
                        '_context' => $context,
                    ]);
                }
            }

            if (!empty($fields = DtoUtil::mergeRecursively($bags['request'] ?? [], $bags['files'] ?? []))) {
                $operation->requestBody = new RequestBody(['content' => [
                    new MediaType([
                        'mediaType' => 'application/json',
                        '_context' => $context,
                        'schema' => $this->resolveSchema($fields, $context),
                    ]),
                ], '_context' => $context]);
            }

            if (empty($this->httpActions)) {
                continue;
            }

            // @phpstan-ignore-next-line
            if (Generator::UNDEFINED === $operation->responses) {
                $operation->responses = [];
            }

            foreach ($this->httpActions as $action) {
                if ($this->hasHttpResponse($operation->responses, $action->getHttpStatusCode())) {
                    continue;
                }

                $operation->responses[] = new Response([
                    'response' => $action->getHttpStatusCode(),
                    'description' => $action->getDescription() ?? "No description set",
                    '_context' => $context,
                ]);
            }
        }
    }

    /**
     * @param Response[] $responses
     * @param int $statusCode
     *
     * @return bool
     */
    private function hasHttpResponse(
        array $responses,
        int $statusCode
    ): bool {
        foreach ($responses as $response) {
            if ($statusCode === (int)$response->response) {
                return true;
            }
        }

        return false;
    }

    /**
     * @phpstan-ignore-next-line
     *
     * @param array $fields
     * @param Context $context
     *
     * @return Schema
     */
    private function resolveSchema(
        array $fields,
        Context $context
    ): Schema {
        // special logic check
        if (array_key_exists('', $fields)) {
            return new Schema([
                'type' => 'array',
                'items' => new Items([
                    'properties' => $this->resolveProperties($fields['']),
                    'required' => $this->resolveRequiredProperties($fields['']),
                ]),
                '_context' => $context,
            ]);
        }

        return new Schema([
            'properties' => $this->resolveProperties($fields),
            'required' => $this->resolveRequiredProperties($fields),
            '_context' => $context,
        ]);
    }

    /**
     * @phpstan-ignore-next-line
     * @param array $fields
     *
     * @return Property[]
     */
    private function resolveProperties(
        array $fields
    ): array {
        $final = [];

        foreach ($fields as $key => $model) {
            $property = new Property([
                'property' => $key,
            ]);

            if (is_array($model)) {
                $property->properties = $this->resolveProperties($model);
                $property->type = 'object';
                $property->required = $this->resolveRequiredProperties($model);
            } else {
                $assignTo = $property;

                if ($model->isCollection()) {
                    $property->type = "array";

                    if ('array' === ($expected = $model->getOAType())) { // array-array?
                        $expected = 'string';
                    }

                    $property->items = new Items([
                        'type' => $expected,
                    ]);
                    $model->applyCollectionConstraints($property);
                    $assignTo = $property->items;
                } else {
                    $property->type = $model->getOAType();
                }
                $property->description = $model->getDescription() ?? Generator::UNDEFINED;

                // extra work for files
                if ('files' === $model->getBag()->bag) {
                    if (mb_strlen($property->description)) {
                        $property->description .= "<br>";
                    } else {
                        $property->description = "";
                    }
                    $property->description .= "This field is a file and can be passed as a http upload by using the same path,
                    or by encoding as base64 in the body";

                    $property->example = $model->isCollection() ? [self::FILE_EXAMPLE_BASE64] : self::FILE_EXAMPLE_BASE64;
                }

                $model->applySchemaConstraints($assignTo);
            }

            $final[] = $property;
        }

        return $final;
    }

    /**
     * @template T of array<string, PropertyTypeModel>
     *
     * @param array<string, PropertyTypeModel|T> $fields
     *
     * @return string[]
     */
    private function resolveRequiredProperties(
        array $fields
    ): array {
        $keys = [];

        foreach ($fields as $key => $model) {
            if (is_array($model) || !$model->isRequired()) {
                continue;
            }

            $keys[] = $key;
        }

        return $keys;
    }

    /**
     * Converts a model into fields and merges them into appropriate bags
     *
     * @param DtoTypeModel $dto
     * @param string|null $parentPath
     *
     * @return array<string, PropertyTypeModel|array<string, PropertyTypeModel>>
     */
    private function getClassBags(
        DtoTypeModel $dto,
        ?string $parentPath = null
    ): array {
        $bags = [];

        /** @var DtoTypeModel|PropertyTypeModel $item */
        foreach ($dto as $item) {
            $propertyPath = null !== $parentPath ?
                    $parentPath.'.'.$item->getRealPath() :
                    $item->getRealPath();

            if (null !== $item->getHttpAction()) {
                /** @psalm-suppress InvalidPropertyAssignmentValue */
                $this->httpActions[] = $item->getHttpAction();
            }

            if (null !== ($find = $item->getFindAnnotation())) {
                // only non-dynamic fields are visible here because of how the type extractor works
                /** @var PropertyTypeModel $field */
                foreach ($item as $key => $field) {
                    $fieldPath = null !== $parentPath ?
                        $parentPath.'.'.$find->getFields()[$key] :
                        $find->getFields()[$key];

                    $this->insertIntoBags($bags, $fieldPath, $field);
                }

                continue;
            }

            if ($item instanceof DtoTypeModel) {
                $bags = DtoUtil::mergeRecursively($bags, $this->getClassBags($item, $propertyPath));

                continue;
            }

            $this->insertIntoBags($bags, $propertyPath, $item);
        }

        return $bags;
    }

    /**
     * @param array<string, PropertyTypeModel|array<string, PropertyTypeModel>> $bag
     * @param string $previous
     *
     * @return array<string, PropertyTypeModel>
     */
    private function getParametersFromBag(
        array $bag,
        string $previous = ''
    ): array {
        $final = [];

        foreach ($bag as $key => $value) {
            if (is_array($value)) {
                $final = array_merge($final, $this->getParametersFromBag($value, $key));
            } else {
                $final[ltrim($previous.'.'.$key, '.')] = $value;
            }
        }

        return $final;
    }

    /**
     * @phpstan-ignore-next-line
     * @param array $bags
     * @param string $path
     * @param PropertyTypeModel $model
     */
    private function insertIntoBags(
        array &$bags,
        string $path,
        PropertyTypeModel $model
    ): void {
        if (!array_key_exists($model->getBag()->bag, $bags)) {
            $bags[$model->getBag()->bag] = [];
        }

        $selected = &$bags[$model->getBag()->bag];

        foreach (explode('.', $path) as $index) {
            if (!array_key_exists($index, $selected)) {
                $selected[$index] = [];
            }

            $selected = &$selected[$index]; // jump to next index
        }

        $selected = $model;
    }

    /**
     * @param PathItem $path
     * @param \ReflectionMethod $reflectionMethod
     *
     * @return Context
     *
     * @psalm-suppress UndefinedPropertyAssignment
     */
    private function getContext(
        PathItem $path,
        \ReflectionMethod $reflectionMethod
    ): Context {
        $context = OAUtil::createContext(['nested' => $path], $path->_context);
        $context->namespace = $reflectionMethod->getNamespaceName();
        $context->class = $reflectionMethod->getShortName();
        $context->method = $reflectionMethod->name;
        // @phpstan-ignore-next-line
        $context->filename = $reflectionMethod->getFileName();

        return $context;
    }

    /**
     * @param \ReflectionMethod $reflectionMethod
     *
     * @return \ReflectionParameter[]
     */
    private function getDtoParameters(
        \ReflectionMethod $reflectionMethod
    ): array {
        $parameters = [];

        foreach ($reflectionMethod->getParameters() as $parameter) {
            if (null === $parameter->getClass() ||
                DtoInterface::class === $parameter->getClass()->getName() ||
                !is_subclass_of($parameter->getClass()->getName(), DtoInterface::class)) {
                continue;
            }

            $parameters[] = $parameter;
        }

        return $parameters;
    }

    /**
     * @param Route $route
     *
     * @return string[]
     */
    private function getSupportedHttpMethods(
        Route $route
    ): array {
        $allMethods = OAUtil::OPERATIONS;
        $methods = array_map('strtolower', $route->getMethods());

        return array_intersect($methods ?: $allMethods, $allMethods);
    }
}