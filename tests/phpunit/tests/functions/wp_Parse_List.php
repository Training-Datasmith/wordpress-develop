<?php

declare(strict_types=1);

/**
 * Tests for the wp_parse_list() function.
 *
 * @group functions
 *
 * @covers ::wp_parse_list
 */
class Tests_Functions_wpParseList extends WP_UnitTestCase
{
    /**
     * @ticket 43977
     *
     * @dataProvider data_wp_parse_list
     */
    public function test_wp_parse_list($input_list, $expected)
    {
        $this->assertSameSets($expected, wp_parse_list($input_list));
    }

    /**
     * Data provider.
     *
     * @return array[]
     */
    public function data_wp_parse_list()
    {
        return [
            'ids only'           => [
                'input_list' => '1,2,3,4',
                'expected'   => [ '1', '2', '3', '4' ],
            ],
            'slugs only'         => [
                'input_list' => 'apple,banana,carrot,dog',
                'expected'   => [ 'apple', 'banana', 'carrot', 'dog' ],
            ],
            'ids and slugs'      => [
                'input_list' => '1,2,apple,banana',
                'expected'   => [ '1', '2', 'apple', 'banana' ],
            ],
            'space after comma'  => [
                'input_list' => '1, 2,apple,banana',
                'expected'   => [ '1', '2', 'apple', 'banana' ],
            ],
            'double comma'       => [
                'input_list' => '1,2,apple,,banana',
                'expected'   => [ '1', '2', 'apple', 'banana' ],
            ],
            'leading comma'      => [
                'input_list' => ',1,2,apple,banana',
                'expected'   => [ '1', '2', 'apple', 'banana' ],
            ],
            'trailing comma'     => [
                'input_list' => '1,2,apple,banana,',
                'expected'   => [ '1', '2', 'apple', 'banana' ],
            ],
            'space before comma' => [
                'input_list' => '1,2 ,apple,banana',
                'expected'   => [ '1', '2', 'apple', 'banana' ],
            ],
            'empty string'       => [
                'input_list' => '',
                'expected'   => [],
            ],
            'comma only'         => [
                'input_list' => ',',
                'expected'   => [],
            ],
            'double comma only'  => [
                'input_list' => ',,',
                'expected'   => [],
            ],
        ];
    }
}
