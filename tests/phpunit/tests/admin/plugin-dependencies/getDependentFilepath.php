<?php

declare(strict_types=1);
/**
 * Tests for the WP_Plugin_Dependencies::get_dependent_filepath() method.
 *
 * @package WordPress
 */

require_once __DIR__ . '/base.php';

/**
 * @group admin
 * @group plugins
 *
 * @covers WP_Plugin_Dependencies::get_dependent_filepath
 * @covers WP_Plugin_Dependencies::get_plugin_dirnames
 */
class Tests_Admin_WPPluginDependencies_GetDependentFilepath extends WP_PluginDependencies_UnitTestCase
{
    /**
     * Tests that the expected dependent filepath is retrieved.
     *
     * @ticket 22316
     *
     * @dataProvider data_get_dependent_filepath
     *
     * @param string       $dependent_slug The dependent slug.
     * @param string[]     $plugins        An array of plugin data.
     * @param string|false $expected       The expected result.
     */
    public function test_should_return_filepaths_for_installed_dependents($dependent_slug, $plugins, $expected)
    {
        $this->set_property_value('plugins', $plugins);
        self::$instance::initialize();

        $this->assertSame(
            $expected,
            self::$instance::get_dependent_filepath($dependent_slug),
            'The incorrect filepath was returned.'
        );
    }

    /**
     * Data provider.
     *
     * @return array[]
     */
    public function data_get_dependent_filepath()
    {
        return [
            'a plugin that exists'            => [
                'dependent_slug' => 'dependent',
                'plugins'        => [ 'dependent/dependent.php' => [ 'RequiresPlugins' => 'woocommerce' ] ],
                'expected'       => 'dependent/dependent.php',
            ],
            'no plugins'                      => [
                'dependent_slug' => 'dependent',
                'plugins'        => [],
                'expected'       => false,
            ],
            'a plugin that starts with slug/' => [
                'dependent_slug' => 'dependent',
                'plugins'        => [ 'dependent-pro/dependent.php' => [ 'RequiresPlugins' => 'woocommerce' ] ],
                'expected'       => false,
            ],
            'a plugin that ends with slug/'   => [
                'dependent_slug' => 'dependent',
                'plugins'        => [ 'not-dependent/not-dependent.php' => [ 'RequiresPlugins' => 'woocommerce' ] ],
                'expected'       => false,
            ],
            'a plugin that does not exist'    => [
                'dependent_slug' => 'dependent2',
                'plugins'        => [ 'dependent/dependent.php' => [ 'RequiresPlugins' => 'woocommerce' ] ],
                'expected'       => false,
            ],
        ];
    }
}
