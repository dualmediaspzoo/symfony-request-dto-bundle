<?php

namespace DualMedia\DtoRequestBundle;

use Symfony\Component\Validator\ConstraintViolationInterface;

class Util
{
    /**
     * array_merge_recursive does indeed merge arrays, but it converts values with duplicate
     * keys to arrays rather than overwriting the value in the first array with the duplicate
     * value in the second array, as array_merge does. I.e., with array_merge_recursive,
     * this happens (documented behavior):.
     *
     * array_merge_recursive(array('key' => 'org value'), array('key' => 'new value'));
     *     => array('key' => array('org value', 'new value'));
     *
     * mergeRecursively does not change the datatypes of the values in the arrays.
     * Matching keys' values in the second array overwrite those in the first array, as is the
     * case with array_merge, i.e.:
     *
     * mergeRecursively(array('key' => 'org value'), array('key' => 'new value'));
     *     => array('key' => array('new value'));
     *
     * @phpstan-ignore-next-line
     *
     * @param array<mixed, mixed> ...$arrays
     *
     * @phpstan-ignore-next-line
     *
     * @author Daniel <daniel (at) danielsmedegaardbuus (dot) dk>
     * @author Gabriel Sobrinho <gabriel (dot) sobrinho (at) gmail (dot) com>
     *
     * @psalm-pure
     */
    public static function mergeRecursively(
        array ...$arrays
    ): array {
        $merge =
            /**
             * @psalm-return array<array|mixed>
             */
            function (array $array1, array $array2): array {
                $merged = $array1;

                foreach ($array2 as $key => &$value) {
                    if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
                        $merged[$key] = self::mergeRecursively($merged[$key], $value);
                    } else {
                        $merged[$key] = $value;
                    }
                }

                return $merged;
            };

        $merged = $arrays[0];

        for ($i = 1; $i < count($arrays); ++$i) {
            /** @psalm-suppress ImpureFunctionCall */
            $merged = $merge($merged, $arrays[$i]);
        }

        return $merged;
    }

    /**
     * @param mixed[] $array
     */
    public static function removeIndexByConstraintViolation(
        array &$array,
        string $propertyPath,
        ConstraintViolationInterface $violation
    ): void {
        $index = (int)trim(
            mb_substr($violation->getPropertyPath(), mb_strlen($propertyPath)),
            '[]'
        );

        unset($array[$index]);
    }
}
