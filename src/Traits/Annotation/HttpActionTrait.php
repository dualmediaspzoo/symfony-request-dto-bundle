<?php

namespace DM\DtoRequestBundle\Traits\Annotation;

trait HttpActionTrait
{
    /**
     * @param int $statusCode
     * @param string|null $message
     * @param array $headers
     * @psalm-param array<string, string> $headers
     * @param string|null $description
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
