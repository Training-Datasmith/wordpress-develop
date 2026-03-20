<?php

declare(strict_types=1);

/**
 * Test wp_list_filter().
 *
 * @group functions
 *
 * @covers ::wp_list_filter
 */
class Tests_Functions_wpListFilter extends WP_UnitTestCase
{
    /**
     * @dataProvider data_wp_list_filter
     *
     * @param array  $input_list An array of objects to filter.
     * @param array  $args       An array of key => value arguments to match
     *                           against each object.
     * @param string $operator   The logical operation to perform.
     * @param array  $expected   Expected result.
     */
    public function test_wp_list_filter($input_list, $args, $operator, $expected)
    {
        $this->assertEqualSetsWithIndex($expected, wp_list_filter($input_list, $args, $operator));
    }

    /**
     * Data provider.
     *
     * @return array[]
     */
    public function data_wp_list_filter()
    {
        return [
            'string instead of array'  => [
                'foo',
                [],
                'AND',
                [],
            ],
            'object instead of array'  => [
                (object) [ 'foo' ],
                [],
                'AND',
                [],
            ],
            'empty args'               => [
                [ 'foo', 'bar' ],
                [],
                'AND',
                [ 'foo', 'bar' ],
            ],
            'invalid operator'         => [
                [
                    (object) [ 'foo' => 'bar' ],
                    (object) [ 'foo' => 'baz' ],
                ],
                [ 'foo' => 'bar' ],
                'XOR',
                [],
            ],
            'single argument to match' => [
                [
                    (object) [
                        'foo' => 'bar',
                        'bar' => 'baz',
                        'abc' => 'xyz',
                        'key' => 'foo',
                    ],
                    (object) [
                        'foo'   => 'foo',
                        '123'   => '456',
                        'lorem' => 'ipsum',
                        'key'   => 'bar',
                    ],
                    (object) [
                        'foo' => 'baz',
                        'key' => 'value',
                    ],
                    (object) [
                        'foo' => 'bar',
                        'key' => 'value',
                    ],
                ],
                [ 'foo' => 'bar' ],
                'AND',
                [
                    0 => (object) [
                        'foo' => 'bar',
                        'bar' => 'baz',
                        'abc' => 'xyz',
                        'key' => 'foo',
                    ],
                    3 => (object) [
                        'foo' => 'bar',
                        'key' => 'value',
                    ],
                ],
            ],
            'all must match'           => [
                [
                    (object) [
                        'foo' => 'bar',
                        'bar' => 'baz',
                        'abc' => 'xyz',
                        'key' => 'foo',
                    ],
                    (object) [
                        'foo'   => 'foo',
                        '123'   => '456',
                        'lorem' => 'ipsum',
                        'key'   => 'bar',
                    ],
                    (object) [
                        'foo' => 'baz',
                        'key' => 'value',
                        'bar' => 'baz',
                    ],
                    (object) [
                        'foo' => 'bar',
                        'key' => 'value',
                    ],
                ],
                [
                    'foo' => 'bar',
                    'bar' => 'baz',
                ],
                'AND',
                [
                    0 => (object) [
                        'foo' => 'bar',
                        'bar' => 'baz',
                        'abc' => 'xyz',
                        'key' => 'foo',
                    ],
                ],
            ],
            'any must match'           => [
                [
                    (object) [
                        'foo' => 'bar',
                        'bar' => 'baz',
                        'abc' => 'xyz',
                        'key' => 'foo',
                    ],
                    (object) [
                        'foo'   => 'foo',
                        '123'   => '456',
                        'lorem' => 'ipsum',
                        'key'   => 'bar',
                    ],
                    (object) [
                        'foo' => 'baz',
                        'key' => 'value',
                        'bar' => 'baz',
                    ],
                    (object) [
                        'foo' => 'bar',
                        'key' => 'value',
                    ],
                ],
                [
                    'key' => 'value',
                    'bar' => 'baz',
                ],
                'OR',
                [
                    0 => (object) [
                        'foo' => 'bar',
                        'bar' => 'baz',
                        'abc' => 'xyz',
                        'key' => 'foo',
                    ],
                    2 => (object) [
                        'foo' => 'baz',
                        'key' => 'value',
                        'bar' => 'baz',
                    ],
                    3 => (object) [
                        'foo' => 'bar',
                        'key' => 'value',
                    ],
                ],
            ],
            'none must match'          => [
                [
                    (object) [
                        'foo' => 'bar',
                        'bar' => 'baz',
                        'abc' => 'xyz',
                        'key' => 'foo',
                    ],
                    (object) [
                        'foo'   => 'foo',
                        '123'   => '456',
                        'lorem' => 'ipsum',
                        'key'   => 'bar',
                    ],
                    (object) [
                        'foo' => 'baz',
                        'key' => 'value',
                    ],
                    (object) [
                        'foo' => 'bar',
                        'key' => 'value',
                    ],
                ],
                [
                    'key' => 'value',
                    'bar' => 'baz',
                ],
                'NOT',
                [
                    1 => (object) [
                        'foo'   => 'foo',
                        '123'   => '456',
                        'lorem' => 'ipsum',
                        'key'   => 'bar',
                    ],
                ],
            ],
            'string to int comparison' => [
                [
                    (object) [
                        'foo' => '1',
                    ],
                ],
                [ 'foo' => 1 ],
                'AND',
                [
                    0 => (object) [
                        'foo' => '1',
                    ],
                ],
            ],
        ];
    }
}
