<?php

namespace DualMedia\DtoRequestBundle\Enum;

enum BagEnum: string
{
    case Query = 'query';
    case Request = 'request';
    case Attributes = 'attributes';
    case Files = 'files';
    case Cookies = 'cookies';
    case Headers = 'headers';

    public function isHeaders(): bool
    {
        return self::Headers->value === $this->value;
    }
}
