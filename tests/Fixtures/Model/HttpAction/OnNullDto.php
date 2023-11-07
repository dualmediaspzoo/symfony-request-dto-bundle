<?php

namespace DualMedia\DtoRequestBundle\Tests\Fixtures\Model\HttpAction;

use DualMedia\DtoRequestBundle\Attributes\Dto\Http\OnNull;
use DualMedia\DtoRequestBundle\Model\AbstractDto;
use DualMedia\DtoRequestBundle\Tests\Fixtures\Model\DummyModel;
use Symfony\Component\HttpFoundation\Response;

class OnNullDto extends AbstractDto
{
    #[OnNull(Response::HTTP_NOT_FOUND)]
    public DummyModel|null $model = null;
}
