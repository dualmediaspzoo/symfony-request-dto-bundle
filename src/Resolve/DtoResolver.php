<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Resolve;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use DualMedia\DtoRequestBundle\Metadata\Enum\BagEnum;
use DualMedia\DtoRequestBundle\Resolve\Model\PendingValue;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class DtoResolver
{
    public function __construct(
        private readonly Extractor $extractor,
        private readonly ValidatorInterface $validator
    ) {
    }

    /**
     * @param class-string<T> $class
     *
     * @return T
     *
     * @template T of AbstractDto
     */
    public function resolve(
        string $class,
        Request $request,
        BagEnum $defaultBag = BagEnum::Request
    ): AbstractDto {
        $dto = new $class();
        $pending = [];

        // phase 1: recursively extract and coerce all values across the tree
        $this->extractor->extract($dto, $request, $defaultBag, [], $pending);

        // phase 2: validate all type constraints in one pass
        $context = $this->validator->startContext();

        foreach ($pending as $entry) {
            if ([] !== $entry->constraints) {
                $context->atPath($entry->validationPath)
                    ->validate($entry->value, $entry->constraints);
            }
        }

        $violations = $context->getViolations();

        $violated = [];

        for ($i = 0; $i < $violations->count(); ++$i) {
            $violation = $violations->get($i);
            $violated[$violation->getPropertyPath()] = true;
        }

        // phase 3: set valid values and add violations to their respective DTOs
        foreach ($pending as $entry) {
            if (isset($violated[$entry->validationPath])) {
                continue;
            }

            $entry->dto->{$entry->name} = $entry->value;
        }

        for ($i = 0; $i < $violations->count(); ++$i) {
            $this->addViolationToDto($violations->get($i)->getPropertyPath(), $pending, $violations->get($i));
        }

        return $dto;
    }

    /**
     * Finds the DTO that owns the violated path and adds the violation to it.
     *
     * @param list<PendingValue> $pending
     */
    private function addViolationToDto(
        string $violationPath,
        array $pending,
        ConstraintViolationInterface $violation
    ): void {
        foreach ($pending as $entry) {
            if ($entry->validationPath === $violationPath) {
                $entry->dto->addConstraintViolation($violation);

                return;
            }
        }
    }
}