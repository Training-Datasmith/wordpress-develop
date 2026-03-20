<?php

declare(strict_types=1);
/**
 * Test WP_Font_Utils::sanitize_font_family().
 *
 * @package WordPress
 * @subpackage Font Library
 *
 * @group fonts
 * @group font-library
 *
 * @covers WP_Font_Utils::sanitize_font_family
 */
class Tests_Fonts_WpFontUtils_SanitizeFontFamily extends WP_UnitTestCase
{
    /**
     * @dataProvider data_should_sanitize_font_family
     *
     * @param string $font_family Font family to test.
     * @param string $expected    Expected family.
     */
    public function test_should_sanitize_font_family($font_family, $expected)
    {
        $this->assertSame(
            $expected,
            WP_Font_Utils::sanitize_font_family(
                $font_family
            )
        );
    }

    /**
     * Data provider.
     *
     * @return array
     */
    public function data_should_sanitize_font_family()
    {
        return [
            'data_families_with_spaces_and_numbers' => [
                'font_family' => 'Arial, Rock 3D , Open Sans,serif',
                'expected'    => 'Arial, "Rock 3D", "Open Sans", serif',
            ],
            'data_single_font_family'               => [
                'font_family' => 'Rock 3D',
                'expected'    => '"Rock 3D"',
            ],
            'data_many_spaces_and_existing_quotes'  => [
                'font_family' => 'Rock 3D serif, serif,sans-serif, "Open Sans"',
                'expected'    => '"Rock 3D serif", serif, sans-serif, "Open Sans"',
            ],
            'data_empty_family'                     => [
                'font_family' => ' ',
                'expected'    => '',
            ],
            'data_font_family_with_whitespace_tags_new_lines' => [
                'font_family' => "   Rock      3D</style><script>alert('XSS');</script>\n    ",
                'expected'    => '"Rock 3D"',
            ],
            'data_font_family_with_generic_names'   => [
                'font_family' => 'generic(kai), generic(font[name]), generic(fangsong), Rock 3D',
                'expected'    => 'generic(kai), "generic(font[name])", generic(fangsong), "Rock 3D"',
            ],
        ];
    }
}
