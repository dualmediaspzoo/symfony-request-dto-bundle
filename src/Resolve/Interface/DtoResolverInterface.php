<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Resolve\Interface;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use DualMedia\DtoRequestBundle\Metadata\Enum\BagEnum;
use Symfony\Component\HttpFoundation\Request;

interface DtoResolverInterface
{
    /**
     * @param class-string<T> $class
     *
     * @return T
     *
     * @template T of AbstractDto
     */
    public function resolve(
        string $class,
        Request $request,
        BagEnum $defaultBag = BagEnum::Request
    ): AbstractDto;
}
