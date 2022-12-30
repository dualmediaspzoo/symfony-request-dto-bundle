<?php

namespace DM\DtoRequestBundle\Attributes\Dto\Http;

use DM\DtoRequestBundle\Interfaces\Attribute\HttpActionInterface;
use DM\DtoRequestBundle\Traits\Annotation\HttpActionTrait;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * When property marked with this annotation and the result is a null
 * a {@link HttpException} will be thrown
 */
#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::IS_REPEATABLE)]
class OnNull implements HttpActionInterface
{
    use HttpActionTrait;
}
