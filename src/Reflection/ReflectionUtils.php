<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Reflection;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ServiceLocator;

final class ReflectionUtils
{
    private function __construct()
    {
    }

    /**
     * Resolves the service id that should be fetched from a service locator
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
                'Closure declared at %s:%d must have at least one parameter.',
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
            'Cannot resolve service id for closure declared at %s:%d: '
            .'first parameter must have a non-builtin class type hint or a #[Autowire(service: ...)] attribute.',
            $reflection->getFileName() ?: '<unknown>',
            $reflection->getStartLine()
        ));
    }

    /**
     * Resolves the service id from the closure and asserts the id is present in the given locator.
     *
     * @param ServiceLocator<object> $locator
     * @param string $attributeLabel human-readable attribute name (e.g. "#[ValidateWithGroups]")
     * @param string $contextLabel human-readable location (e.g. class name, "Class::property")
     * @param string $tagDescription what the missing service should have been (e.g. "a group provider")
     *
     * @throws \LogicException
     */
    public static function resolveAndValidateServiceId(
        \Closure $closure,
        ServiceLocator $locator,
        string $attributeLabel,
        string $contextLabel,
        string $tagDescription
    ): string {
        try {
            $serviceId = self::resolveServiceId($closure);
        } catch (\LogicException $e) {
            throw new \LogicException(sprintf(
                'Invalid %s closure on %s: %s',
                $attributeLabel,
                $contextLabel,
                $e->getMessage()
            ), previous: $e);
        }

        if (!$locator->has($serviceId)) {
            throw new \LogicException(sprintf(
                '%s on %s references service "%s" which is not tagged as %s.',
                $attributeLabel,
                $contextLabel,
                $serviceId,
                $tagDescription
            ));
        }

        return $serviceId;
    }

    /**
     * Extracts the descriptive text (everything before the first @tag) from a PHPDoc block.
     * Paragraph breaks are preserved as double newlines.
     */
    public static function extractShortDescription(
        string|false $docComment
    ): string|null {
        if (false === $docComment) {
            return null;
        }

        $stripped = preg_replace('#^\s*/\*+|\*+/\s*$#', '', $docComment) ?? '';
        $lines = preg_split('/\R/', $stripped) ?: [];

        $cleaned = [];

        foreach ($lines as $line) {
            $line = rtrim(preg_replace('/^\s*\*\s?/', '', $line) ?? '');

            if (str_starts_with(ltrim($line), '@')) {
                break;
            }

            $cleaned[] = $line;
        }

        $text = trim(implode("\n", $cleaned));

        return '' === $text ? null : $text;
    }
}
