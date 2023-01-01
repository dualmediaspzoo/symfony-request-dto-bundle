<?php

namespace DualMedia\DtoRequestBundle\Interfaces\Entity;

/**
 * @template T of object
 *
 * @extends ProviderInterface<T>
 */
interface TargetProviderInterface extends ProviderInterface
{
    /**
     * @param class-string<T> $fqcn
     *
     * @return bool if true this provider will support this fqcn, you can proceed to call next methods
     */
    public function setFqcn(
        string $fqcn
    ): bool;
}
