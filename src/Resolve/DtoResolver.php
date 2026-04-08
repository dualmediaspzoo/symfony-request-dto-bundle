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

        // phase 2: validate all type constraints in one pass
        $context = $this->validator->startContext();

        foreach ($pending as $entry) {
            if (empty($entry->constraints)) {
                continue;
            }

            $context->atPath($entry->validationPath)
                ->validate($entry->value, $entry->constraints);
        }

        $violations = $context->getViolations();

        $violated = [];

        for ($i = 0; $i < $violations->count(); ++$i) {
            $violation = $violations->get($i);
            $violated[$violation->getPropertyPath()] = true;
        }

        // phase 3: set valid values and add violations to their respective DTOs
        foreach ($pending as $entry) {
            if (!$entry->assignable || isset($violated[$entry->validationPath])) {
                continue;
            }

            $entry->dto->{$entry->name} = $entry->value;
        }

        foreach ($violations as $violation) {
            $dto->addConstraintViolation($violation);
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
