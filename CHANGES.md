# Breaking changes

## 3.x -> 4.0

Complete bundle rewrite.

Added new attributes to support the new composition based syntax.

* Violation paths for property-level constraints on entity properties (`#[FindOneBy]`/`#[FindBy]`)
  now correctly use the input field name instead of the PHP property name.

* Added `#[WithErrorPath]` attribute to override the violation path for entity properties.

* Added `#[AsRoot]` attribute to read a nested DTO's fields directly from the parent bag
  without an extra nesting key.

* Added `#[WithObjectProvider]` attribute for custom entity loading.

* Added `#[ValidateWithGroups]` attribute for dynamic validation groups.

* Added `#[WithAllowedEnum]` and `#[WithLabelProcessor]` attributes.

* Added `#[MappedToPath]` attribute.

* Support for unserializable constraints (closures) via runtime resolution.

## 2.x -> 3.0

* Variables being coerced no longer validate constraints from type properties, unless
they're being validated from `FindByX` attributes.

This behavior makes the bundle behave more in-line with how Symfony itself validates objects.

* Attribute `Valid` must now be used to validate sub-dtos as expected.

* Context in validation should now always be valid.
