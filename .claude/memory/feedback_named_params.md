---
name: Use named parameters
description: Always use named parameters instead of array syntax for constructor/method calls
type: feedback
---

Always use named parameters instead of array-based syntax.
E.g. `new Type(type: 'bool')` not `new Type(['type' => 'bool'])`.

**Why:** User preference for readability and modern PHP style.
**How to apply:** Any constructor or method call — use named params over associative arrays.