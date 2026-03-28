<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Resolve;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use DualMedia\DtoRequestBundle\Metadata\Enum\BagEnum;
use DualMedia\DtoRequestBundle\Metadata\Model\Dto;
use DualMedia\DtoRequestBundle\Reflection\CacheReflector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class DtoResolver
{
    public function __construct(
        private readonly PropertyResolver $propertyResolver,
        private readonly CacheReflector $cacheReflector,
        private readonly ValidatorInterface $validator
    ) {
    }

    /**
     * @param class-string<T> $class
     * @param list<string> $prefix path segments from parent DTOs
     *
     * @return T
     *
     * @template T of AbstractDto
     */
    public function resolve(
        string $class,
        Request $request,
        BagEnum $defaultBag = BagEnum::Request,
        array $prefix = []
    ): AbstractDto {
        $dto = new $class();
        $metadata = $this->cacheReflector->get($class) ?? [];

        // phase 1: extract and coerce all properties
        /** @var array<string, mixed> $values coerced values keyed by property name */
        $values = [];
        /** @var array<string, list<Constraint>> $typeConstraints coercer constraints keyed by property name */
        $typeConstraints = [];

        foreach ($metadata as $name => $meta) {
            if ($meta instanceof Dto) {
                // todo: recursive DTO resolution (pass [...$prefix, $name])
                continue;
            }

            $result = $this->propertyResolver->resolve($meta, $request, $defaultBag, $prefix);

            if (null === $result) {
                continue;
            }

            $dto->visit($name);
            $values[$name] = $result->value;
            $typeConstraints[$name] = $result->constraints;
        }

        // phase 2: validate type constraints in one pass
        $context = $this->validator->startContext();

        foreach ($typeConstraints as $name => $constraints) {
            $context->atPath($name)
                ->validate($values[$name], $constraints);
        }

        $violations = $context->getViolations();

        // collect violated property names
        $violated = [];

        for ($i = 0; $i < $violations->count(); ++$i) {
            $path = $violations->get($i)->getPropertyPath();
            $violated[$path] = true;
            $dto->addConstraintViolation($violations->get($i));
        }

        // phase 3: set values that passed type checks
        foreach ($values as $name => $value) {
            if (isset($violated[$name])) {
                continue;
            }

            $dto->{$name} = $value;
        }

        return $dto;
    }
}
