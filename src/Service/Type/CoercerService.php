<?php

namespace DualMedia\DtoRequestBundle\Service\Type;

use DualMedia\DtoRequestBundle\Interface\Type\CoercerInterface;
use DualMedia\DtoRequestBundle\Interface\Type\CoercionServiceInterface;
use DualMedia\DtoRequestBundle\Model\Type\CoerceResult;
use DualMedia\DtoRequestBundle\Model\Type\Property;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @implements CoercionServiceInterface<mixed>
 */
class CoercerService implements CoercionServiceInterface
{
    /**
     * @var list<CoercerInterface<mixed>>
     */
    private array $coercers;

    /**
     * @param \IteratorAggregate<array-key, CoercerInterface> $iterator
     *
     * @noinspection PhpDocMissingThrowsInspection
     */
    public function __construct(
        \IteratorAggregate $iterator,
        private readonly ValidatorInterface $validator
    ) {
        $this->coercers = iterator_to_array($iterator->getIterator());
    }

    #[\Override]
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

    #[\Override]
    public function coerce(
        string $propertyPath,
        Property $property,
        mixed $value
    ): CoerceResult|null {
        if ($property->isCollection()) {
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
