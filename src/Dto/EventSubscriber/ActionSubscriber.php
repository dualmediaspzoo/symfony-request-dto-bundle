<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Dto\EventSubscriber;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use DualMedia\DtoRequestBundle\Dto\Enum\ActionCondition;
use DualMedia\DtoRequestBundle\Dto\Event\ActionEvent;
use DualMedia\DtoRequestBundle\Dto\Event\PropertyMetaEvent;
use DualMedia\DtoRequestBundle\Dto\Event\ResolvedEvent;
use DualMedia\DtoRequestBundle\Dto\Util\ActionConditionUtils;
use DualMedia\DtoRequestBundle\Metadata\Model\Action;
use DualMedia\DtoRequestBundle\MetadataUtils;
use DualMedia\DtoRequestBundle\Util;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;

class ActionSubscriber implements EventSubscriberInterface
{
    /**
     * Paths with Action metadata, keyed by normalized path.
     *
     * @var array<string, list<Action>>
     */
    private array $trackedPaths = [];

    /**
     * Triggered actions waiting to be dispatched on ControllerArgumentsEvent.
     *
     * @var list<array{AbstractDto, string, mixed, Action}>
     */
    private array $triggered = [];

    public function __construct(
        private readonly EventDispatcherInterface $dispatcher
    ) {
    }

    #[\Override]
    public static function getSubscribedEvents(): array
    {
        return [
            PropertyMetaEvent::class => 'onPropertyMeta',
            ResolvedEvent::class => 'onResolved',
            ControllerArgumentsEvent::class => ['onControllerArguments', 10],
        ];
    }

    public function onPropertyMeta(
        PropertyMetaEvent $event
    ): void {
        $actions = MetadataUtils::list(Action::class, $event->meta->meta);

        if ([] === $actions) {
            return;
        }

        $this->trackedPaths[$event->path] = $actions;
    }

    public function onResolved(
        ResolvedEvent $event
    ): void {
        if (empty($this->trackedPaths)) {
            return;
        }

        $dto = $event->getDto();
        $this->evaluateDto($dto, $dto, []);
    }

    public function onControllerArguments(
        ControllerArgumentsEvent $event
    ): void {
        if (empty($this->triggered)) {
            return;
        }

        $request = $event->getRequest();
        $requestType = $event->getRequestType();

        foreach ($this->triggered as [$dto, $property, $value, $action]) {
            $output = $this->dispatcher->dispatch(
                new ActionEvent($dto, $property, $value, $action, $request, $requestType)
            );

            if (null !== ($response = $output->getResponse())) {
                $event->setController(static fn (): Response => $response);
                break;
            }
        }

        $this->reset();
    }

    /**
     * @param list<string> $prefix
     */
    private function evaluateDto(
        AbstractDto $rootDto,
        AbstractDto $dto,
        array $prefix
    ): void {
        foreach ($this->trackedPaths as $normalizedPath => $actions) {
            // extract property name from the normalized path relative to current prefix
            $normalizedPrefix = Util::buildNonUniquePropertyPath($prefix);

            if ('' !== $normalizedPrefix) {
                if (!str_starts_with($normalizedPath, $normalizedPrefix.'.')) {
                    continue;
                }

                $remainder = substr($normalizedPath, strlen($normalizedPrefix) + 1);
            } else {
                $remainder = $normalizedPath;
            }

            // strip leading collection marker if present (e.g. "[].field" → "field")
            $remainder = ltrim($remainder, '[].');

            // the property name is the first segment (before '.' or '[]')
            if (false !== ($bracketPos = strpos($remainder, '['))) {
                $propertyName = substr($remainder, 0, $bracketPos);
                $hasMore = true;
            } elseif (false !== ($dotPos = strpos($remainder, '.'))) {
                $propertyName = substr($remainder, 0, $dotPos);
                $hasMore = true;
            } else {
                $propertyName = $remainder;
                $hasMore = false;
            }

            if ('' === $propertyName || !property_exists($dto, $propertyName)) {
                continue;
            }

            // this path goes deeper — recurse into nested DTOs/collections
            if ($hasMore) {
                $child = $dto->{$propertyName};

                if ($child instanceof AbstractDto) {
                    $this->evaluateDto($rootDto, $child, [...$prefix, $propertyName]);
                } elseif (is_iterable($child)) {
                    foreach ($child as $index => $item) {
                        if ($item instanceof AbstractDto) {
                            $this->evaluateDto($rootDto, $item, [...$prefix, $propertyName, (string)$index]);
                        }
                    }
                }

                continue;
            }

            $value = $dto->{$propertyName};

            foreach ($actions as $action) {
                $check = $action->when instanceof ActionCondition
                    ? ActionConditionUtils::resolve($action->when)
                    : $action->when;

                if ($check($value)) {
                    $this->triggered[] = [$rootDto->getHighestParentDto(), $normalizedPath, $value, $action];

                    break; // first matching action wins for this property
                }
            }
        }
    }

    private function reset(): void
    {
        $this->trackedPaths = [];
        $this->triggered = [];
    }
}
