<?php

namespace DualMedia\DtoRequestBundle\Tests\Unit\Service\Validation;

use DualMedia\DtoRequestBundle\Interfaces\DtoInterface;
use DualMedia\DtoRequestBundle\Interfaces\Validation\GroupProviderInterface;
use DualMedia\DtoRequestBundle\Service\Validation\GroupProviderService;
use DualMedia\DtoRequestBundle\Tests\PHPUnit\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Request;

#[Group('unit')]
#[Group('service')]
#[Group('validation')]
#[CoversClass(GroupProviderService::class)]
class GroupProviderServiceTest extends TestCase
{
    #[DataProvider('provideInputCases')]
    public function testInput(
        array $expected,
        array $providers,
        array $ids
    ): void {
        foreach ($providers as $index => $providerData) {
            $providers[$index] = $this->makeProvider(...$providerData);
        }

        $this->assertEquals(
            $expected,
            (new GroupProviderService($providers))->provideGroups(
                $this->createMock(Request::class),
                $this->createMock(DtoInterface::class),
                $ids
            )
        );
    }

    public static function provideInputCases(): iterable
    {
        yield [
            ['Default'],
            [],
            [],
        ];
        yield [
            ['Default', 'custom'],
            [
                'custom' => [['custom']],
                'non_custom' => [['non_custom'], false],
            ],
            ['custom'],
        ];
        yield [
            ['Default', 'non-repeating'],
            [
                'no' => [['non-repeating']],
                'yes' => [['non-repeating']],
            ],
            ['no', 'yes'],
        ];
    }

    private function makeProvider(
        array $returns,
        bool $willBeUsed = true
    ): MockObject {
        $mock = $this->createMock(GroupProviderInterface::class);
        $mock->expects($this->exactly((int)$willBeUsed))
            ->method('provideValidationGroups')
            ->willReturn($returns);

        return $mock;
    }
}
