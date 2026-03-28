<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Type;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use DualMedia\DtoRequestBundle\Reflection\CacheReflector;
use DualMedia\DtoRequestBundle\Reflection\Reflector;
use Symfony\Component\Cache\Adapter\PhpFilesAdapter;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

class DtoCacheWarmer implements CacheWarmerInterface
{
    /**
     * @param list<class-string<AbstractDto>> $dtoClassList
     */
    public function __construct(
        private readonly array $dtoClassList,
        private readonly CacheReflector $cacheReflector
    ) {
    }

    public function isOptional(): bool
    {
        return false;
    }

    public function warmUp(
        string $cacheDir,
        string|null $buildDir = null
    ): array {
        foreach ($this->dtoClassList as $class) {
            $this->cacheReflector->save($class);
        }

        return $this->dtoClassList;
    }
}
