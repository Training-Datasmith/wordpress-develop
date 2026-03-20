<?php

declare(strict_types=1);
/**
 * Tests the Style Engine global functions that interact with the WP_Style_Engine class.
 *
 * @package WordPress
 * @subpackage StyleEngine
 * @since 6.1.0
 *
 * @group style-engine
 */

/**
 * Tests for registering, storing and generating styles.
 */
class Tests_wpStyleEngine extends WP_UnitTestCase
{
    /**
     * Tests generating block styles and classnames based on various manifestations of the $block_styles argument.
     *
     * @ticket 56467
     * @ticket 58549
     * @ticket 58590
     * @ticket 60175
     * @ticket 61720
     * @ticket 62189
     * @ticket 63799
     *
     * @covers ::wp_style_engine_get_styles
     *
     * @dataProvider data_wp_style_engine_get_styles
     *
     * @param array  $block_styles    The incoming block styles object.
     * @param array  $options         {
     *     An array of options to pass to `wp_style_engine_get_styles()`.
     *
     *     @type string|null $context                    An identifier describing the origin of the style object, e.g., 'block-supports' or 'global-styles'. Default is `null`.
     *                                                   When set, the style engine will attempt to store the CSS rules, where a selector is also passed.
     *     @type bool        $convert_vars_to_classnames Whether to skip converting incoming CSS var patterns, e.g., `var:preset|<PRESET_TYPE>|<PRESET_SLUG>`, to var( --wp--preset--* ) values. Default `false`.
     *     @type string      $selector                   Optional. When a selector is passed, the value of `$css` in the return value will comprise a full CSS rule `$selector { ...$css_declarations }`,
     *                                                   otherwise, the value will be a concatenated string of CSS declarations.
     * }
     * @param string $expected_output The expected output.
     */
    public function test_wp_style_engine_get_styles($block_styles, $options, $expected_output)
    {
        $generated_styles = wp_style_engine_get_styles($block_styles, $options);

        $this->assertSame($expected_output, $generated_styles);
    }

    /**
     * Data provider for test_wp_style_engine_get_styles().
     *
     * @return array
     */
    public function data_wp_style_engine_get_styles()
    {
        return [
            'default_return_value'                         => [
                'block_styles'    => [],
                'options'         => null,
                'expected_output' => [],
            ],

            'inline_invalid_block_styles_empty'            => [
                'block_styles'    => 'hello world!',
                'options'         => null,
                'expected_output' => [],
            ],

            'inline_invalid_block_styles_unknown_style'    => [
                'block_styles'    => [
                    'pageBreakAfter' => 'verso',
                ],
                'options'         => null,
                'expected_output' => [],
            ],

            'inline_invalid_block_styles_unknown_definition' => [
                'block_styles'    => [
                    'pageBreakAfter' => 'verso',
                ],
                'options'         => null,
                'expected_output' => [],
            ],

            'inline_invalid_block_styles_unknown_property' => [
                'block_styles'    => [
                    'spacing' => [
                        'gap' => '1000vw',
                    ],
                ],
                'options'         => null,
                'expected_output' => [],
            ],

            'valid_inline_css_and_classnames_as_default_context' => [
                'block_styles'    => [
                    'color'   => [
                        'text' => 'var:preset|color|texas-flood',
                    ],
                    'spacing' => [
                        'margin'  => '111px',
                        'padding' => '0',
                    ],
                    'border'  => [
                        'color' => 'var:preset|color|cool-caramel',
                        'width' => '2rem',
                        'style' => 'dotted',
                    ],
                ],
                'options'         => [ 'convert_vars_to_classnames' => true ],
                'expected_output' => [
                    'css'          => 'border-style:dotted;border-width:2rem;padding:0;margin:111px;',
                    'declarations' => [
                        'border-style' => 'dotted',
                        'border-width' => '2rem',
                        'padding'      => '0',
                        'margin'       => '111px',
                    ],
                    'classnames'   => 'has-text-color has-texas-flood-color has-border-color has-cool-caramel-border-color',
                ],
            ],

            'inline_valid_dimension_preset_style'          => [
                'block_styles'    => [
                    'dimensions' => [
                        'width'  => 'var:preset|dimension|large',
                        'height' => 'var:preset|dimension|modestly-small',
                    ],
                ],
                'options'         => null,
                'expected_output' => [
                    'css'          => 'height:var(--wp--preset--dimension--modestly-small);width:var(--wp--preset--dimension--large);',
                    'declarations' => [
                        'height' => 'var(--wp--preset--dimension--modestly-small)',
                        'width'  => 'var(--wp--preset--dimension--large)',
                    ],
                ],
            ],

            'inline_valid_box_model_style'                 => [
                'block_styles'    => [
                    'spacing' => [
                        'padding' => [
                            'top'    => '42px',
                            'left'   => '2%',
                            'bottom' => '44px',
                            'right'  => '5rem',
                        ],
                        'margin'  => [
                            'top'    => '12rem',
                            'left'   => '2vh',
                            'bottom' => '2px',
                            'right'  => '10em',
                        ],
                    ],
                    'border'  => [
                        'radius' => [
                            'topLeft'     => '99px',
                            'topRight'    => '98px',
                            'bottomLeft'  => '97px',
                            'bottomRight' => '96px',
                        ],
                    ],
                ],
                'options'         => null,
                'expected_output' => [
                    'css'          => 'border-top-left-radius:99px;border-top-right-radius:98px;border-bottom-left-radius:97px;border-bottom-right-radius:96px;padding-top:42px;padding-left:2%;padding-bottom:44px;padding-right:5rem;margin-top:12rem;margin-left:2vh;margin-bottom:2px;margin-right:10em;',
                    'declarations' => [
                        'border-top-left-radius'     => '99px',
                        'border-top-right-radius'    => '98px',
                        'border-bottom-left-radius'  => '97px',
                        'border-bottom-right-radius' => '96px',
                        'padding-top'                => '42px',
                        'padding-left'               => '2%',
                        'padding-bottom'             => '44px',
                        'padding-right'              => '5rem',
                        'margin-top'                 => '12rem',
                        'margin-left'                => '2vh',
                        'margin-bottom'              => '2px',
                        'margin-right'               => '10em',
                    ],
                ],
            ],

            'inline_valid_border_radius_presets'           => [
                'block_styles'    => [
                    'border' => [
                        'radius' => [
                            'topLeft'     => 'var:preset|border-radius|large',
                            'topRight'    => 'var:preset|border-radius|large',
                            'bottomLeft'  => 'var:preset|border-radius|large',
                            'bottomRight' => 'var:preset|border-radius|large',
                        ],
                    ],
                ],
                'options'         => null,
                'expected_output' => [
                    'css'          => 'border-top-left-radius:var(--wp--preset--border-radius--large);border-top-right-radius:var(--wp--preset--border-radius--large);border-bottom-left-radius:var(--wp--preset--border-radius--large);border-bottom-right-radius:var(--wp--preset--border-radius--large);',
                    'declarations' => [
                        'border-top-left-radius'     => 'var(--wp--preset--border-radius--large)',
                        'border-top-right-radius'    => 'var(--wp--preset--border-radius--large)',
                        'border-bottom-left-radius'  => 'var(--wp--preset--border-radius--large)',
                        'border-bottom-right-radius' => 'var(--wp--preset--border-radius--large)',
                    ],
                ],
            ],

            'inline_valid_dimensions_style'                => [
                'block_styles'    => [
                    'dimensions' => [
                        'minHeight' => '50vh',
                    ],
                ],
                'options'         => null,
                'expected_output' => [
                    'css'          => 'min-height:50vh;',
                    'declarations' => [
                        'min-height' => '50vh',
                    ],
                ],
            ],

            'inline_valid_aspect_ratio_style'              => [
                'block_styles'    => [
                    'dimensions' => [
                        'aspectRatio' => '4/3',
                        'minHeight'   => 'unset',
                    ],
                ],
                'options'         => null,
                'expected_output' => [
                    'css'          => 'aspect-ratio:4/3;min-height:unset;',
                    'declarations' => [
                        'aspect-ratio' => '4/3',
                        'min-height'   => 'unset',
                    ],
                    'classnames'   => 'has-aspect-ratio',
                ],
            ],

            'inline_valid_shadow_style'                    => [
                'block_styles'    => [
                    'shadow' => 'inset 5em 1em gold',
                ],
                'options'         => null,
                'expected_output' => [
                    'css'          => 'box-shadow:inset 5em 1em gold;',
                    'declarations' => [
                        'box-shadow' => 'inset 5em 1em gold',
                    ],
                ],
            ],

            'inline_valid_typography_style'                => [
                'block_styles'    => [
                    'typography' => [
                        'fontSize'       => 'clamp(2em, 2vw, 4em)',
                        'fontFamily'     => 'Roboto,Oxygen-Sans,Ubuntu,sans-serif',
                        'fontStyle'      => 'italic',
                        'fontWeight'     => '800',
                        'lineHeight'     => '1.3',
                        'textColumns'    => '2',
                        'textDecoration' => 'underline',
                        'textTransform'  => 'uppercase',
                        'letterSpacing'  => '2',
                        'writingMode'    => 'vertical-rl',
                    ],
                ],
                'options'         => null,
                'expected_output' => [
                    'css'          => 'font-size:clamp(2em, 2vw, 4em);font-family:Roboto,Oxygen-Sans,Ubuntu,sans-serif;font-style:italic;font-weight:800;line-height:1.3;column-count:2;text-decoration:underline;text-transform:uppercase;letter-spacing:2;writing-mode:vertical-rl;',
                    'declarations' => [
                        'font-size'       => 'clamp(2em, 2vw, 4em)',
                        'font-family'     => 'Roboto,Oxygen-Sans,Ubuntu,sans-serif',
                        'font-style'      => 'italic',
                        'font-weight'     => '800',
                        'line-height'     => '1.3',
                        'column-count'    => '2',
                        'text-decoration' => 'underline',
                        'text-transform'  => 'uppercase',
                        'letter-spacing'  => '2',
                        'writing-mode'    => 'vertical-rl',
                    ],
                ],
            ],

            'style_block_with_selector'                    => [
                'block_styles'    => [
                    'spacing' => [
                        'padding' => [
                            'top'    => '42px',
                            'left'   => '2%',
                            'bottom' => '44px',
                            'right'  => '5rem',
                        ],
                    ],
                ],
                'options'         => [ 'selector' => '.wp-selector > p' ],
                'expected_output' => [
                    'css'          => '.wp-selector > p{padding-top:42px;padding-left:2%;padding-bottom:44px;padding-right:5rem;}',
                    'declarations' => [
                        'padding-top'    => '42px',
                        'padding-left'   => '2%',
                        'padding-bottom' => '44px',
                        'padding-right'  => '5rem',
                    ],
                ],
            ],

            'elements_with_css_var_value'                  => [
                'block_styles'    => [
                    'color'      => [
                        'text' => 'var:preset|color|my-little-pony',
                    ],
                    'typography' => [
                        'fontSize'   => 'var:preset|font-size|cabbage-patch',
                        'fontFamily' => 'var:preset|font-family|transformers',
                    ],
                ],
                'options'         => [
                    'selector' => '.wp-selector',
                ],
                'expected_output' => [
                    'css'          => '.wp-selector{color:var(--wp--preset--color--my-little-pony);font-size:var(--wp--preset--font-size--cabbage-patch);font-family:var(--wp--preset--font-family--transformers);}',
                    'declarations' => [
                        'color'       => 'var(--wp--preset--color--my-little-pony)',
                        'font-size'   => 'var(--wp--preset--font-size--cabbage-patch)',
                        'font-family' => 'var(--wp--preset--font-family--transformers)',

                    ],
                    'classnames'   => 'has-text-color has-my-little-pony-color has-cabbage-patch-font-size has-transformers-font-family',
                ],
            ],

            'elements_with_invalid_preset_style_property'  => [
                'block_styles'    => [
                    'color' => [
                        'text' => 'var:preset|invalid_property|my-little-pony',
                    ],
                ],
                'options'         => [ 'selector' => '.wp-selector' ],
                'expected_output' => [
                    'classnames' => 'has-text-color',
                ],
            ],

            'valid_classnames_deduped'                     => [
                'block_styles'    => [
                    'color'      => [
                        'text'       => 'var:preset|color|copper-socks',
                        'background' => 'var:preset|color|splendid-carrot',
                        'gradient'   => 'var:preset|gradient|like-wow-dude',
                    ],
                    'typography' => [
                        'fontSize'   => 'var:preset|font-size|fantastic',
                        'fontFamily' => 'var:preset|font-family|totally-awesome',
                    ],
                ],
                'options'         => [ 'convert_vars_to_classnames' => true ],
                'expected_output' => [
                    'classnames' => 'has-text-color has-copper-socks-color has-background has-splendid-carrot-background-color has-like-wow-dude-gradient-background has-fantastic-font-size has-totally-awesome-font-family',
                ],
            ],

            'valid_classnames_and_css_vars'                => [
                'block_styles'    => [
                    'color' => [
                        'text' => 'var:preset|color|teal-independents',
                    ],
                ],
                'options'         => [],
                'expected_output' => [
                    'css'          => 'color:var(--wp--preset--color--teal-independents);',
                    'declarations' => [
                        'color' => 'var(--wp--preset--color--teal-independents)',
                    ],
                    'classnames'   => 'has-text-color has-teal-independents-color',
                ],
            ],

            'valid_classnames_with_null_style_values'      => [
                'block_styles'    => [
                    'color' => [
                        'text'       => '#fff',
                        'background' => null,
                    ],
                ],
                'options'         => [],
                'expected_output' => [
                    'css'          => 'color:#fff;',
                    'declarations' => [
                        'color' => '#fff',
                    ],
                    'classnames'   => 'has-text-color',
                ],
            ],

            'invalid_classnames_preset_value'              => [
                'block_styles'    => [
                    'color'   => [
                        'text'       => 'var:cheese|color|fantastic',
                        'background' => 'var:preset|fromage|fantastic',
                    ],
                    'spacing' => [
                        'margin'  => 'var:cheese|spacing|margin',
                        'padding' => 'var:preset|spacing|padding',
                    ],
                ],
                'options'         => [ 'convert_vars_to_classnames' => true ],
                'expected_output' => [
                    'classnames' => 'has-text-color has-background',
                ],
            ],

            'valid_spacing_single_preset_values'           => [
                'block_styles'    => [
                    'spacing' => [
                        'margin'  => 'var:preset|spacing|10',
                        'padding' => 'var:preset|spacing|20',
                    ],
                ],
                'options'         => [],
                'expected_output' => [
                    'css'          => 'padding:var(--wp--preset--spacing--20);margin:var(--wp--preset--spacing--10);',
                    'declarations' => [
                        'padding' => 'var(--wp--preset--spacing--20)',
                        'margin'  => 'var(--wp--preset--spacing--10)',
                    ],
                ],
            ],

            'valid_spacing_multi_preset_values'            => [
                'block_styles'    => [
                    'spacing' => [
                        'margin'  => [
                            'left'   => 'var:preset|spacing|10',
                            'right'  => 'var:preset|spacing|20',
                            'top'    => '1rem',
                            'bottom' => '1rem',
                        ],
                        'padding' => [
                            'left'   => 'var:preset|spacing|30',
                            'right'  => 'var:preset|spacing|40',
                            'top'    => '14px',
                            'bottom' => '14px',
                        ],
                    ],
                ],
                'options'         => [],
                'expected_output' => [
                    'css'          => 'padding-left:var(--wp--preset--spacing--30);padding-right:var(--wp--preset--spacing--40);padding-top:14px;padding-bottom:14px;margin-left:var(--wp--preset--spacing--10);margin-right:var(--wp--preset--spacing--20);margin-top:1rem;margin-bottom:1rem;',
                    'declarations' => [
                        'padding-left'   => 'var(--wp--preset--spacing--30)',
                        'padding-right'  => 'var(--wp--preset--spacing--40)',
                        'padding-top'    => '14px',
                        'padding-bottom' => '14px',
                        'margin-left'    => 'var(--wp--preset--spacing--10)',
                        'margin-right'   => 'var(--wp--preset--spacing--20)',
                        'margin-top'     => '1rem',
                        'margin-bottom'  => '1rem',
                    ],
                ],
            ],

            'invalid_spacing_multi_preset_values'          => [
                'block_styles'    => [
                    'spacing' => [
                        'margin' => [
                            'left'   => 'var:preset|spaceman|10',
                            'right'  => 'var:preset|spaceman|20',
                            'top'    => '1rem',
                            'bottom' => '0',
                        ],
                    ],
                ],
                'options'         => [],
                'expected_output' => [
                    'css'          => 'margin-top:1rem;margin-bottom:0;',
                    'declarations' => [
                        'margin-top'    => '1rem',
                        'margin-bottom' => '0',
                    ],
                ],
            ],

            'invalid_classnames_options'                   => [
                'block_styles'    => [
                    'typography' => [
                        'fontSize'   => [
                            'tomodachi' => 'friends',
                        ],
                        'fontFamily' => [
                            'oishii' => 'tasty',
                        ],
                    ],
                ],
                'options'         => [],
                'expected_output' => [],
            ],

            'inline_valid_box_model_style_with_sides'      => [
                'block_styles'    => [
                    'border' => [
                        'top'    => [
                            'color' => '#fe1',
                            'width' => '1.5rem',
                            'style' => 'dashed',
                        ],
                        'right'  => [
                            'color' => '#fe2',
                            'width' => '1.4rem',
                            'style' => 'solid',
                        ],
                        'bottom' => [
                            'color' => '#fe3',
                            'width' => '1.3rem',
                        ],
                        'left'   => [
                            'color' => 'var:preset|color|swampy-yellow',
                            'width' => '0.5rem',
                            'style' => 'dotted',
                        ],
                    ],
                ],
                'options'         => [],
                'expected_output' => [
                    'css'          => 'border-top-color:#fe1;border-top-width:1.5rem;border-top-style:dashed;border-right-color:#fe2;border-right-width:1.4rem;border-right-style:solid;border-bottom-color:#fe3;border-bottom-width:1.3rem;border-left-color:var(--wp--preset--color--swampy-yellow);border-left-width:0.5rem;border-left-style:dotted;',
                    'declarations' => [
                        'border-top-color'    => '#fe1',
                        'border-top-width'    => '1.5rem',
                        'border-top-style'    => 'dashed',
                        'border-right-color'  => '#fe2',
                        'border-right-width'  => '1.4rem',
                        'border-right-style'  => 'solid',
                        'border-bottom-color' => '#fe3',
                        'border-bottom-width' => '1.3rem',
                        'border-left-color'   => 'var(--wp--preset--color--swampy-yellow)',
                        'border-left-width'   => '0.5rem',
                        'border-left-style'   => 'dotted',
                    ],
                ],
            ],

            'inline_invalid_box_model_style_with_sides'    => [
                'block_styles'    => [
                    'border' => [
                        'top'    => [
                            'top'    => '#fe1',
                            'right'  => '1.5rem',
                            'cheese' => 'dashed',
                        ],
                        'right'  => [
                            'right' => '#fe2',
                            'top'   => '1.4rem',
                            'bacon' => 'solid',
                        ],
                        'bottom' => [
                            'color'  => 'var:preset|color|terrible-lizard',
                            'bottom' => '1.3rem',
                        ],
                        'left'   => [
                            'left'  => null,
                            'width' => null,
                            'top'   => 'dotted',
                        ],
                    ],
                ],
                'options'         => [],
                'expected_output' => [
                    'css'          => 'border-bottom-color:var(--wp--preset--color--terrible-lizard);',
                    'declarations' => [
                        'border-bottom-color' => 'var(--wp--preset--color--terrible-lizard)',
                    ],
                ],
            ],

            'inline_background_image_url_with_background_size' => [
                'block_styles'    => [
                    'background' => [
                        'backgroundImage'      => [
                            'url' => 'https://example.com/image.jpg',
                        ],
                        'backgroundPosition'   => 'center',
                        'backgroundRepeat'     => 'no-repeat',
                        'backgroundSize'       => 'cover',
                        'backgroundAttachment' => 'fixed',
                    ],
                ],
                'options'         => [],
                'expected_output' => [
                    'css'          => "background-image:url('https://example.com/image.jpg');background-position:center;background-repeat:no-repeat;background-size:cover;background-attachment:fixed;",
                    'declarations' => [
                        'background-image'      => "url('https://example.com/image.jpg')",
                        'background-position'   => 'center',
                        'background-repeat'     => 'no-repeat',
                        'background-size'       => 'cover',
                        'background-attachment' => 'fixed',
                    ],
                ],
            ],
        ];
    }

    /**
     * Tests adding rules to a store and retrieving a generated stylesheet.
     *
     * @ticket 56467
     *
     * @covers ::wp_style_engine_get_styles
     */
    public function test_should_store_block_styles_using_context()
    {
        $block_styles = [
            'spacing' => [
                'padding' => [
                    'top'    => '42px',
                    'left'   => '2%',
                    'bottom' => '44px',
                    'right'  => '5rem',
                ],
            ],
        ];

        $generated_styles = wp_style_engine_get_styles(
            $block_styles,
            [
                'context'  => 'block-supports',
                'selector' => 'article',
            ]
        );
        $store            = WP_Style_Engine::get_store('block-supports');
        $rule             = $store->get_all_rules()['article'];

        $this->assertSame($generated_styles['css'], $rule->get_css());
    }

    /**
     * Tests that passing no context does not store styles.
     *
     * @ticket 56467
     *
     * @covers ::wp_style_engine_get_styles
     */
    public function test_should_not_store_block_styles_without_context()
    {
        $block_styles = [
            'typography' => [
                'fontSize' => '999px',
            ],
        ];

        wp_style_engine_get_styles(
            $block_styles,
            [
                'selector' => '#font-size-rulez',
            ]
        );

        $all_stores = WP_Style_Engine_CSS_Rules_Store::get_stores();

        $this->assertEmpty($all_stores);
    }

    /**
     * Tests adding rules to a store and retrieving a generated stylesheet.
     *
     * @ticket 56467
     *
     * @covers ::wp_style_engine_get_stylesheet_from_context
     */
    public function test_should_get_stored_stylesheet_from_context()
    {
        $css_rules           = [
            [
                'selector'     => '.frodo',
                'declarations' => [
                    'color'        => 'brown',
                    'height'       => '10px',
                    'width'        => '30px',
                    'border-style' => 'dotted',
                ],
            ],
            [
                'selector'     => '.samwise',
                'declarations' => [
                    'color'        => 'brown',
                    'height'       => '20px',
                    'width'        => '50px',
                    'border-style' => 'solid',
                ],
            ],
        ];
        $compiled_stylesheet = wp_style_engine_get_stylesheet_from_css_rules(
            $css_rules,
            [
                'context' => 'test-store',
            ]
        );

        $this->assertSame($compiled_stylesheet, wp_style_engine_get_stylesheet_from_context('test-store'));
    }

    /**
     * Tests returning a generated stylesheet from a set of rules.
     *
     * @ticket 56467
     *
     * @covers ::wp_style_engine_get_stylesheet_from_css_rules
     */
    public function test_should_return_stylesheet_from_css_rules()
    {
        $css_rules = [
            [
                'selector'     => '.saruman',
                'declarations' => [
                    'color'        => 'white',
                    'height'       => '100px',
                    'border-style' => 'solid',
                    'align-self'   => 'unset',
                ],
            ],
            [
                'selector'     => '.gandalf',
                'declarations' => [
                    'color'        => 'grey',
                    'height'       => '90px',
                    'border-style' => 'dotted',
                    'align-self'   => 'safe center',
                ],
            ],
            [
                'selector'     => '.radagast',
                'declarations' => [
                    'color'        => 'brown',
                    'height'       => '60px',
                    'border-style' => 'dashed',
                    'align-self'   => 'stretch',
                ],
            ],
        ];

        $compiled_stylesheet = wp_style_engine_get_stylesheet_from_css_rules($css_rules, [ 'prettify' => false ]);

        $this->assertSame('.saruman{color:white;height:100px;border-style:solid;align-self:unset;}.gandalf{color:grey;height:90px;border-style:dotted;align-self:safe center;}.radagast{color:brown;height:60px;border-style:dashed;align-self:stretch;}', $compiled_stylesheet);
    }

    /**
     * Tests that incoming styles are deduped and merged.
     *
     * @ticket 58811
     * @ticket 56467
     *
     * @covers ::wp_style_engine_get_stylesheet_from_css_rules
     */
    public function test_should_dedupe_and_merge_css_rules()
    {
        $css_rules = [
            [
                'selector'     => '.gandalf',
                'declarations' => [
                    'color'        => 'grey',
                    'height'       => '90px',
                    'border-style' => 'dotted',
                ],
            ],
            [
                'selector'     => '.gandalf',
                'declarations' => [
                    'color'         => 'white',
                    'height'        => '190px',
                    'padding'       => '10px',
                    'margin-bottom' => '100px',
                ],
            ],
            [
                'selector'     => '.dumbledore',
                'declarations' => [
                    'color'        => 'grey',
                    'height'       => '90px',
                    'border-style' => 'dotted',
                ],
            ],
            [
                'selector'     => '.rincewind',
                'declarations' => [
                    'color'        => 'grey',
                    'height'       => '90px',
                    'border-style' => 'dotted',
                ],
            ],
        ];

        $compiled_stylesheet = wp_style_engine_get_stylesheet_from_css_rules($css_rules, [ 'prettify' => false ]);

        $this->assertSame('.gandalf{color:white;height:190px;border-style:dotted;padding:10px;margin-bottom:100px;}.dumbledore{color:grey;height:90px;border-style:dotted;}.rincewind{color:grey;height:90px;border-style:dotted;}', $compiled_stylesheet);
    }

    /**
     * Tests returning a generated stylesheet from a set of nested rules and merging their declarations.
     *
     * @ticket 61099
     *
     * @covers ::wp_style_engine_get_stylesheet_from_css_rules
     */
    public function test_should_merge_declarations_for_rules_groups()
    {
        $css_rules = [
            [
                'selector'     => '.saruman',
                'rules_group'  => '@container (min-width: 700px)',
                'declarations' => [
                    'color'        => 'white',
                    'height'       => '100px',
                    'border-style' => 'solid',
                    'align-self'   => 'stretch',
                ],
            ],
            [
                'selector'     => '.saruman',
                'rules_group'  => '@container (min-width: 700px)',
                'declarations' => [
                    'color'       => 'black',
                    'font-family' => 'The-Great-Eye',
                ],
            ],
        ];

        $compiled_stylesheet = wp_style_engine_get_stylesheet_from_css_rules($css_rules, [ 'prettify' => false ]);

        $this->assertSame('@container (min-width: 700px){.saruman{color:black;height:100px;border-style:solid;align-self:stretch;font-family:The-Great-Eye;}}', $compiled_stylesheet);
    }

    /**
     * Tests returning a generated stylesheet from a set of nested rules.
     *
     * @ticket 61099
     *
     * @covers ::wp_style_engine_get_stylesheet_from_css_rules
     */
    public function test_should_return_stylesheet_with_nested_rules()
    {
        $css_rules = [
            [
                'rules_group'  => '.foo',
                'selector'     => '@media (orientation: landscape)',
                'declarations' => [
                    'background-color' => 'blue',
                ],
            ],
            [
                'rules_group'  => '.foo',
                'selector'     => '@media (min-width > 1024px)',
                'declarations' => [
                    'background-color' => 'cotton-blue',
                ],
            ],
        ];

        $compiled_stylesheet = wp_style_engine_get_stylesheet_from_css_rules($css_rules, [ 'prettify' => false ]);

        $this->assertSame('.foo{@media (orientation: landscape){background-color:blue;}}.foo{@media (min-width > 1024px){background-color:cotton-blue;}}', $compiled_stylesheet);
    }
}
