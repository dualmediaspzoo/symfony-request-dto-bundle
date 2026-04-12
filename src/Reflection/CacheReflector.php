<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Reflection;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use DualMedia\DtoRequestBundle\Metadata\Model\MainDto;
use Symfony\Component\Cache\Adapter\PhpFilesAdapter;

class CacheReflector
{
    public function __construct(
        private readonly PhpFilesAdapter $cache,
        private readonly Reflector $reflector
    ) {
    }

    /**
     * @param class-string<AbstractDto> $class
     *
     * @return MainDto|null if class not found in cache
     */
    public function get(
        string $class
    ): MainDto|null {
        assert(is_subclass_of($class, AbstractDto::class), 'Items passed to CacheReflector must be instances of AbstractDto');
        $item = $this->cache->getItem($class);

        if (!$item->isHit()) {
            return null;
        }

        $value = $item->get();
        assert($value instanceof MainDto, 'Items loaded from this instance of cache must be instances of MainDto');

        // load any runtime needed logic

        return $value;
    }

    /**
     * @param class-string<AbstractDto> $class
     */
    public function save(
        string $class
    ): bool {
        $cacheItem = $this->cache->getItem($class);
        $cacheItem->set($this->reflector->reflect($class));
        $this->cache->save($cacheItem);

        return true;
    }
}
