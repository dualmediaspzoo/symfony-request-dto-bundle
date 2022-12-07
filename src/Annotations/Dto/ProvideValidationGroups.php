<?php

namespace DM\DtoRequestBundle\Annotations\Dto;

use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;
use Doctrine\Common\Annotations\Annotation\Target;
use DM\DtoRequestBundle\Interfaces\Attribute\DtoAnnotationInterface;
use DM\DtoRequestBundle\Interfaces\DtoInterface;
use DM\DtoRequestBundle\Interfaces\Validation\GroupProviderInterface;

/**
 * This annotation should be put on {@link DtoInterface} objects, which wish to specify what groups they'll use
 *
 * Services that will determine this, must implement {@link GroupProviderInterface}
 *
 * @Annotation
 *
 * @Target({"CLASS"})
 * @NamedArgumentConstructor()
 */
class ProvideValidationGroups implements DtoAnnotationInterface
{
    public string $provider;

    public function __construct(
        string $provider
    ) {
        $this->provider = $provider;
    }
}
