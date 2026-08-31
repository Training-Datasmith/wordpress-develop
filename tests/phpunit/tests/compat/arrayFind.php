<?php

declare(strict_types=1);

/**
 * @group compat
 *
 * @covers ::array_find
 */
class Tests_Compat_arrayFind extends WP_UnitTestCase
{
    /**
     * Test that array_find() is always available (either from PHP or WP).
     *
     * @ticket 62558
     */
    public function test_array_find_availability()
    {
        $this->assertTrue(function_exists('array_find'));
    }

    /**
     * @dataProvider data_array_find
     *
     * @ticket 62558
     *
     * @param mixed $expected The expected value.
     * @param array $arr      The array.
     * @param callable $callback The needle.
     */
    public function test_array_find($expected, array $arr, callable $callback)
    {
        $this->assertSame($expected, array_find($arr, $callback));
    }

    /**
     * Data provider.
     *
     * @return array[]
     */
    public function data_array_find(): array
    {
        return [
            'empty array'          => [
                'expected' => null,
                'arr'      => [],
                'callback' => function ($value) {
                    return 1 === $value;
                },
            ],
            'no match'             => [
                'expected' => null,
                'arr'      => [ 2, 3, 4 ],
                'callback' => function ($value) {
                    return 1 === $value;
                },
            ],
            'match'                => [
                'expected' => 3,
                'arr'      => [ 2, 3, 4 ],
                'callback' => function ($value) {
                    return 3 === $value;
                },
            ],
            'key match'            => [
                'expected' => 3,
                'arr'      => [
                    'a' => 2,
                    'b' => 3,
                    'c' => 4,
                ],
                'callback' => function ($value) {
                    return 3 === $value;
                },
            ],
            'two callback matches' => [
                'expected' => 2,
                'arr'      => [ 2, 3, 4 ],
                'callback' => function ($value) {
                    return 0 === $value % 2;
                },
            ],

        ];
    }
}
