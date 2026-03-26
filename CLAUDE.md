# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What This Is

A Symfony bundle that automatically resolves controller argument DTOs from HTTP request data. It handles type coercion, Doctrine entity loading, validation, and integrates with Nelmio API Docs. DTOs are configured via PHP 8 attributes.

## Commands

```bash
composer test              # Run PHPUnit test suite
composer lint              # Run PHP-CS-Fixer (applies fixes)
composer baseline          # Regenerate PHPStan baseline
vendor/bin/phpunit --filter=TestClassName  # Run a single test class
vendor/bin/phpunit --filter=testMethodName # Run a single test method
vendor/bin/php-cs-fixer fix --dry-run     # Check code style without fixing
vendor/bin/phpstan                         # Run static analysis
```

## Architecture

**Request flow:** Controller argument → `DtoValueResolver` → `DtoResolverService` → resolved DTO → `DtoSubscriber` dispatches action/invalid events.

**DtoResolverService** is the orchestrator. It delegates to:
- `TypeValidationHelper` — validates and coerces request data types
- `DtoTypeExtractorHelper` — extracts type metadata from DTO class reflection
- `EntityProviderService` — loads entities via Doctrine repositories
- `ComplexLoaderService` — loads entities via custom complex loaders
- `DynamicResolverService` — applies dynamic resolvers
- `CoercerService` — type casting pipeline (bool, int, float, string, enum, datetime, uploaded file)
- `GroupProviderService` — provides validation groups
- `ActionValidatorService` — validates HTTP action attributes

**Attributes** (in `src/Attribute/`) configure DTO behavior:
- `Dto/` — field-level: `Path`, `FindBy`, `FindOneBy`, `FindComplex`, `Type`, `Format`, `AllowEnum`, `FromKey`, `Bag`, `AllowInvalid`
- `Entity/` — `EntityProvider` marks custom entity providers
- `Parameter/` — controller parameter-level attributes

**Extension points** use Symfony service tags (defined as constants on `DtoBundle`):
- Coercers: `dm.dto_bundle.coercer`
- Dynamic resolvers: `dm.dto_bundle.dynamic_resolver`
- Entity providers: `dm.dto_bundle.entity_provider.pre_config`
- Complex loaders: `dm.dto_bundle.complex_loader`
- Validation group providers: `dm.dto_bundle.validation_group_provider`
- Label processors: `dm.dto_bundle.label_processor`

**Compiler passes** (registered in `DtoBundle::build()`) auto-discover and wire tagged services: Doctrine repositories, entity providers, complex loaders, validation groups, label processors.

**Profiling:** `services_dev.php` wraps core services with `Profiler/` decorators for Symfony profiler integration.

## Testing

- PHPUnit 12, config in `phpunit.xml.dist`
- Tests in `tests/Unit/`, fixtures in `tests/Fixtures/`
- Uses a `TestKernel` (`DualMedia\DtoRequestBundle\Tests\TestKernel`)
- CI requires 65-80% code coverage

## CI Matrix

GitHub Actions runs on PRs against Symfony 6.4 and 7.3 with PHP 8.4 and 8.5. Jobs: PHPStan, PHP-CS-Fixer (dry-run), PHPUnit with coverage.

## Requirements

PHP >= 8.4, Symfony 6.4+ or 7.3+. Optional: Doctrine ORM for entity loading, Nelmio API Doc for OpenAPI schema generation.