<?php

declare(strict_types=1);

/**
 * Tests for the absint() function.
 *
 * @group functions
 *
 * @covers ::absint
 */
class Tests_Functions_Absint extends WP_UnitTestCase
{
    /**
     * @ticket 60101
     *
     * @dataProvider data_absint
     */
    public function test_absint($test_value, $expected_value)
    {
        $this->assertSame($expected_value, absint($test_value));
    }

    /**
     * Data provider.
     *
     * @return array[] Test parameters {
     *     @type string $test_value Test value.
     *     @type string $expected   Expected return value.
     * }
     */
    public function data_absint()
    {
        return [
            '1 int'                 => [
                'test_value'     => 1,
                'expected_value' => 1,
            ],
            '1 string'              => [
                'test_value'     => '1',
                'expected_value' => 1,
            ],
            '-1 int'                => [
                'test_value'     => -1,
                'expected_value' => 1,
            ],
            '-1 string'             => [
                'test_value'     => '-1',
                'expected_value' => 1,
            ],
            '9.1 float'             => [
                'test_value'     => 9.1,
                'expected_value' => 9,
            ],
            '9.9 float'             => [
                'test_value'     => 9.9,
                'expected_value' => 9,
            ],
            'string'                => [
                'test_value'     => 'string',
                'expected_value' => 0,
            ],
            'string_1'              => [
                'test_value'     => 'string_1',
                'expected_value' => 0,
            ],
            '999_string'            => [
                'test_value'     => '999_string',
                'expected_value' => 999,
            ],
            '99 string with spaces' => [
                'test_value'     => '99 string with spaces',
                'expected_value' => 99,
            ],
            '99 array'              => [
                'test_value'     => [ 99 ],
                'expected_value' => 1,
            ],
            '99 string array'       => [
                'test_value'     => [ '99' ],
                'expected_value' => 1,
            ],
        ];
    }
}
