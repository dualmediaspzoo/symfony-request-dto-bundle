<?php

namespace DM\DtoRequestBundle\Exception\DependencyInjection\Entity;

class AnnotationMissingException extends \Exception
{
    public function __construct(
        private readonly string $class,
        string $message = "",
        int $code = 0,
        \Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function getClass(): string
    {
        return $this->class;
    }
}
