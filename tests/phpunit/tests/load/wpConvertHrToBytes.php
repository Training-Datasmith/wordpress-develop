<?php

declare(strict_types=1);

/**
 * Tests for wp_convert_hr_to_bytes().
 *
 * @group load
 *
 * @covers ::wp_convert_hr_to_bytes
 */
class Tests_Load_wpConvertHrToBytes extends WP_UnitTestCase
{
    /**
     * Tests converting (PHP ini) byte values to integer byte values.
     *
     * @ticket 32075
     *
     * @dataProvider data_wp_convert_hr_to_bytes
     *
     * @param int|string $value    The value passed to wp_convert_hr_to_bytes().
     * @param int        $expected The expected output of wp_convert_hr_to_bytes().
     */
    public function test_wp_convert_hr_to_bytes($value, $expected)
    {
        $this->assertSame($expected, wp_convert_hr_to_bytes($value));
    }

    /**
     * Data provider for test_wp_convert_hr_to_bytes().
     *
     * @return array {
     *     @type array {
     *         @type int|string $value    The value passed to wp_convert_hr_to_bytes().
     *         @type int        $expected The expected output of wp_convert_hr_to_bytes().
     *     }
     * }
     */
    public function data_wp_convert_hr_to_bytes()
    {
        $array = [
            // Integer input.
            [ -1, -1 ], // = no memory limit.
            [ 8388608, 8388608 ], // 8M.

            // String input (memory limit shorthand values).
            [ '32k', 32768 ],
            [ '64K', 65536 ],
            [ '128m', 134217728 ],
            [ '256M', 268435456 ],
            [ '1g', 1073741824 ],
            [ '128m ', 134217728 ], // Leading/trailing whitespace gets trimmed.
            [ '1024', 1024 ], // No letter will be interpreted as integer value.

            // Edge cases.
            [ 'g', 0 ],
            [ 'g1', 0 ],
            [ 'null', 0 ],
            [ 'off', 0 ],
        ];

        // Test for running into maximum integer size limit on 32bit systems.
        if (2147483647 === PHP_INT_MAX) {
            $array[] = [ '2G', 2147483647 ];
            $array[] = [ '4G', 2147483647 ];
        } else {
            $array[] = [ '2G', 2147483648 ];
            $array[] = [ '4G', 4294967296 ];
        }

        return $array;
    }
}
