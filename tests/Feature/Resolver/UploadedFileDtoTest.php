<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Feature\Resolver;

use DualMedia\DtoRequestBundle\Resolve\DtoResolver;
use DualMedia\DtoRequestBundle\Tests\Fixture\Dto\UploadedFileDto;
use DualMedia\DtoRequestBundle\Tests\PHPUnit\KernelTestCase;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

#[Group('feature')]
#[Group('resolver')]
class UploadedFileDtoTest extends KernelTestCase
{
    private DtoResolver $resolver;

    protected function setUp(): void
    {
        $this->resolver = static::getService(DtoResolver::class);
    }

    public function testValidSingleFile(): void
    {
        $file = $this->createUploadedFile();

        $dto = $this->resolver->resolve(
            UploadedFileDto::class,
            new Request(files: [
                'file' => $file,
            ])
        );

        static::assertTrue($dto->isValid());
        static::assertSame($file, $dto->file);
    }

    public function testValidFileCollection(): void
    {
        $file1 = $this->createUploadedFile('a.txt');
        $file2 = $this->createUploadedFile('b.txt');

        $dto = $this->resolver->resolve(
            UploadedFileDto::class,
            new Request(files: [
                'files' => [$file1, $file2],
            ])
        );

        static::assertTrue($dto->isValid());
        static::assertCount(2, $dto->files);
        static::assertSame($file1, $dto->files[0]);
        static::assertSame($file2, $dto->files[1]);
    }

    public function testMissingFileStaysNull(): void
    {
        $dto = $this->resolver->resolve(
            UploadedFileDto::class,
            new Request()
        );

        static::assertTrue($dto->isValid());
        static::assertNull($dto->file);
        static::assertSame([], $dto->files);
    }

    public function testSingleFileWithCollection(): void
    {
        $single = $this->createUploadedFile('single.txt');
        $multi1 = $this->createUploadedFile('multi1.txt');
        $multi2 = $this->createUploadedFile('multi2.txt');

        $dto = $this->resolver->resolve(
            UploadedFileDto::class,
            new Request(files: [
                'file' => $single,
                'files' => [$multi1, $multi2],
            ])
        );

        static::assertTrue($dto->isValid());
        static::assertSame($single, $dto->file);
        static::assertCount(2, $dto->files);
    }

    private function createUploadedFile(
        string $name = 'test.txt'
    ): UploadedFile {
        return new UploadedFile(
            tempnam(sys_get_temp_dir(), 'test'),
            $name,
            'text/plain',
            null,
            true
        );
    }
}
