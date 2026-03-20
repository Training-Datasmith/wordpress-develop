<?php

declare(strict_types=1);

/**
 * Tests for the behavior of `wp_cache_get_multiple_salted()`
 *
 * @group functions
 * @group cache
 *
 * @covers ::wp_cache_get_multiple_salted
 */
class Tests_Functions_wpCacheGetMultipleSalted extends WP_UnitTestCase
{
    /**
     * Test that wp_cache_get_multiple_salted returns the cached data.
     *
     * @ticket 59592
     */
    public function test_wp_cache_get_multiple_salted_return_data()
    {
        $last_changed = wp_cache_get_last_changed('query_data');
        $cache_value  = [
            'salt' => $last_changed,
            'data' => [
                'key1' => 'value1',
                'key2' => 'value2',
            ],
        ];
        wp_cache_set('cache_key', $cache_value, 'query_data');

        $result = wp_cache_get_multiple_salted([ 'cache_key' ], 'query_data', $last_changed);

        $this->assertSameSets($cache_value['data'], $result['cache_key']);
    }

    /**
     * Test that wp_cache_get_multiple_salted returns the cached data with a salt.
     *
     * @ticket 59592
     */
    public function test_wp_cache_get_multiple_salted_return_data_array_salt()
    {
        $last_changed        = [
            wp_cache_get_last_changed('query_data_1'),
            wp_cache_get_last_changed('query_data_2'),
        ];
        $last_changed_string = implode(':', $last_changed);
        $cache_value         = [
            'salt' => $last_changed_string,
            'data' => [
                'key1' => 'value1',
                'key2' => 'value2',
            ],
        ];
        wp_cache_set('cache_key', $cache_value, 'query_data');

        $result = wp_cache_get_multiple_salted([ 'cache_key' ], 'query_data', $last_changed);

        $this->assertSameSets($cache_value['data'], $result['cache_key']);
    }

    /**
     * Test that wp_cache_get_multiple_salted returns an array of false values when no data is cached.
     *
     * @ticket 59592
     */
    public function test_wp_cache_get_multiple_salted_return_false()
    {
        wp_cache_set('cache_key', false, 'query_data');
        wp_cache_set('another_key', null, 'query_data');

        $last_changed = wp_cache_get_last_changed('query_data');

        $result = wp_cache_get_multiple_salted([ 'cache_key', 'another_key' ], 'query_data', $last_changed);

        $this->assertSameSets(
            [
                'cache_key'   => false,
                'another_key' => false,
            ],
            $result
        );
    }

    /**
     * Test that wp_cache_get_multiple_salted returns the cached data for multiple keys.
     *
     * @ticket 59592
     */
    public function test_wp_cache_get_multiple_salted_with_some_false()
    {
        $last_changed = wp_cache_get_last_changed('query_data');
        wp_cache_set(
            'cache_key',
            [
                'salt' => $last_changed,
                'data' => [ 123 ],
            ],
            'query_data'
        );
        wp_cache_set(
            'another_key',
            [
                'salt' => '123',
                'data' => [],
            ],
            'query_data'
        );

        $last_changed = wp_cache_get_last_changed('query_data');

        $result = wp_cache_get_multiple_salted([ 'cache_key', 'another_key' ], 'query_data', $last_changed);

        $this->assertSameSets(
            [
                'cache_key'   => [ 123 ],
                'another_key' => false,
            ],
            $result
        );
    }
}
