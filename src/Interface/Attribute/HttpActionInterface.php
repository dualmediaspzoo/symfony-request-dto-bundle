<?php

namespace DualMedia\DtoRequestBundle\Interface\Attribute;

interface HttpActionInterface extends DtoAttributeInterface
{
    /**
     * Gets the exception http status code.
     */
    public function getHttpStatusCode(): int;

    /**
     * Gets a message passed to the exception.
     */
    public function getMessage(): string|null;

    /**
     * Used for documentation.
     */
    public function getDescription(): string|null;

    /**
     * Gets http exception headers.
     *
     * @return array<string, string>
     */
    public function getHeaders(): array;
}
