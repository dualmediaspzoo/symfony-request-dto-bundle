<?php

namespace DualMedia\DtoRequestBundle\Exception\Http;

use DualMedia\DtoRequestBundle\Attribute\Dto\Http\OnNull;
use DualMedia\DtoRequestBundle\Interface\DtoInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Thrown when a DTO property marked with {@link OnNull} is null.
 */
class DtoHttpException extends HttpException
{
    /**
     * @psalm-param array<string, string> $headers
     */
    public function __construct(
        private readonly DtoInterface $dto,
        int $statusCode,
        string $message = '',
        \Throwable|null $previous = null,
        array $headers = [],
        int $code = 0
    ) {
        parent::__construct($statusCode, $message, $previous, $headers, $code);
    }

    public function getDto(): DtoInterface
    {
        return $this->dto;
    }
}
