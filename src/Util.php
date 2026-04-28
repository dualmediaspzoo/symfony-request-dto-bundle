<?php

namespace DualMedia\DtoRequestBundle;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\GroupSequence;

class Util
{
    private function __construct()
    {
    }

    /**
     * Ensure the `Default` validation group is present in `$groups`.
     *
     * Without this, a `#[ValidateWithGroups]` closure that returns e.g.
     * `['extra']` would silently disable every Default-group constraint
     * (NotNull/NotBlank/Length/...) on the DTO.
     *
     * - `string` → wrapped into `[$g, 'Default']` (no-op when already `Default`).
     * - `GroupSequence` → cloned with `Default` appended when absent; left
     *   intact otherwise (the user's explicit ordering is preserved).
     * - `list<string|GroupSequence>` → `Default` appended when no plain
     *   `'Default'` token is present and no nested `GroupSequence` already
     *   covers it.
     *
     * @param string|GroupSequence|list<string|GroupSequence> $groups
     *
     * @return string|GroupSequence|list<string|GroupSequence>
     */
    public static function mergeDefaultGroup(
        string|GroupSequence|array $groups
    ): string|GroupSequence|array {
        if (is_string($groups)) {
            return Constraint::DEFAULT_GROUP === $groups ? $groups : [$groups, Constraint::DEFAULT_GROUP];
        }

        if ($groups instanceof GroupSequence) {
            if (self::groupsContainDefault($groups->groups)) {
                return $groups;
            }

            return new GroupSequence([...$groups->groups, Constraint::DEFAULT_GROUP]);
        }

        if (self::groupsContainDefault($groups)) {
            return $groups;
        }

        $groups[] = Constraint::DEFAULT_GROUP;

        return $groups;
    }

    /**
     * Builds a validation path from segments, using bracket syntax for numeric indices.
     *
     * e.g. ['children', '0', 'intField'] → 'children[0].intField'
     * e.g. ['parent', 'child', 'intField'] → 'parent.child.intField'
     *
     * @param list<string> $segments
     */
    public static function buildValidationPath(
        array $segments
    ): string {
        if ([] === $segments) {
            return '';
        }

        $path = ctype_digit($segments[0])
            ? '['.$segments[0].']'
            : $segments[0];

        for ($i = 1; $i < count($segments); ++$i) {
            if (ctype_digit($segments[$i])) {
                $path .= '['.$segments[$i].']';
            } else {
                $path .= '.'.$segments[$i];
            }
        }

        return $path;
    }

    /**
     * Builds a path with numeric indices replaced by [], suitable for deduplication across collections.
     *
     * e.g. ['children', '0', 'intField'] → 'children[].intField'
     *
     * @param list<string> $segments
     */
    public static function buildNonUniquePropertyPath(
        array $segments
    ): string {
        return (string)preg_replace('/\[\d+]/', '[]', self::buildValidationPath($segments));
    }

    public static function escapeCharactersForCache(
        string $text
    ): string {
        return strtr($text, '{}()/\@:', '<>_-,.?\'');
    }

    /**
     * Recursive scan: `Default` may appear at any nesting depth allowed by
     * Symfony's group representation — a plain string entry, an entry inside
     * a parallel-step sub-array within a `GroupSequence`, or inside a nested
     * `GroupSequence`.
     *
     * @param array<mixed, mixed> $groups
     */
    private static function groupsContainDefault(
        array $groups
    ): bool {
        foreach ($groups as $entry) {
            if (is_string($entry)) {
                if (Constraint::DEFAULT_GROUP === $entry) {
                    return true;
                }

                continue;
            }

            if ($entry instanceof GroupSequence) {
                if (self::groupsContainDefault($entry->groups)) {
                    return true;
                }

                continue;
            }

            if (is_array($entry) && self::groupsContainDefault($entry)) {
                return true;
            }
        }

        return false;
    }
}
