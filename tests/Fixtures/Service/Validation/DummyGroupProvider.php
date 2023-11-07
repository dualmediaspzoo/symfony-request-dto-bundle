<?php

namespace DualMedia\DtoRequestBundle\Tests\Fixtures\Service\Validation;

use DualMedia\DtoRequestBundle\Interfaces\DtoInterface;
use DualMedia\DtoRequestBundle\Interfaces\Validation\GroupProviderInterface;
use Symfony\Component\HttpFoundation\Request;

class DummyGroupProvider implements GroupProviderInterface
{
    public function provideValidationGroups(
        Request $request,
        DtoInterface $dto
    ): array {
        return ['Dummy'];
    }
}
