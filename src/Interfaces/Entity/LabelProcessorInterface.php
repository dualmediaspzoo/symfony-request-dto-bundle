<?php

namespace DualMedia\DtoRequestBundle\Interfaces\Entity;

interface LabelProcessorInterface
{
    /**
     * Turns the label into a possible result key.
     */
    public function normalize(
        string $value
    ): string;

    /**
     * Turns the possible result key into a label.
     */
    public function denormalize(
        string $value
    ): string;
}
