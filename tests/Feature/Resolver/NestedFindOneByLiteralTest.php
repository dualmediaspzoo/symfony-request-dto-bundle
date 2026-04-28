<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Feature\Resolver;

use DualMedia\DtoRequestBundle\Resolve\DtoResolver;
use DualMedia\DtoRequestBundle\Tests\Fixture\Dto\NestedFindOneByLiteralRootDto;
use DualMedia\DtoRequestBundle\Tests\Fixture\Service\TestObjectProvider;
use DualMedia\DtoRequestBundle\Tests\PHPUnit\KernelTestCase;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;

#[Group('feature')]
#[Group('resolver')]
class NestedFindOneByLiteralTest extends KernelTestCase
{
    private DtoResolver $resolver;

    private TestObjectProvider $provider;

    protected function setUp(): void
    {
        $this->resolver = static::getService(DtoResolver::class);
        $this->provider = static::getService(TestObjectProvider::class);
        $this->provider->store = [];
        $this->provider->calls = [];
    }

    public function testDeeplyNestedFindOneByValidatesNotNullWhenInputMissing(): void
    {
        $dto = $this->resolver->resolve(
            NestedFindOneByLiteralRootDto::class,
            new Request(request: [
                'attachments' => [
                    ['files' => [[]]], // file entry exists, but no someId
                ],
            ])
        );

        // Resolution must complete without exceptions and produce a validation
        // failure (NotNull on the leaf entity), not a 500-style provider crash.
        static::assertFalse($dto->isValid(), 'expected NotNull violation on the leaf entity');

        $messages = [];

        foreach ($dto->getConstraintViolationList() as $violation) {
            $messages[] = $violation->getMessage();
        }

        static::assertContains('entity is required', $messages);

        // Provider must not have been invoked when no input identifier was sent —
        // otherwise the entity loader would run with criteria=['id' => null, ...]
        // and trip the underlying database driver.
        static::assertSame([], $this->provider->calls);
    }

    public function testDeeplyNestedFindOneByLoadsEntityWhenInputProvided(): void
    {
        $expected = new \stdClass();
        $this->provider->store['42'] = $expected;

        $dto = $this->resolver->resolve(
            NestedFindOneByLiteralRootDto::class,
            new Request(request: [
                'attachments' => [
                    ['files' => [['someId' => '42']]],
                ],
            ])
        );

        static::assertTrue($dto->isValid(), (string)$dto->getConstraintViolationList());
        static::assertCount(1, $dto->attachments);
        static::assertCount(1, $dto->attachments[0]->files);
        static::assertSame($expected, $dto->attachments[0]->files[0]->entity);

        // Provider was called exactly once with the resolved id and the literal.
        static::assertCount(1, $this->provider->calls);
        static::assertSame('42', $this->provider->calls[0]['criteria']['id']);
        static::assertTrue($this->provider->calls[0]['criteria']['other']);
    }
}
