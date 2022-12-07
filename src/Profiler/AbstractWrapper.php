<?php

namespace DM\DtoRequestBundle\Profiler;

use Symfony\Component\Stopwatch\Stopwatch;

abstract class AbstractWrapper
{
    private ?Stopwatch $stopwatch;

    private int $counter = 0;

    public function __construct(
        ?Stopwatch $stopwatch = null
    ) {
        $this->stopwatch = $stopwatch;
    }

    /**
     * @param string $name
     * @param callable $fn
     *
     * @return mixed
     * @throws \Throwable
     */
    public function wrap(
        string $name,
        callable $fn
    ) {
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
