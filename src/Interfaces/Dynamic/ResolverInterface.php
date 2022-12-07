<?php

namespace DM\DtoRequestBundle\Interfaces\Dynamic;

use DM\DtoRequestBundle\Exception\Dynamic\ParameterNotSupportedException;

/**
 * This interface should be implemented on objects which you wish to provide dynamically by the application
 */
interface ResolverInterface
{
    /**
     * Lists supported parameters for the resolver
     *
     * @return string[]
     */
    public function getSupportedParameters(): array;

    /**
     * This method should return any value that the resolver deems correct
     *
     * @param string $name
     *
     * @return mixed
     *
     * @throws ParameterNotSupportedException
     */
    public function resolveParameter(
        string $name
    );
}
