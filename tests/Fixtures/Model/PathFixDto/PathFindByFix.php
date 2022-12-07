<?php

namespace DM\DtoRequestBundle\Tests\Fixtures\Model\PathFixDto;

use DM\DtoRequestBundle\Annotations\Dto\FindBy;
use DM\DtoRequestBundle\Annotations\Dto\FindOneBy;
use DM\DtoRequestBundle\Tests\Fixtures\Model\DummyModel;

class PathFindByFix
{
    /**
     * @FindOneBy(fields={"dynamic": "$dynamic", "id": "whatever"})
     */
    public ?DummyModel $dummy = null;

    /**
     * @FindOneBy(fields={"dynamic": "$dynamic", "id": "whatever"}, errorPath="overrideError")
     */
    public ?DummyModel $dummy2 = null;

    /**
     * @FindBy(fields={"dynamic": "$dynamic", "id": "whatever"})
     * @var DummyModel[]
     */
    public array $dummies = [];

    /**
     * @FindBy(fields={"dynamic": "$dynamic", "id": "whatever"}, errorPath="overrideError")
     * @var DummyModel[]
     */
    public array $otherDummies = [];

    /**
     * @FindBy(fields={"dynamic": "$dynamic", "id": "whatever"}, errorPath="overrideError")
     * @var DummyModel[]
     */
    public array $superDummies = [];
}
