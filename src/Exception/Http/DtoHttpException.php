<?php

namespace DualMedia\DtoRequestBundle\Exception\Http;

use DualMedia\DtoRequestBundle\Attributes\Dto\Http\OnNull;
use DualMedia\DtoRequestBundle\Interfaces\DtoInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Thrown when a DTO property marked with {@link OnNull} is null
 */
class DtoHttpException extends HttpException
{
    /**
     * @param DtoInterface $dto
     * @param int $statusCode
     * @param string $message
     * @param \Throwable|null $previous
     * @param array $headers
     * @psalm-param array<string, string> $headers
     * @param int $code
     */
    public function __construct(
        private readonly DtoInterface $dto,
        int $statusCode,
        string $message = '',
        \Throwable $previous = null,
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
