<?php

namespace DM\DtoRequestBundle\Tests\Fixtures\Model\Dto;

use DM\DtoRequestBundle\Annotations\Dto\FindOneBy;
use DM\DtoRequestBundle\Annotations\Dto\Type;
use DM\DtoRequestBundle\Model\AbstractDto;
use DM\DtoRequestBundle\Tests\Fixtures\Model\DummyModel;
use Symfony\Component\Validator\Constraints as Assert;

class FindWithSomeSecondErrorDto extends AbstractDto
{
    /**
     * @FindOneBy(
     *     fields={"id": "something_id", "second": "something_second"},
     *     types={"id": @Type("int")},
     *     constraints={"id": @Assert\NotBlank(), "second": @Assert\NotBlank()}
     * )
     */
    public ?DummyModel $model = null;
}
