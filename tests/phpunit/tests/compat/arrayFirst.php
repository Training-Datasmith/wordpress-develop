<?php

declare(strict_types=1);

/**
 * @group compat
 *
 * @covers ::array_first
 */
class Tests_Compat_arrayFirst extends WP_UnitTestCase
{
    /**
     * @ticket 63853
     *
     * Test that array_first() is always available (either from PHP or WP).
     */
    public function test_array_first_availability(): void
    {
        $this->assertTrue(function_exists('array_first'));
    }

    /**
     * @ticket 63853
     *
     * @dataProvider data_array_first
     *
     * @param mixed $expected The value extracted from the given array.
     * @param array $arr      The array to get the first value from.
     */
    public function test_array_first($expected, $arr): void
    {
        $this->assertSame($expected, array_first($arr));
    }

    /**
     * Data provider.
     *
     * @return array[]
     */
    public function data_array_first(): array
    {
        $obj = new \stdClass();
        return [
            'string values'        => [
                'expected' => 'a',
                'arr'      => [ 'a', 'b', 'c' ],
            ],
            'associative array'    => [
                'expected' => 10,
                'arr'      => [
                    'foo' => 10,
                    'bar' => 20,
                ],
            ],
            'empty array'          => [
                'expected' => null,
                'arr'      => [],
            ],
            'single element array' => [
                'expected' => 42,
                'arr'      => [ 42 ],
            ],
            'null values'          => [
                'expected' => null,
                'arr'      => [ null, 'b', 'c' ],
            ],
            'objects'              => [
                'expected' => $obj,
                'arr'      => [
                    $obj,
                    1,
                    2,
                ],
            ],
            'boolean values'       => [
                'expected' => false,
                'arr'      => [ false, true, 1, 2, 3 ],
            ],
        ];
    }

    /**
     * Test that array_first() returns the pointer is not the first element.
     *
     * @ticket 63853
     */
    public function test_array_first_with_end_pointer()
    {
        $arr = [
            'key1' => 'val1',
            'key2' => 'val2',
        ];
        // change the pointer to the last element
        end($arr);

        $val = array_first($arr);
        $this->assertSame('val2', current($arr));
        $this->assertSame('val1', $val);
    }
}
