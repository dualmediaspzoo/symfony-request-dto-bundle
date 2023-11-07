<?php

namespace DualMedia\DtoRequestBundle\Traits\Annotation;

trait HttpActionTrait
{
    /**
     * @psalm-param array<string, string> $headers
     */
    public function __construct(
        public int $statusCode,
        public string|null $message = '',
        public array $headers = [],
        public string|null $description = null
    ) {
    }

    public function getHttpStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getMessage(): string|null
    {
        return $this->message;
    }

    public function getDescription(): string|null
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
