<?php

namespace DualMedia\DtoRequestBundle\Service\Entity\LabelProcessor;

use DualMedia\DtoRequestBundle\Interface\Entity\LabelProcessorInterface;

class PascalCaseProcessor implements LabelProcessorInterface
{
    #[\Override]
    public function normalize(
        string $value
    ): string {
        return strtoupper((string)preg_replace('/[A-Z]/', '_\\0', lcfirst($value)));
    }

    #[\Override]
    public function denormalize(
        string $value
    ): string {
        return implode(
            '',
            array_map(
                fn (string $s) => ucfirst(strtolower($s)),
                explode('_', $value)
            )
        );
    }
}
