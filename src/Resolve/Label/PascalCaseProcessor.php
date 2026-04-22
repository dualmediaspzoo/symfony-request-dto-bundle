<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Resolve\Label;

use DualMedia\DtoRequestBundle\Resolve\Interface\LabelProcessorInterface;

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
