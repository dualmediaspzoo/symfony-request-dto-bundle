<?php

namespace DualMedia\DtoRequestBundle\Exception\DependencyInjection\Entity;

class DuplicateDefaultProviderException extends \RuntimeException
{
    /**
     * @param array<class-string, list<string>> $duplicates
     * @param string $message
     * @param int $code
     * @param \Throwable|null $previous
     */
    public function __construct(
        private readonly array $duplicates,
        string $message = "",
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return array<class-string, list<string>>
     */
    public function getDuplicates(): array
    {
        return $this->duplicates;
    }
}
