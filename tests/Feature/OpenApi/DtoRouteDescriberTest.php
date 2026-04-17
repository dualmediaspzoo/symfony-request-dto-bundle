<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Feature\OpenApi;

use DualMedia\DtoRequestBundle\OpenApi\DtoRouteDescriber;
use DualMedia\DtoRequestBundle\Tests\Fixture\Controller\DtoFixtureController;
use DualMedia\DtoRequestBundle\Tests\Fixture\Dto\OpenApi\SampleRequestDto;
use DualMedia\DtoRequestBundle\Tests\Fixture\Enum\StringBackedEnum;
use DualMedia\DtoRequestBundle\Tests\PHPUnit\KernelTestCase;
use OpenApi\Annotations as OA;
use OpenApi\Generator;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\Routing\Route;

#[Group('feature')]
#[Group('openapi')]
class DtoRouteDescriberTest extends KernelTestCase
{
    private DtoRouteDescriber $describer;

    private OA\OpenApi $api;

    private OA\Operation $operation;

    protected function setUp(): void
    {
        $this->describer = static::getService(DtoRouteDescriber::class);
        $this->api = new OA\OpenApi(['_context' => new \OpenApi\Context()]);

        $this->describer->describe(
            $this->api,
            new Route('/fixture/{id}', methods: ['POST']),
            new \ReflectionMethod(DtoFixtureController::class, 'submit')
        );

        $path = null;

        foreach ($this->api->paths as $candidate) {
            if ('/fixture/{id}' === $candidate->path) {
                $path = $candidate;

                break;
            }
        }

        static::assertInstanceOf(OA\PathItem::class, $path);
        static::assertInstanceOf(OA\Post::class, $path->post);
        $this->operation = $path->post;
    }

    public function testQueryParameterEmittedWithLengthConstraint(): void
    {
        $search = $this->findParameter($this->operation, 'search');

        static::assertInstanceOf(OA\Parameter::class, $search);
        static::assertSame('query', $search->in);
        static::assertInstanceOf(OA\Schema::class, $search->schema);
        static::assertSame('string', $search->schema->type);
        static::assertSame(3, $search->schema->minLength);
        static::assertSame(10, $search->schema->maxLength);
    }

    public function testPathParameterEmittedForRouteToken(): void
    {
        $id = $this->findParameter($this->operation, 'id');

        static::assertInstanceOf(OA\Parameter::class, $id);
        static::assertSame('path', $id->in);
        static::assertTrue($id->required);
        static::assertInstanceOf(OA\Schema::class, $id->schema);
        static::assertSame('integer', $id->schema->type);
    }

    public function testRequestBodyHasNestedObjectWithConstraints(): void
    {
        static::assertInstanceOf(OA\RequestBody::class, $this->operation->requestBody);
        static::assertIsArray($this->operation->requestBody->content);
        $media = $this->operation->requestBody->content[0];
        static::assertInstanceOf(OA\MediaType::class, $media);
        static::assertSame('application/json', $media->mediaType);

        $schema = $media->schema;
        static::assertInstanceOf(OA\Schema::class, $schema);
        static::assertSame('object', $schema->type);

        $nested = $this->findProperty($schema->properties, 'nested');
        static::assertInstanceOf(OA\Property::class, $nested);
        static::assertSame('object', $nested->type);
        static::assertIsArray($nested->properties);

        $name = $this->findProperty($nested->properties, 'name');
        static::assertInstanceOf(OA\Property::class, $name);
        static::assertSame('string', $name->type);
        static::assertSame(2, $name->minLength);
        static::assertSame(32, $name->maxLength);

        static::assertIsArray($nested->required);
        static::assertContains('name', $nested->required);
    }

    public function testEnumFieldExposesCases(): void
    {
        $schema = $this->operation->requestBody->content[0]->schema;
        $status = $this->findProperty($schema->properties, 'status');

        static::assertInstanceOf(OA\Property::class, $status);
        static::assertSame('string', $status->type);
        static::assertIsArray($status->enum);
        static::assertSame(array_map(static fn (StringBackedEnum $e): string => $e->value, StringBackedEnum::cases()), $status->enum);
    }

    public function testFileFieldCarriesBase64Example(): void
    {
        $schema = $this->operation->requestBody->content[0]->schema;
        $avatar = $this->findProperty($schema->properties, 'avatar');

        static::assertInstanceOf(OA\Property::class, $avatar);
        static::assertIsString($avatar->description);
        static::assertStringContainsString('base64', $avatar->description);
        static::assertIsString($avatar->example);
        static::assertStringStartsWith('data:image/jpeg;base64,', $avatar->example);
    }

    public function testNonDtoControllerProducesNoChanges(): void
    {
        $api = new OA\OpenApi(['_context' => new \OpenApi\Context()]);

        $this->describer->describe(
            $api,
            new Route('/unrelated', methods: ['GET']),
            new \ReflectionMethod(self::class, 'setUp')
        );

        static::assertSame(Generator::UNDEFINED, $api->paths);
    }

    public function testMainDtoClassMatches(): void
    {
        static::assertSame(SampleRequestDto::class, SampleRequestDto::class);
    }

    private function findParameter(
        OA\Operation $operation,
        string $name
    ): OA\Parameter|null {
        if (!is_array($operation->parameters)) {
            return null;
        }

        foreach ($operation->parameters as $parameter) {
            $paramName = rtrim((string)$parameter->name, '[]');

            if ($paramName === $name) {
                return $parameter;
            }
        }

        return null;
    }

    /**
     * @param list<OA\Property>|string $properties
     */
    private function findProperty(
        array|string $properties,
        string $name
    ): OA\Property|null {
        if (!is_array($properties)) {
            return null;
        }

        foreach ($properties as $property) {
            if ((string)$property->property === $name) {
                return $property;
            }
        }

        return null;
    }
}
