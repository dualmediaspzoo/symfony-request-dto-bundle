<?php

namespace DualMedia\DtoRequestBundle\Tests\Fixtures\Service\Entity;

use DualMedia\DtoRequestBundle\Attribute\Entity\EntityProvider;
use DualMedia\DtoRequestBundle\Tests\Fixtures\Model\DummyModel;

/**
 * @see DummyModel
 */
#[EntityProvider(DummyModel::class)]
class NonDefaultDummyModelProvider extends DummyModelProvider
{
}
