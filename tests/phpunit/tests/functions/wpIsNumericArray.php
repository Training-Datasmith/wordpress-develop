<?php

declare(strict_types=1);

/**
 * @group functions
 *
 * @covers ::wp_is_numeric_array
 */
class Tests_Functions_wpIsNumericArray extends WP_UnitTestCase
{
    /**
     * @dataProvider data_wp_is_numeric_array
     *
     * @ticket 53971
     *
     * @param mixed $input    Input to test.
     * @param array $expected Expected result.
     */
    public function test_wp_is_numeric_array($input, $expected)
    {
        $this->assertSame($expected, wp_is_numeric_array($input));
    }

    /**
     * Data provider.
     *
     * @return array[]
     */
    public function data_wp_is_numeric_array()
    {
        return [
            'no index'             => [
                'test_array' => [ 'www', 'eee' ],
                'expected'   => true,
            ],
            'text index'           => [
                'test_array' => [ 'www' => 'eee' ],
                'expected'   => false,
            ],
            'numeric index'        => [
                'test_array' => [ 99 => 'eee' ],
                'expected'   => true,
            ],
            '- numeric index'      => [
                'test_array' => [ -11 => 'eee' ],
                'expected'   => true,
            ],
            'numeric string index' => [
                'test_array' => [ '11' => 'eee' ],
                'expected'   => true,
            ],
            'nested number index'  => [
                'test_array' => [
                    'next' => [
                        11 => 'vvv',
                    ],
                ],
                'expected'   => false,
            ],
            'nested string index'  => [
                'test_array' => [
                    '11' => [
                        'eee' => 'vvv',
                    ],
                ],
                'expected'   => true,
            ],
            'not an array'         => [
                'test_array' => null,
                'expected'   => false,
            ],
        ];
    }
}
