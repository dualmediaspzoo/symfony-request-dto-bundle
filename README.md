# Symfony DTO Bundle

This bundle aims to lower the burden of typechecking, casting, loading entities
and related annoyances of using requests in your api.

## Usage

1. Create a DTO class for your request

```php

use \DM\DtoRequestBundle\Attributes\Dto\Path;
use \DM\DtoRequestBundle\Model\AbstractDto;

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