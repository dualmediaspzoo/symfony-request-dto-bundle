<?php

namespace DM\DtoRequestBundle\Profiler\Service\Resolver;

use DM\DtoRequestBundle\Attributes\Dto\Bag;
use DM\DtoRequestBundle\Interfaces\Resolver\DtoTypeExtractorInterface;
use DM\DtoRequestBundle\Model\Type\Dto;
use DM\DtoRequestBundle\Profiler\AbstractWrapper;
use Symfony\Component\Stopwatch\Stopwatch;

class ProfilingDtoTypeExtractorService extends AbstractWrapper implements DtoTypeExtractorInterface
{
    private DtoTypeExtractorInterface $dtoTypeExtractor;

    public function __construct(
        DtoTypeExtractorInterface $dtoTypeExtractor,
        ?Stopwatch $stopwatch = null
    ) {
        $this->dtoTypeExtractor = $dtoTypeExtractor;
        parent::__construct($stopwatch);
    }

    public function extract(
        \ReflectionClass $class,
        ?Bag $root = null
    ): Dto {
        return $this->wrap(
            'extract:%d:'.$class->getName(),
            fn () => $this->dtoTypeExtractor->extract($class, $root)
        );
    }
}
