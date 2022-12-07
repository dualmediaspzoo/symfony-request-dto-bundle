<?php

namespace DM\DtoRequestBundle\Tests\Fixtures\Model\HttpAction;

use DM\DtoRequestBundle\Annotations\Dto\FindOneBy;
use DM\DtoRequestBundle\Annotations\Dto\Http\OnNull;
use DM\DtoRequestBundle\Model\AbstractDto;
use DM\DtoRequestBundle\Tests\Fixtures\Model\DummyModel;

class FindOneOnNullDto extends AbstractDto
{
    /**
     * @OnNull(404)
     * @FindOneBy(fields={"id": "something"})
     */
    public ?DummyModel $model = null;
}
