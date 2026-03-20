<?php

declare(strict_types=1);

/**
 * @group compat
 *
 * @covers ::array_last
 */
class Tests_Compat_arrayLast extends WP_UnitTestCase
{
    /**
     * @ticket 63853
     *
     * Test that array_last() is always available (either from PHP or WP).
     */
    public function test_array_last_availability(): void
    {
        $this->assertTrue(function_exists('array_last'));
    }

    /**
     * @ticket 63853
     *
     * @dataProvider data_array_last
     *
     * @param mixed $expected The expected last value.
     * @param array $arr      The array to get the last value from.
     */
    public function test_array_last($expected, $arr): void
    {
        $this->assertSame($expected, array_last($arr));
    }

    /**
     * Data provider for array_last().
     *
     * @return array[]
     */
    public function data_array_last(): array
    {
        $obj = new \stdClass();
        return [
            'string values'          => [
                'expected' => 'c',
                'arr'      => [ 'a', 'b', 'c' ],
            ],
            'associative array'      => [
                'expected' => 20,
                'arr'      => [
                    'foo' => 10,
                    'bar' => 20,
                ],
            ],
            'empty array'            => [
                'expected' => null,
                'arr'      => [],
            ],
            'single element array'   => [
                'expected' => 42,
                'arr'      => [ 42 ],
            ],
            'null values'            => [
                'expected' => null,
                'arr'      => [ 'a', 'b', null ],
            ],
            'objects'                => [
                'expected' => $obj,
                'arr'      => [
                    1,
                    2,
                    $obj,
                ],
            ],
            'boolean values'         => [
                'expected' => false,
                'arr'      => [ true, false ],
            ],
            'null values in between' => [
                'expected' => 'c',
                'arr'      => [ 'a', null, 'b', 'c' ],
            ],
            'empty string values'    => [
                'expected' => '',
                'arr'      => [ 'a', 'b', '' ],
            ],
        ];
    }
}
