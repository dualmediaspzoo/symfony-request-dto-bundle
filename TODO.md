# V4 Rewrite Progress

## Metadata

- [x] `DtoMetadata` — top-level model wrapping the property tree for a DTO class
- [x] `PropertyMetadata` builder — fluent builder that outputs a sealed `Property`
- [x] `FieldMetadata` — per-field model for Find attribute fields (path, type, constraints)
- [x] `MetadataRegistry` — serves cached metadata by FQCN, falls back to runtime reflection
- [x] Cache warmer — iterates `dm.dto_bundle.dto` tagged classes, extracts metadata,
  serializes the model tree via `PhpFilesAdapter`
- [x] `requiresRuntimeResolve` detection — try/catch serialization of constraints at warm-up,
  flag properties that fail
- [x] `Property` — readonly metadata model
- [x] `BagEnum` — request bag enum

## Coercion

- [x] `CoercerService` — O(1) coercer lookup by type/FQCN from pre-built map,
  no `supports()` scan at runtime
- [x] `EnumCoercer` — backed enum coercion with label processing and `FromKey` support
- [x] `DateTimeImmutableCoercer` — date parsing with configurable format
- [x] `UploadedFileCoercer` — validates uploaded file instances
- [x] Coercer warm-up — resolve `#[Supports]` closures against property metadata at cache
  warm-up, store coercer key in `PropertyMetadata`
- [x] `CoercerInterface` — pure transform returning value + constraints, no validator
- [x] `Result` model — holds coerced value + constraints for batch validation
- [x] `#[Supports]` attribute — static closure on coercer class for warm-up matching
- [x] `BooleanCoercer`
- [x] `IntegerCoercer`
- [x] `FloatCoercer`
- [x] `StringCoercer`

## Validation

- [x] Batch validation pass — single `$validator->startContext()` collecting constraints
  from all coercion results + property metadata
- [x] Pre-check phase — validate preconditions (e.g. "is collection actually an array?")
  before coercion runs
- [x] `WhenVisited` constraint + validator — conditional validation based on visited state
- [ ] `MappedToPath` constraint + validator — attach constraints to specific property path
- [ ] `ArrayAll` constraint + validator
- [ ] `ObjectCollection` constraint + validator
- [ ] Validation group support — `GroupProviderService` for dynamic groups

## Resolution

- [x] `DtoResolverService` — walks cached metadata tree, delegates to extraction,
  coercion, validation, entity loading
- [x] `RequestDataExtractor` — reads values from request bags (query, body, headers, files),
  replaces the old `safeGetPath` logic
- [x] `PropertyResolver` — handles regular scalar/object property resolution
- [ ] `FindResolver` — handles `FindOneBy`/`FindBy` entity loading
- [x] `NestedDtoResolver` — handles recursive DTO-in-DTO resolution, collects coercion
  results up the tree

## Entity Loading

- [x] `EntityProviderService` — registry of entity providers keyed by FQCN
- [ ] `TargetProviderService` — Doctrine repository auto-wiring
- [ ] `ComplexLoaderService` — custom loader implementations for non-standard lookups
- [x] `QueryCreator` — ORM query building for Find attributes - done from dualmedia/query-creator
- [x] `ReferenceHelper` — Doctrine association/reference handling - same as above
- [ ] Label processors — `DefaultProcessor`, `PascalCaseProcessor`, `LabelProcessorService`

## Attributes

- [x] `FindOneBy` — single entity lookup with fields, constraints, types
- [x] `FindBy` — collection entity lookup with limit/offset
- [ ] `FindComplex` — custom complex loader reference
- [x] `Type` — explicit type override -- no longer needed, using symfony's TypeInfo
- [x] `Format` — datetime format specifier
- [x] `FromKey` — enum label key mapping
- [x] `AsDoctrineReference` — treat as Doctrine entity reference
- [ ] `ProvideValidationGroups` — custom validation group provider
- [ ] `Http/OnNull` — HTTP action when property is null
- [ ] `EntityProvider` — marks custom entity providers
- [ ] `AllowInvalid` — allows DTO resolution to proceed despite validation failures
- [x] `AllowEnum` — allowed enum case filtering
- [x] `Bag` — request bag assignment
- [x] `Path` — custom request path override

## Events

- [x] `DtoResolvedEvent` — dispatched after successful resolution
- [ ] `DtoInvalidEvent` — dispatched after validation failure, allows custom response
- [ ] `DtoActionEvent` — dispatched when DTO has HTTP action attributes
- [ ] `DtoSubscriber` — listens to controller arguments, dispatches action/invalid events

## HTTP Integration

- [x] `DtoValueResolver` — Symfony `ValueResolverInterface` entry point
- [ ] `ActionValidatorService` — validates HTTP action attributes
- [ ] `OnNullActionValidator` — handles null property HTTP actions

## DependencyInjection

- [ ] `services.php` — main service definitions with new architecture
- [ ] `services_dev.php` — profiler decorator wiring
- [ ] `services_test.php` — test service overrides
- [ ] `DtoBundle` — update `build()` for new compiler passes and tags
- [ ] Compiler passes — review and update for new service structure

## Profiling

- [ ] `AbstractWrapper` — base profiling decorator
- [ ] Resolver profiler — wraps `DtoResolverService`
- [ ] Entity provider profiler — wraps `EntityProviderService`
- [ ] Type validation profiler — wraps batch validation

## Nelmio Integration

- [ ] `DtoOADescriber` — OpenAPI schema generation from cached metadata

## DTO Base

- [x] `AbstractDto` — base class with visited tracking and constraint collection
- [x] `DtoInterface`