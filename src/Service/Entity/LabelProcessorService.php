<?php

namespace DualMedia\DtoRequestBundle\Service\Entity;

use DualMedia\DtoRequestBundle\Exception\Entity\LabelProcessorNotFoundException;
use DualMedia\DtoRequestBundle\Interfaces\Entity\LabelProcessorInterface;
use DualMedia\DtoRequestBundle\Interfaces\Entity\LabelProcessorServiceInterface;

class LabelProcessorService implements LabelProcessorServiceInterface
{
    /**
     * @param array<string, LabelProcessorInterface> $services
     */
    public function __construct(
        private readonly array $services
    ) {
    }

    #[\Override]
    public function getProcessor(
        string $service
    ): LabelProcessorInterface {
        if (null === ($processor = ($this->services[$service] ?? null))) {
            throw new LabelProcessorNotFoundException(sprintf(
                'Requested label processor with service %s but it was not found',
                $service
            ));
        }

        return $processor;
    }
}
