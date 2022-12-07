<?php

namespace DM\DtoRequestBundle\Annotations\Dto\Http;

use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;
use DM\DtoRequestBundle\Interfaces\Attribute\HttpActionInterface;
use DM\DtoRequestBundle\Traits\Annotation\HttpActionTrait;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * When property marked with this annotation and the result is a null
 * a {@link HttpException} will be thrown
 *
 * @Annotation
 * @NamedArgumentConstructor()
 */
class OnNull implements HttpActionInterface
{
    use HttpActionTrait;

    /**
     * @param int $statusCode
     * @param string|null $message
     * @param array $headers
     * @psalm-param array<string, string> $headers
     * @param string|null $description
     */
    public function __construct(
        int $statusCode,
        ?string $message = '',
        array $headers = [],
        ?string $description = null
    ) {
        $this->statusCode = $statusCode;
        $this->message = $message;
        $this->headers = $headers;
        $this->description = $description;
    }
}
