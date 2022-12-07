<?php

namespace DM\DtoRequestBundle\Interfaces\Attribute;

interface HttpActionInterface extends DtoAnnotationInterface
{
    /**
     * Gets the exception http status code
     *
     * @return int
     */
    public function getHttpStatusCode(): int;

    /**
     * Gets a message passed to the exception
     *
     * @return string|null
     */
    public function getMessage(): ?string;

    /**
     * Used for documentation
     *
     * @return string|null
     */
    public function getDescription(): ?string;

    /**
     * Gets http exception headers
     *
     * @return array<string, string>
     */
    public function getHeaders(): array;
}
