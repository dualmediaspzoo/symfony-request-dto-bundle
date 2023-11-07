<?php

namespace DualMedia\DtoRequestBundle\Tests\Model;

/**
 * Helper callable to allow saving parameters for callables.
 */
class BoundCallable
{
    /**
     * @var callable
     */
    private $callable;
    private array $args;

    public function __construct(
        callable $callable,
        ...$args
    ) {
        $this->callable = $callable;
        $this->args = $args;
    }

    public function __invoke(
        ...$args
    ) {
        return call_user_func_array($this->callable, array_merge($this->args, $args));
    }

    public function params(
        ...$args
    ): self {
        $this->args = array_merge($this->args, $args);

        return $this;
    }

    public function overwrite(
        ...$args
    ): self {
        $this->args = $args;

        return $this;
    }

    public function set(
        array $args
    ): self {
        $this->args = $args;

        return $this;
    }
}
