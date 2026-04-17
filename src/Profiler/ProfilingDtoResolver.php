<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Profiler;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use DualMedia\DtoRequestBundle\Metadata\Enum\BagEnum;
use DualMedia\DtoRequestBundle\Metadata\Model\MainDto;
use DualMedia\DtoRequestBundle\Profiler\DataCollector\DtoDataCollector;
use DualMedia\DtoRequestBundle\Provider\Interface\GroupProviderInterface;
use DualMedia\DtoRequestBundle\Reflection\Interface\MainDtoMemoizerInterface;
use DualMedia\DtoRequestBundle\Resolve\DtoResolver;
use DualMedia\DtoRequestBundle\Resolve\Interface\ExtractorInterface;
use DualMedia\DtoRequestBundle\Resolve\ViolationMapper;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProfilingDtoResolver extends DtoResolver
{
    private const string CATEGORY = 'dm_dto';

    private float $phase1Ms = 0.0;
    private float $phase2Ms = 0.0;
    private float $phase3Ms = 0.0;
    private float $phase4Ms = 0.0;
    private int $phase4ViolationStart = 0;

    /**
     * @var list<array{path: string, phase: int, messages: list<string>}>
     */
    private array $phase2FailuresSnapshot = [];

    /**
     * @param ServiceLocator<GroupProviderInterface> $groupProviderLocator
     */
    public function __construct(
        ExtractorInterface $extractor,
        MainDtoMemoizerInterface $memoizer,
        ValidatorInterface $validator,
        ServiceLocator $groupProviderLocator,
        ViolationMapper $violationMapper,
        private readonly Stopwatch $stopwatch,
        private readonly DtoDataCollector $collector
    ) {
        parent::__construct($extractor, $memoizer, $validator, $groupProviderLocator, $violationMapper);
    }

    #[\Override]
    public function resolve(
        string $class,
        Request $request,
        BagEnum $defaultBag = BagEnum::Request
    ): AbstractDto {
        $short = false !== ($pos = strrpos($class, '\\')) ? substr($class, $pos + 1) : $class;

        $this->phase1Ms = 0.0;
        $this->phase2Ms = 0.0;
        $this->phase3Ms = 0.0;
        $this->phase4Ms = 0.0;
        $this->phase2FailuresSnapshot = [];

        $event = $this->stopwatch->start('dto.resolve.'.$short, self::CATEGORY);
        $dto = parent::resolve($class, $request, $defaultBag);
        $totalMs = (float)$event->stop()->getDuration();

        $list = $dto->getConstraintViolationList();
        $phase4Violations = [];

        for ($i = $this->phase4ViolationStart, $total = $list->count(); $i < $total; ++$i) {
            $v = $list->get($i);
            $constraint = $v->getConstraint();
            $phase4Violations[] = [
                'path' => $v->getPropertyPath(),
                'message' => (string)$v->getMessage(),
                'constraint' => null !== $constraint ? $constraint::class : null,
            ];
        }

        $this->collector->addResolverRow([
            'class' => $class,
            'total_ms' => $totalMs,
            'phase1_ms' => $this->phase1Ms,
            'phase2_ms' => $this->phase2Ms,
            'phase3_ms' => $this->phase3Ms,
            'phase4_ms' => $this->phase4Ms,
            'violations' => $list->count(),
            'phase2_failures' => $this->phase2FailuresSnapshot,
            'phase4_violations' => $phase4Violations,
        ]);

        return $dto;
    }

    #[\Override]
    protected function extractPhase(
        MainDto $mainDto,
        AbstractDto $dto,
        Request $request,
        BagEnum $defaultBag,
        array &$pending
    ): void {
        $event = $this->stopwatch->start('dto.resolve.phase1_extract', self::CATEGORY);
        parent::extractPhase($mainDto, $dto, $request, $defaultBag, $pending);
        $this->phase1Ms = (float)$event->stop()->getDuration();
    }

    #[\Override]
    protected function coerceValidatePhase(
        array $pending,
        AbstractDto $dto,
        array &$violated,
        array &$finalValues,
        array &$phase2Failures
    ): void {
        $event = $this->stopwatch->start('dto.resolve.phase2_coerce_validate', self::CATEGORY);
        parent::coerceValidatePhase($pending, $dto, $violated, $finalValues, $phase2Failures);
        $this->phase2Ms = (float)$event->stop()->getDuration();
        $this->phase2FailuresSnapshot = $phase2Failures;
    }

    #[\Override]
    protected function assignPhase(
        array $pending,
        array $violated,
        array $finalValues
    ): void {
        $event = $this->stopwatch->start('dto.resolve.phase3_assign', self::CATEGORY);
        parent::assignPhase($pending, $violated, $finalValues);
        $this->phase3Ms = (float)$event->stop()->getDuration();
    }

    #[\Override]
    protected function finalValidatePhase(
        AbstractDto $dto,
        MainDto $mainDto,
        Request $request
    ): void {
        $this->phase4ViolationStart = $dto->getConstraintViolationList()->count();
        $event = $this->stopwatch->start('dto.resolve.phase4_final_validate', self::CATEGORY);
        parent::finalValidatePhase($dto, $mainDto, $request);
        $this->phase4Ms = (float)$event->stop()->getDuration();
    }
}
