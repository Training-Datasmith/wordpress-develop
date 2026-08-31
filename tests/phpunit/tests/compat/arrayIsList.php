<?php

declare(strict_types=1);

/**
 * @group compat
 *
 * @covers ::array_is_list
 */
class Tests_Compat_arrayIsList extends WP_UnitTestCase
{
    /**
     * Test that array_is_list() is always available (either from PHP or WP).
     *
     * @ticket 55105
     */
    public function test_array_is_list_availability()
    {
        $this->assertTrue(function_exists('array_is_list'));
    }

    /**
     * @dataProvider data_array_is_list
     *
     * @ticket 55105
     *
     * @param bool  $expected Whether the array is a list.
     * @param array $arr      The array.
     */
    public function test_array_is_list($expected, $arr)
    {
        $this->assertSame($expected, array_is_list($arr));
    }

    /**
     * Data provider.
     *
     * @return array[]
     */
    public function data_array_is_list()
    {
        return [
            'empty array'                   => [
                'expected' => true,
                'arr'      => [],
            ],
            'array(NAN)'                    => [
                'expected' => true,
                'arr'      => [ NAN ],
            ],
            'array( INF )'                  => [
                'expected' => true,
                'arr'      => [ INF ],
            ],
            'consecutive int keys from 0'   => [
                'expected' => true,
                'arr'      => [
                    0 => 'one',
                    1 => 'two',
                ],
            ],
            'consecutive float keys from 0' => [
                'expected' => true,
                'arr'      => [
                    0.0 => 'one',
                    1.0 => 'two',
                ],
            ],
            'consecutive str keys from 0'   => [
                'expected' => true,
                'arr'      => [
                    '0' => 'one',
                    '1' => 'two',
                ],
            ],
            'consecutive int keys from 1'   => [
                'expected' => false,
                'arr'      => [
                    1 => 'one',
                    2 => 'two',
                ],
            ],
            'consecutive float keys from 1' => [
                'expected' => false,
                'arr'      => [
                    1.0 => 'one',
                    2.0 => 'two',
                ],
            ],
            'consecutive str keys from 1'   => [
                'expected' => false,
                'arr'      => [
                    '1' => 'one',
                    '2' => 'two',
                ],
            ],
            'non-consecutive int keys'      => [
                'expected' => false,
                'arr'      => [
                    1 => 'one',
                    0 => 'two',
                ],
            ],
            'non-consecutive float keys'    => [
                'expected' => false,
                'arr'      => [
                    1.0 => 'one',
                    0.0 => 'two',
                ],
            ],
            'non-consecutive string keys'   => [
                'expected' => false,
                'arr'      => [
                    '1' => 'one',
                    '0' => 'two',
                ],
            ],
        ];
    }
}
