<?php

namespace DM\DtoRequestBundle\Tests\Fixtures\Service\Entity;

use DM\DtoRequestBundle\Attributes\Entity\EntityProvider;
use DM\DtoRequestBundle\Tests\Fixtures\Model\DummyModel;

/**
 * @see DummyModel
 */
#[EntityProvider(DummyModel::class)]
class NonDefaultDummyModelProvider extends DummyModelProvider
{
}
