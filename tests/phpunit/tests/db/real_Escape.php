<?php

declare(strict_types=1);

/**
 * Test WPDB _real_escape() method.
 *
 * @group wpdb
 *
 * @covers wpdb::_real_escape
 */
class Tests_DB_RealEscape extends WP_UnitTestCase
{
    /**
     * Test that various types of input passed to `wpdb::_real_escape()` are handled correctly.
     *
     * Note: this test does not test the actual escaping or other logic in the function.
     * It just and only tests and documents how the function handles various input types.
     *
     * @ticket 53635
     *
     * @dataProvider data_real_escape_input_type_handling
     *
     * @param mixed  $input    The input to escape.
     * @param string $expected The expected function output.
     */
    public function test_real_escape_input_type_handling($input, $expected)
    {
        global $wpdb;

        $this->assertSame($expected, $wpdb->_real_escape($input));
    }

    /**
     * Data provider.
     *
     * @var array
     */
    public function data_real_escape_input_type_handling()
    {
        return [
            'null'             => [
                'input'    => null,
                'expected' => '',
            ],
            'boolean false'    => [
                'input'    => false,
                'expected' => '',
            ],
            'boolean true'     => [
                'input'    => true,
                'expected' => '1',
            ],
            'integer zero'     => [
                'input'    => 0,
                'expected' => '0',
            ],
            'negative integer' => [
                'input'    => -1327,
                'expected' => '-1327',
            ],
            'positive integer' => [
                'input'    => 47896,
                'expected' => '47896',
            ],
            'float zero'       => [
                'input'    => 0.0,
                'expected' => '0',
            ],
            'positive float'   => [
                'input'    => 25.52,
                'expected' => '25.52',
            ],
            'simple string'    => [
                'input'    => 'foobar',
                'expected' => 'foobar',
            ],
            'empty array'      => [
                'input'    => [],
                'expected' => '',
            ],
            'non-empty array'  => [
                'input'    => [ 1, 2, 3 ],
                'expected' => '',
            ],
            'simple object'    => [
                'input'    => new stdClass(),
                'expected' => '',
            ],
        ];
    }
}
