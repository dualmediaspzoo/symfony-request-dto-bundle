<?php

namespace DualMedia\DtoRequestBundle;

class Util
{
    private function __construct()
    {
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
}
