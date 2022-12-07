<?php

namespace DM\DtoRequestBundle\Traits\Annotation;

trait HttpActionTrait
{
    public int $statusCode;
    public ?string $message;
    public ?string $description;

    /**
     * @var array<string, string>
     */
    public array $headers;

    public function getHttpStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @return array<string, string>
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }
}
