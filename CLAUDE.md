# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What This Is

A Symfony bundle that automatically resolves controller argument DTOs from HTTP request data. It handles type coercion, Doctrine entity loading, validation, and integrates with Nelmio API Docs. DTOs are configured via PHP 8 attributes.

## Commands

```bash
vendor/bin/phpunit             # Run PHPUnit test suite
composer lint              # Run PHP-CS-Fixer (applies fixes)
composer baseline          # Regenerate PHPStan baseline
vendor/bin/phpunit --filter=TestClassName  # Run a single test class
vendor/bin/phpunit --filter=testMethodName # Run a single test method
vendor/bin/php-cs-fixer fix --dry-run     # Check code style without fixing
vendor/bin/phpstan                         # Run static analysis
```

## Architecture

Everything in this bundle is DTO-related. The structure follows DDD principles organized by domain concern.

### Request Flow

```
HTTP Request
  → DtoValueResolver (Symfony entry point)
    → MetadataRegistry (cached DTO metadata tree)
    → DtoResolverService (orchestrator)
      → RequestDataExtractor (reads values from request bags)
      → CoercerService (transforms raw values → typed values)
      → Validator (single context, all constraints at once)
      → EntityProviderService / ComplexLoaderService (loads entities)
    → DtoSubscriber (dispatches action/invalid events)
```

### Domain Structure

**Metadata** — static DTO structure, extracted from attributes at warm-up time, cached.
- `DtoMetadata` — tree of property metadata for an entire DTO class
- `PropertyMetadata` — per-property: type, path, bag, coercer key, constraints, nested children
- `FieldMetadata` — per-field within Find attributes: path, type, constraints
- `MetadataRegistry` — serves cached metadata by FQCN, falls back to runtime reflection
- Constraint instances are serialized into the cache. Properties with unserializable constraints (e.g. closure-based) are flagged `requiresRuntimeResolve` and reflected at request time.

**Coercion** — transforms raw string input into typed values. No validation here.
- `CoercerInterface::coerce(Property, mixed): CoercionResult` — pure transformation, returns the coerced value + constraints to validate later
- One coercer per type (int, float, bool, enum, datetime, uploaded file).
- Coercer lookup is O(1) by type/FQCN from a pre-built map, cached from dto properties — no `supports()` scan at runtime.

**Validation** — runs once per DTO resolution, not per-property.
- Collects all constraints from coercion results + property metadata
- Executes in a single `$validator->startContext()` call
- Handles pre-checks (e.g. "is collection actually an array?") before coercion

**Resolution** — orchestrates the per-request flow.
- `DtoResolverService` — walks the metadata tree, delegates to extraction, coercion, validation, entity loading
- `RequestDataExtractor` — reads values from request bags (query, body, headers, files)
- `PropertyResolver`, `FindResolver`, `NestedDtoResolver` — handle each property category

**Entity Loading** — loads Doctrine entities from validated/coerced field values.
- `EntityProviderService` — registry of entity providers keyed by FQCN
- `ComplexLoaderService` — custom loader implementations for non-standard lookups

**Events** — extension points for consumers.
- `DtoResolvedEvent` — after successful resolution
- `DtoInvalidEvent` — after validation failure (allows custom response)
- `DtoActionEvent` — when DTO has HTTP action attributes

**Attributes** (in `src/Attribute/`) — the user-facing API for DTO configuration.
- `Dto/` — field-level: `Path`, `FindBy`, `FindOneBy`, `FindComplex`, `Type`, `Format`, `AllowEnum`, `FromKey`, `Bag`, `AllowInvalid`
- `Entity/` — `EntityProvider` marks custom entity providers
- `Parameter/` — controller parameter-level attributes

### Extension Points

Service tags (defined as constants on `DtoBundle`):
- Dynamic resolvers: `dm.dto_bundle.dynamic_resolver`
- Entity providers: `dm.dto_bundle.entity_provider.pre_config`
- Complex loaders: `dm.dto_bundle.complex_loader`
- Validation group providers: `dm.dto_bundle.validation_group_provider`
- Label processors: `dm.dto_bundle.label_processor`

Compiler passes (registered in `DtoBundle::build()`) auto-discover and wire tagged services.

### Profiling

`services_dev.php` wraps core services with `Profiler/` decorators for Symfony profiler integration.

### Caching Strategy

DTO classes are tagged `dm.dto_bundle.dto` at container compile time. A cache warmer iterates all tagged classes, extracts metadata via reflection, and serializes the resulting model tree.
Backed by Symfony's `PhpFilesAdapter` — cached metadata is `var_export`'d to PHP files and loaded via opcache, making deserialization effectively free after first load. At runtime, `MetadataRegistry` serves cached metadata — zero reflection for the common case. Properties flagged `requiresRuntimeResolve` fall back to reflection.

## Testing

- PHPUnit 12, config in `phpunit.xml.dist`
- Tests in `tests/Unit/`, fixtures in `tests/Fixtures/`
- Uses a `TestKernel` (`DualMedia\DtoRequestBundle\Tests\TestKernel`)
- CI requires 65-80% code coverage

## CI Matrix

GitHub Actions runs on PRs against Symfony 6.4 and 7.3 with PHP 8.4 and 8.5. Jobs: PHPStan, PHP-CS-Fixer (dry-run), PHPUnit with coverage.

## Requirements

PHP >= 8.4, Symfony 6.4+ or 7.3+. Optional: Doctrine ORM for entity loading, Nelmio API Doc for OpenAPI schema generation.

## Memory

Check .claude/memory/MEMORY.md for memory access