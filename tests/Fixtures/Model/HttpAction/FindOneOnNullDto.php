<?php

namespace DualMedia\DtoRequestBundle\Tests\Fixtures\Model\HttpAction;

use DualMedia\DtoRequestBundle\Attributes\Dto\FindOneBy;
use DualMedia\DtoRequestBundle\Attributes\Dto\Http\OnNull;
use DualMedia\DtoRequestBundle\Model\AbstractDto;
use DualMedia\DtoRequestBundle\Tests\Fixtures\Model\DummyModel;
use Symfony\Component\HttpFoundation\Response;

class FindOneOnNullDto extends AbstractDto
{
    #[OnNull(Response::HTTP_NOT_FOUND)]
    #[FindOneBy(fields: ['id' => 'something'])]
    public ?DummyModel $model = null;
}
