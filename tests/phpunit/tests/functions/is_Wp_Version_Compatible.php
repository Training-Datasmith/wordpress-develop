<?php

declare(strict_types=1);

/**
 * Tests the is_wp_version_compatible() function.
 *
 * @group functions
 *
 * @covers ::is_wp_version_compatible
 */
class Tests_Functions_IsWpVersionCompatible extends WP_UnitTestCase
{
    /**
     * The current WordPress version.
     *
     * @var string
     */
    private static $wp_version;

    /**
     * Sets the test WordPress version property and global before any tests run.
     */
    public static function set_up_before_class()
    {
        parent::set_up_before_class();
        self::$wp_version                = wp_get_wp_version();
        $GLOBALS['_wp_tests_wp_version'] = self::$wp_version;
    }

    /**
     * Resets the test WordPress version global after each test runs.
     */
    public function tear_down()
    {
        $GLOBALS['_wp_tests_wp_version'] = self::$wp_version;
        parent::tear_down();
    }

    /**
     * Unsets the test WordPress version global after all tests run.
     */
    public static function tear_down_after_class()
    {
        unset($GLOBALS['_wp_tests_wp_version']);
        parent::tear_down_after_class();
    }

    /**
     * Tests is_wp_version_compatible().
     *
     * @dataProvider data_is_wp_version_compatible
     *
     * @ticket 54257
     * @ticket 61781
     *
     * @param mixed $required The minimum required WordPress version.
     * @param bool  $expected The expected result.
     */
    public function test_is_wp_version_compatible($required, $expected)
    {
        $this->assertSame($expected, is_wp_version_compatible($required));
    }

    /**
     * Data provider.
     *
     * @return array[]
     */
    public function data_is_wp_version_compatible()
    {
        $wp_version     = wp_get_wp_version();
        $version_parts  = explode('.', $wp_version);
        $lower_version  = $version_parts;
        $higher_version = $version_parts;

        // Adjust the major version numbers.
        --$lower_version[0];
        ++$higher_version[0];

        $lower_version  = implode('.', $lower_version);
        $higher_version = implode('.', $higher_version);

        return [
            // Happy paths.
            'the same version'                => [
                'required' => $wp_version,
                'expected' => true,
            ],
            'a lower required version'        => [
                'required' => $lower_version,
                'expected' => true,
            ],
            'a higher required version'       => [
                'required' => $higher_version,
                'expected' => false,
            ],

            // Acceptable versions containing '.0'.
            'correct version ending with x.0' => [
                'required' => '5.0',
                'expected' => true,
            ],
            'correct version with x.0.x in middle of version' => [
                'required' => '5.0.1',
                'expected' => true,
            ],

            // Falsey values.
            'false'                           => [
                'required' => false,
                'expected' => true,
            ],
            'null'                            => [
                'required' => null,
                'expected' => true,
            ],
            '0 int'                           => [
                'required' => 0,
                'expected' => true,
            ],
            '0.0 float'                       => [
                'required' => 0.0,
                'expected' => true,
            ],
            '0 string'                        => [
                'required' => '0',
                'expected' => true,
            ],
            'empty string'                    => [
                'required' => '',
                'expected' => true,
            ],
            'empty array'                     => [
                'required' => [],
                'expected' => true,
            ],
        ];
    }

    /**
     * Tests that is_wp_version_compatible() gracefully handles incorrect version numbering.
     *
     * @dataProvider data_is_wp_version_compatible_should_gracefully_handle_trailing_point_zero_version_numbers
     *
     * @ticket 59448
     * @ticket 61781
     *
     * @param mixed  $required The minimum required WordPress version.
     * @param string $wp       The value for the $wp_version global variable.
     * @param bool   $expected The expected result.
     */
    public function test_is_wp_version_compatible_should_gracefully_handle_trailing_point_zero_version_numbers($required, $wp, $expected)
    {
        $GLOBALS['_wp_tests_wp_version'] = $wp;
        $this->assertSame($expected, is_wp_version_compatible($required), 'The expected result was not returned.');
    }

    /**
     * Data provider.
     *
     * @return array[]
     */
    public function data_is_wp_version_compatible_should_gracefully_handle_trailing_point_zero_version_numbers()
    {
        return [
            'an incorrect trailing .0 and the same version' => [
                'required' => '5.2.0',
                'wp'       => '5.2',
                'expected' => true,
            ],
            'an incorrect trailing .0 and the same x.0 version' => [
                'required' => '5.0.0',
                'wp'       => '5.0',
                'expected' => true,
            ],
            'an incorrect trailing .0 and space and same x.0 version' => [
                'required' => '5.0.0 ',
                'wp'       => '5.0',
                'expected' => true,
            ],
            'incorrect preceding and trailing spaces trailing .0' => [
                'required' => ' 5.0.0 ',
                'wp'       => '5.0',
                'expected' => true,
            ],
            'an incorrect trailing .0 on x.0.x version'    => [
                'required' => '5.0.1.0',
                'wp'       => '5.0.1',
                'expected' => true,
            ],
            'an incorrect trailing .0 and an earlier version' => [
                'required' => '5.0.0',
                'wp'       => '4.0',
                'expected' => false,
            ],
            'an incorrect trailing .0 and an earlier x.0 version' => [
                'required' => '5.0.0',
                'wp'       => '4.0',
                'expected' => false,
            ],
            'an incorrect trailing .0 and a later version' => [
                'required' => '5.0.0',
                'wp'       => '6.0',
                'expected' => true,
            ],
            'an incorrect trailing .0 and a later x.0 version' => [
                'required' => '5.0.0',
                'wp'       => '6.0',
                'expected' => true,
            ],
        ];
    }

    /**
     * Tests is_wp_version_compatible() with development versions.
     *
     * @dataProvider data_is_wp_version_compatible_with_development_versions
     *
     * @ticket 54257
     * @ticket 61781
     *
     * @param string $required  The minimum required WordPress version.
     * @param string $wp        The value for the $wp_version global variable.
     * @param bool   $expected  The expected result.
     */
    public function test_is_wp_version_compatible_with_development_versions($required, $wp, $expected)
    {
        $GLOBALS['_wp_tests_wp_version'] = $wp;
        $this->assertSame($expected, is_wp_version_compatible($required));
    }

    /**
     * Data provider.
     *
     * @return array[]
     */
    public function data_is_wp_version_compatible_with_development_versions()
    {
        // For consistent results, remove possible suffixes.
        list($version) = explode('-', wp_get_wp_version());

        $version_parts  = explode('.', $version);
        $lower_version  = $version_parts;
        $higher_version = $version_parts;

        // Adjust the major version numbers.
        --$lower_version[0];
        ++$higher_version[0];

        $lower_version  = implode('.', $lower_version);
        $higher_version = implode('.', $higher_version);

        return [
            'a lower required version and an alpha wordpress version' => [
                'required' => $lower_version,
                'wp'       => $version . '-alpha-12341-src',
                'expected' => true,
            ],
            'a lower required version and a beta wordpress version'   => [
                'required' => $lower_version,
                'wp'       => $version . '-beta1',
                'expected' => true,
            ],
            'a lower required version and a release candidate wordpress version'   => [
                'required' => $lower_version,
                'wp'       => $version . '-RC1',
                'expected' => true,
            ],
            'the same required version and an alpha wordpress version' => [
                'required' => $version,
                'wp'       => $version . '-alpha-12341-src',
                'expected' => true,
            ],
            'the same required version and a beta wordpress version' => [
                'required' => $version,
                'wp'       => $version . '-beta1',
                'expected' => true,
            ],
            'the same required version and a release candidate wordpress version' => [
                'required' => $version,
                'wp'       => $version . '-RC1',
                'expected' => true,
            ],
            'a higher required version and an alpha wordpress version'   => [
                'required' => $higher_version,
                'wp'       => $version . '-alpha-12341-src',
                'expected' => false,
            ],
            'a higher required version and a beta wordpress version'   => [
                'required' => $higher_version,
                'wp'       => $version . '-beta1',
                'expected' => false,
            ],
            'a higher required version and a release candidate wordpress version'   => [
                'required' => $higher_version,
                'wp'       => $version . '-RC1',
                'expected' => false,
            ],
        ];
    }
}
