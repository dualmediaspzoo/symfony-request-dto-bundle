<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\OpenApi;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use Nelmio\ApiDocBundle\OpenApiPhp\Util as OAUtil;
use Nelmio\ApiDocBundle\RouteDescriber\RouteDescriberInterface;
use OpenApi\Annotations as OA;
use OpenApi\Context;
use Symfony\Component\Routing\Route;

class DtoRouteDescriber implements RouteDescriberInterface
{
    public function __construct(
        private readonly FieldCollector $collector,
        private readonly SchemaBuilder $builder
    ) {
    }

    #[\Override]
    public function describe(
        OA\OpenApi $api,
        Route $route,
        \ReflectionMethod $reflectionMethod
    ): void {
        $dtoClasses = $this->findDtoArguments($reflectionMethod);

        if (empty($dtoClasses)) {
            return;
        }

        $path = OAUtil::getPath($api, $route->getPath());
        $methods = $this->resolveMethods($route);

        foreach ($methods as $method) {
            $operation = OAUtil::getOperation($path, $method);
            $context = $this->buildContext($path, $reflectionMethod);

            foreach ($dtoClasses as $class) {
                $described = $this->collector->collect($class);

                if (null === $described) {
                    continue;
                }

                $operation->parameters = [
                    ...$this->existingParameters($operation->parameters),
                    ...$this->builder->buildParameters($described, $route->getPath(), $context),
                ];

                $body = $this->builder->buildRequestBody($described, $context);

                if (null !== $body && !$this->hasRequestBody($operation->requestBody)) {
                    $operation->requestBody = $body;
                }

                $existingResponses = $this->existingResponses($operation->responses);

                foreach ($this->builder->buildResponses($described, $context) as $response) {
                    if ($this->hasStatus($existingResponses, (string)$response->response)) {
                        continue;
                    }

                    $existingResponses[] = $response;
                }

                $operation->responses = $existingResponses;
            }
        }
    }

    /**
     * @return list<class-string<AbstractDto>>
     */
    private function findDtoArguments(
        \ReflectionMethod $method
    ): array {
        $classes = [];

        foreach ($method->getParameters() as $parameter) {
            $type = $parameter->getType();

            if (!$type instanceof \ReflectionNamedType || $type->isBuiltin()) {
                continue;
            }

            $name = $type->getName();

            if (!is_subclass_of($name, AbstractDto::class)) {
                continue;
            }

            /** @var class-string<AbstractDto> $name */
            $classes[] = $name;
        }

        return $classes;
    }

    /**
     * @return list<string>
     */
    private function resolveMethods(
        Route $route
    ): array {
        $methods = array_map('strtolower', $route->getMethods());

        if ([] === $methods) {
            return OAUtil::OPERATIONS;
        }

        return array_values(array_intersect($methods, OAUtil::OPERATIONS));
    }

    /**
     * @param array<int, OA\Response> $responses
     */
    private function hasStatus(
        array $responses,
        string $status
    ): bool {
        foreach ($responses as $existing) {
            if ((string)$existing->response === $status) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return list<OA\Parameter>
     */
    private function existingParameters(
        mixed $value
    ): array {
        if (!is_array($value)) {
            return [];
        }

        $out = [];

        foreach ($value as $item) {
            if ($item instanceof OA\Parameter) {
                $out[] = $item;
            }
        }

        return $out;
    }

    /**
     * @return list<OA\Response>
     */
    private function existingResponses(
        mixed $value
    ): array {
        if (!is_array($value)) {
            return [];
        }

        $out = [];

        foreach ($value as $item) {
            if ($item instanceof OA\Response) {
                $out[] = $item;
            }
        }

        return $out;
    }

    private function hasRequestBody(
        mixed $value
    ): bool {
        return $value instanceof OA\RequestBody;
    }

    private function buildContext(
        OA\PathItem $path,
        \ReflectionMethod $method
    ): Context {
        $context = OAUtil::createContext(['nested' => $path], $path->_context);
        $context->namespace = $method->getDeclaringClass()->getNamespaceName();
        $context->class = $method->getDeclaringClass()->getShortName();
        $context->method = $method->name;
        $file = $method->getFileName();

        if (false !== $file) {
            $context->filename = $file;
        }

        return $context;
    }
}
