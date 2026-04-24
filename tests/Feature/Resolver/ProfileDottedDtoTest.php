<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Feature\Resolver;

use DualMedia\DtoRequestBundle\Metadata\Enum\BagEnum;
use DualMedia\DtoRequestBundle\OpenApi\FieldCollector;
use DualMedia\DtoRequestBundle\OpenApi\SchemaBuilder;
use DualMedia\DtoRequestBundle\Resolve\DtoResolver;
use DualMedia\DtoRequestBundle\Tests\Fixture\Dto\ProfileDottedDto;
use DualMedia\DtoRequestBundle\Tests\PHPUnit\KernelTestCase;
use OpenApi\Annotations\MediaType;
use OpenApi\Annotations\Schema;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;

#[Group('feature')]
#[Group('resolver')]
class ProfileDottedDtoTest extends KernelTestCase
{
    public function testSiblingDottedFieldsMergeIntoSingleObject(): void
    {
        $resolver = static::getService(DtoResolver::class);
        $collector = static::getService(FieldCollector::class);
        $builder = static::getService(SchemaBuilder::class);

        $dto = $resolver->resolve(
            ProfileDottedDto::class,
            new Request(request: [
                'profile' => [
                    'username' => 'alice',
                    'description' => 'bio',
                    'dateOfBirth' => '2000-01-01',
                ],
                'tag' => 'vip',
            ])
        );

        static::assertTrue($dto->isValid(), (string)$dto->getConstraintViolationList());
        static::assertSame('alice', $dto->username);
        static::assertSame('bio', $dto->description);
        static::assertSame('2000-01-01', $dto->dateOfBirth);
        static::assertSame('vip', $dto->tag);

        $described = $collector->collect(ProfileDottedDto::class, BagEnum::Request);
        static::assertNotNull($described);

        $body = $builder->buildRequestBody($described);
        static::assertNotNull($body);

        $content = $body->content;
        static::assertIsArray($content);
        $media = $content[0];
        static::assertInstanceOf(MediaType::class, $media);
        $schema = $media->schema;
        static::assertInstanceOf(Schema::class, $schema);

        $profileProps = array_values(array_filter(
            $schema->properties,
            static fn ($p): bool => 'profile' === $p->property
        ));

        static::assertCount(1, $profileProps, 'profile object must be emitted exactly once');
        static::assertCount(3, $profileProps[0]->properties);

        $names = array_map(static fn ($p): string => (string)$p->property, $profileProps[0]->properties);
        sort($names);
        static::assertSame(['dateOfBirth', 'description', 'username'], $names);
    }
}
