<?php

declare(strict_types=1);

/**
 * @group option
 *
 * @covers ::wp_determine_option_autoload_value
 */
class Tests_WP_Determine_Option_Autoload_Value extends WP_UnitTestCase
{
    public function set_up()
    {
        add_filter('wp_max_autoloaded_option_size', [ $this, 'filter_max_option_size' ]);
        parent::set_up();
    }

    /**
     * @ticket 42441
     *
     * @dataProvider data_values
     *
     * @param $autoload
     * @param $expected
     */
    public function test_determine_option_autoload_value($autoload, $expected)
    {
        $test = wp_determine_option_autoload_value(null, '', '', $autoload);
        $this->assertSame($expected, $test);
    }

    public function data_values()
    {
        return [
            'yes'      => [
                'autoload' => 'yes',
                'expected' => 'on',
            ],
            'on'       => [
                'autoload' => 'on',
                'expected' => 'on',
            ],
            'true'     => [
                'autoload' => true,
                'expected' => 'on',
            ],
            'no'       => [
                'autoload' => 'no',
                'expected' => 'off',
            ],
            'off'      => [
                'autoload' => 'off',
                'expected' => 'off',
            ],
            'false'    => [
                'autoload' => false,
                'expected' => 'off',
            ],
            'invalid'  => [
                'autoload' => 'foo',
                'expected' => 'auto',
            ],
            'null'     => [
                'autoload' => null,
                'expected' => 'auto',
            ],
            'auto'     => [
                'autoload' => 'auto',
                'expected' => 'auto',
            ],
            'auto-on'  => [
                'autoload' => 'auto-on',
                'expected' => 'auto',
            ],
            'auto-off' => [
                'autoload' => 'auto-off',
                'expected' => 'auto',
            ],
        ];
    }

    /**
     * @ticket 42441
     */
    public function test_small_option()
    {
        $test = wp_determine_option_autoload_value('foo', 'bar', 'bar', null);
        $this->assertSame('auto', $test);
    }

    /**
     * @ticket 42441
     */
    public function test_large_option()
    {
        $value            = file(DIR_TESTDATA . '/formatting/entities.txt');
        $serialized_value = maybe_serialize($value);
        $test             = wp_determine_option_autoload_value('foo', $value, $serialized_value, null);
        $this->assertSame('auto-off', $test);
    }

    /**
     * @ticket 42441
     */
    public function test_large_option_json()
    {
        $value            = file(DIR_TESTDATA . '/themedir1/block-theme/theme.json');
        $serialized_value = maybe_serialize($value);
        $test             = wp_determine_option_autoload_value('foo', $value, $serialized_value, null);
        $this->assertSame('auto-off', $test);
    }

    public function filter_max_option_size($current)
    {
        return 1000;
    }
}
