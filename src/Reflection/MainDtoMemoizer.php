<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Reflection;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use DualMedia\DtoRequestBundle\Metadata\Model\MainDto;
use DualMedia\DtoRequestBundle\Reflection\Interface\MainDtoMemoizerInterface;
use Symfony\Contracts\Service\ResetInterface;

class MainDtoMemoizer implements MainDtoMemoizerInterface, ResetInterface
{
    /**
     * @var array<class-string<AbstractDto>, MainDto|null>
     */
    private array $memoized = [];

    public function __construct(
        private readonly CacheReflector $cacheReflector
    ) {
    }

    #[\Override]
    public function get(
        string $class
    ): MainDto|null {
        if (!array_key_exists($class, $this->memoized)) {
            $this->memoized[$class] = $this->cacheReflector->get($class);
        }

        return $this->memoized[$class];
    }

    #[\Override]
    public function reset(): void
    {
        $this->memoized = [];
    }
}
