<?php

namespace DM\DtoRequestBundle\Tests\Unit\Service\Validation;

use DM\DtoRequestBundle\Interfaces\DtoInterface;
use DM\DtoRequestBundle\Interfaces\Validation\GroupProviderInterface;
use DM\DtoRequestBundle\Service\Validation\GroupProviderService;
use DM\DtoRequestBundle\Tests\PHPUnit\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Request;

class GroupProviderServiceTest extends TestCase
{
    /**
     * @dataProvider inputProvider
     */
    public function testInputs(
        array $expected,
        array $providers,
        array $ids
    ): void {
        $this->assertEquals(
            $expected,
            (new GroupProviderService($providers))->provideGroups(
                $this->createMock(Request::class),
                $this->createMock(DtoInterface::class),
                $ids
            )
        );
    }

    public function inputProvider(): iterable
    {
        yield [
            ['Default'],
            [],
            [],
        ];
        yield [
            ['Default', 'custom'],
            [
                'custom' => $this->makeProvider(['custom']),
                'non_custom' => $this->makeProvider(['non_custom'], false),
            ],
            ['custom'],
        ];
        yield [
            ['Default', 'non-repeating'],
            [
                'no' => $this->makeProvider(['non-repeating']),
                'yes' => $this->makeProvider(['non-repeating']),
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
