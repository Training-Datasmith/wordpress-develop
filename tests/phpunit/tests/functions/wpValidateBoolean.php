<?php

declare(strict_types=1);

/**
 * Tests for the wp_validate_boolean() function.
 *
 * @group functions
 *
 * @covers ::wp_validate_boolean
 */
class Tests_Functions_wpValidateBoolean extends WP_UnitTestCase
{
    /**
     * Tests wp_validate_boolean().
     *
     * @dataProvider data_wp_validate_boolean
     *
     * @ticket 30238
     * @ticket 39868
     *
     * @param mixed $test_value Test value.
     * @param bool  $expected   Expected return value.
     */
    public function test_wp_validate_boolean($test_value, $expected)
    {
        $this->assertSame($expected, wp_validate_boolean($test_value));
    }

    /**
     * Data provider for test_wp_validate_boolean().
     *
     * @return array[] Test parameters {
     *     @type mixed $test_value Test value.
     *     @type bool  $expected   Expected return value.
     * }
     */
    public function data_wp_validate_boolean()
    {
        $std = new \stdClass();

        return [
            [ null, false ],
            [ true, true ],
            [ false, false ],
            [ 'true', true ],
            [ 'false', false ],
            [ 'FalSE', false ], // @ticket 30238
            [ 'FALSE', false ], // @ticket 30238
            [ 'TRUE', true ],
            [ ' FALSE ', true ],
            [ 'yes', true ],
            [ 'no', true ],
            [ 'string', true ],
            [ '', false ],
            [ [], false ],
            [ 1, true ],
            [ 0, false ],
            [ -1, true ],
            [ 99, true ],
            [ 0.1, true ],
            [ 0.0, false ],
            [ '1', true ],
            [ '0', false ],
            [ $std, true ],
        ];
    }
}
