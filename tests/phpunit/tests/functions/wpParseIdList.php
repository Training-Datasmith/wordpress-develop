<?php

declare(strict_types=1);

/**
 * Tests for the wp_parse_id_list() function.
 *
 * @group functions
 *
 * @covers ::wp_parse_id_list
 */
class Tests_Functions_wpParseIdList extends WP_UnitTestCase
{
    /**
     * @ticket 22074
     * @ticket 60218
     *
     * @dataProvider data_wp_parse_id_list
     * @dataProvider data_unexpected_input
     */
    public function test_wp_parse_id_list($input_list, $expected)
    {
        $this->assertSameSets($expected, wp_parse_id_list($input_list));
    }

    /**
     * Data provider.
     *
     * @return array[]
     */
    public function data_wp_parse_id_list()
    {
        return [
            'regular'                  => [
                'input_list' => '1,2,3,4',
                'expected'   => [ 1, 2, 3, 4 ],
            ],
            'double comma'             => [
                'input_list' => '1, 2,,3,4',
                'expected'   => [ 1, 2, 3, 4 ],
            ],
            'duplicate id in a string' => [
                'input_list' => '1,2,2,3,4',
                'expected'   => [ 1, 2, 3, 4 ],
            ],
            'duplicate id in an array' => [
                'input_list' => [ '1', '2', '3', '4', '3' ],
                'expected'   => [ 1, 2, 3, 4 ],
            ],
            'mixed type'               => [
                'input_list' => [ 1, '2', 3, '4' ],
                'expected'   => [ 1, 2, 3, 4 ],
            ],
            'negative ids in a string' => [
                'input_list' => '-1,2,-3,4',
                'expected'   => [ 1, 2, 3, 4 ],
            ],
            'negative ids in an array' => [
                'input_list' => [ -1, 2, '-3', '4' ],
                'expected'   => [ 1, 2, 3, 4 ],
            ],
        ];
    }

    /**
     * Data provider.
     *
     * @return array[]
     */
    public function data_unexpected_input()
    {
        return [
            'string with commas' => [
                'input_list' => '1,2,string with spaces',
                'expected'   => [ 1, 2, 0 ],
            ],
            'array'              => [
                'input_list' => [ '1', 2, 'string with spaces' ],
                'expected'   => [ 1, 2, 0 ],
            ],
            'string with spaces' => [
                'input_list' => '1 2 string with spaces',
                'expected'   => [ 1, 2, 0 ],
            ],
            'array with spaces'  => [
                'input_list' => [ '1 2 string with spaces' ],
                'expected'   => [ 1 ],
            ],
            'string with html'   => [
                'input_list' => '1 2 string <strong>with</strong> <h1>HEADING</h1>',
                'expected'   => [ 1, 2, 0 ],
            ],
            'array with html'    => [
                'input_list' => [ '1', 2, 'string <strong>with</strong> <h1>HEADING</h1>' ],
                'expected'   => [ 1, 2, 0 ],
            ],
            'array with null'    => [
                'input_list' => [ 1, 2, null ],
                'expected'   => [ 1, 2 ],
            ],
            'array with false'   => [
                'input_list' => [ 1, 2, false ],
                'expected'   => [ 1, 2, 0 ],
            ],
        ];
    }
}
