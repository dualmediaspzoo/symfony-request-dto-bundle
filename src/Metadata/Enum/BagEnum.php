<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Metadata\Enum;

enum BagEnum: string
{
    case Query = 'query';
    case Request = 'request';
    case Attributes = 'attributes';
    case Files = 'files';
    case Cookies = 'cookies';
    case Headers = 'headers';
}