<?php

declare(strict_types=1);

/**
 * Tests for the behavior of `wp_cache_get_salted()`
 *
 * @group functions
 * @group cache
 *
 * @covers ::wp_cache_get_salted
 */
class Tests_Functions_wpCacheGetSalted extends WP_UnitTestCase
{
    /**
     * Test that wp_cache_get_salted returns the cached data.
     *
     * @ticket 59592
     */
    public function test_wp_cache_get_salted_return_data()
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

        $result = wp_cache_get_salted('cache_key', 'query_data', $last_changed);

        $this->assertSameSets($cache_value['data'], $result);
    }

    /**
     * Test that wp_cache_get_salted returns the cached data with a salt.
     *
     * @ticket 59592
     */
    public function test_wp_cache_get_salted_return_data_array_salt()
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

        $result = wp_cache_get_salted('cache_key', 'query_data', $last_changed);

        $this->assertSameSets($cache_value['data'], $result);
    }

    /**
     * Test that wp_cache_get_salted returns false when no data is cached.
     *
     * @dataProvider data_wp_cache_get_salted_return_false
     *
     * @ticket 59592
     */
    public function test_wp_cache_get_salted_return_false($cache_value)
    {
        wp_cache_set('cache_key', $cache_value, 'query_data');
        $last_changed = wp_cache_get_last_changed('query_data');
        $this->assertFalse(wp_cache_get_salted('cache_key', 'query_data', $last_changed));
    }

    /**
     * Data provider for test_wp_cache_get_salted_return_false.
     *
     * @return array[] Data provider.
     */
    public function data_wp_cache_get_salted_return_false()
    {
        return [
            [ false ],
            [ null ],
            [ '' ],
            [ 0 ],
            [ [] ],
            [ new StdClass() ],
            [ [ 'salt' => '123' ] ],
            [
                [
                    'salt' => '123',
                    'data' => [],
                ],
            ],
            [ [ 'data' => [] ] ],
        ];
    }
}
