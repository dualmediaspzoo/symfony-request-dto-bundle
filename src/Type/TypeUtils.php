<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Type;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\TypeInfo\Type;

class TypeUtils
{
    public const \Closure INT = static function () {
        return Type::int();
    };

    public const \Closure STRING = static function () {
        return Type::string();
    };

    public const \Closure FLOAT = static function () {
        return Type::float();
    };

    public const \Closure BOOL = static function () {
        return Type::bool();
    };

    public const \Closure DATETIME = static function () {
        return Type::object(\DateTimeInterface::class);
    };

    public const \Closure UPLOADED_FILE = static function () {
        return Type::object(UploadedFile::class);
    };

    public const \Closure LIST_INT = static function () {
        return Type::list(Type::int());
    };

    public const \Closure LIST_STRING = static function () {
        return Type::list(Type::string());
    };

    public const \Closure LIST_FLOAT = static function () {
        return Type::list(Type::float());
    };

    public const \Closure LIST_BOOL = static function () {
        return Type::list(Type::bool());
    };

    public const \Closure LIST_DATETIME = static function () {
        return Type::list(Type::object(\DateTimeInterface::class));
    };

    public const \Closure LIST_UPLOADED_FILE = static function () {
        return Type::list(Type::object(UploadedFile::class));
    };

    private function __construct()
    {
    }
}
