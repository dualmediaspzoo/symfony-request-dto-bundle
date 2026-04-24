# Examples

## Enums and dates

```php
use DualMedia\DtoRequestBundle\Dto\AbstractDto;

enum Status: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Pending = 'pending';
}

// input:
// [
//     'status' => 'active',
//     'createdAt' => '2025-01-15',
// ]

class SimpleFilterDto extends AbstractDto
{
    public Status|null $status = null;

    public \DateTimeImmutable|null $createdAt = null;
}
```

```php
use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use DualMedia\DtoRequestBundle\Dto\Attribute\Format;
use DualMedia\DtoRequestBundle\Dto\Attribute\FromKey;
use DualMedia\DtoRequestBundle\Dto\Attribute\WithAllowedEnum;

// input:
// [
//     'status' => 'Active',       <- matched by case name, not backed value
//     'publishedAt' => '15/01/2025 14:30',
// ]

class AdvancedFilterDto extends AbstractDto
{
    #[FromKey]                                             // match by case name instead of backed value
    #[WithAllowedEnum([Status::Active, Status::Pending])] // reject Status::Inactive
    public Status|null $status = null;

    #[Format('d/m/Y H:i')]
    public \DateTimeImmutable|null $publishedAt = null;
}
```

## Nested DTOs

```php
use DualMedia\DtoRequestBundle\Dto\AbstractDto;

// input:
// [
//     'name' => 'John',
//     'filter' => [
//         'status' => 'active',
//         'createdAt' => '2025-01-15',
//     ],
// ]

class SingleChildDto extends AbstractDto
{
    public string|null $name = null;

    public SimpleFilterDto|null $filter = null;
}
```

```php
use DualMedia\DtoRequestBundle\Dto\AbstractDto;

// input:
// [
//     'name' => 'Batch update',
//     'items' => [
//         ['status' => 'active', 'createdAt' => '2025-01-15'],
//         ['status' => 'inactive', 'createdAt' => '2025-02-20'],
//     ],
// ]

class ListChildDto extends AbstractDto
{
    public string|null $name = null;

    /** @var list<SimpleFilterDto> */
    public array $items = [];
}
```

## Loading entities

```php
use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use DualMedia\DtoRequestBundle\Dto\Attribute\Field;
use DualMedia\DtoRequestBundle\Dto\Attribute\FindOneBy;

// input:
// [
//     'userId' => '42',
// ]

class SimpleEntityDto extends AbstractDto
{
    #[FindOneBy]
    #[Field('id', 'userId')]
    public User|null $user = null;
}
```

```php
use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use DualMedia\DtoRequestBundle\Dto\Attribute\Field;
use DualMedia\DtoRequestBundle\Dto\Attribute\FindOneBy;
use Symfony\Component\TypeInfo\Type\BuiltinType;
use Symfony\Component\TypeInfo\TypeIdentifier;
use Symfony\Component\Validator\Constraints as Assert;

// input:
// [
//     'userId' => '42',
// ]
//
// violations when userId is missing or negative:
//   path "userId" => NotNull / Positive

class ConstrainedEntityDto extends AbstractDto
{
    #[FindOneBy]
    #[Field('id', 'userId', new BuiltinType(TypeIdentifier::INT), [new Assert\NotNull(), new Assert\Positive()])]
    public User|null $user = null;
}
```

```php
use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use DualMedia\DtoRequestBundle\Dto\Attribute\Field;
use DualMedia\DtoRequestBundle\Dto\Attribute\FindOneBy;
use DualMedia\DtoRequestBundle\Dto\Attribute\WithErrorPath;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

// input:
// [
//     'userId' => '42',
// ]
//
// Field-level violations (missing/invalid userId):
//   path "userId"
//
// Property-level violations (entity loaded but rejected by callback):
//   path "userId" by default (first non-dynamic field input)
//   path "user_error" if #[WithErrorPath] is used (see below)

class AssertedEntityDto extends AbstractDto
{
    #[FindOneBy]
    #[Field('id', 'userId')]
    #[WithErrorPath('user_error')]     // violations on the property itself use this path
    #[Assert\Callback(callback: static function (mixed $value, ExecutionContextInterface $context): void {
        if (null !== $value && !$value->isActive()) {
            $context->buildViolation('User is not active.')
                ->addViolation();
        }
    })]
    public User|null $user = null;
}
```

## Dotted paths

`#[Path(...)]` with dots walks the request array segment by segment, so a single
flat property can reach arbitrarily deep into nested input. The OpenAPI schema
is also emitted as nested `object` properties matching the path.

```php
use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use DualMedia\DtoRequestBundle\Dto\Attribute\Path;

// input:
// [
//     'inner' => [
//         'child' => [
//             'something' => [
//                 'here' => 'field data',
//             ],
//         ],
//     ],
// ]
//
// OpenAPI request body:
// properties:
//   inner:
//     type: object
//     properties:
//       child:
//         type: object
//         properties:
//           something:
//             type: object
//             properties:
//               here:
//                 type: string

class DeepDottedPathDto extends AbstractDto
{
    #[Path('inner.child.something.here')]
    public string|null $here = null;
}
```

## Root-level DTOs

`#[AsRoot]` reads the child DTO's fields directly from the parent's request bag,
without an extra nesting key.

```php
use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use DualMedia\DtoRequestBundle\Dto\Attribute\AsRoot;

// input (note: no "items" key, data is at the root):
// [
//     ['status' => 'active', 'createdAt' => '2025-01-15'],
//     ['status' => 'inactive', 'createdAt' => '2025-02-20'],
// ]

class RootListDto extends AbstractDto
{
    /** @var list<SimpleFilterDto> */
    #[AsRoot]
    public array $items = [];
}
```
