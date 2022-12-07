<?php

namespace DM\DtoRequestBundle\Tests\Fixtures\Model\Dto;

use DM\DtoRequestBundle\Annotations\Dto\FindBy;
use DM\DtoRequestBundle\Annotations\Dto\Type;
use DM\DtoRequestBundle\Model\AbstractDto;
use DM\DtoRequestBundle\Tests\Fixtures\Model\DummyModel;
use Symfony\Component\Validator\Constraints as Assert;

class MultiFindDto extends AbstractDto
{
    /**
     * @FindBy(
     *     fields={"id": "something"},
     *     types={"id": @Type("int", true)},
     *     constraints={"id": {
     *         @Assert\NotNull(),
     *         @Assert\Count(min=1, max=15)
     *     }}
     * )
     * @var DummyModel[]
     */
    public array $models = [];
}
