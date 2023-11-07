<?php

namespace DualMedia\DtoRequestBundle\Tests\Unit\Service\Resolver\DtoTypeExtractorHelper;

use DualMedia\DtoRequestBundle\Attributes\Dto\ProvideValidationGroups;
use DualMedia\DtoRequestBundle\Service\Resolver\DtoTypeExtractorHelper;
use DualMedia\DtoRequestBundle\Tests\Fixtures\Model\Dto\DtoWithGroupProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;

class GroupProviderExtractTest extends TestCase
{
    public function test(): void
    {
        $helper = new DtoTypeExtractorHelper(
            new PropertyInfoExtractor()
        );

        $dto = $helper->extract(new \ReflectionClass(DtoWithGroupProvider::class));
        $this->assertTrue($dto->hasDtoAttribute(ProvideValidationGroups::class));
    }
}
