<?php

namespace DM\DtoRequestBundle\Tests\Fixtures\Model\HttpAction;

use DM\DtoRequestBundle\Annotations\Dto\Http\OnNull;
use DM\DtoRequestBundle\Model\AbstractDto;
use DM\DtoRequestBundle\Tests\Fixtures\Model\DummyModel;

class OnNullDto extends AbstractDto
{
    /**
     * @OnNull(404)
     */
    public ?DummyModel $model = null;
}
