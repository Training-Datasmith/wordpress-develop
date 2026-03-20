<?php

declare(strict_types=1);

/**
 * @group block-supports
 *
 * @covers ::wp_get_block_style_variation_name_from_class
 */
class Tests_Block_Supports_WpGetBlockStyleVariationNameFromClass extends WP_UnitTestCase
{
    /**
     * Tests variation names are extracted correctly from a CSS class string.
     *
     * @ticket 61312
     *
     * @covers ::wp_get_block_style_variation_name_from_class
     *
     * @dataProvider data_block_style_variation_name_extraction
     *
     * @param string     $class_string CSS class string.
     * @param array|null $expected     Expected variation names.
     */
    public function test_block_style_variation_name_extraction($class_string, $expected)
    {
        $actual = wp_get_block_style_variation_name_from_class($class_string);

        $this->assertSame(
            $expected,
            $actual,
            'Block style variation names extracted from CSS class string should match'
        );
    }

    /**
     * Data provider.
     *
     * @return array
     */
    public function data_block_style_variation_name_extraction()
    {
        return [
            // @ticket 61312
            'missing class string' => [
                'class_string' => null,
                'expected'     => null,
            ],
            // @ticket 61312
            'empty class string'   => [
                'class_string' => '',
                'expected'     => [],
            ],
            // @ticket 61312
            'no variation'         => [
                'class_string' => 'is-style no-variation',
                'expected'     => [],
            ],
            // @ticket 61312
            'single variation'     => [
                'class_string' => 'custom-class is-style-outline',
                'expected'     => [ 'outline' ],
            ],
            // @ticket 61312
            'multiple variations'  => [
                'class_string' => 'is-style-light custom-class is-style-outline',
                'expected'     => [ 'light', 'outline' ],
            ],
        ];
    }
}
