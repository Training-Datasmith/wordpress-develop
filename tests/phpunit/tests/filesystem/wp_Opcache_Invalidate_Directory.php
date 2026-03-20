<?php

declare(strict_types=1);

/**
 * Tests wp_opcache_invalidate_directory().
 *
 * @group file
 * @group filesystem
 *
 * @covers ::wp_opcache_invalidate_directory
 */
class Tests_Filesystem_WpOpcacheInvalidateDirectory extends WP_UnitTestCase
{
    /**
     * Sets up the filesystem before any tests run.
     */
    public static function set_up_before_class()
    {
        global $wp_filesystem;

        parent::set_up_before_class();

        if (! $wp_filesystem) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
            WP_Filesystem();
        }
    }

    /**
     * Tests that wp_opcache_invalidate_directory() returns a WP_Error object
     * when the $dir argument invalid.
     *
     * @ticket 57375
     *
     * @dataProvider data_should_trigger_error_with_invalid_dir
     *
     * @param mixed $dir An invalid directory path.
     */
    public function test_should_trigger_error_with_invalid_dir($dir)
    {
        $this->expectError();
        $this->expectErrorMessage(
            '<code>wp_opcache_invalidate_directory()</code> expects a non-empty string.',
            'The expected error was not triggered.'
        );

        wp_opcache_invalidate_directory($dir);
    }

    /**
     * Data provider.
     *
     * @return array[]
     */
    public function data_should_trigger_error_with_invalid_dir()
    {
        return [
            'an empty string'                => [ '' ],
            'a string with spaces'           => [ '   ' ],
            'a string with tabs'             => [ "\t" ],
            'a string with new lines'        => [ "\n" ],
            'a string with carriage returns' => [ "\r" ],
            'int -1'                         => [ -1 ],
            'int 0'                          => [ 0 ],
            'int 1'                          => [ 1 ],
            'float -1.0'                     => [ -1.0 ],
            'float 0.0'                      => [ 0.0 ],
            'float 1.0'                      => [ 1.0 ],
            'false'                          => [ false ],
            'true'                           => [ true ],
            'null'                           => [ null ],
            'an empty array'                 => [ [] ],
            'a non-empty array'              => [ [ 'directory_path' ] ],
            'an empty object'                => [ new stdClass() ],
            'a non-empty object'             => [ (object) [ 'directory_path' ] ],
            'INF'                            => [ INF ],
            'NAN'                            => [ NAN ],
        ];
    }

    /**
     * Tests that wp_opcache_invalidate_directory() does not trigger an error
     * with a valid directory.
     *
     * @ticket 57375
     *
     * @dataProvider data_should_not_trigger_error_wp_opcache_valid_directory
     *
     * @param string $dir A directory path.
     */
    public function test_should_not_trigger_error_wp_opcache_valid_directory($dir)
    {
        $this->assertNull(wp_opcache_invalidate_directory($dir));
    }

    /**
     * Data provider.
     *
     * @return array[]
     */
    public function data_should_not_trigger_error_wp_opcache_valid_directory()
    {
        return [
            'an existing directory'    => [ DIR_TESTDATA ],
            'a non-existent directory' => [ 'non_existent_directory' ],
        ];
    }
}
