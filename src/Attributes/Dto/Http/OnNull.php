<?php

namespace DualMedia\DtoRequestBundle\Attributes\Dto\Http;

use DualMedia\DtoRequestBundle\Interfaces\Attribute\HttpActionInterface;
use DualMedia\DtoRequestBundle\Traits\Annotation\HttpActionTrait;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * When property marked with this annotation and the result is a null
 * a {@link HttpException} will be thrown.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::IS_REPEATABLE)]
class OnNull implements HttpActionInterface
{
    use HttpActionTrait;
}
