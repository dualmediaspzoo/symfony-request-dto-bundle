<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Resolve;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use DualMedia\DtoRequestBundle\Metadata\Enum\BagEnum;
use DualMedia\DtoRequestBundle\Resolve\Model\PendingValue;
use Symfony\Component\HttpFoundation\Request;
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
        /** @var list<PendingValue> $pending */
        $pending = [];

        // phase 1: recursively extract and coerce all values across the tree
        $accessor = new BagAccessor($request);
        $this->extractor->extract($dto, $accessor, $defaultBag, [], $pending);

        // phase 2: coerce and validate in sequenced phases per property
        $violated = [];
        $finalValues = [];

        foreach ($pending as $i => $entry) {
            $value = $entry->value;

            foreach ($entry->phases as [$coerce, $phaseConstraints]) {
                $value = $coerce($value);

                $context = $this->validator->startContext();

                $context->atPath($entry->validationPath)
                    ->validate($value, $phaseConstraints);

                $phaseViolations = $context->getViolations();

                if ($phaseViolations->count() > 0) {
                    $violated[$entry->validationPath] = true;

                    foreach ($phaseViolations as $violation) {
                        $dto->addConstraintViolation($violation);
                    }

                    break;
                }
            }

            if (!isset($violated[$entry->validationPath])) {
                $finalValues[$i] = $value;
            }
        }

        // phase 3: set valid values
        foreach ($pending as $i => $entry) {
            if (isset($violated[$entry->validationPath])) {
                continue;
            }

            $entry->dto->{$entry->name} = $finalValues[$i];
        }

        // phase 4: validate the main object
        $violations = $this->validator->startContext()
            ->validate($dto)
            ->getViolations();

        // todo: fix violation paths
        foreach ($violations as $violation) {
            $dto->addConstraintViolation($violation);
        }

        return $dto;
    }
}
