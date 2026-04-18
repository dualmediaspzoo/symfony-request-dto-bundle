<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Feature\OpenApi;

use DualMedia\DtoRequestBundle\Metadata\Model\Action;
use DualMedia\DtoRequestBundle\OpenApi\FieldCollector;
use DualMedia\DtoRequestBundle\Tests\Fixture\Dto\OpenApi\ActionRequestDto;
use DualMedia\DtoRequestBundle\Tests\PHPUnit\KernelTestCase;
use PHPUnit\Framework\Attributes\Group;

#[Group('feature')]
#[Group('openapi')]
class FieldCollectorTest extends KernelTestCase
{
    public function testActionsCollectedFromNestedDto(): void
    {
        $collector = static::getService(FieldCollector::class);
        $described = $collector->collect(ActionRequestDto::class);

        static::assertNotNull($described);

        $byStatus = [];

        foreach ($described->actions as $action) {
            static::assertInstanceOf(Action::class, $action);
            $byStatus[$action->statusCode] = $action;
        }

        static::assertArrayHasKey(404, $byStatus);
        static::assertSame('Thing not found', $byStatus[404]->description);

        static::assertArrayHasKey(403, $byStatus);
        static::assertSame('Nested forbidden', $byStatus[403]->description);
    }
}
