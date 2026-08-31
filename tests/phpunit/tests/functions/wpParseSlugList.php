<?php

declare(strict_types=1);

/**
 * Tests for the wp_parse_slug_list() function.
 *
 * @group functions
 *
 * @covers ::wp_parse_slug_list
 */
class Tests_Functions_WpParseSlugList extends WP_UnitTestCase
{
    /**
     * @ticket 35582
     * @ticket 60217
     *
     * @dataProvider data_wp_parse_slug_list
     * @dataProvider data_unexpected_input
     */
    public function test_wp_parse_slug_list($input_list, $expected)
    {
        $this->assertSameSets($expected, wp_parse_slug_list($input_list));
    }

    /**
     * Data provider.
     *
     * @return array[]
     */
    public function data_wp_parse_slug_list()
    {
        return [
            'regular'                    => [
                'input_list' => 'apple,banana,carrot,dog',
                'expected'   => [ 'apple', 'banana', 'carrot', 'dog' ],
            ],
            'double comma'               => [
                'input_list' => 'apple, banana,,carrot,dog',
                'expected'   => [ 'apple', 'banana', 'carrot', 'dog' ],
            ],
            'duplicate slug in a string' => [
                'input_list' => 'apple,banana,carrot,carrot,dog',
                'expected'   => [ 'apple', 'banana', 'carrot', 'dog' ],
            ],
            'duplicate slug in an array' => [
                'input_list' => [ 'apple', 'banana', 'carrot', 'carrot', 'dog' ],
                'expected'   => [ 'apple', 'banana', 'carrot', 'dog' ],
            ],
            'string with spaces'         => [
                'input_list' => 'apple banana carrot dog',
                'expected'   => [ 'apple', 'banana', 'carrot', 'dog' ],
            ],
            'array with spaces'          => [
                'input_list' => [ 'apple ', 'banana carrot', 'd o g' ],
                'expected'   => [ 'apple', 'banana-carrot', 'd-o-g' ],
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
                'expected'   => [ '1', '2', 'string', 'with', 'spaces' ],
            ],
            'array'              => [
                'input_list' => [ '1', 2, 'string with spaces' ],
                'expected'   => [ '1', '2', 'string-with-spaces' ],
            ],
            'string with spaces' => [
                'input_list' => '1 2 string with spaces',
                'expected'   => [ '1', '2', 'string', 'with', 'spaces' ],
            ],
            'array with spaces'  => [
                'input_list' => [ '1 2 string with spaces' ],
                'expected'   => [ '1-2-string-with-spaces' ],
            ],
            'string with html'   => [
                'input_list' => '1 2 string <strong>with</strong> <h1>HEADING</h1>',
                'expected'   => [ '1', '2', 'string', 'with', 'heading' ],
            ],
            'array with html'    => [
                'input_list' => [ '1', 2, 'string <strong>with</strong> <h1>HEADING</h1>' ],
                'expected'   => [ '1', '2', 'string-with-heading' ],
            ],
            'array with null'    => [
                'input_list' => [ 1, 2, null ],
                'expected'   => [ '1', '2' ],
            ],
            'array with false'   => [
                'input_list' => [ 1, 2, false ],
                'expected'   => [ '1', '2', '' ],
            ],
        ];
    }
}
