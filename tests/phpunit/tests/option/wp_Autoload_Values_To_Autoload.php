<?php

declare(strict_types=1);

/**
 * Tests for the wp_autoload_values_to_autoload function.
 *
 * @group Option
 *
 * @covers ::wp_autoload_values_to_autoload
 */
class Tests_Option_wpAutoloadValuesToAutoload extends WP_UnitTestCase
{
    /**
     * @ticket 42441
     */
    public function test_wp_autoload_values_to_autoload()
    {
        $this->assertSameSets([ 'yes', 'on', 'auto-on', 'auto' ], wp_autoload_values_to_autoload());
    }

    /**
     * @ticket 42441
     */
    public function test_wp_autoload_values_to_autoload_filter_remove()
    {

        add_filter(
            'wp_autoload_values_to_autoload',
            static function () {
                return [ 'yes' ];
            }
        );

        $this->assertSameSets([ 'yes' ], wp_autoload_values_to_autoload());
    }

    /**
     * @ticket 42441
     */
    public function test_wp_autoload_values_to_autoload_filter_extra()
    {

        add_filter(
            'wp_autoload_values_to_autoload',
            static function () {
                return [ 'yes', 'on', 'auto-on', 'auto', 'extra' ];
            }
        );

        $this->assertSameSets([ 'yes', 'on', 'auto-on', 'auto' ], wp_autoload_values_to_autoload());
    }

    /**
     * @ticket 42441
     */
    public function test_wp_autoload_values_to_autoload_filter_replace()
    {

        add_filter(
            'wp_autoload_values_to_autoload',
            static function () {
                return [ 'yes', 'on', 'auto-on', 'extra' ];
            }
        );

        $this->assertSameSets([ 'yes', 'on', 'auto-on' ], wp_autoload_values_to_autoload());
    }
}
