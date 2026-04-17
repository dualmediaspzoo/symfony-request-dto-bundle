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

    /**
     * OpenAPI `in` value for bags that map to a Parameter location.
     * Request and Files don't — they belong in a request body.
     *
     * @return 'query'|'header'|'path'|'cookie'|null
     */
    public function parameterLocation(): string|null
    {
        return match ($this) {
            self::Query => 'query',
            self::Headers => 'header',
            self::Cookies => 'cookie',
            self::Attributes => 'path',
            default => null,
        };
    }
}
