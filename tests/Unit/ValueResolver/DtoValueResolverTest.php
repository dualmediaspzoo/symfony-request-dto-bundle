<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Unit\ValueResolver;

use DualMedia\DtoRequestBundle\Dto\Event\ResolvedEvent;
use DualMedia\DtoRequestBundle\Parameter\Attribute\AllowInvalid;
use DualMedia\DtoRequestBundle\Resolve\DtoResolver;
use DualMedia\DtoRequestBundle\Tests\Fixture\Dto\Request\ScalarRequestDto;
use DualMedia\DtoRequestBundle\ValueResolver\DtoValueResolver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

#[CoversClass(DtoValueResolver::class)]
#[Group('unit')]
#[Group('value-resolver')]
class DtoValueResolverTest extends TestCase
{
    /** @var DtoResolver&MockObject */
    private DtoResolver $dtoResolver;

    /** @var EventDispatcherInterface&MockObject */
    private EventDispatcherInterface $dispatcher;

    private DtoValueResolver $resolver;

    protected function setUp(): void
    {
        $this->dtoResolver = $this->createMock(DtoResolver::class);
        $this->dispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->resolver = new DtoValueResolver($this->dtoResolver, $this->dispatcher);
    }

    public function testReturnsEmptyWhenArgumentTypeIsNull(): void
    {
        $this->dtoResolver->expects(static::never())->method('resolve');
        $this->dispatcher->expects(static::never())->method('dispatch');

        $out = iterator_to_array($this->resolver->resolve(
            new Request(),
            $this->argument(null)
        ));

        static::assertSame([], $out);
    }

    public function testReturnsEmptyWhenArgumentTypeIsNotAbstractDto(): void
    {
        $this->dtoResolver->expects(static::never())->method('resolve');
        $this->dispatcher->expects(static::never())->method('dispatch');

        $out = iterator_to_array($this->resolver->resolve(
            new Request(),
            $this->argument(\stdClass::class)
        ));

        static::assertSame([], $out);
    }

    public function testResolvesDtoAndDispatchesResolvedEvent(): void
    {
        $request = new Request();
        $dto = new ScalarRequestDto();

        $this->dtoResolver->expects(static::once())
            ->method('resolve')
            ->with(ScalarRequestDto::class, $request)
            ->willReturn($dto);

        $this->dispatcher->expects(static::once())
            ->method('dispatch')
            ->with(static::callback(
                static fn (ResolvedEvent $event): bool => $event->getDto() === $dto
            ))
            ->willReturnArgument(0);

        $out = iterator_to_array($this->resolver->resolve(
            $request,
            $this->argument(ScalarRequestDto::class)
        ));

        static::assertSame([$dto], $out);
        static::assertFalse($dto->isOptional());
    }

    public function testSetsOptionalWhenAllowInvalidAttributePresent(): void
    {
        $request = new Request();
        $dto = new ScalarRequestDto();

        $this->dtoResolver->method('resolve')->willReturn($dto);
        $this->dispatcher->method('dispatch')->willReturnArgument(0);

        iterator_to_array($this->resolver->resolve(
            $request,
            $this->argument(ScalarRequestDto::class, [new AllowInvalid()])
        ));

        static::assertTrue($dto->isOptional());
    }

    /**
     * @param list<object> $attributes
     */
    private function argument(
        string|null $type,
        array $attributes = []
    ): ArgumentMetadata {
        return new ArgumentMetadata(
            name: 'dto',
            type: $type,
            isVariadic: false,
            hasDefaultValue: false,
            defaultValue: null,
            attributes: $attributes,
        );
    }
}
