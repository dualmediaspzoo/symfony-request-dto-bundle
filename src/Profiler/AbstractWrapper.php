<?php

namespace DualMedia\DtoRequestBundle\Profiler;

use Symfony\Component\Stopwatch\Stopwatch;

/**
 * @template T
 */
abstract class AbstractWrapper
{
    private int $counter = 0;

    public function __construct(
        private readonly Stopwatch|null $stopwatch = null
    ) {
    }

    /**
     * @param string $name
     * @param callable $fn
     *
     * @return T
     *
     * @throws \Throwable
     */
    public function wrap(
        string $name,
        callable $fn
    ): mixed {
        if (null === $this->stopwatch) {
            return $fn();
        }

        $name = sprintf($name, $this->counter++);
        $this->stopwatch->start($name);

        try {
            $resolved = $fn();
        } catch (\Throwable $e) {
            $this->stopwatch->stop($name);

            throw $e;
        }

        $this->stopwatch->stop($name);

        return $resolved;
    }
}
