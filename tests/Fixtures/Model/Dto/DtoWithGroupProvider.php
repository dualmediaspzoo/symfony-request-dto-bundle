<?php

namespace DualMedia\DtoRequestBundle\Tests\Fixtures\Model\Dto;

use DualMedia\DtoRequestBundle\Attribute\Dto\ProvideValidationGroups;
use DualMedia\DtoRequestBundle\Model\AbstractDto;
use DualMedia\DtoRequestBundle\Tests\Fixtures\Service\Validation\DummyGroupProvider;

#[ProvideValidationGroups(DummyGroupProvider::class)]
class DtoWithGroupProvider extends AbstractDto
{
}
