<?php

namespace SyncTools\Tests\Util;

use AuditLogClient\Enums\AuditLogEventObjectType;
use AuditLogClient\Util\ArrayUtil;
use Illuminate\Support\Arr;
use Orchestra\Testbench\TestCase;

class ArrayUtilTest extends TestCase
{
    /** @return array<array{ AuditLogEventObjectType }> */
    public static function provideDiffArgumentsAndExpectedResults(): array
    {
        $argumentsAndExpectedResults = [
            [[], [], []],
            [['id' => 1], ['id' => 1], []],
            [['id' => 1], ['id' => 2], ['id' => 1]],
            [['items' => []], ['items' => []], []],
            [['items' => [1, 2]], ['items' => [2]], ['items' => [1, 2]]],
            [['items' => [['id' => 1], ['id' => 2]]], ['items' => [['id' => 1], ['id' => 2]]], []],
            [['items' => [['id' => 1], ['id' => 2]]], ['items' => [['id' => 2], ['id' => 1]]], []],
            [['items' => [['id' => 1, 'name' => 'A'], ['id' => 2, 'name' => 'A']]], ['items' => [['id' => 1, 'name' => 'A'], ['id' => 2, 'name' => 'B']]], ['items' => [['id' => 1, 'name' => 'A'], ['id' => 2, 'name' => 'A']]]],
            [['object' => ['id' => 1]], ['object' => ['id' => 1]], []],
            [['object' => ['id' => 1, 'name' => 'Test']], ['object' => ['id' => 1]], ['object' => ['name' => 'Test']]],
        ];

        return collect($argumentsAndExpectedResults)
            ->mapWithKeys(function (array $row) {
                [$firstOperand, $secondOperand, $expectedResult] = $row;
                $firstOperandJSON = json_encode($firstOperand);
                $secondOperandJSON = json_encode($secondOperand);
                $expectedResultJSON = json_encode($expectedResult);

                return [
                    "$firstOperandJSON + $secondOperandJSON => $expectedResultJSON" => $row,
                ];
            })
            ->all();
    }

    /** @dataProvider provideDiffArgumentsAndExpectedResults */
    public function test_computeMultiDimensionalAssocDiff(array $firstOperand, array $secondOperand, array $expectedResult)
    {
        $actualResult = ArrayUtil::computeMultiDimensionalDiff($firstOperand, $secondOperand);

        static::assertEquals(
            Arr::sortRecursive($expectedResult),
            Arr::sortRecursive($actualResult)
        );
    }
}
