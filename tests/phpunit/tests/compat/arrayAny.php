<?php

declare(strict_types=1);

/**
 * @group compat
 *
 * @covers ::array_any
 */
class Test_Compat_arrayAny extends WP_UnitTestCase
{
    /**
     * Test that array_any() is always available (either from PHP or WP).
     *
     * @ticket 62558
     */
    public function test_array_any_availability()
    {
        $this->assertTrue(function_exists('array_any'));
    }

    /**
     * @dataProvider data_array_any
     *
     * @ticket 62558
     *
     * @param bool $expected The expected value.
     * @param array $arr The array.
     * @param callable $callback The callback.
     */
    public function test_array_any(bool $expected, array $arr, callable $callback)
    {
        $this->assertSame($expected, array_any($arr, $callback));
    }

    /**
     * Data provider.
     *
     * @return array[]
     */
    public function data_array_any(): array
    {
        return [
            'empty array' => [
                'expected' => false,
                'arr'      => [],
                'callback' => function ($value) {
                    return 1 === $value;
                },
            ],
            'no match'    => [
                'expected' => false,
                'arr'      => [ 2, 3, 4 ],
                'callback' => function ($value) {
                    return 1 === $value;
                },
            ],
            'match'       => [
                'expected' => true,
                'arr'      => [ 2, 3, 4 ],
                'callback' => function ($value) {
                    return 3 === $value;
                },
            ],
            'key match'   => [
                'expected' => true,
                'arr'      => [
                    'a' => 2,
                    'b' => 3,
                    'c' => 4,
                ],
                'callback' => function ($value, $key) {
                    return 'c' === $key;
                },
            ],
        ];
    }
}
