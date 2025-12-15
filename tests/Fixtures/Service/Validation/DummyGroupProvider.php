<?php

namespace DualMedia\DtoRequestBundle\Tests\Fixtures\Service\Validation;

use DualMedia\DtoRequestBundle\Interface\DtoInterface;
use DualMedia\DtoRequestBundle\Interface\Validation\GroupProviderInterface;
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
