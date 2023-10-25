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
            static::computeMultiDimensionalAssocDiff($first, $second),
            static::computeMultiDimensionalAssocDiff($second, $first),
        ];
    }

    public static function computeMultiDimensionalAssocDiff(array $firstOperand, array $secondOperand): array
    {
        $sortedDottedFirst = Arr::dot(Arr::sortRecursive($firstOperand));
        $sortedDottedSecond = Arr::dot(Arr::sortRecursive($secondOperand));

        return collect($sortedDottedFirst)
            ->diffAssoc($sortedDottedSecond)
            ->undot()
            ->all();
    }
}
