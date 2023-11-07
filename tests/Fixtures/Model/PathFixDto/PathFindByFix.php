<?php

namespace DualMedia\DtoRequestBundle\Tests\Fixtures\Model\PathFixDto;

use DualMedia\DtoRequestBundle\Attributes\Dto\FindBy;
use DualMedia\DtoRequestBundle\Attributes\Dto\FindOneBy;
use DualMedia\DtoRequestBundle\Tests\Fixtures\Model\DummyModel;

class PathFindByFix
{
    #[FindOneBy(fields: ['dynamic' => '$dynamic', 'id' => 'whatever'])]
    public DummyModel|null $dummy = null;

    #[FindOneBy(fields: ['dynamic' => '$dynamic', 'id' => 'whatever'], errorPath: 'overrideError')]
    public DummyModel|null $dummy2 = null;

    /**
     * @var DummyModel[]
     */
    #[FindBy(fields: ['dynamic' => '$dynamic', 'id' => 'whatever'])]
    public array $dummies = [];

    /**
     * @var DummyModel[]
     */
    #[FindBy(fields: ['dynamic' => '$dynamic', 'id' => 'whatever'], errorPath: 'overrideError')]
    public array $otherDummies = [];

    /**
     * @var DummyModel[]
     */
    #[FindBy(fields: ['dynamic' => '$dynamic', 'id' => 'whatever'], errorPath: 'overrideError')]
    public array $superDummies = [];
}
