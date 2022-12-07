<?php

namespace DM\DtoRequestBundle\Tests\Fixtures\Model\Dto;

use DM\DtoRequestBundle\Annotations\Dto\FindOneBy;
use DM\DtoRequestBundle\Annotations\Dto\Type;
use DM\DtoRequestBundle\Model\AbstractDto;
use DM\DtoRequestBundle\Tests\Fixtures\Model\DummyModel;

class FindDto extends AbstractDto
{
    /**
     * @FindOneBy(fields={"id": "id", "date": "whatever"}, types={"id": @Type("int"), "date": @Type("datetime")})
     */
    public ?DummyModel $model = null;
}
