<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Feature\Resolver;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use DualMedia\DtoRequestBundle\Dto\Event\ActionEvent;
use DualMedia\DtoRequestBundle\Dto\Event\ResolvedEvent;
use DualMedia\DtoRequestBundle\Resolve\DtoResolver;
use DualMedia\DtoRequestBundle\Tests\Fixture\Dto\Action\ParentWithChildActionDto;
use DualMedia\DtoRequestBundle\Tests\Fixture\Dto\Action\ParentWithChildrenActionDto;
use DualMedia\DtoRequestBundle\Tests\Fixture\Dto\Action\SimpleActionDto;
use DualMedia\DtoRequestBundle\Tests\PHPUnit\KernelTestCase;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

#[Group('feature')]
#[Group('resolver')]
#[Group('action')]
class ActionDtoTest extends KernelTestCase
{
    private DtoResolver $resolver;
    private EventDispatcherInterface $dispatcher;

    /** @var list<ActionEvent> */
    private array $actionEvents = [];

    protected function setUp(): void
    {
        $this->resolver = static::getService(DtoResolver::class);
        $this->dispatcher = static::getService(EventDispatcherInterface::class);
        $this->actionEvents = [];

        $this->dispatcher->addListener(ActionEvent::class, function (ActionEvent $event): void {
            $this->actionEvents[] = $event;
        });
    }

    public function testSimpleActionTriggersWhenNull(): void
    {
        $this->resolveAndDispatch(
            SimpleActionDto::class,
            new Request(request: [])
        );

        static::assertCount(1, $this->actionEvents);
        static::assertNull($this->actionEvents[0]->getValue());
        static::assertSame(404, $this->actionEvents[0]->getAction()->statusCode);
    }

    public function testSimpleActionDoesNotTriggerWhenValueSet(): void
    {
        $this->resolveAndDispatch(
            SimpleActionDto::class,
            new Request(request: [
                'value' => '42',
            ])
        );

        static::assertCount(0, $this->actionEvents);
    }

    public function testChildActionTriggersWhenNull(): void
    {
        $this->resolveAndDispatch(
            ParentWithChildActionDto::class,
            new Request(request: [
                'parentValue' => '10',
                'child' => [
                    // actionable not set → null
                ],
            ])
        );

        static::assertCount(1, $this->actionEvents);
        static::assertNull($this->actionEvents[0]->getValue());
        static::assertSame(404, $this->actionEvents[0]->getAction()->statusCode);
    }

    public function testChildActionDoesNotTriggerWhenValueSet(): void
    {
        $this->resolveAndDispatch(
            ParentWithChildActionDto::class,
            new Request(request: [
                'parentValue' => '10',
                'child' => [
                    'actionable' => '5',
                ],
            ])
        );

        static::assertCount(0, $this->actionEvents);
    }

    public function testCollectionChildActionTriggersWhenNull(): void
    {
        $this->resolveAndDispatch(
            ParentWithChildrenActionDto::class,
            new Request(request: [
                'parentValue' => '10',
                'children' => [
                    ['actionable' => '5'],
                    [], // actionable null
                    ['actionable' => '3'],
                ],
            ])
        );

        static::assertCount(1, $this->actionEvents);
    }

    public function testCollectionChildActionDoesNotTriggerWhenAllSet(): void
    {
        $this->resolveAndDispatch(
            ParentWithChildrenActionDto::class,
            new Request(request: [
                'parentValue' => '10',
                'children' => [
                    ['actionable' => '1'],
                    ['actionable' => '2'],
                ],
            ])
        );

        static::assertCount(0, $this->actionEvents);
    }

    public function testCollectionAllChildrenNullTriggersForEach(): void
    {
        $this->resolveAndDispatch(
            ParentWithChildrenActionDto::class,
            new Request(request: [
                'parentValue' => '10',
                'children' => [
                    [],
                    [],
                    [],
                ],
            ])
        );

        static::assertCount(3, $this->actionEvents);
    }

    /**
     * @param class-string<AbstractDto> $class
     */
    private function resolveAndDispatch(
        string $class,
        Request $request
    ): void {
        $dto = $this->resolver->resolve($class, $request);

        $this->dispatcher->dispatch(new ResolvedEvent($dto));

        $kernel = static::$kernel;
        assert($kernel instanceof HttpKernelInterface);

        $controllerEvent = new ControllerArgumentsEvent(
            $kernel,
            static fn () => null,
            [$dto],
            $request,
            HttpKernelInterface::MAIN_REQUEST
        );

        $this->dispatcher->dispatch($controllerEvent, KernelEvents::CONTROLLER_ARGUMENTS);
    }
}
