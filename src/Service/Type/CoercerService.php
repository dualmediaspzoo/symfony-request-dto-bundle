<?php

namespace DualMedia\DtoRequestBundle\Service\Type;

use DualMedia\DtoRequestBundle\Interfaces\Type\CoercerInterface;
use DualMedia\DtoRequestBundle\Interfaces\Type\CoercionServiceInterface;
use DualMedia\DtoRequestBundle\Model\Type\CoerceResult;
use DualMedia\DtoRequestBundle\Model\Type\Property;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @template T
 * @implements CoercionServiceInterface<T>
 */
class CoercerService implements CoercionServiceInterface
{
    /**
     * @var list<CoercerInterface<T>>
     */
    private array $coercers;

    /**
     * @param \IteratorAggregate<array-key, CoercerInterface> $iterator
     * @param ValidatorInterface $validator
     *
     * @noinspection PhpDocMissingThrowsInspection
     */
    public function __construct(
        \IteratorAggregate $iterator,
        private readonly ValidatorInterface $validator
    ) {
        $this->coercers = iterator_to_array($iterator->getIterator());
    }

    /**
     * {@inheritdoc}
     */
    public function supports(
        Property $property
    ): bool {
        foreach ($this->coercers as $coercer) {
            if ($coercer->supports($property)) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     *
     * @psalm-suppress InvalidReturnType
     * @psalm-suppress InvalidReturnStatement
     */
    public function coerce(
        string $propertyPath,
        Property $property,
        $value
    ): ?CoerceResult {
        if (null === $value && !empty($property->getConstraints())) {
            $violations = $this->validator->startContext()
                ->atPath($propertyPath)
                ->validate($value, $property->getConstraints())
                ->getViolations();

            if (0 !== $violations->count()) {
                return new CoerceResult( // @phpstan-ignore-line
                    null,
                    $violations
                );
            }
        } elseif ($property->isCollection()) {
            $violations = $this->validator->startContext()
                ->atPath($propertyPath)
                ->validate($value, new Assert\Type(['type' => 'array']))
                ->getViolations();

            if (0 !== $violations->count()) {
                return new CoerceResult( // @phpstan-ignore-line
                    [],
                    $violations
                );
            }
        }

        foreach ($this->coercers as $coercer) {
            if ($coercer->supports($property)) {
                return $coercer->coerce($propertyPath, $property, $value);
            }
        }

        return null;
    }
}
