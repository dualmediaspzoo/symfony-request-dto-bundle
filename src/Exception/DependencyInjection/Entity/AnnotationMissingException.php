<?php

namespace DM\DtoRequestBundle\Exception\DependencyInjection\Entity;

class AnnotationMissingException extends \Exception
{
    private string $class;

    public function __construct(
        string $class,
        string $message = "",
        int $code = 0,
        \Throwable $previous = null
    ) {
        $this->class = $class;

        parent::__construct($message, $code, $previous);
    }

    public function getClass(): string
    {
        return $this->class;
    }
}
