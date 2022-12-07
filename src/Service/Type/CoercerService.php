<?php

namespace DM\DtoRequestBundle\Service\Type;

use DM\DtoRequestBundle\Interfaces\Type\CoercerInterface;
use DM\DtoRequestBundle\Interfaces\Type\CoercionServiceInterface;
use DM\DtoRequestBundle\Model\Type\CoerceResult;
use DM\DtoRequestBundle\Model\Type\Property;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CoercerService implements CoercionServiceInterface
{
    /**
     * @var CoercerInterface[]
     */
    private array $coercers;
    private ValidatorInterface $validator;

    /**
     * @param \IteratorAggregate<array-key, CoercerInterface> $iterator
     * @param ValidatorInterface $validator
     *
     * @noinspection PhpDocMissingThrowsInspection
     */
    public function __construct(
        \IteratorAggregate $iterator,
        ValidatorInterface $validator
    ) {
        $this->coercers = iterator_to_array($iterator->getIterator());
        $this->validator = $validator;
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
                return new CoerceResult(
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
                return new CoerceResult(
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
