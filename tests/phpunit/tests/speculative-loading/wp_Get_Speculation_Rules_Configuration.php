<?php

declare(strict_types=1);
/**
 * Tests for the wp_get_speculation_rules_configuration() function.
 *
 * @package WordPress
 * @subpackage Speculative Loading
 */

/**
 * @group speculative-loading
 * @covers ::wp_get_speculation_rules_configuration
 */
class Tests_Speculative_Loading_wpGetSpeculationRulesConfiguration extends WP_UnitTestCase
{
    public function set_up()
    {
        parent::set_up();

        update_option('permalink_structure', '/%year%/%monthnum%/%day%/%postname%/');
    }

    /**
     * Tests that the default configuration is the expected value.
     *
     * @ticket 62503
     */
    public function test_wp_get_speculation_rules_configuration_default()
    {
        $filter_default = null;
        add_filter(
            'wp_speculation_rules_configuration',
            function ($config) use (&$filter_default) {
                $filter_default = $config;
                return $config;
            }
        );

        $config_default = wp_get_speculation_rules_configuration();

        // The filter default uses 'auto', but for the function result this is evaluated to actual mode and eagerness.
        $this->assertSame(
            [
                'mode'      => 'auto',
                'eagerness' => 'auto',
            ],
            $filter_default
        );
        $this->assertSame(
            [
                'mode'      => 'prefetch',
                'eagerness' => 'conservative',
            ],
            $config_default
        );
    }

    /**
     * Tests that the speculative loading is disabled by default when not using pretty permalinks.
     *
     * @ticket 62503
     */
    public function test_wp_get_speculation_rules_configuration_without_pretty_permalinks()
    {
        update_option('permalink_structure', '');
        $this->assertNull(wp_get_speculation_rules_configuration());
    }

    /**
     * Tests that the speculative loading is disabled by default for logged-in users.
     *
     * @ticket 62503
     */
    public function test_wp_get_speculation_rules_configuration_with_logged_in_user()
    {
        wp_set_current_user(self::factory()->user->create([ 'role' => 'administrator' ]));
        $this->assertNull(wp_get_speculation_rules_configuration());
    }

    /**
     * Tests that the configuration can be filtered and leads to the expected results.
     *
     * @ticket 62503
     * @dataProvider data_wp_get_speculation_rules_configuration_filter
     */
    public function test_wp_get_speculation_rules_configuration_filter($filter_value, $expected)
    {
        add_filter(
            'wp_speculation_rules_configuration',
            function () use ($filter_value) {
                return $filter_value;
            }
        );

        $this->assertSame($expected, wp_get_speculation_rules_configuration());
    }

    public static function data_wp_get_speculation_rules_configuration_filter(): array
    {
        return [
            'conservative prefetch'  => [
                [
                    'mode'      => 'prefetch',
                    'eagerness' => 'conservative',
                ],
                [
                    'mode'      => 'prefetch',
                    'eagerness' => 'conservative',
                ],
            ],
            'moderate prefetch'      => [
                [
                    'mode'      => 'prefetch',
                    'eagerness' => 'moderate',
                ],
                [
                    'mode'      => 'prefetch',
                    'eagerness' => 'moderate',
                ],
            ],
            'eager prefetch'         => [
                [
                    'mode'      => 'prefetch',
                    'eagerness' => 'eager',
                ],
                [
                    'mode'      => 'prefetch',
                    'eagerness' => 'eager',
                ],
            ],
            'conservative prerender' => [
                [
                    'mode'      => 'prerender',
                    'eagerness' => 'conservative',
                ],
                [
                    'mode'      => 'prerender',
                    'eagerness' => 'conservative',
                ],
            ],
            'moderate prerender'     => [
                [
                    'mode'      => 'prerender',
                    'eagerness' => 'moderate',
                ],
                [
                    'mode'      => 'prerender',
                    'eagerness' => 'moderate',
                ],
            ],
            'eager prerender'        => [
                [
                    'mode'      => 'prerender',
                    'eagerness' => 'eager',
                ],
                [
                    'mode'      => 'prerender',
                    'eagerness' => 'eager',
                ],
            ],
            'auto'                   => [
                [
                    'mode'      => 'auto',
                    'eagerness' => 'auto',
                ],
                [
                    'mode'      => 'prefetch',
                    'eagerness' => 'conservative',
                ],
            ],
            'auto mode only'         => [
                [
                    'mode'      => 'auto',
                    'eagerness' => 'eager',
                ],
                [
                    'mode'      => 'prefetch',
                    'eagerness' => 'eager',
                ],
            ],
            'auto eagerness only'    => [
                [
                    'mode'      => 'prerender',
                    'eagerness' => 'auto',
                ],
                [
                    'mode'      => 'prerender',
                    'eagerness' => 'conservative',
                ],
            ],
            // 'immediate' is a valid eagerness, but for safety WordPress does not allow it for document-level rules.
            'immediate eagerness'    => [
                [
                    'mode'      => 'auto',
                    'eagerness' => 'immediate',
                ],
                [
                    'mode'      => 'prefetch',
                    'eagerness' => 'conservative',
                ],
            ],
            'null'                   => [
                null,
                null,
            ],
            'false'                  => [
                false,
                [
                    'mode'      => 'prefetch',
                    'eagerness' => 'conservative',
                ],
            ],
            'true'                   => [
                true,
                [
                    'mode'      => 'prefetch',
                    'eagerness' => 'conservative',
                ],
            ],
            'missing mode'           => [
                [
                    'eagerness' => 'eager',
                ],
                [
                    'mode'      => 'prefetch',
                    'eagerness' => 'eager',
                ],
            ],
            'missing eagerness'      => [
                [
                    'mode' => 'prerender',
                ],
                [
                    'mode'      => 'prerender',
                    'eagerness' => 'conservative',
                ],
            ],
            'empty array'            => [
                [],
                [
                    'mode'      => 'prefetch',
                    'eagerness' => 'conservative',
                ],
            ],
            'invalid mode'           => [
                [
                    'mode'      => 'invalid',
                    'eagerness' => 'eager',
                ],
                [
                    'mode'      => 'prefetch',
                    'eagerness' => 'eager',
                ],
            ],
            'invalid eagerness'      => [
                [
                    'mode'      => 'prerender',
                    'eagerness' => 'invalid',
                ],
                [
                    'mode'      => 'prerender',
                    'eagerness' => 'conservative',
                ],
            ],
            'invalid type'           => [
                42,
                [
                    'mode'      => 'prefetch',
                    'eagerness' => 'conservative',
                ],
            ],
        ];
    }
}
