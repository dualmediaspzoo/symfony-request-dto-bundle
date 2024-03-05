# Symfony DTO Bundle

![Code Coverage](https://camo.githubusercontent.com/e8b50014309ca69d187dae1eb8f3a522910f0c6971e42404e768a79fa2f2b505/68747470733a2f2f696d672e736869656c64732e696f2f62616467652f436f6465253230436f7665726167652d38342532352d737563636573733f7374796c653d666c6174)
[![Packagist Downloads](https://img.shields.io/packagist/dt/dualmedia/symfony-request-dto-bundle)](https://packagist.org/packages/dualmedia/symfony-request-dto-bundle)

This bundle aims to lower the burden of typechecking, casting, loading entities
and related annoyances of using requests in your api.

Bundle will automatically hook into [Doctrine ORM Bundle](https://github.com/doctrine/DoctrineBundle) and [Nelmio API Docs Bundle](https://github.com/nelmio/NelmioApiDocBundle) so no additional configuration should be needed.

## Install

Simply `composer require dualmedia/symfony-request-dto-bundle`, if applicable your Doctrine entity managers will be detected automatically and used as default providers for classes to be loaded with your requests if needed.

Then add the bundle to your `config/bundles.php` file like so

```php
return [
    Symfony\Bundle\FrameworkBundle\FrameworkBundle::class => ['all' => true],
    // other bundles ...
    DualMedia\DtoRequestBundle\DtoBundle::class => ['all' => true],
];
```

## Upgrades

See [CHANGES.md](CHANGES.md)

## Usage

1. Create a DTO class for your request

```php

use \DualMedia\DtoRequestBundle\Attributes\Dto\Path;
use \DualMedia\DtoRequestBundle\Model\AbstractDto;

class MyDto extends AbstractDto
{
    public int|null $myVar = null;
    
    #[Path("custom_path")]
    public string|null $myString = null;
}

```

2. Add your dto as a controller argument

```php

class MyController extends \Symfony\Bundle\FrameworkBundle\Controller\AbstractController
{
    public function myAction(MyDto $dto): \Symfony\Component\HttpFoundation\Response
    {
        // your dto here is already validated!
    }
}
```

## Application wide handling of DTO issues

If you wish to automatically return a 4XX response code when a dto has failed validation you may use something like the following:

```yaml
# config/services.yaml
App\EventSubscriber\ErrorSubscriber:
  decorates: exception_listener
  arguments:
    - '@App\EventSubscriber\ErrorSubscriber.inner'
```

```php
class ErrorSubscriber implements \Symfony\Component\EventDispatcher\EventSubscriberInterface
{
    public function __construct(
        private readonly ErrorListener $decorated
    ) {
    }

    public static function getSubscribedEvents(){
        return [
            \Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent::class => 'onControllerArguments',
        ];
    }
    
    public function onControllerArguments(
        ControllerArgumentsEvent $event
    ): void {
        $this->decorated->onControllerArguments($event);

        $violationList = new ConstraintViolationList();

        foreach ($event->getArguments() as $argument) {
            if ($argument instanceof DtoInterface
                && !$argument->isOptional()
                && !$argument->isValid()) {
                $violationList->addAll($argument->getConstraintViolationList());
            }
        }

        if (0 !== $violationList->count()) {
            throw new ValidatorException($violationList); // handle and display, or just do whatever really
        }
    }
}
```

If you want to map a class-wide assert to a path without having to directly modify the constraint itself you may wrap it in MappedToPath

```php

use \DualMedia\DtoRequestBundle\Constraints\MappedToPath;
use \DualMedia\DtoRequestBundle\Model\AbstractDto;
use Symfony\Component\Validator\Constraints as Assert;

#[MappedToPath(
    'property',
    new Assert\Expression(
        'this.property != null',
        message: 'This property cannot be null'
    )
)]
class MyDto extends AbstractDto
{
    public int|null $property = null;
}

```

## Docs


Currently no documentation is available, but will be added in the future. For the time being see [the DTO models for tests](tests/Fixtures/Model/Dto)
