<?php

declare(strict_types=1);

/**
 * @group block-supports
 *
 * @covers ::wp_render_elements_support
 */
class Tests_Block_Supports_WpRenderElementsSupport extends WP_UnitTestCase
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
     * Tests that block supports leaves block content alone if the block type
     * isn't registered.
     *
     * @ticket 59578
     *
     * @covers ::wp_render_elements_support
     */
    public function test_leaves_block_content_alone_when_block_type_not_registered()
    {
        $block = [
            'blockName' => 'test/element-block-supports',
            'attrs'     => [
                'style' => [
                    'elements' => [
                        'button' => [
                            'color' => [
                                'text'       => 'var:preset|color|vivid-red',
                                'background' => '#fff',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $block_markup = '<p>Hello <a href="http://www.wordpress.org/">WordPress</a>!</p>';
        $actual       = wp_render_elements_class_name($block_markup, $block);

        $this->assertSame($block_markup, $actual, 'Expected to leave block content unmodified, but found changes.');
    }

    /**
     * Tests that elements block support applies the correct classname.
     *
     * @ticket 59555
     *
     * @covers ::wp_render_elements_support
     *
     * @dataProvider data_elements_block_support_class
     *
     * @param array  $color_settings  The color block support settings used for elements support.
     * @param array  $elements_styles The elements styles within the block attributes.
     * @param string $block_markup    Original block markup.
     * @param string $expected_markup Resulting markup after application of elements block support.
     */
    public function test_elements_block_support_class($color_settings, $elements_styles, $block_markup, $expected_markup)
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

        /*
         * To ensure a consistent elements class name it is generated within a
         * `render_block_data` filter and stored in the `className` attribute.
         * As a result, the block data needs to be passed through the same
         * function for this test.
         */
        $filtered_block = wp_render_elements_support_styles($block);
        $actual         = wp_render_elements_class_name($block_markup, $filtered_block);

        $this->assertMatchesRegularExpression(
            $expected_markup,
            $actual,
            'Block wrapper markup should be correct'
        );
    }

    /**
     * Data provider.
     *
     * @return array
     */
    public function data_elements_block_support_class()
    {
        $color_styles = [
            'text'       => 'var:preset|color|vivid-red',
            'background' => '#fff',
        ];

        return [
            // @ticket 59578
            'empty block markup remains untouched'         => [
                'color_settings'  => [
                    'button' => true,
                ],
                'elements_styles' => [
                    'button' => [ 'color' => $color_styles ],
                ],
                'block_markup'    => '',
                'expected_markup' => '/^$/',
            ],
            'empty block markup remains untouched when no block attributes' => [
                'color_settings'  => [
                    'button' => true,
                ],
                'elements_styles' => null,
                'block_markup'    => '',
                'expected_markup' => '/^$/',
            ],
            'block markup remains untouched when block has no attributes' => [
                'color_settings'  => [
                    'button' => true,
                ],
                'elements_styles' => null,
                'block_markup'    => '<p>Hello <a href="http://www.wordpress.org/">WordPress</a>!</p>',
                'expected_markup' => '/^<p>Hello <a href="http:\/\/www.wordpress.org\/">WordPress<\/a>!<\/p>$/',
            ],
            // @ticket 5418
            'button element styles with serialization skipped' => [
                'color_settings'  => [
                    'button'                          => true,
                    '__experimentalSkipSerialization' => true,
                ],
                'elements_styles' => [
                    'button' => [ 'color' => $color_styles ],
                ],
                'block_markup'    => '<p>Hello <a href="http://www.wordpress.org/">WordPress</a>!</p>',
                'expected_markup' => '/^<p>Hello <a href="http:\/\/www.wordpress.org\/">WordPress<\/a>!<\/p>$/',
            ],
            'link element styles with serialization skipped' => [
                'color_settings'  => [
                    'link'                            => true,
                    '__experimentalSkipSerialization' => true,
                ],
                'elements_styles' => [
                    'link' => [ 'color' => $color_styles ],
                ],
                'block_markup'    => '<p>Hello <a href="http://www.wordpress.org/">WordPress</a>!</p>',
                'expected_markup' => '/^<p>Hello <a href="http:\/\/www.wordpress.org\/">WordPress<\/a>!<\/p>$/',
            ],
            'heading element styles with serialization skipped' => [
                'color_settings'  => [
                    'heading'                         => true,
                    '__experimentalSkipSerialization' => true,
                ],
                'elements_styles' => [
                    'heading' => [ 'color' => $color_styles ],
                ],
                'block_markup'    => '<p>Hello <a href="http://www.wordpress.org/">WordPress</a>!</p>',
                'expected_markup' => '/^<p>Hello <a href="http:\/\/www.wordpress.org\/">WordPress<\/a>!<\/p>$/',
            ],
            'button element styles apply class to wrapper' => [
                'color_settings'  => [ 'button' => true ],
                'elements_styles' => [
                    'button' => [ 'color' => $color_styles ],
                ],
                'block_markup'    => '<p>Hello <a href="http://www.wordpress.org/">WordPress</a>!</p>',
                'expected_markup' => '/^<p class="wp-elements-[a-f0-9]{32}">Hello <a href="http:\/\/www.wordpress.org\/">WordPress<\/a>!<\/p>$/',
            ],
            'link element styles apply class to wrapper'   => [
                'color_settings'  => [ 'link' => true ],
                'elements_styles' => [
                    'link' => [ 'color' => $color_styles ],
                ],
                'block_markup'    => '<p>Hello <a href="http://www.wordpress.org/">WordPress</a>!</p>',
                'expected_markup' => '/^<p class="wp-elements-[a-f0-9]{32}">Hello <a href="http:\/\/www.wordpress.org\/">WordPress<\/a>!<\/p>$/',
            ],
            'heading element styles apply class to wrapper' => [
                'color_settings'  => [ 'heading' => true ],
                'elements_styles' => [
                    'heading' => [ 'color' => $color_styles ],
                ],
                'block_markup'    => '<p>Hello <a href="http://www.wordpress.org/">WordPress</a>!</p>',
                'expected_markup' => '/^<p class="wp-elements-[a-f0-9]{32}">Hello <a href="http:\/\/www.wordpress.org\/">WordPress<\/a>!<\/p>$/',
            ],
            'element styles apply class to wrapper when it has other classes' => [
                'color_settings'  => [ 'link' => true ],
                'elements_styles' => [
                    'link' => [ 'color' => $color_styles ],
                ],
                'block_markup'    => '<p class="has-dark-gray-background-color has-background">Hello <a href="http://www.wordpress.org/">WordPress</a>!</p>',
                'expected_markup' => '/^<p class="has-dark-gray-background-color has-background wp-elements-[a-f0-9]{32}">Hello <a href="http:\/\/www.wordpress.org\/">WordPress<\/a>!<\/p>$/',
            ],
            'element styles apply class to wrapper when it has other attributes' => [
                'color_settings'  => [ 'link' => true ],
                'elements_styles' => [
                    'link' => [ 'color' => $color_styles ],
                ],
                'block_markup'    => '<p id="anchor">Hello <a href="http://www.wordpress.org/">WordPress</a>!</p>',
                'expected_markup' => '/^<p class="wp-elements-[a-f0-9]{32}" id="anchor">Hello <a href="http:\/\/www.wordpress.org\/">WordPress<\/a>!<\/p>$/',
            ],
        ];
    }
}
