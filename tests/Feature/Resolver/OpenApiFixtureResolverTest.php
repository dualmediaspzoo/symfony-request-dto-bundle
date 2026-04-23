<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Feature\Resolver;

use DualMedia\DtoRequestBundle\Resolve\Interface\DtoResolverInterface;
use DualMedia\DtoRequestBundle\Tests\Fixture\Dto\OpenApi\HeaderParentDto;
use DualMedia\DtoRequestBundle\Tests\Fixture\Dto\OpenApi\SampleRequestDto;
use DualMedia\DtoRequestBundle\Tests\Fixture\Enum\StringBackedEnum;
use DualMedia\DtoRequestBundle\Tests\PHPUnit\KernelTestCase;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

#[Group('feature')]
#[Group('resolver')]
class OpenApiFixtureResolverTest extends KernelTestCase
{
    private DtoResolverInterface $resolver;

    protected function setUp(): void
    {
        $this->resolver = static::getService(DtoResolverInterface::class);
    }

    public function testSampleRequestDtoResolvesAllBags(): void
    {
        $file = new UploadedFile(
            tempnam(sys_get_temp_dir(), 'dto'),
            'avatar.jpg',
            'image/jpeg',
            null,
            true
        );

        $request = new Request(
            query: ['search' => 'hello'],
            request: [
                'nested' => [
                    'name' => 'alice',
                    'count' => 5,
                ],
                'status' => 'foo',
                'password' => 'Abcdef1!',
            ],
            attributes: ['id' => 42],
            files: ['avatar' => $file],
        );

        $dto = $this->resolver->resolve(SampleRequestDto::class, $request);

        static::assertTrue($dto->isValid());
        static::assertSame(42, $dto->id);
        static::assertSame('hello', $dto->search);
        static::assertNotNull($dto->nested);
        static::assertSame('alice', $dto->nested->name);
        static::assertSame(5, $dto->nested->count);
        static::assertSame(StringBackedEnum::Foo, $dto->status);
        static::assertSame($file, $dto->avatar);
    }

    public function testSampleRequestDtoRejectsInvalidQueryLength(): void
    {
        $request = new Request(
            query: ['search' => 'ab'],
            request: [
                'nested' => ['name' => 'alice', 'count' => 1],
            ],
            attributes: ['id' => 1],
        );

        $dto = $this->resolver->resolve(SampleRequestDto::class, $request);

        static::assertFalse($dto->isValid());
        $violations = static::getConstraintViolationsMappedToPropertyPaths($dto->getConstraintViolationList());
        static::assertArrayHasKey('search', $violations);
    }

    public function testSampleRequestDtoMissingRequiredIdCollectsViolation(): void
    {
        $request = new Request(
            query: ['search' => 'hello'],
            request: ['nested' => ['name' => 'alice', 'count' => 1]],
        );

        $dto = $this->resolver->resolve(SampleRequestDto::class, $request);

        static::assertFalse($dto->isValid());
        $violations = static::getConstraintViolationsMappedToPropertyPaths($dto->getConstraintViolationList());
        static::assertArrayHasKey('id', $violations);
    }

    public function testHeaderParentDtoPullsHeadersIntoChildFields(): void
    {
        $request = new Request();
        $request->headers->set('X-Main', 'primary-value');
        $request->headers->set('X-Other', 'secondary-value');

        $dto = $this->resolver->resolve(HeaderParentDto::class, $request);

        static::assertTrue($dto->isValid());
        static::assertNotNull($dto->headers);
        static::assertSame('primary-value', $dto->headers->mainHeader);
        static::assertSame('secondary-value', $dto->headers->otherHeader);
    }

    public function testHeaderParentDtoMissingHeadersStayNull(): void
    {
        $dto = $this->resolver->resolve(HeaderParentDto::class, new Request());

        static::assertTrue($dto->isValid());
        static::assertNotNull($dto->headers);
        static::assertNull($dto->headers->mainHeader);
        static::assertNull($dto->headers->otherHeader);
    }
}
