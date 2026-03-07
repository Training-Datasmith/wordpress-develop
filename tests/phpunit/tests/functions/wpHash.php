<?php

declare(strict_types=1);

/**
 * Tests for the behavior of `wp_hash()`
 *
 * @group functions
 *
 * @covers ::wp_hash
 */
class Tests_Functions_wpHash extends WP_UnitTestCase
{
    /**
     * @dataProvider data_wp_hash_uses_specified_algorithm
     *
     * @ticket 62005
     */
    public function test_wp_hash_uses_specified_algorithm(string $algo, int $expected_length)
    {
        $hash = wp_hash('data', 'auth', $algo);

        $this->assertSame($expected_length, strlen($hash));
    }

    public function data_wp_hash_uses_specified_algorithm()
    {
        return [
            [ 'md5', 32 ],
            [ 'sha1', 40 ],
            [ 'sha256', 64 ],
        ];
    }

    /**
     * @ticket 62005
     */
    public function test_wp_hash_throws_exception_on_invalid_algorithm()
    {
        $this->expectException('InvalidArgumentException');

        wp_hash('data', 'auth', 'invalid');
    }
}
