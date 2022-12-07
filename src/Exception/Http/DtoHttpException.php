<?php

namespace DM\DtoRequestBundle\Exception\Http;

use DM\DtoRequestBundle\Annotations\Dto\Http\OnNull;
use DM\DtoRequestBundle\Interfaces\DtoInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Thrown when a DTO property marked with {@link OnNull} is null
 */
class DtoHttpException extends HttpException
{
    private DtoInterface $dto;

    /**
     * @param DtoInterface $dto
     * @param int $statusCode
     * @param string|null $message
     * @param \Throwable|null $previous
     * @param array $headers
     * @psalm-param array<string, string> $headers
     * @param int|null $code
     */
    public function __construct(
        DtoInterface $dto,
        int $statusCode,
        ?string $message = '',
        \Throwable $previous = null,
        array $headers = [],
        ?int $code = 0
    ) {
        parent::__construct($statusCode, $message, $previous, $headers, $code);
        $this->dto = $dto;
    }

    public function getDto(): DtoInterface
    {
        return $this->dto;
    }
}
