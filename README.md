# Symfony DTO Bundle

![Code Coverage](https://camo.githubusercontent.com/ffe54b3b9a48d4d6bd374e2630b48e088c99858500db95ebed37184e8c1a6a3b/68747470733a2f2f696d672e736869656c64732e696f2f62616467652f436f6465253230436f7665726167652d38342532352d737563636573733f7374796c653d666c6174)

This bundle aims to lower the burden of typechecking, casting, loading entities
and related annoyances of using requests in your api.

## Usage

1. Create a DTO class for your request

```php

use \DualMedia\DtoRequestBundle\Attributes\Dto\Path;
use \DualMedia\DtoRequestBundle\Model\AbstractDto;

class MyDto extends AbstractDto
{
    public ?int $myVar = null;
    
    #[Path("custom_path")]
    public ?string $myString = null;
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

## Docs

Currently no documentation is available, but will be added in the future. For the time being see [the DTO models for tests](tests/Fixtures/Model/Dto)

## Missing features

Currently, some setup is required. Doctrine will not automatically supply entities at this point
and a custom CompilerPass is needed. This will be resolved with an update for the bundle in the future.