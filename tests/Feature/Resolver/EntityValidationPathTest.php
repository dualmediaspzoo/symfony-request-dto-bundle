<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Feature\Resolver;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use DualMedia\DtoRequestBundle\Resolve\DtoResolver;
use DualMedia\DtoRequestBundle\Tests\Fixture\Dto\ConstrainedFindDto;
use DualMedia\DtoRequestBundle\Tests\Fixture\Dto\SimpleFindDto;
use DualMedia\DtoRequestBundle\Tests\Fixture\Entity\SimpleEntity;
use DualMedia\DtoRequestBundle\Tests\PHPUnit\KernelTestCase;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;

/**
 * Verifies that validation errors on entity virtual fields
 * use the INPUT field name (e.g. "inputId") rather than the
 * entity column name ("id") or a compound path ("entity.id").
 */
#[Group('feature')]
#[Group('resolver')]
class EntityValidationPathTest extends KernelTestCase
{
    private DtoResolver $resolver;
    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        $this->resolver = static::getService(DtoResolver::class);
        $this->em = static::getService(EntityManagerInterface::class);

        $schemaTool = new SchemaTool($this->em);
        $schemaTool->createSchema(
            $this->em->getMetadataFactory()->getAllMetadata()
        );
    }

    protected function tearDown(): void
    {
        $schemaTool = new SchemaTool($this->em);
        $schemaTool->dropSchema(
            $this->em->getMetadataFactory()->getAllMetadata()
        );

        parent::tearDown();
    }

    public function testConstraintViolationUsesInputPath(): void
    {
        $dto = $this->resolver->resolve(
            ConstrainedFindDto::class,
            new Request(request: [
                'inputId' => '-5',
            ])
        );

        static::assertFalse($dto->isValid());

        $violations = static::getConstraintViolationsMappedToPropertyPaths($dto->getConstraintViolationList());

        // The violation path should reference the user-facing input name "inputId",
        // prefixed by the DTO property "entity" since the field belongs to that property.
        // Current behavior: "entity.inputId"
        static::assertArrayHasKey('entity.inputId', $violations);
        // Must NOT use entity column name
        static::assertArrayNotHasKey('entity.id', $violations);
        // Must NOT be bare column name
        static::assertArrayNotHasKey('id', $violations);
    }

    public function testMissingRequiredFieldViolationUsesInputPath(): void
    {
        $dto = $this->resolver->resolve(
            ConstrainedFindDto::class,
            new Request(request: [])
        );

        static::assertFalse($dto->isValid());

        $violations = static::getConstraintViolationsMappedToPropertyPaths($dto->getConstraintViolationList());
        static::assertArrayHasKey('entity.inputId', $violations);
        static::assertArrayNotHasKey('entity.id', $violations);
    }

    public function testTypeCoercionViolationUsesInputPath(): void
    {
        $dto = $this->resolver->resolve(
            ConstrainedFindDto::class,
            new Request(request: [
                'inputId' => 'not-a-number',
            ])
        );

        static::assertFalse($dto->isValid());

        $violations = static::getConstraintViolationsMappedToPropertyPaths($dto->getConstraintViolationList());
        static::assertArrayHasKey('entity.inputId', $violations);
        static::assertArrayNotHasKey('entity.id', $violations);
    }

    public function testSuccessfulResolutionHasNoViolations(): void
    {
        $entity = new SimpleEntity();
        $this->em->persist($entity);
        $this->em->flush();

        $dto = $this->resolver->resolve(
            SimpleFindDto::class,
            new Request(request: [
                'inputId' => (string) $entity->getId(),
            ])
        );

        static::assertTrue($dto->isValid());
        static::assertInstanceOf(SimpleEntity::class, $dto->entity);
    }
}
