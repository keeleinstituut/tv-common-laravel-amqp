<?php

namespace AuditLogClient\Util;

use Illuminate\Support\Arr;

class ArrayUtil
{
    /**
     * @return array{ array, array }
     */
    public static function multiDiffPairs(array $first, array $second): array
    {
        return [
            static::computeMultiDimensionalDiff($first, $second),
            static::computeMultiDimensionalDiff($second, $first),
        ];
    }

    public static function computeMultiDimensionalDiff(array $firstOperand, array $secondOperand): array
    {
        return static::computeMultiDimensionalDiffConsideringOrder(
            Arr::sortRecursive($firstOperand),
            Arr::sortRecursive($secondOperand)
        );
    }

    /**
     * @copyright Roger Vilà
     * @author Roger Vilà
     * @license MIT
     *
     * @note Adapted from source code of rogervila/array-diff-multidimensional on 25/10/2023.
     *
     * @link https://github.com/rogervila/array-diff-multidimensional/blob/1c3b418c915f1fad3659e679dd3401a66a224178/src/ArrayDiffMultidimensional.php
     */
    public static function computeMultiDimensionalDiffConsideringOrder(array $array1, ?array $array2): array
    {
        $result = [];

        if ($array2 == null) {
            return $array1;
        }

        if (array_is_list($array1) && array_is_list($array2)) {
            return $array1 === $array2 ? [] : $array1;
        }

        if (array_is_list($array1) && ! array_is_list($array2) || array_is_list($array2) && ! array_is_list($array1)) {
            return $array1;
        }

        foreach ($array1 as $key => $value) {
            if (! array_key_exists($key, $array2)) {
                $result[$key] = $value;

                continue;
            }

            if (is_array($value) && count($value) > 0) {
                $recursiveArrayDiff = static::computeMultiDimensionalDiffConsideringOrder($value, $array2[$key]);

                if (count($recursiveArrayDiff) > 0) {
                    $result[$key] = $recursiveArrayDiff;
                }

                continue;
            }

            $value1 = $value;
            $value2 = $array2[$key];

            if (is_float($value1) && is_float($value2)) {
                $value1 = (string) $value1;
                $value2 = (string) $value2;
            }

            if ($value1 !== $value2) {
                $result[$key] = $value;
            }
        }

        return $result;
    }
}
