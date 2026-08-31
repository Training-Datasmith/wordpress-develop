<?php

declare(strict_types=1);

/**
 * Test wp_list_sort().
 *
 * @group functions
 *
 * @covers ::wp_list_sort
 */
class Tests_Functions_wpListSort extends WP_UnitTestCase
{
    /**
     * @dataProvider data_wp_list_sort
     *
     * @param string|array $orderby Either the field name to order by or an array
     *                              of multiple orderby fields as `$orderby => $order`.
     * @param string       $order   Either 'ASC' or 'DESC'.
     */
    public function test_wp_list_sort($input_list, $orderby, $order, $expected)
    {
        $this->assertSame($expected, wp_list_sort($input_list, $orderby, $order));
    }

    /**
     * Data provider.
     *
     * @return array[]
     */
    public function data_wp_list_sort()
    {
        return [
            'single orderby ascending'        => [
                [
                    [
                        'foo' => 'bar',
                        'bar' => 'baz',
                        'key' => 'foo',
                    ],
                    [
                        'foo'   => 'foo',
                        'lorem' => 'ipsum',
                        'key'   => 'bar',
                    ],
                    [
                        'foo' => 'baz',
                        'key' => 'value',
                    ],
                ],
                'foo',
                'ASC',
                [
                    [
                        'foo' => 'bar',
                        'bar' => 'baz',
                        'key' => 'foo',
                    ],
                    [
                        'foo' => 'baz',
                        'key' => 'value',
                    ],
                    [
                        'foo'   => 'foo',
                        'lorem' => 'ipsum',
                        'key'   => 'bar',
                    ],
                ],
            ],
            'single orderby descending'       => [
                [
                    [
                        'foo' => 'bar',
                        'bar' => 'baz',
                        'key' => 'foo',
                    ],
                    [
                        'foo'   => 'foo',
                        'lorem' => 'ipsum',
                        'key'   => 'bar',
                    ],
                    [
                        'foo' => 'baz',
                        'key' => 'value',
                    ],
                ],
                'foo',
                'DESC',
                [
                    [
                        'foo'   => 'foo',
                        'lorem' => 'ipsum',
                        'key'   => 'bar',
                    ],
                    [
                        'foo' => 'baz',
                        'key' => 'value',
                    ],
                    [
                        'foo' => 'bar',
                        'bar' => 'baz',
                        'key' => 'foo',
                    ],
                ],
            ],
            'single orderby array ascending'  => [
                [
                    [
                        'foo' => 'bar',
                        'bar' => 'baz',
                        'key' => 'foo',
                    ],
                    [
                        'foo'   => 'foo',
                        'lorem' => 'ipsum',
                        'key'   => 'bar',
                    ],
                    [
                        'foo' => 'baz',
                        'key' => 'value',
                    ],
                ],
                [ 'foo' => 'ASC' ],
                'IGNORED',
                [
                    [
                        'foo' => 'bar',
                        'bar' => 'baz',
                        'key' => 'foo',
                    ],
                    [
                        'foo' => 'baz',
                        'key' => 'value',
                    ],
                    [
                        'foo'   => 'foo',
                        'lorem' => 'ipsum',
                        'key'   => 'bar',
                    ],
                ],
            ],
            'single orderby array descending' => [
                [
                    [
                        'foo' => 'bar',
                        'bar' => 'baz',
                        'key' => 'foo',
                    ],
                    [
                        'foo'   => 'foo',
                        'lorem' => 'ipsum',
                        'key'   => 'bar',
                    ],
                    [
                        'foo' => 'baz',
                        'key' => 'value',
                    ],
                ],
                [ 'foo' => 'DESC' ],
                'IGNORED',
                [
                    [
                        'foo'   => 'foo',
                        'lorem' => 'ipsum',
                        'key'   => 'bar',
                    ],
                    [
                        'foo' => 'baz',
                        'key' => 'value',
                    ],
                    [
                        'foo' => 'bar',
                        'bar' => 'baz',
                        'key' => 'foo',
                    ],
                ],
            ],
            'multiple orderby ascending'      => [
                [
                    [
                        'foo' => 'bar',
                        'bar' => 'baz',
                        'key' => 'foo',
                    ],
                    [
                        'foo'   => 'foo',
                        'lorem' => 'ipsum',
                        'key'   => 'bar',
                    ],
                    [
                        'foo' => 'foo',
                        'key' => 'key',
                    ],
                    [
                        'foo' => 'baz',
                        'key' => 'key',
                    ],
                    [
                        'foo' => 'bar',
                        'key' => 'value',
                    ],
                ],
                [
                    'key' => 'ASC',
                    'foo' => 'ASC',
                ],
                'IGNORED',
                [
                    [
                        'foo'   => 'foo',
                        'lorem' => 'ipsum',
                        'key'   => 'bar',
                    ],
                    [
                        'foo' => 'bar',
                        'bar' => 'baz',
                        'key' => 'foo',
                    ],
                    [
                        'foo' => 'baz',
                        'key' => 'key',
                    ],
                    [
                        'foo' => 'foo',
                        'key' => 'key',
                    ],
                    [
                        'foo' => 'bar',
                        'key' => 'value',
                    ],
                ],
            ],
            'multiple orderby descending'     => [
                [
                    [
                        'foo' => 'bar',
                        'bar' => 'baz',
                        'key' => 'foo',
                    ],
                    [
                        'foo'   => 'foo',
                        'lorem' => 'ipsum',
                        'key'   => 'bar',
                    ],
                    [
                        'foo' => 'foo',
                        'key' => 'key',
                    ],
                    [
                        'foo' => 'baz',
                        'key' => 'key',
                    ],
                    [
                        'foo' => 'bar',
                        'key' => 'value',
                    ],
                ],
                [
                    'key' => 'DESC',
                    'foo' => 'DESC',
                ],
                'IGNORED',
                [
                    [
                        'foo' => 'bar',
                        'key' => 'value',
                    ],
                    [
                        'foo' => 'foo',
                        'key' => 'key',
                    ],
                    [
                        'foo' => 'baz',
                        'key' => 'key',
                    ],
                    [
                        'foo' => 'bar',
                        'bar' => 'baz',
                        'key' => 'foo',
                    ],
                    [
                        'foo'   => 'foo',
                        'lorem' => 'ipsum',
                        'key'   => 'bar',
                    ],
                ],
            ],
            'multiple orderby mixed'          => [
                [
                    [
                        'foo' => 'bar',
                        'bar' => 'baz',
                        'key' => 'foo',
                    ],
                    [
                        'foo'   => 'foo',
                        'lorem' => 'ipsum',
                        'key'   => 'bar',
                    ],
                    [
                        'foo' => 'foo',
                        'key' => 'key',
                    ],
                    [
                        'foo' => 'baz',
                        'key' => 'key',
                    ],
                    [
                        'foo' => 'bar',
                        'key' => 'value',
                    ],
                ],
                [
                    'key' => 'DESC',
                    'foo' => 'ASC',
                ],
                'IGNORED',
                [
                    [
                        'foo' => 'bar',
                        'key' => 'value',
                    ],
                    [
                        'foo' => 'baz',
                        'key' => 'key',
                    ],
                    [
                        'foo' => 'foo',
                        'key' => 'key',
                    ],
                    [
                        'foo' => 'bar',
                        'bar' => 'baz',
                        'key' => 'foo',
                    ],
                    [
                        'foo'   => 'foo',
                        'lorem' => 'ipsum',
                        'key'   => 'bar',
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider data_wp_list_sort_preserve_keys
     *
     * @param string|array $orderby Either the field name to order by or an array
     *                              of multiple orderby fields as `$orderby => $order`.
     * @param string       $order   Either 'ASC' or 'DESC'.
     */
    public function test_wp_list_sort_preserve_keys($input_list, $orderby, $order, $expected)
    {
        $this->assertSame($expected, wp_list_sort($input_list, $orderby, $order, true));
    }

    /**
     * Data provider.
     *
     * @return array[]
     */
    public function data_wp_list_sort_preserve_keys()
    {
        return [
            'single orderby ascending'        => [
                [
                    'foobar' => [
                        'foo' => 'bar',
                        'bar' => 'baz',
                        'key' => 'foo',
                    ],
                    'foofoo' => [
                        'foo'   => 'foo',
                        'lorem' => 'ipsum',
                        'key'   => 'bar',
                    ],
                    'foobaz' => [
                        'foo' => 'baz',
                        'key' => 'value',
                    ],
                ],
                'foo',
                'ASC',
                [
                    'foobar' => [
                        'foo' => 'bar',
                        'bar' => 'baz',
                        'key' => 'foo',
                    ],
                    'foobaz' => [
                        'foo' => 'baz',
                        'key' => 'value',
                    ],
                    'foofoo' => [
                        'foo'   => 'foo',
                        'lorem' => 'ipsum',
                        'key'   => 'bar',
                    ],
                ],
            ],
            'single orderby descending'       => [
                [
                    'foobar' => [
                        'foo' => 'bar',
                        'bar' => 'baz',
                        'key' => 'foo',
                    ],
                    'foofoo' => [
                        'foo'   => 'foo',
                        'lorem' => 'ipsum',
                        'key'   => 'bar',
                    ],
                    'foobaz' => [
                        'foo' => 'baz',
                        'key' => 'value',
                    ],
                ],
                'foo',
                'DESC',
                [
                    'foofoo' => [
                        'foo'   => 'foo',
                        'lorem' => 'ipsum',
                        'key'   => 'bar',
                    ],
                    'foobaz' => [
                        'foo' => 'baz',
                        'key' => 'value',
                    ],
                    'foobar' => [
                        'foo' => 'bar',
                        'bar' => 'baz',
                        'key' => 'foo',
                    ],
                ],
            ],
            'single orderby array ascending'  => [
                [
                    'foobar' => [
                        'foo' => 'bar',
                        'bar' => 'baz',
                        'key' => 'foo',
                    ],
                    'foofoo' => [
                        'foo'   => 'foo',
                        'lorem' => 'ipsum',
                        'key'   => 'bar',
                    ],
                    'foobaz' => [
                        'foo' => 'baz',
                        'key' => 'value',
                    ],
                ],
                [ 'foo' => 'ASC' ],
                'IGNORED',
                [
                    'foobar' => [
                        'foo' => 'bar',
                        'bar' => 'baz',
                        'key' => 'foo',
                    ],
                    'foobaz' => [
                        'foo' => 'baz',
                        'key' => 'value',
                    ],
                    'foofoo' => [
                        'foo'   => 'foo',
                        'lorem' => 'ipsum',
                        'key'   => 'bar',
                    ],
                ],
            ],
            'single orderby array descending' => [
                [
                    'foobar' => [
                        'foo' => 'bar',
                        'bar' => 'baz',
                        'key' => 'foo',
                    ],
                    'foofoo' => [
                        'foo'   => 'foo',
                        'lorem' => 'ipsum',
                        'key'   => 'bar',
                    ],
                    'foobaz' => [
                        'foo' => 'baz',
                        'key' => 'value',
                    ],
                ],
                [ 'foo' => 'DESC' ],
                'IGNORED',
                [
                    'foofoo' => [
                        'foo'   => 'foo',
                        'lorem' => 'ipsum',
                        'key'   => 'bar',
                    ],
                    'foobaz' => [
                        'foo' => 'baz',
                        'key' => 'value',
                    ],
                    'foobar' => [
                        'foo' => 'bar',
                        'bar' => 'baz',
                        'key' => 'foo',
                    ],
                ],
            ],
            'multiple orderby ascending'      => [
                [
                    'foobarfoo'   => [
                        'foo' => 'bar',
                        'bar' => 'baz',
                        'key' => 'foo',
                    ],
                    'foofoobar'   => [
                        'foo'   => 'foo',
                        'lorem' => 'ipsum',
                        'key'   => 'bar',
                    ],
                    'foofookey'   => [
                        'foo' => 'foo',
                        'key' => 'key',
                    ],
                    'foobazkey'   => [
                        'foo' => 'baz',
                        'key' => 'key',
                    ],
                    'foobarvalue' => [
                        'foo' => 'bar',
                        'key' => 'value',
                    ],
                ],
                [
                    'key' => 'ASC',
                    'foo' => 'ASC',
                ],
                'IGNORED',
                [
                    'foofoobar'   => [
                        'foo'   => 'foo',
                        'lorem' => 'ipsum',
                        'key'   => 'bar',
                    ],
                    'foobarfoo'   => [
                        'foo' => 'bar',
                        'bar' => 'baz',
                        'key' => 'foo',
                    ],
                    'foobazkey'   => [
                        'foo' => 'baz',
                        'key' => 'key',
                    ],
                    'foofookey'   => [
                        'foo' => 'foo',
                        'key' => 'key',
                    ],
                    'foobarvalue' => [
                        'foo' => 'bar',
                        'key' => 'value',
                    ],
                ],
            ],
            'multiple orderby descending'     => [
                [
                    'foobarfoo'   => [
                        'foo' => 'bar',
                        'bar' => 'baz',
                        'key' => 'foo',
                    ],
                    'foofoobar'   => [
                        'foo'   => 'foo',
                        'lorem' => 'ipsum',
                        'key'   => 'bar',
                    ],
                    'foofookey'   => [
                        'foo' => 'foo',
                        'key' => 'key',
                    ],
                    'foobazkey'   => [
                        'foo' => 'baz',
                        'key' => 'key',
                    ],
                    'foobarvalue' => [
                        'foo' => 'bar',
                        'key' => 'value',
                    ],
                ],
                [
                    'key' => 'DESC',
                    'foo' => 'DESC',
                ],
                'IGNORED',
                [
                    'foobarvalue' => [
                        'foo' => 'bar',
                        'key' => 'value',
                    ],
                    'foofookey'   => [
                        'foo' => 'foo',
                        'key' => 'key',
                    ],
                    'foobazkey'   => [
                        'foo' => 'baz',
                        'key' => 'key',
                    ],
                    'foobarfoo'   => [
                        'foo' => 'bar',
                        'bar' => 'baz',
                        'key' => 'foo',
                    ],
                    'foofoobar'   => [
                        'foo'   => 'foo',
                        'lorem' => 'ipsum',
                        'key'   => 'bar',
                    ],
                ],
            ],
            'multiple orderby mixed'          => [
                [
                    'foobarfoo'   => [
                        'foo' => 'bar',
                        'bar' => 'baz',
                        'key' => 'foo',
                    ],
                    'foofoobar'   => [
                        'foo'   => 'foo',
                        'lorem' => 'ipsum',
                        'key'   => 'bar',
                    ],
                    'foofookey'   => [
                        'foo' => 'foo',
                        'key' => 'key',
                    ],
                    'foobazkey'   => [
                        'foo' => 'baz',
                        'key' => 'key',
                    ],
                    'foobarvalue' => [
                        'foo' => 'bar',
                        'key' => 'value',
                    ],
                ],
                [
                    'key' => 'DESC',
                    'foo' => 'ASC',
                ],
                'IGNORED',
                [
                    'foobarvalue' => [
                        'foo' => 'bar',
                        'key' => 'value',
                    ],
                    'foobazkey'   => [
                        'foo' => 'baz',
                        'key' => 'key',
                    ],
                    'foofookey'   => [
                        'foo' => 'foo',
                        'key' => 'key',
                    ],
                    'foobarfoo'   => [
                        'foo' => 'bar',
                        'bar' => 'baz',
                        'key' => 'foo',
                    ],
                    'foofoobar'   => [
                        'foo'   => 'foo',
                        'lorem' => 'ipsum',
                        'key'   => 'bar',
                    ],
                ],
            ],
        ];
    }
}
