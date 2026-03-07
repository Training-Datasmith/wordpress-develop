<?php

declare(strict_types=1);

/**
 * Test the block WP_Duotone class.
 *
 * @group block-supports
 *
 * @coversDefaultClass WP_Duotone
 */

class Tests_Block_Supports_Duotone extends WP_UnitTestCase
{
    /**
     * Tests whether the duotone preset class is added to the block.
     *
     * @ticket 58555
     *
     * @covers ::render_duotone_support
     */
    public function test_render_duotone_support_preset()
    {
        $block         = [
            'blockName' => 'core/image',
            'attrs'     => [ 'style' => [ 'color' => [ 'duotone' => 'var:preset|duotone|blue-orange' ] ] ],
        ];
        $wp_block      = new WP_Block($block);
        $block_content = '<figure class="wp-block-image size-full"><img src="/my-image.jpg" /></figure>';
        $expected      = '<figure class="wp-block-image size-full wp-duotone-blue-orange"><img src="/my-image.jpg" /></figure>';
        $this->assertSame($expected, WP_Duotone::render_duotone_support($block_content, $block, $wp_block));
    }

    /**
     * Tests whether the duotone unset class is added to the block.
     *
     * @ticket 58555
     *
     * @covers ::render_duotone_support
     */
    public function test_render_duotone_support_css()
    {
        $block         = [
            'blockName' => 'core/image',
            'attrs'     => [ 'style' => [ 'color' => [ 'duotone' => 'unset' ] ] ],
        ];
        $wp_block      = new WP_Block($block);
        $block_content = '<figure class="wp-block-image size-full"><img src="/my-image.jpg" /></figure>';
        $expected      = '/<figure class="wp-block-image size-full wp-duotone-unset-\d+"><img src="\\/my-image.jpg" \\/><\\/figure>/';
        $this->assertMatchesRegularExpression($expected, WP_Duotone::render_duotone_support($block_content, $block, $wp_block));
    }

    /**
     * Tests whether the duotone custom class is added to the block.
     *
     * @covers ::render_duotone_support
     */
    public function test_render_duotone_support_custom()
    {
        $block         = [
            'blockName' => 'core/image',
            'attrs'     => [ 'style' => [ 'color' => [ 'duotone' => [ '#FFFFFF', '#000000' ] ] ] ],
        ];
        $wp_block      = new WP_Block($block);
        $block_content = '<figure class="wp-block-image size-full"><img src="/my-image.jpg" /></figure>';
        $expected      = '/<figure class="wp-block-image size-full wp-duotone-ffffff-000000-\d+"><img src="\\/my-image.jpg" \\/><\\/figure>/';
        $this->assertMatchesRegularExpression($expected, WP_Duotone::render_duotone_support($block_content, $block, $wp_block));
    }

    /**
     * Tests whether the slug is extracted from the attribute.
     *
     * @dataProvider data_get_slug_from_attribute
     * @covers ::get_slug_from_attribute
     */
    public function test_get_slug_from_attribute($data_attr, $expected)
    {

        $reflection = new ReflectionMethod('WP_Duotone', 'get_slug_from_attribute');
        if (PHP_VERSION_ID < 80100) {
            $reflection->setAccessible(true);
        }

        $this->assertSame($expected, $reflection->invoke(null, $data_attr));
    }

    /**
     * Data provider.
     *
     * @return array[].
     */
    public function data_get_slug_from_attribute()
    {
        return [
            'pipe-slug'                       => [ 'var:preset|duotone|blue-orange', 'blue-orange' ],
            'css-var'                         => [ 'var(--wp--preset--duotone--blue-orange)', 'blue-orange' ],
            'css-var-invalid-slug-chars'      => [ 'var(--wp--preset--duotone--.)', '.' ],
            'css-var-missing-end-parenthesis' => [ 'var(--wp--preset--duotone--blue-orange', '' ],
            'invalid'                         => [ 'not a valid attribute', '' ],
            'css-var-no-value'                => [ 'var(--wp--preset--duotone--)', '' ],
            'pipe-slug-no-value'              => [ 'var:preset|duotone|', '' ],
            'css-var-spaces'                  => [ 'var(--wp--preset--duotone--    ', '' ],
            'pipe-slug-spaces'                => [ 'var:preset|duotone|  ', '' ],
            'array-of-colors'                 => [ [ '#000000', '#ffffff' ], '' ],
            'empty-array'                     => [ [], '' ],
        ];
    }

    /**
     * Tests whether the CSS declarations are generated even if the block content is
     * empty. This is needed to make the CSS output stable across paginations for
     * features like the enhanced pagination of the Query block.
     *
     * @ticket 59694
     *
     * @covers ::render_duotone_support
     */
    public function test_css_declarations_are_generated_even_with_empty_block_content()
    {
        $block    = [
            'blockName' => 'core/image',
            'attrs'     => [ 'style' => [ 'color' => [ 'duotone' => 'var:preset|duotone|blue-orange' ] ] ],
        ];
        $wp_block = new WP_Block($block);

        /*
         * Handling to access the static WP_Duotone::$block_css_declarations property.
         *
         * Why is an instance needed?
         * WP_Duotone is a static class by design, meaning it only contains static properties and methods.
         * In production, it should not be instantiated. However, as of PHP 8.3, ReflectionProperty::setValue()
         * needs an object.
         */
        $wp_duotone                      = new WP_Duotone();
        $block_css_declarations_property = new ReflectionProperty('WP_Duotone', 'block_css_declarations');
        if (PHP_VERSION_ID < 80100) {
            $block_css_declarations_property->setAccessible(true);
        }
        $previous_value = $block_css_declarations_property->getValue();
        $block_css_declarations_property->setValue($wp_duotone, []);

        WP_Duotone::render_duotone_support('', $block, $wp_block);
        $actual = $block_css_declarations_property->getValue();

        // Reset the property.
        $block_css_declarations_property->setValue($wp_duotone, $previous_value);
        if (PHP_VERSION_ID < 80100) {
            $block_css_declarations_property->setAccessible(false);
        }

        $this->assertNotEmpty($actual);
    }

    /**
     * @dataProvider data_is_preset
     */
    public function test_is_preset($data_attr, $expected)
    {
        $reflection = new ReflectionMethod('WP_Duotone', 'is_preset');
        if (PHP_VERSION_ID < 80100) {
            $reflection->setAccessible(true);
        }

        $this->assertSame($expected, $reflection->invoke(null, $data_attr));
    }

    /**
     * Data provider.
     *
     * @return array[].
     */
    public function data_is_preset()
    {
        return [
            'pipe-slug'                       => [ 'var:preset|duotone|blue-orange', true ],
            'css-var'                         => [ 'var(--wp--preset--duotone--blue-orange)', true ],
            'css-var-invalid-slug-chars'      => [ 'var(--wp--preset--duotone--.)', false ],
            'css-var-missing-end-parenthesis' => [ 'var(--wp--preset--duotone--blue-orange', false ],
            'invalid'                         => [ 'not a valid attribute', false ],
            'array-of-colors'                 => [ [ '#000000', '#ffffff' ], false ],
            'empty-array'                     => [ [], false ],
        ];
    }

    /**
     * @dataProvider data_colord_parse_hue
     * @ticket 59496
     */
    public function test_colord_parse_hue($value, $unit, $expected)
    {
        $reflection = new ReflectionMethod('WP_Duotone', 'colord_parse_hue');
        if (PHP_VERSION_ID < 80100) {
            $reflection->setAccessible(true);
        }

        $this->assertSame($expected, $reflection->invoke(null, $value, $unit));
    }

    /**
     * Data provider.
     *
     * @return array[].
     */
    public function data_colord_parse_hue()
    {
        return [
            'deg-angle-unit'                => [ 120, 'deg', 120.0 ],
            'grad-angle-unit'               => [ 120, 'grad', 108.0 ],
            'turn-angle-unit'               => [ 120, 'turn', 43200.0 ],
            'rad-angle-unit'                => [ 120, 'rad', 6875.493541569878 ],
            'empty-angle-unit'              => [ 120, '', 120.0 ],
            'invalid-angle-unit'            => [ 120, 'invalid', 120.0 ],
            'negative-value-deg-angle-unit' => [ -120, 'deg', -120.0 ],
        ];
    }
}
