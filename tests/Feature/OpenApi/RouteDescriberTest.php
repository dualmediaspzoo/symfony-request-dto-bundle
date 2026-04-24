<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Feature\OpenApi;

use DualMedia\DtoRequestBundle\OpenApi\RouteDescriber;
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
class RouteDescriberTest extends KernelTestCase
{
    private RouteDescriber $describer;

    private OA\OpenApi $api;

    private OA\Operation $operation;

    protected function setUp(): void
    {
        $this->describer = static::getService(RouteDescriber::class);
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
        $schema = $this->getBodySchema();
        $status = $this->findProperty($schema->properties, 'status');

        static::assertInstanceOf(OA\Property::class, $status);
        static::assertSame('string', $status->type);
        static::assertIsArray($status->enum);
        static::assertSame(array_map(static fn (StringBackedEnum $e): string => $e->value, StringBackedEnum::cases()), $status->enum);
    }

    public function testFileFieldCarriesBase64Example(): void
    {
        $schema = $this->getBodySchema();
        $avatar = $this->findProperty($schema->properties, 'avatar');

        static::assertInstanceOf(OA\Property::class, $avatar);
        static::assertIsString($avatar->description);
        static::assertStringContainsString('base64', $avatar->description);
        static::assertIsString($avatar->example);
        static::assertStringStartsWith('data:image/jpeg;base64,', $avatar->example);
    }

    public function testMultipleRegexConstraintsCombinedOnPattern(): void
    {
        $schema = $this->getBodySchema();
        $password = $this->findProperty($schema->properties, 'password');

        static::assertInstanceOf(OA\Property::class, $password);
        static::assertSame('string', $password->type);
        static::assertIsString($password->pattern);
        static::assertStringContainsString('[0-9!@#', $password->pattern);
        static::assertStringContainsString('[a-z]', $password->pattern);
        static::assertStringContainsString('[A-Z]', $password->pattern);
        print_r($password->pattern);
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

    public function testResponsesBuiltFromAction(): void
    {
        $operation = $this->runDescriber('/actions', 'POST', 'actions', OA\Post::class);

        static::assertIsArray($operation->responses);

        $byStatus = [];

        foreach ($operation->responses as $response) {
            $byStatus[(string)$response->response] = $response;
        }

        static::assertArrayHasKey('404', $byStatus);
        static::assertSame('Thing not found', $byStatus['404']->description);
        static::assertArrayHasKey('403', $byStatus);
        static::assertSame('Nested forbidden', $byStatus['403']->description);
    }

    public function testDescriptionFromDocBlockOnProperty(): void
    {
        $operation = $this->runDescriber('/actions', 'POST', 'actions', OA\Post::class);

        static::assertInstanceOf(OA\RequestBody::class, $operation->requestBody);
        static::assertIsArray($operation->requestBody->content);
        $schema = $operation->requestBody->content[0]->schema;
        static::assertInstanceOf(OA\Schema::class, $schema);

        $thing = $this->findProperty($schema->properties, 'thing');
        static::assertInstanceOf(OA\Property::class, $thing);
        static::assertIsString($thing->description);
        static::assertStringContainsString('Top-level thing description.', $thing->description);
        static::assertStringContainsString('Continues here.', $thing->description);
    }

    public function testHeaderChildDtoFlattensToTopLevelParameters(): void
    {
        $api = new OA\OpenApi(['_context' => new \OpenApi\Context()]);

        $this->describer->describe(
            $api,
            new Route('/headers', methods: ['GET']),
            new \ReflectionMethod(DtoFixtureController::class, 'headers')
        );

        $path = null;

        foreach ($api->paths as $candidate) {
            if ('/headers' === $candidate->path) {
                $path = $candidate;

                break;
            }
        }

        static::assertInstanceOf(OA\PathItem::class, $path);
        static::assertInstanceOf(OA\Get::class, $path->get);

        static::assertIsArray($path->get->parameters);
        $names = [];

        foreach ($path->get->parameters as $parameter) {
            static::assertSame('header', $parameter->in);
            static::assertInstanceOf(OA\Schema::class, $parameter->schema);
            static::assertSame('string', $parameter->schema->type);
            $names[] = (string)$parameter->name;
        }

        static::assertContains('X-Main', $names);
        static::assertContains('X-Other', $names);
        static::assertNotContains('headers.X-Main', $names);
    }

    /**
     * @param class-string<OA\Operation> $operationClass
     */
    private function runDescriber(
        string $path,
        string $method,
        string $controllerMethod,
        string $operationClass
    ): OA\Operation {
        $api = new OA\OpenApi(['_context' => new \OpenApi\Context()]);

        $this->describer->describe(
            $api,
            new Route($path, methods: [$method]),
            new \ReflectionMethod(DtoFixtureController::class, $controllerMethod)
        );

        $pathItem = null;

        foreach ($api->paths as $candidate) {
            if ($path === $candidate->path) {
                $pathItem = $candidate;

                break;
            }
        }

        static::assertInstanceOf(OA\PathItem::class, $pathItem);
        $operation = $pathItem->{strtolower($method)};
        static::assertInstanceOf($operationClass, $operation);

        return $operation;
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

    private function getBodySchema(): OA\Schema
    {
        $body = $this->operation->requestBody;
        static::assertInstanceOf(OA\RequestBody::class, $body);
        static::assertIsArray($body->content);
        $media = $body->content[0];
        static::assertInstanceOf(OA\MediaType::class, $media);
        $schema = $media->schema;
        static::assertInstanceOf(OA\Schema::class, $schema);

        return $schema;
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
