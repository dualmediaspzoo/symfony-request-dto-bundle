# Breaking changes

## 2.x -> 3.0

* Variables being coerced no longer validate constraints from type properties, unless
they're being validated from `FindByX` attributes.

This behavior makes the bundle behave more in-line with how Symfony itself validates objects.

* Attribute `Valid` must now be used to validate sub-dtos as expected.

* Context in validation should now always be valid.
