<?php

namespace DM\DtoRequestBundle\Exception\DependencyInjection\Entity;

class DuplicateDefaultProviderException extends \RuntimeException
{
    /**
     * @var array<class-string, list<string>>
     */
    private array $duplicates;

    /**
     * @param array<class-string, list<string>> $duplicates
     * @param string $message
     * @param int $code
     * @param \Throwable|null $previous
     */
    public function __construct(
        array $duplicates,
        string $message = "",
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        $this->duplicates = $duplicates;

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
