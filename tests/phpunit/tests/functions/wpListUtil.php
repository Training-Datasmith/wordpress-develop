<?php

declare(strict_types=1);

/**
 * Test WP_List_Util class.
 *
 * @group functions
 */
class Tests_Functions_wpListUtil extends WP_UnitTestCase
{
    /**
     * @covers WP_List_Util::get_input
     */
    public function test_wp_list_util_get_input()
    {
        $input = [ 'foo', 'bar' ];
        $util  = new WP_List_Util($input);

        $this->assertSameSets($input, $util->get_input());
    }

    /**
     * @covers WP_List_Util::get_output
     */
    public function test_wp_list_util_get_output_immediately()
    {
        $input = [ 'foo', 'bar' ];
        $util  = new WP_List_Util($input);

        $this->assertSameSets($input, $util->get_output());
    }

    /**
     * @covers WP_List_Util::get_output
     */
    public function test_wp_list_util_get_output()
    {
        $expected = [
            (object) [
                'foo' => 'bar',
                'bar' => 'baz',
            ],
        ];

        $util   = new WP_List_Util(
            [
                (object) [
                    'foo' => 'bar',
                    'bar' => 'baz',
                ],
                (object) [ 'bar' => 'baz' ],
            ]
        );
        $actual = $util->filter([ 'foo' => 'bar' ]);

        $this->assertEqualSets($expected, $actual);
        $this->assertEqualSets($expected, $util->get_output());
    }

    /**
     * @ticket 55300
     *
     * @dataProvider data_wp_list_util_pluck
     *
     * @covers WP_List_Util::pluck
     * @covers ::wp_list_pluck
     *
     * @param array  $target_array The array to create the list from.
     * @param string $target_key   The key to pluck.
     * @param array  $expected     The expected array.
     * @param string $index_key    Optional. Field from the element to use as keys for the new array.
     *                             Default null.
     */
    public function test_wp_list_util_pluck($target_array, $target_key, $expected, $index_key = null)
    {
        $util   = new WP_List_Util($target_array);
        $actual = $util->pluck($target_key, $index_key);

        $this->assertEqualSetsWithIndex(
            $expected,
            $actual,
            'The plucked value did not match the expected value.'
        );

        $this->assertEqualSetsWithIndex(
            $expected,
            $util->get_output(),
            '::get_output() did not return the expected value.'
        );
    }

    /**
     * Data provider for test_wp_list_util_pluck().
     *
     * @return array[]
     */
    public function data_wp_list_util_pluck()
    {
        return [
            'simple'        => [
                'target_array' => [
                    0 => [ 'foo' => 'bar' ],
                ],
                'target_key'   => 'foo',
                'expected'     => [ 'bar' ],
            ],
            'simple_object' => [
                'target_array' => [
                    0 => (object) [ 'foo' => 'bar' ],
                ],
                'target_key'   => 'foo',
                'expected'     => [ 'bar' ],
            ],
        ];
    }

    /**
     * Tests that wp_list_pluck() throws _doing_it_wrong() with invalid input.
     *
     * @ticket 56650
     *
     * @dataProvider data_wp_list_pluck_should_throw_doing_it_wrong_with_invalid_input
     *
     * @covers WP_List_Util::pluck
     * @covers ::wp_list_pluck
     *
     * @expectedIncorrectUsage WP_List_Util::pluck
     *
     * @param array $input An invalid input array.
     */
    public function test_wp_list_pluck_should_throw_doing_it_wrong_with_invalid_input($input)
    {
        $this->assertSame([], wp_list_pluck($input, 'a_field'));
    }

    /**
     * Tests that wp_list_pluck() throws _doing_it_wrong() with an index key and invalid input.
     *
     * @ticket 56650
     *
     * @dataProvider data_wp_list_pluck_should_throw_doing_it_wrong_with_invalid_input
     *
     * @covers WP_List_Util::pluck
     * @covers ::wp_list_pluck
     *
     * @expectedIncorrectUsage WP_List_Util::pluck
     *
     * @param array $input An invalid input array.
     */
    public function test_wp_list_pluck_should_throw_doing_it_wrong_with_index_key_and_invalid_input($input)
    {
        $this->assertSame([], wp_list_pluck($input, 'a_field', 'an_index_key'));
    }

    /**
     * Data provider that provides invalid input arrays.
     *
     * @return array[]
     */
    public function data_wp_list_pluck_should_throw_doing_it_wrong_with_invalid_input()
    {
        return [
            'int[] 0'                   => [ [ 0 ] ],
            'int[] 1'                   => [ [ 1 ] ],
            'int[] -1'                  => [ [ -1 ] ],
            'float[] 0.0'               => [ [ 0.0 ] ],
            'float[] 1.0'               => [ [ 1.0 ] ],
            'float[] -1.0'              => [ [ -1.0 ] ],
            'string[] and empty string' => [ [ '' ] ],
            'string[] and "0"'          => [ [ '0' ] ],
            'string[] and "1"'          => [ [ '1' ] ],
            'string[] and "-1"'         => [ [ '-1' ] ],
            'array and null'            => [ [ null ] ],
            'array and false'           => [ [ false ] ],
            'array and true'            => [ [ true ] ],
        ];
    }

    /**
     * @ticket 55300
     *
     * @covers WP_List_Util::sort
     * @covers ::wp_list_sort
     */
    public function test_wp_list_util_sort_simple()
    {
        $expected     = [
            1 => 'one',
            2 => 'two',
            3 => 'three',
            4 => 'four',
        ];
        $target_array = [
            4 => 'four',
            2 => 'two',
            3 => 'three',
            1 => 'one',
        ];

        $util   = new WP_List_Util($target_array);
        $actual = $util->sort();

        $this->assertEqualSets(
            $expected,
            $actual,
            'The sorted value did not match the expected value.'
        );

        $this->assertEqualSets(
            $expected,
            $util->get_output(),
            '::get_output() did not return the expected value.'
        );
    }

    /**
     * @ticket 55300
     *
     * @dataProvider data_wp_list_util_sort_string_arrays
     * @dataProvider data_wp_list_util_sort_int_arrays
     * @dataProvider data_wp_list_util_sort_arrays_of_arrays
     * @dataProvider data_wp_list_util_sort_object_arrays
     * @dataProvider data_wp_list_util_sort_non_existent_orderby_fields
     *
     * @covers WP_List_Util::sort
     * @covers ::wp_list_sort
     *
     * @param array  $expected      The expected array.
     * @param array  $target_array  The array to create a list from.
     * @param array  $orderby       Optional. Either the field name to order by or an array
     *                              of multiple orderby fields as `$orderby => $order`.
     *                              Default empty array.
     * @param string $order         Optional. Either 'ASC' or 'DESC'. Only used if `$orderby`
     *                              is a string. Default 'ASC'.
     * @param bool   $preserve_keys Optional. Whether to preserve keys. Default false.
     */
    public function test_wp_list_util_sort($expected, $target_array, $orderby = [], $order = 'ASC', $preserve_keys = false)
    {
        $util   = new WP_List_Util($target_array);
        $actual = $util->sort($orderby, $order, $preserve_keys);

        $this->assertEqualSetsWithIndex(
            $expected,
            $actual,
            'The sorted value did not match the expected value.'
        );

        $this->assertEqualSetsWithIndex(
            $expected,
            $util->get_output(),
            '::get_output() did not return the expected value.'
        );
    }

    /**
     * Data provider that provides string arrays to test_wp_list_util_sort().
     *
     * @return array[]
     */
    public function data_wp_list_util_sort_string_arrays()
    {
        return [
            'string[], no keys, no ordering'     => [
                'expected'     => [ 'four', 'two', 'three', 'one' ],
                'target_array' => [ 'four', 'two', 'three', 'one' ],
            ],
            'string[], int keys, no ordering'    => [
                'expected'     => [
                    4 => 'four',
                    2 => 'two',
                    3 => 'three',
                    1 => 'one',
                ],
                'target_array' => [
                    4 => 'four',
                    2 => 'two',
                    3 => 'three',
                    1 => 'one',
                ],
            ],
            'string[], int keys, $orderby a non-existent field, $order = DESC and $preserve_keys = true' => [
                'expected'      => [
                    4 => 'four',
                    2 => 'two',
                    3 => 'three',
                    1 => 'one',
                ],
                'target_array'  => [
                    4 => 'four',
                    2 => 'two',
                    3 => 'three',
                    1 => 'one',
                ],
                'orderby'       => 'id',
                'order'         => 'DESC',
                'preserve_keys' => true,
            ],
            'string[], string keys, no ordering' => [
                'expected'     => [
                    'four'  => 'four',
                    'two'   => 'two',
                    'three' => 'three',
                    'one'   => 'one',
                ],
                'target_array' => [
                    'four'  => 'four',
                    'two'   => 'two',
                    'three' => 'three',
                    'one'   => 'one',
                ],
            ],
            'string[], string keys, $orderby a non-existent field, $order = DESC and $preserve_keys = true' => [
                'expected'      => [
                    'four'  => 'four',
                    'two'   => 'two',
                    'three' => 'three',
                    'one'   => 'one',
                ],
                'target_array'  => [
                    'four'  => 'four',
                    'two'   => 'two',
                    'three' => 'three',
                    'one'   => 'one',
                ],
                'orderby'       => 'id',
                'order'         => 'DESC',
                'preserve_keys' => true,
            ],
        ];
    }

    /**
     * Data provider that provides int arrays for test_wp_list_util_sort().
     *
     * @return array[]
     */
    public function data_wp_list_util_sort_int_arrays()
    {
        return [
            'int[], no keys, no ordering'     => [
                'expected'     => [ 4, 2, 3, 1 ],
                'target_array' => [ 4, 2, 3, 1 ],
            ],
            'int[], int keys, no ordering'    => [
                'expected'     => [
                    4 => 4,
                    2 => 2,
                    3 => 3,
                    1 => 1,
                ],
                'target_array' => [
                    4 => 4,
                    2 => 2,
                    3 => 3,
                    1 => 1,
                ],
            ],
            'int[], int keys, $orderby a non-existent field, $order = DESC and $preserve_keys = true' => [
                'expected'      => [
                    4 => 4,
                    2 => 2,
                    3 => 3,
                    1 => 1,
                ],
                'target_array'  => [
                    4 => 4,
                    2 => 2,
                    3 => 3,
                    1 => 1,
                ],
                'orderby'       => 'id',
                'order'         => 'DESC',
                'preserve_keys' => true,
            ],
            'int[], string keys, no ordering' => [
                'expected'     => [
                    'four'  => 4,
                    'two'   => 2,
                    'three' => 3,
                    'one'   => 1,
                ],
                'target_array' => [
                    'four'  => 4,
                    'two'   => 2,
                    'three' => 3,
                    'one'   => 1,
                ],
            ],
            'int[], string keys, $orderby a non-existent field, $order = DESC and $preserve_keys = true' => [
                'expected'      => [
                    'four'  => 4,
                    'two'   => 2,
                    'three' => 3,
                    'one'   => 1,
                ],
                'target_array'  => [
                    'four'  => 4,
                    'two'   => 2,
                    'three' => 3,
                    'one'   => 1,
                ],
                'orderby'       => 'id',
                'order'         => 'DESC',
                'preserve_keys' => true,
            ],
        ];
    }

    /**
     * Data provider that provides arrays of arrays for test_wp_list_util_sort().
     *
     * @return array[]
     */
    public function data_wp_list_util_sort_arrays_of_arrays()
    {
        return [
            'array[], no keys, no ordering'     => [
                'expected'     => [
                    [ 'four' ],
                    [ 'two' ],
                    [ 'three' ],
                    [ 'one' ],
                ],
                'target_array' => [
                    [ 'four' ],
                    [ 'two' ],
                    [ 'three' ],
                    [ 'one' ],
                ],
            ],
            'array[], int keys, no ordering'    => [
                'expected'     => [
                    4 => [ 'four' ],
                    2 => [ 'two' ],
                    3 => [ 'three' ],
                    1 => [ 'one' ],
                ],
                'target_array' => [
                    4 => [ 'four' ],
                    2 => [ 'two' ],
                    3 => [ 'three' ],
                    1 => [ 'one' ],
                ],
            ],
            'array[], int keys, $orderby a non-existent field, $order = DESC and $preserve_keys = true' => [
                'expected'      => [
                    4 => [ 'value' => 'four' ],
                    2 => [ 'value' => 'two' ],
                    3 => [ 'value' => 'three' ],
                    1 => [ 'value' => 'one' ],
                ],
                'target_array'  => [
                    4 => [ 'value' => 'four' ],
                    2 => [ 'value' => 'two' ],
                    3 => [ 'value' => 'three' ],
                    1 => [ 'value' => 'one' ],
                ],
                'orderby'       => 'id',
                'order'         => 'DESC',
                'preserve_keys' => true,
            ],
            'array[], int keys, $orderby an existing field, $order = ASC and $preserve_keys = false' => [
                'expected'      => [
                    [
                        'id'    => 1,
                        'value' => 'one',
                    ],
                    [
                        'id'    => 2,
                        'value' => 'two',
                    ],
                    [
                        'id'    => 3,
                        'value' => 'three',
                    ],
                    [
                        'id'    => 4,
                        'value' => 'four',
                    ],
                ],
                'target_array'  => [
                    4 => [
                        'id'    => 4,
                        'value' => 'four',
                    ],
                    2 => [
                        'id'    => 2,
                        'value' => 'two',
                    ],
                    3 => [
                        'id'    => 3,
                        'value' => 'three',
                    ],
                    1 => [
                        'id'    => 1,
                        'value' => 'one',
                    ],
                ],
                'orderby'       => 'id',
                'order'         => 'ASC',
                'preserve_keys' => false,
            ],
            'array[], int keys, $orderby an existing field, $order = DESC and $preserve_keys = true' => [
                'expected'      => [
                    3 => [
                        'id'    => 4,
                        'value' => 'four',
                    ],
                    2 => [
                        'id'    => 3,
                        'value' => 'three',
                    ],
                    1 => [
                        'id'    => 2,
                        'value' => 'two',
                    ],
                    0 => [
                        'id'    => 1,
                        'value' => 'one',
                    ],
                ],
                'target_array'  => [
                    [
                        'id'    => 1,
                        'value' => 'one',
                    ],
                    [
                        'id'    => 2,
                        'value' => 'two',
                    ],
                    [
                        'id'    => 3,
                        'value' => 'three',
                    ],
                    [
                        'id'    => 4,
                        'value' => 'four',
                    ],
                ],
                'orderby'       => 'id',
                'order'         => 'DESC',
                'preserve_keys' => true,
            ],
            'array[], string keys, no ordering' => [
                'expected'     => [
                    'four'  => [ 'value' => 'four' ],
                    'two'   => [ 'value' => 'two' ],
                    'three' => [ 'value' => 'three' ],
                    'one'   => [ 'value' => 'one' ],
                ],
                'target_array' => [
                    'four'  => [ 'value' => 'four' ],
                    'two'   => [ 'value' => 'two' ],
                    'three' => [ 'value' => 'three' ],
                    'one'   => [ 'value' => 'one' ],
                ],
            ],
            'array[], string keys, $orderby a non-existent field, $order = DESC and $preserve_keys = true' => [
                'expected'      => [
                    'four'  => [ 'value' => 'four' ],
                    'two'   => [ 'value' => 'two' ],
                    'three' => [ 'value' => 'three' ],
                    'one'   => [ 'value' => 'one' ],
                ],
                'target_array'  => [
                    'four'  => [ 'value' => 'four' ],
                    'two'   => [ 'value' => 'two' ],
                    'three' => [ 'value' => 'three' ],
                    'one'   => [ 'value' => 'one' ],
                ],
                'orderby'       => 'id',
                'order'         => 'DESC',
                'preserve_keys' => true,
            ],
            'array[], string keys, $orderby an existing field, $order = ASC and $preserve_keys = false' => [
                'expected'      => [
                    [
                        'id'    => 1,
                        'value' => 'one',
                    ],
                    [
                        'id'    => 2,
                        'value' => 'two',
                    ],
                    [
                        'id'    => 3,
                        'value' => 'three',
                    ],
                    [
                        'id'    => 4,
                        'value' => 'four',
                    ],
                ],
                'target_array'  => [
                    'four'  => [
                        'id'    => 4,
                        'value' => 'four',
                    ],
                    'two'   => [
                        'id'    => 2,
                        'value' => 'two',
                    ],
                    'three' => [
                        'id'    => 3,
                        'value' => 'three',
                    ],
                    'one'   => [
                        'id'    => 1,
                        'value' => 'one',
                    ],
                ],
                'orderby'       => 'id',
                'order'         => 'ASC',
                'preserve_keys' => false,
            ],
            'array[], string keys, $orderby an existing field, $order = DESC and $preserve_keys = true' => [
                'expected'      => [
                    'four'  => [
                        'id'    => 4,
                        'value' => 'four',
                    ],
                    'three' => [
                        'id'    => 3,
                        'value' => 'three',
                    ],
                    'two'   => [
                        'id'    => 2,
                        'value' => 'two',
                    ],
                    'one'   => [
                        'id'    => 1,
                        'value' => 'one',
                    ],
                ],
                'target_array'  => [
                    'one'   => [
                        'id'    => 1,
                        'value' => 'one',
                    ],
                    'two'   => [
                        'id'    => 2,
                        'value' => 'two',
                    ],
                    'three' => [
                        'id'    => 3,
                        'value' => 'three',
                    ],
                    'four'  => [
                        'id'    => 4,
                        'value' => 'four',
                    ],
                ],
                'orderby'       => 'id',
                'order'         => 'DESC',
                'preserve_keys' => true,
            ],
            'array[], string keys, $orderby an existing field, $order = asc (lowercase) and $preserve_keys = false' => [
                'expected'      => [
                    [
                        'id'    => 1,
                        'value' => 'one',
                    ],
                    [
                        'id'    => 2,
                        'value' => 'two',
                    ],
                    [
                        'id'    => 3,
                        'value' => 'three',
                    ],
                    [
                        'id'    => 4,
                        'value' => 'four',
                    ],
                ],
                'target_array'  => [
                    'four'  => [
                        'id'    => 4,
                        'value' => 'four',
                    ],
                    'two'   => [
                        'id'    => 2,
                        'value' => 'two',
                    ],
                    'three' => [
                        'id'    => 3,
                        'value' => 'three',
                    ],
                    'one'   => [
                        'id'    => 1,
                        'value' => 'one',
                    ],
                ],
                'orderby'       => 'id',
                'order'         => 'asc',
                'preserve_keys' => false,
            ],
            'array[], string keys, $orderby an existing field, no order and $preserve_keys = false' => [
                'expected'      => [
                    'four'  => [
                        'id'    => 4,
                        'value' => 'four',
                    ],
                    'three' => [
                        'id'    => 3,
                        'value' => 'three',
                    ],
                    'two'   => [
                        'id'    => 2,
                        'value' => 'two',
                    ],
                    'one'   => [
                        'id'    => 1,
                        'value' => 'one',
                    ],
                ],
                'target_array'  => [
                    'one'   => [
                        'id'    => 1,
                        'value' => 'one',
                    ],
                    'two'   => [
                        'id'    => 2,
                        'value' => 'two',
                    ],
                    'three' => [
                        'id'    => 3,
                        'value' => 'three',
                    ],
                    'four'  => [
                        'id'    => 4,
                        'value' => 'four',
                    ],
                ],
                'orderby'       => [ 'id' ],
                'order'         => null,
                'preserve_keys' => true,
            ],
            'array[], string keys, $orderby two existing fields, differing orders and $preserve_keys = false' => [
                'expected'      => [
                    [
                        'id'    => 1,
                        'value' => 'one',
                    ],
                    [
                        'id'    => 2,
                        'value' => 'two',
                    ],
                    [
                        'id'    => 3,
                        'value' => 'three',
                    ],
                    [
                        'id'    => 4,
                        'value' => 'four',
                    ],
                ],
                'target_array'  => [
                    'four'  => [
                        'id'    => 4,
                        'value' => 'four',
                    ],
                    'two'   => [
                        'id'    => 2,
                        'value' => 'two',
                    ],
                    'three' => [
                        'id'    => 3,
                        'value' => 'three',
                    ],
                    'one'   => [
                        'id'    => 1,
                        'value' => 'one',
                    ],
                ],
                'orderby'       => [
                    'id'    => 'asc',
                    'value' => 'DESC',
                ],
                'order'         => null,
                'preserve_keys' => false,
            ],
        ];
    }

    /**
     * Data provider that provides object arrays for test_wp_list_util_sort().
     *
     * @return array[]
     */
    public function data_wp_list_util_sort_object_arrays()
    {
        return [
            'object[], no keys, no ordering'     => [
                'expected'     => [
                    (object) [ 'four' ],
                    (object) [ 'two' ],
                    (object) [ 'three' ],
                    (object) [ 'one' ],
                ],
                'target_array' => [
                    (object) [ 'four' ],
                    (object) [ 'two' ],
                    (object) [ 'three' ],
                    (object) [ 'one' ],
                ],
            ],
            'object[], int keys, no ordering'    => [
                'expected'     => [
                    4 => (object) [ 'four' ],
                    2 => (object) [ 'two' ],
                    3 => (object) [ 'three' ],
                    1 => (object) [ 'one' ],
                ],
                'target_array' => [
                    4 => (object) [ 'four' ],
                    2 => (object) [ 'two' ],
                    3 => (object) [ 'three' ],
                    1 => (object) [ 'one' ],
                ],
            ],
            'object[], int keys, $orderby an existing field, $order = ASC and $preserve_keys = false' => [
                'expected'      => [
                    (object) [
                        'id'    => 1,
                        'value' => 'one',
                    ],
                    (object) [
                        'id'    => 2,
                        'value' => 'two',
                    ],
                    (object) [
                        'id'    => 3,
                        'value' => 'three',
                    ],
                    (object) [
                        'id'    => 4,
                        'value' => 'four',
                    ],
                ],
                'target_array'  => [
                    4 => (object) [
                        'id'    => 4,
                        'value' => 'four',
                    ],
                    2 => (object) [
                        'id'    => 2,
                        'value' => 'two',
                    ],
                    3 => (object) [
                        'id'    => 3,
                        'value' => 'three',
                    ],
                    1 => (object) [
                        'id'    => 1,
                        'value' => 'one',
                    ],
                ],
                'orderby'       => 'id',
                'order'         => 'ASC',
                'preserve_keys' => false,
            ],
            'object[], int keys, $orderby an existing field, $order = DESC and $preserve_keys = true' => [
                'expected'      => [
                    3 => (object) [
                        'id'    => 4,
                        'value' => 'four',
                    ],
                    2 => (object) [
                        'id'    => 3,
                        'value' => 'three',
                    ],
                    1 => (object) [
                        'id'    => 2,
                        'value' => 'two',
                    ],
                    0 => (object) [
                        'id'    => 1,
                        'value' => 'one',
                    ],
                ],
                'target_array'  => [
                    (object) [
                        'id'    => 1,
                        'value' => 'one',
                    ],
                    (object) [
                        'id'    => 2,
                        'value' => 'two',
                    ],
                    (object) [
                        'id'    => 3,
                        'value' => 'three',
                    ],
                    (object) [
                        'id'    => 4,
                        'value' => 'four',
                    ],
                ],
                'orderby'       => 'id',
                'order'         => 'DESC',
                'preserve_keys' => true,
            ],
            'object[], string keys, no ordering' => [
                'expected'     => [
                    'four'  => (object) [ 'value' => 'four' ],
                    'two'   => (object) [ 'value' => 'two' ],
                    'three' => (object) [ 'value' => 'three' ],
                    'one'   => (object) [ 'value' => 'one' ],
                ],
                'target_array' => [
                    'four'  => (object) [ 'value' => 'four' ],
                    'two'   => (object) [ 'value' => 'two' ],
                    'three' => (object) [ 'value' => 'three' ],
                    'one'   => (object) [ 'value' => 'one' ],
                ],
            ],
            'object[], string keys, $orderby a non-existent field, $order = DESC and $preserve_keys = true' => [
                'expected'      => [
                    'four'  => (object) [ 'value' => 'four' ],
                    'two'   => (object) [ 'value' => 'two' ],
                    'three' => (object) [ 'value' => 'three' ],
                    'one'   => (object) [ 'value' => 'one' ],
                ],
                'target_array'  => [
                    'four'  => (object) [ 'value' => 'four' ],
                    'two'   => (object) [ 'value' => 'two' ],
                    'three' => (object) [ 'value' => 'three' ],
                    'one'   => (object) [ 'value' => 'one' ],
                ],
                'orderby'       => 'id',
                'order'         => 'DESC',
                'preserve_keys' => true,
            ],
            'object[], string keys, $orderby an existing field, $order = ASC and $preserve_keys = false' => [
                'expected'      => [
                    (object) [
                        'id'    => 1,
                        'value' => 'one',
                    ],
                    (object) [
                        'id'    => 2,
                        'value' => 'two',
                    ],
                    (object) [
                        'id'    => 3,
                        'value' => 'three',
                    ],
                    (object) [
                        'id'    => 4,
                        'value' => 'four',
                    ],
                ],
                'target_array'  => [
                    'four'  => (object) [
                        'id'    => 4,
                        'value' => 'four',
                    ],
                    'two'   => (object) [
                        'id'    => 2,
                        'value' => 'two',
                    ],
                    'three' => (object) [
                        'id'    => 3,
                        'value' => 'three',
                    ],
                    'one'   => (object) [
                        'id'    => 1,
                        'value' => 'one',
                    ],
                ],
                'orderby'       => 'id',
                'order'         => 'ASC',
                'preserve_keys' => false,
            ],
            'object[], string keys, $orderby an existing field, $order = DESC and $preserve_keys = true' => [
                'expected'      => [
                    'four'  => (object) [
                        'id'    => 4,
                        'value' => 'four',
                    ],
                    'three' => (object) [
                        'id'    => 3,
                        'value' => 'three',
                    ],
                    'two'   => (object) [
                        'id'    => 2,
                        'value' => 'two',
                    ],
                    'one'   => (object) [
                        'id'    => 1,
                        'value' => 'one',
                    ],
                ],
                'target_array'  => [
                    'one'   => (object) [
                        'id'    => 1,
                        'value' => 'one',
                    ],
                    'two'   => (object) [
                        'id'    => 2,
                        'value' => 'two',
                    ],
                    'three' => (object) [
                        'id'    => 3,
                        'value' => 'three',
                    ],
                    'four'  => (object) [
                        'id'    => 4,
                        'value' => 'four',
                    ],
                ],
                'orderby'       => 'id',
                'order'         => 'DESC',
                'preserve_keys' => true,
            ],
        ];
    }

    /**
     * Data provider for test_wp_list_util_sort().
     *
     * @return array[]
     */
    public function data_wp_list_util_sort_non_existent_orderby_fields()
    {
        return [
            'int[], int keys, $orderby a non-existent field, $order = ASC and $preserve_keys = false' => [
                'expected'      => [ 4, 2, 3, 1 ],
                'target_array'  => [
                    4 => 4,
                    2 => 2,
                    3 => 3,
                    1 => 1,
                ],
                'orderby'       => 'id',
                'order'         => 'ASC',
                'preserve_keys' => false,
            ],
            'int[], string keys, $orderby a non-existent field, $order = ASC and $preserve_keys = false' => [
                'expected'      => [ 4, 2, 3, 1 ],
                'target_array'  => [
                    'four'  => 4,
                    'two'   => 2,
                    'three' => 3,
                    'one'   => 1,
                ],
                'orderby'       => 'id',
                'order'         => 'ASC',
                'preserve_keys' => false,
            ],
            'string[], int keys, $orderby a non-existent field, $order = ASC and $preserve_keys = false' => [
                'expected'      => [ 'four', 'two', 'three', 'one' ],
                'target_array'  => [
                    4 => 'four',
                    2 => 'two',
                    3 => 'three',
                    1 => 'one',
                ],
                'orderby'       => 'id',
                'order'         => 'ASC',
                'preserve_keys' => false,
            ],
            'string[], string keys, $orderby a non-existent field, $order = ASC and $preserve_keys = false' => [
                'expected'      => [ 'four', 'two', 'three', 'one' ],
                'target_array'  => [
                    'four'  => 'four',
                    'two'   => 'two',
                    'three' => 'three',
                    'one'   => 'one',
                ],
                'orderby'       => 'id',
                'order'         => 'ASC',
                'preserve_keys' => false,
            ],
            'array[], int keys, $orderby a non-existent field, $order = ASC and $preserve_keys = false' => [
                'expected'      => [
                    [ 'value' => 'four' ],
                    [ 'value' => 'two' ],
                    [ 'value' => 'three' ],
                    [ 'value' => 'one' ],
                ],
                'target_array'  => [
                    4 => [ 'value' => 'four' ],
                    2 => [ 'value' => 'two' ],
                    3 => [ 'value' => 'three' ],
                    1 => [ 'value' => 'one' ],
                ],
                'orderby'       => 'id',
                'order'         => 'ASC',
                'preserve_keys' => false,
            ],
            'array[], string keys, $orderby a non-existent field, $order = ASC and $preserve_keys = false' => [
                'expected'      => [
                    [ 'value' => 'four' ],
                    [ 'value' => 'two' ],
                    [ 'value' => 'three' ],
                    [ 'value' => 'one' ],
                ],
                'target_array'  => [
                    'four'  => [ 'value' => 'four' ],
                    'two'   => [ 'value' => 'two' ],
                    'three' => [ 'value' => 'three' ],
                    'one'   => [ 'value' => 'one' ],
                ],
                'orderby'       => 'id',
                'order'         => 'ASC',
                'preserve_keys' => false,
            ],
            'object[], int keys, $orderby a non-existent field, $order = ASC and $preserve_keys = false' => [
                'expected'      => [
                    (object) [ 'value' => 'four' ],
                    (object) [ 'value' => 'two' ],
                    (object) [ 'value' => 'three' ],
                    (object) [ 'value' => 'one' ],
                ],
                'target_array'  => [
                    4 => (object) [ 'value' => 'four' ],
                    2 => (object) [ 'value' => 'two' ],
                    3 => (object) [ 'value' => 'three' ],
                    1 => (object) [ 'value' => 'one' ],
                ],
                'orderby'       => 'id',
                'order'         => 'ASC',
                'preserve_keys' => false,
            ],
            'object[], int keys, $orderby a non-existent field, $order = DESC and $preserve_keys = true' => [
                'expected'      => [
                    4 => (object) [ 'value' => 'four' ],
                    2 => (object) [ 'value' => 'two' ],
                    3 => (object) [ 'value' => 'three' ],
                    1 => (object) [ 'value' => 'one' ],
                ],
                'target_array'  => [
                    4 => (object) [ 'value' => 'four' ],
                    2 => (object) [ 'value' => 'two' ],
                    3 => (object) [ 'value' => 'three' ],
                    1 => (object) [ 'value' => 'one' ],
                ],
                'orderby'       => 'id',
                'order'         => 'DESC',
                'preserve_keys' => true,
            ],
            'object[], string keys, $orderby a non-existent field, $order = ASC and $preserve_keys = false' => [
                'expected'      => [
                    (object) [ 'value' => 'four' ],
                    (object) [ 'value' => 'two' ],
                    (object) [ 'value' => 'three' ],
                    (object) [ 'value' => 'one' ],
                ],
                'target_array'  => [
                    'four'  => (object) [ 'value' => 'four' ],
                    'two'   => (object) [ 'value' => 'two' ],
                    'three' => (object) [ 'value' => 'three' ],
                    'one'   => (object) [ 'value' => 'one' ],
                ],
                'orderby'       => 'id',
                'order'         => 'ASC',
                'preserve_keys' => false,
            ],
            'object[], string keys, $orderby a non-existent field, $order = DESC and $preserve_keys = true' => [
                'expected'      => [
                    'four'  => (object) [ 'value' => 'four' ],
                    'two'   => (object) [ 'value' => 'two' ],
                    'three' => (object) [ 'value' => 'three' ],
                    'one'   => (object) [ 'value' => 'one' ],
                ],
                'target_array'  => [
                    'four'  => (object) [ 'value' => 'four' ],
                    'two'   => (object) [ 'value' => 'two' ],
                    'three' => (object) [ 'value' => 'three' ],
                    'one'   => (object) [ 'value' => 'one' ],
                ],
                'orderby'       => 'id',
                'order'         => 'DESC',
                'preserve_keys' => true,
            ],
        ];
    }
}
