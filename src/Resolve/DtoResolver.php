<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Resolve;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use DualMedia\DtoRequestBundle\Metadata\Enum\BagEnum;
use DualMedia\DtoRequestBundle\Metadata\Model\MainDto;
use DualMedia\DtoRequestBundle\Metadata\Model\ValidateWithGroups;
use DualMedia\DtoRequestBundle\MetadataUtils;
use DualMedia\DtoRequestBundle\Provider\Interface\GroupProviderInterface;
use DualMedia\DtoRequestBundle\Reflection\Interface\MainDtoMemoizerInterface;
use DualMedia\DtoRequestBundle\Resolve\Interface\DtoResolverInterface;
use DualMedia\DtoRequestBundle\Resolve\Interface\ExtractorInterface;
use DualMedia\DtoRequestBundle\Resolve\Model\PendingEntityValue;
use DualMedia\DtoRequestBundle\Resolve\Model\PendingValue;
use DualMedia\DtoRequestBundle\Util;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class DtoResolver implements DtoResolverInterface
{
    /**
     * @param ServiceLocator<GroupProviderInterface> $groupProviderLocator
     */
    public function __construct(
        private readonly ExtractorInterface $extractor,
        protected readonly MainDtoMemoizerInterface $memoizer,
        private readonly ValidatorInterface $validator,
        private readonly ServiceLocator $groupProviderLocator,
        private readonly ViolationMapper $violationMapper
    ) {
    }

    #[\Override]
    public function resolve(
        string $class,
        Request $request,
        BagEnum $defaultBag = BagEnum::Request
    ): AbstractDto {
        $dto = new $class();
        $mainDto = $this->memoizer->get($class);

        if (null === $mainDto) {
            return $dto;
        }

        /** @var list<PendingValue|PendingEntityValue> $pending */
        $pending = [];

        $this->extractPhase($mainDto, $dto, $request, $mainDto->defaultBag ?? $defaultBag, $pending);

        $violated = [];
        $finalValues = [];
        $phase2Failures = [];

        $this->coerceValidatePhase($pending, $dto, $violated, $finalValues, $phase2Failures);

        $this->assignPhase($pending, $violated, $finalValues);

        $this->finalValidatePhase($dto, $mainDto, $request);

        return $dto;
    }

    /**
     * Phase 1: recursively extract and coerce all values across the tree.
     *
     * @param list<PendingValue|PendingEntityValue> $pending
     */
    protected function extractPhase(
        MainDto $mainDto,
        AbstractDto $dto,
        Request $request,
        BagEnum $defaultBag,
        array &$pending
    ): void {
        $accessor = new BagAccessor($request);
        $this->extractor->extract($mainDto, $dto, $accessor, $defaultBag, [], $pending);
    }

    /**
     * Phase 2: coerce and validate in sequenced phases per property.
     *
     * @param list<PendingValue|PendingEntityValue> $pending
     * @param array<string, true> $violated
     * @param array<int, mixed> $finalValues
     * @param list<array{path: string, phase: int, messages: list<string>}> $phase2Failures
     *     populated with per-property failure details (used by profiling decorator; unused otherwise)
     */
    protected function coerceValidatePhase(
        array $pending,
        AbstractDto $dto,
        array &$violated,
        array &$finalValues,
        array &$phase2Failures
    ): void {
        foreach ($pending as $i => $entry) {
            if ($entry instanceof PendingEntityValue) {
                $criteria = [];
                $entryViolated = false;

                foreach ($entry->fields as $target => $fieldPending) {
                    $value = $fieldPending->value;

                    foreach ($fieldPending->phases as $phaseIndex => [$coerce, $phaseConstraints]) {
                        $value = $coerce($value);

                        $context = $this->validator->startContext();

                        $context->atPath($fieldPending->validationPath)
                            ->validate($value, $phaseConstraints);

                        $phaseViolations = $context->getViolations();

                        if ($phaseViolations->count() > 0) {
                            $entryViolated = true;

                            $messages = [];

                            foreach ($phaseViolations as $violation) {
                                $messages[] = (string)$violation->getMessage();
                            }

                            $phase2Failures[] = [
                                'path' => $fieldPending->validationPath,
                                'phase' => $phaseIndex,
                                'messages' => $messages,
                            ];

                            foreach ($phaseViolations as $violation) {
                                $entry->dto->addConstraintViolation($violation);
                            }

                            break;
                        }
                    }

                    if ($entryViolated) {
                        break;
                    }

                    $criteria[$target] = $value;
                }

                if (!$entryViolated) {
                    $finalValues[$i] = ($entry->load)($criteria);
                } else {
                    $violated[$entry->validationPath] = true;
                }

                continue;
            }

            $value = $entry->value;

            foreach ($entry->phases as $phaseIndex => [$coerce, $phaseConstraints]) {
                $value = $coerce($value);

                $context = $this->validator->startContext();

                $context->atPath($entry->validationPath)
                    ->validate($value, $phaseConstraints);

                $phaseViolations = $context->getViolations();

                if ($phaseViolations->count() > 0) {
                    $violated[$entry->validationPath] = true;

                    $messages = [];

                    foreach ($phaseViolations as $violation) {
                        $messages[] = (string)$violation->getMessage();
                    }

                    $phase2Failures[] = [
                        'path' => $entry->validationPath,
                        'phase' => $phaseIndex,
                        'messages' => $messages,
                    ];

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
    }

    /**
     * Phase 3: set valid values.
     *
     * @param list<PendingValue|PendingEntityValue> $pending
     * @param array<string, true> $violated
     * @param array<int, mixed> $finalValues
     */
    protected function assignPhase(
        array $pending,
        array $violated,
        array $finalValues
    ): void {
        foreach ($pending as $i => $entry) {
            if (isset($violated[$entry->validationPath])) {
                continue;
            }

            $entry->dto->{$entry->name} = $finalValues[$i];
        }
    }

    /**
     * Phase 4: validate the main object.
     */
    protected function finalValidatePhase(
        AbstractDto $dto,
        MainDto $mainDto,
        Request $request
    ): void {
        $groups = null;

        if (null !== $mainDto->validationGroupsServiceId) {
            $vwg = MetadataUtils::single(ValidateWithGroups::class, $mainDto->meta);
            assert(null !== $vwg);

            $provider = $this->groupProviderLocator->get($mainDto->validationGroupsServiceId);
            $groups = Util::mergeDefaultGroup(($vwg->closure)($provider, $dto, $request));
        }

        $violations = $this->validator->startContext()
            ->validate($dto, null, $groups)
            ->getViolations();

        foreach ($violations as $violation) {
            $dto->addConstraintViolation(
                $this->violationMapper->remap($violation, $mainDto)
            );
        }
    }
}
