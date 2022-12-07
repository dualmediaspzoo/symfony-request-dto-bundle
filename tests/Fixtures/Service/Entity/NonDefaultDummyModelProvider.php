<?php

namespace DM\DtoRequestBundle\Tests\Fixtures\Service\Entity;

use DM\DtoRequestBundle\Annotations\Entity\EntityProvider;
use DM\DtoRequestBundle\Tests\Fixtures\Model\DummyModel;

/**
 * @EntityProvider(DummyModel::class)
 *
 * @see DummyModel
 */
class NonDefaultDummyModelProvider extends DummyModelProvider
{
}
