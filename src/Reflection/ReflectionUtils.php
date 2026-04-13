<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Reflection;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Reference;

final class ReflectionUtils
{
    private function __construct()
    {
    }

    /**
     * Resolves the service id that should be fetched from the group-provider locator
     * for the first parameter of the given closure.
     *
     * Priority:
     *   1. #[Autowire(service: '...')] on the parameter
     *   2. Non-builtin named type hint (class/interface FQCN)
     *
     * @throws \LogicException if neither can be resolved
     */
    public static function resolveServiceId(
        \Closure $closure
    ): string {
        $reflection = new \ReflectionFunction($closure);
        $parameters = $reflection->getParameters();

        if ([] === $parameters) {
            throw new \LogicException(sprintf(
                'Closure declared at %s:%d for ValidateWithGroups must have at least one parameter.',
                $reflection->getFileName() ?: '<unknown>',
                $reflection->getStartLine()
            ));
        }

        $first = $parameters[0];

        foreach ($first->getAttributes(Autowire::class) as $attr) {
            /** @var Autowire $autowire */
            $autowire = $attr->newInstance();

            if ($autowire->value instanceof Reference) {
                return (string)$autowire->value;
            }
        }

        $type = $first->getType();

        if ($type instanceof \ReflectionNamedType && !$type->isBuiltin()) {
            return $type->getName();
        }

        throw new \LogicException(sprintf(
            'Cannot resolve group provider service id for closure declared at %s:%d: '
            .'first parameter must have a non-builtin class type hint or a #[Autowire(service: ...)] attribute.',
            $reflection->getFileName() ?: '<unknown>',
            $reflection->getStartLine()
        ));
    }
}
