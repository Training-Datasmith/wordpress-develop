<?php

declare(strict_types=1);

/**
 * @group block-supports
 *
 * @covers ::wp_render_elements_support_styles
 */
class Tests_Block_Supports_WpRenderElementsSupportStyles extends WP_UnitTestCase
{
    /**
     * @var string|null
     */
    private $test_block_name;

    public function tear_down()
    {
        unregister_block_type($this->test_block_name);
        $this->test_block_name = null;
        parent::tear_down();
    }

    /**
     * Tests that elements block support generates appropriate styles.
     *
     * @ticket 59555
     * @ticket 60557
     *
     * @covers ::wp_render_elements_support_styles
     *
     * @dataProvider data_elements_block_support_styles
     *
     * @param mixed  $color_settings  The color block support settings used for elements support.
     * @param mixed  $elements_styles The elements styles within the block attributes.
     * @param string $expected_styles Expected styles enqueued by the style engine.
     */
    public function test_elements_block_support_styles($color_settings, $elements_styles, $expected_styles)
    {
        $this->test_block_name = 'test/element-block-supports';

        register_block_type(
            $this->test_block_name,
            [
                'api_version' => 3,
                'attributes'  => [
                    'style' => [
                        'type' => 'object',
                    ],
                ],
                'supports'    => [
                    'color' => $color_settings,
                ],
            ]
        );

        $block = [
            'blockName' => $this->test_block_name,
            'attrs'     => [
                'style' => [
                    'elements' => $elements_styles,
                ],
            ],
        ];

        wp_render_elements_support_styles($block);
        $actual_stylesheet = wp_style_engine_get_stylesheet_from_context('block-supports', [ 'prettify' => false ]);

        $this->assertMatchesRegularExpression(
            $expected_styles,
            $actual_stylesheet,
            'Elements style rules output should be correct'
        );
    }

    /**
     * Data provider.
     *
     * @return array
     */
    public function data_elements_block_support_styles()
    {
        $color_styles    = [
            'text'       => 'var:preset|color|vivid-red',
            'background' => '#fff',
        ];
        $color_css_rules = preg_quote('{color:var(--wp--preset--color--vivid-red);background-color:#fff;}');

        return [
            'button element styles are not applied if serialization is skipped' => [
                'color_settings'  => [
                    'button'                          => true,
                    '__experimentalSkipSerialization' => true,
                ],
                'elements_styles' => [
                    'button' => [ 'color' => $color_styles ],
                ],
                'expected_styles' => '/^$/',
            ],
            'link element styles are not applied if serialization is skipped' => [
                'color_settings'  => [
                    'link'                            => true,
                    '__experimentalSkipSerialization' => true,
                ],
                'elements_styles' => [
                    'link' => [
                        'color'  => $color_styles,
                        ':hover' => [
                            'color' => $color_styles,
                        ],
                    ],
                ],
                'expected_styles' => '/^$/',
            ],
            'heading element styles are not applied if serialization is skipped' => [
                'color_settings'  => [
                    'heading'                         => true,
                    '__experimentalSkipSerialization' => true,
                ],
                'elements_styles' => [
                    'heading' => [ 'color' => $color_styles ],
                    'h1'      => [ 'color' => $color_styles ],
                    'h2'      => [ 'color' => $color_styles ],
                    'h3'      => [ 'color' => $color_styles ],
                    'h4'      => [ 'color' => $color_styles ],
                    'h5'      => [ 'color' => $color_styles ],
                    'h6'      => [ 'color' => $color_styles ],
                ],
                'expected_styles' => '/^$/',
            ],
            'button element styles are applied'          => [
                'color_settings'  => [ 'button' => true ],
                'elements_styles' => [
                    'button' => [ 'color' => $color_styles ],
                ],
                'expected_styles' => '/^.wp-elements-[a-f0-9]{32} .wp-element-button, .wp-elements-[a-f0-9]{32} .wp-block-button__link' . $color_css_rules . '$/',
            ],
            'link element styles are applied'            => [
                'color_settings'  => [ 'link' => true ],
                'elements_styles' => [
                    'link' => [
                        'color'  => $color_styles,
                        ':hover' => [
                            'color' => $color_styles,
                        ],
                    ],
                ],
                'expected_styles' => '/^.wp-elements-[a-f0-9]{32} a:where\(:not\(.wp-element-button\)\)' . $color_css_rules .
                    '.wp-elements-[a-f0-9]{32} a:where\(:not\(.wp-element-button\)\):hover' . $color_css_rules . '$/',
            ],
            'generic heading element styles are applied' => [
                'color_settings'  => [ 'heading' => true ],
                'elements_styles' => [
                    'heading' => [ 'color' => $color_styles ],
                ],
                'expected_styles' => '/^.wp-elements-[a-f0-9]{32} h1, .wp-elements-[a-f0-9]{32} h2, .wp-elements-[a-f0-9]{32} h3, .wp-elements-[a-f0-9]{32} h4, .wp-elements-[a-f0-9]{32} h5, .wp-elements-[a-f0-9]{32} h6' . $color_css_rules . '$/',
            ],
            'individual heading element styles are applied' => [
                'color_settings'  => [ 'heading' => true ],
                'elements_styles' => [
                    'h1' => [ 'color' => $color_styles ],
                    'h2' => [ 'color' => $color_styles ],
                    'h3' => [ 'color' => $color_styles ],
                    'h4' => [ 'color' => $color_styles ],
                    'h5' => [ 'color' => $color_styles ],
                    'h6' => [ 'color' => $color_styles ],
                ],
                'expected_styles' => '/^.wp-elements-[a-f0-9]{32} h1' . $color_css_rules .
                    '.wp-elements-[a-f0-9]{32} h2' . $color_css_rules .
                    '.wp-elements-[a-f0-9]{32} h3' . $color_css_rules .
                    '.wp-elements-[a-f0-9]{32} h4' . $color_css_rules .
                    '.wp-elements-[a-f0-9]{32} h5' . $color_css_rules .
                    '.wp-elements-[a-f0-9]{32} h6' . $color_css_rules . '$/',
            ],
        ];
    }
}
