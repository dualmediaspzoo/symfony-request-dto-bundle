<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Resolve\Interface;

/**
 * Allows for specifying non-standard names for keys.
 */
interface LabelProcessorInterface
{
    /**
     * Turn key into value.
     */
    public function normalize(
        string $value
    ): string;

    /**
     * Turn value into key.
     */
    public function denormalize(
        string $value
    ): string;
}
