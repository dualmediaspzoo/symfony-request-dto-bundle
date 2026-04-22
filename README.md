# Symfony DTO Bundle

[![Packagist Downloads](https://img.shields.io/packagist/dt/dualmedia/symfony-request-dto-bundle)](https://packagist.org/packages/dualmedia/symfony-request-dto-bundle)

This bundle aims to lower the burden of typechecking, casting, loading entities
and related annoyances of using requests in your api.

Bundle will automatically hook into [Doctrine ORM Bundle](https://github.com/doctrine/DoctrineBundle) and [Nelmio API Docs Bundle](https://github.com/nelmio/NelmioApiDocBundle) so no additional configuration should be needed.

## Features

- Automatic DTO resolution from controller arguments — no manual extraction, casting, or validation wiring
- Type coercion for scalars, enums (backed or by case name), dates, and uploaded files
- Doctrine entity loading directly from request fields (`#[FindOneBy]`, `#[FindBy]`)
- Validator integration — Symfony constraints on properties and fields are enforced in a single pass
- Nested DTOs, collections of DTOs, and `#[AsRoot]` for flat top-level payloads
- Nelmio API Doc integration — parameters, request bodies, enum cases, formats, and PHPDoc descriptions are described automatically
- Configurable request bags (query, body, headers, cookies, attributes, files) via `#[Bag]`
- Configurable custom `#[Action]`s for fields, entities and more. Set custom responses via an easy to handle event.
- Event hooks for resolved / invalid / action scenarios — return custom responses from listeners
- Symfony profiler panel with per-request DTO resolution timings
- Cached metadata via opcache-backed PHP files — near-zero reflection cost after warm-up

## Install

```
composer require dualmedia/symfony-request-dto-bundle
```

Then add the bundle to your `config/bundles.php` file like so

```php
return [
    Symfony\Bundle\FrameworkBundle\FrameworkBundle::class => ['all' => true],
    // other bundles ...
    DualMedia\DtoRequestBundle\DtoBundle::class => ['all' => true],
];
```

If applicable your Doctrine entity managers will be detected automatically and used as default providers for classes to be loaded with your requests if needed.

## Quick start

```php
use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use DualMedia\DtoRequestBundle\Dto\Attribute\Bag;
use DualMedia\DtoRequestBundle\Metadata\Enum\BagEnum;
use Symfony\Component\HttpFoundation\File\UploadedFile;

// input:
// [
//     'name' => 'John',
//     'age' => '25',
//     'score' => '9.5',
//     'active' => '1',
//     'avatar' => <UploadedFile>,
// ]

class ProfileDto extends AbstractDto
{
    public string|null $name = null;

    public int|null $age = null;

    public float|null $score = null;

    public bool|null $active = null;

    #[Bag(BagEnum::Files)]
    public UploadedFile|null $avatar = null;
}
```

## More examples

See [EXAMPLES.md](EXAMPLES.md) for enums, dates, nested DTOs, entity loading, and root-level payloads.

## Upgrades

See [CHANGES.md](CHANGES.md)
