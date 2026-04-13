<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Fixture\Dto;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use DualMedia\DtoRequestBundle\Dto\Attribute\Field;
use DualMedia\DtoRequestBundle\Dto\Attribute\FindOneBy;
use DualMedia\DtoRequestBundle\Dto\Attribute\WithObjectProvider;
use DualMedia\DtoRequestBundle\Tests\Fixture\Service\TestObjectProvider;

class WithObjectProviderDto extends AbstractDto
{
    #[FindOneBy]
    #[Field('id', 'inputId')]
    #[WithObjectProvider(static function (TestObjectProvider $provider, array $criteria, array $meta): mixed {
        return $provider->find($criteria, $meta);
    })]
    public \stdClass|null $thing = null;
}
