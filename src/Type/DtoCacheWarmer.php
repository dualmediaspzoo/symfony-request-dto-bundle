<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Type;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;
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
        private readonly Reflector $reflector,
        private readonly PhpFilesAdapter $cache
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
            $cacheItem = $this->cache->getItem($class);
            $cacheItem->set($this->reflector->reflect($class));
            $this->cache->save($cacheItem);
        }

        return $this->dtoClassList;
    }
}
