<?php

declare(strict_types=1);

/**
 * @group option
 */
class Tests_Option_ThemeMods extends WP_UnitTestCase
{
    /**
     * @covers ::get_theme_mod
     */
    public function test_theme_mod_default()
    {
        $this->assertFalse(get_theme_mod('non_existent'));
    }

    /**
     * @covers ::get_theme_mod
     */
    public function test_theme_mod_defined_default()
    {
        $this->assertSame('default', get_theme_mod('non_existent', 'default'));
    }

    /**
     * @covers ::get_theme_mod
     * @covers ::set_theme_mod
     */
    public function test_theme_mod_set()
    {
        $expected = 'value';
        set_theme_mod('test_name', $expected);
        $this->assertSame($expected, get_theme_mod('test_name'));
    }

    /**
     * @ticket 51423
     *
     * @covers ::set_theme_mod
     */
    public function test_theme_mod_set_with_invalid_theme_mods_option()
    {
        $theme_slug = get_option('stylesheet');
        update_option('theme_mods_' . $theme_slug, '');
        self::test_theme_mod_set();
    }

    /**
     * @covers ::get_theme_mod
     * @covers ::set_theme_mod
     */
    public function test_theme_mod_update()
    {
        set_theme_mod('test_update', 'first_value');
        $expected = 'updated_value';
        set_theme_mod('test_update', $expected);
        $this->assertSame($expected, get_theme_mod('test_update'));
    }

    /**
     * @covers ::set_theme_mod
     * @covers ::remove_theme_mod
     * @covers ::get_theme_mod
     */
    public function test_theme_mod_remove()
    {
        set_theme_mod('test_remove', 'value');
        remove_theme_mod('test_remove');
        $this->assertFalse(get_theme_mod('test_remove'));
    }

    /**
     * @ticket 34290
     *
     * @dataProvider data_theme_mod_default_value_with_percent_symbols
     *
     * @covers ::get_theme_mod
     */
    public function test_theme_mod_default_value_with_percent_symbols($default_value, $expected)
    {
        $this->assertSame($expected, get_theme_mod('test_name', $default_value));
    }

    public function data_theme_mod_default_value_with_percent_symbols()
    {
        return [
            [
                '100%',
                '100%',
            ],
            [
                '%s',
                get_template_directory_uri(),
            ],
            [
                '%s%s',
                get_template_directory_uri() . get_stylesheet_directory_uri(),
            ],
            [
                '%1$s%s',
                get_template_directory_uri() . get_template_directory_uri(),
            ],
            [
                '%2$s%s',
                get_stylesheet_directory_uri() . get_template_directory_uri(),
            ],
            [
                '%1$s%2$s',
                get_template_directory_uri() . get_stylesheet_directory_uri(),
            ],
            [
                '%40s%40s',
                get_template_directory_uri() . get_stylesheet_directory_uri(),
            ],
            [
                '%%1',
                '%%1',
            ],
            [
                '%1%',
                '%1%',
            ],
            [
                '1%%',
                '1%%',
            ],
            [
                '%%s',
                '%%s',
            ],
            [
                '%s%',
                get_template_directory_uri(),
            ],
            [
                's%%',
                's%%',
            ],
        ];
    }
}
