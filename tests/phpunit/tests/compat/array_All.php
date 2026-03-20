<?php

declare(strict_types=1);

/**
 * @group compat
 *
 * @covers ::array_all
 */
class Test_Compat_arrayAll extends WP_UnitTestCase
{
    /**
     * Test that array_all() is always available (either from PHP or WP).
     *
     * @ticket 62558
     */
    public function test_array_all_availability()
    {
        $this->assertTrue(function_exists('array_all'));
    }

    /**
     * @dataProvider data_array_all
     *
     * @ticket 62558
     *
     * @param bool $expected The expected value.
     * @param array $arr The array.
     * @param callable $callback The callback.
     */
    public function test_array_all(bool $expected, array $arr, callable $callback)
    {
        $this->assertSame($expected, array_all($arr, $callback));
    }

    /**
     * Data provider.
     *
     * @return array[]
     */
    public function data_array_all(): array
    {
        return [
            'empty array'   => [
                'expected' => true,
                'arr'      => [],
                'callback' => function ($value) {
                    return 1 === $value;
                },
            ],
            'no match'      => [
                'expected' => false,
                'arr'      => [ 2, 3, 4 ],
                'callback' => function ($value) {
                    return 1 === $value;
                },
            ],
            'not all match' => [
                'expected' => false,
                'arr'      => [ 2, 3, 4 ],
                'callback' => function ($value) {
                    return 0 === $value % 2;
                },
            ],
            'match'         => [
                'expected' => true,
                'arr'      => [ 2, 4, 6 ],
                'callback' => function ($value) {
                    return 0 === $value % 2;
                },
            ],
            'key match'     => [
                'expected' => true,
                'arr'      => [
                    'a' => 2,
                    'b' => 4,
                    'c' => 6,
                ],
                'callback' => function ($value, $key) {
                    return strlen($key) === 1;
                },
            ],
        ];
    }
}
