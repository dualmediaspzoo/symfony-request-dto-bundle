<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Type;

use Symfony\Component\TypeInfo\Type;

class TypeUtils
{
    public const \Closure LIST_INT = static function () {
        return Type::list(Type::int());
    };

    public const \Closure LIST_STRING = static function () {
        return Type::list(Type::string());
    };

    public const \Closure LIST_FLOAT = static function () {
        return Type::list(Type::float());
    };

    private function __construct()
    {
    }
}
