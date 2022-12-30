<?php

namespace DM\DtoRequestBundle\Tests\Fixtures\Model\HttpAction;

use DM\DtoRequestBundle\Attributes\Dto\FindOneBy;
use DM\DtoRequestBundle\Attributes\Dto\Http\OnNull;
use DM\DtoRequestBundle\Model\AbstractDto;
use DM\DtoRequestBundle\Tests\Fixtures\Model\DummyModel;
use Symfony\Component\HttpFoundation\Response;

class FindOneOnNullDto extends AbstractDto
{
    #[OnNull(Response::HTTP_NOT_FOUND)]
    #[FindOneBy(fields: ['id' => 'something'])]
    public ?DummyModel $model = null;
}
