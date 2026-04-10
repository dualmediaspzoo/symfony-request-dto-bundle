<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Fixture\Service;

use DualMedia\DtoRequestBundle\Provider\Attribute\AsDynamicProvider;
use DualMedia\DtoRequestBundle\Tests\Fixture\Enum\PureEnum;

class DynamicServiceProvider
{
    #[AsDynamicProvider('pureAlpha')]
    public function provideEnum(
        string $name
    ): PureEnum {
        return PureEnum::Alpha;
    }
}
