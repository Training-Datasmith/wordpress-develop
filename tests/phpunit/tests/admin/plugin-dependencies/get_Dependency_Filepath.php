<?php

declare(strict_types=1);
/**
 * Tests for the WP_Plugin_Dependencies::get_dependency_filepath() method.
 *
 * @package WordPress
 */

require_once __DIR__ . '/base.php';

/**
 * @group admin
 * @group plugins
 *
 * @covers WP_Plugin_Dependencies::get_dependency_filepath
 * @covers WP_Plugin_Dependencies::get_dependency_filepaths
 * @covers WP_Plugin_Dependencies::get_plugin_dirnames
 */
class Tests_Admin_WPPluginDependencies_GetDependencyFilepath extends WP_PluginDependencies_UnitTestCase
{
    /**
     * Tests that false is returned if Plugin Dependencies has not been initialized.
     *
     * @ticket 60457
     */
    public function test_should_return_false_before_initialization()
    {
        // Ensure Plugin Dependencies has not been initialized.
        $this->assertFalse(
            $this->get_property_value('initialized'),
            'Plugin Dependencies has been initialized.'
        );

        $this->assertSame(
            self::$static_properties['dependency_slugs'],
            $this->get_property_value('dependency_slugs'),
            '"dependency_slugs" was not set to its default value.'
        );

        $this->assertFalse(
            self::$instance->get_dependency_filepath('dependency'),
            'false was not returned before initialization.'
        );
    }

    /**
     * Tests that the expected dependency filepaths are retrieved for installed dependencies.
     *
     * @ticket 22316
     *
     * @dataProvider data_get_dependency_filepath
     *
     * @param string[]     $dependency_slug The dependency slug.
     * @param string[]     $plugins         An array of plugin paths.
     * @param string|false $expected       The expected result.
     */
    public function test_should_return_filepaths_for_installed_dependencies($dependency_slug, $plugins, $expected)
    {
        $this->set_property_value('plugins', $plugins);
        $this->assertNull($this->get_property_value('dependency_filepaths'));
        self::$instance::initialize();

        $this->assertSame(
            $expected,
            self::$instance::get_dependency_filepath($dependency_slug),
            'The incorrect filepath was returned.'
        );
    }

    /**
     * Data provider.
     *
     * @return array[]
     */
    public function data_get_dependency_filepath()
    {
        return [
            'no plugins'                      => [
                'dependency_slug' => 'dependency',
                'plugins'         => [],
                'expected'        => false,
            ],
            'a plugin that starts with slug/' => [
                'dependency_slug' => 'dependency',
                'plugins'         => [
                    'dependency-pro/dependency.php' => [ 'RequiresPlugins' => '' ],
                    'dependent/dependent.php'       => [ 'RequiresPlugins' => 'dependency' ],
                ],
                'expected'        => false,
            ],
            'a plugin that ends with slug/'   => [
                'dependency_slugs' => 'dependency',
                'plugins'          => [
                    'addon-for-dependency/dependency.php' => [ 'RequiresPlugins' => '' ],
                    'dependent/dependent.php'             => [ 'RequiresPlugins' => 'dependency' ],
                ],
                'expected'         => false,
            ],
            'a plugin that does not exist'    => [
                'dependency_slugs' => 'dependency2',
                'plugins'          => [
                    'dependency/dependency.php' => [ 'RequiresPlugins' => '' ],
                    'dependent/dependent.php'   => [ 'RequiresPlugins' => 'dependency2' ],
                ],
                'expected'         => false,
            ],
            'a plugin that exists'            => [
                'dependency_slugs' => 'dependency',
                'plugins'          => [
                    'dependency/dependency.php' => [ 'RequiresPlugins' => '' ],
                    'dependent/dependent.php'   => [ 'RequiresPlugins' => 'dependency' ],
                ],
                'expected'         => 'dependency/dependency.php',
            ],
        ];
    }

    /**
     * Tests that an existing value for dependency filepaths is returned.
     *
     * @ticket 22316
     */
    public function test_should_return_existing_value_for_dependency_filepaths()
    {
        $expected = 'dependency/dependency.php';

        $this->set_property_value('dependency_filepaths', [ 'dependency' => $expected ]);

        /*
         * If existing dependency filepaths are not returned,
         * they'll be built from this data.
         *
         * This data is explicitly set to ensure that no
         * test plugins ever interfere with this test.
         */
        $this->set_property_value(
            'dependency_slugs',
            [ 'dependency', 'dependency2', 'dependency3' ]
        );

        $this->set_property_value(
            'plugins',
            [
                // This is flipped as paths are stored in the keys.
                'dependency/dependency.php'   => [],
                'dependency2/dependency2.php' => [],
                'dependency3/dependency3.php' => [],
            ]
        );

        $this->assertSame($expected, self::$instance::get_dependency_filepath('dependency'));
    }

    /**
     * Tests that an empty array is returned when
     * no plugin directory names are stored.
     *
     * @ticket 22316
     */
    public function test_should_return_empty_array_for_no_plugin_dirnames()
    {
        $this->set_property_value('dependency_slugs', []);
        $this->assertFalse(self::$instance::get_dependency_filepath('dependency'));
    }
}
