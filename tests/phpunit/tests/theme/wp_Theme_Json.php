<?php

declare(strict_types=1);

/**
 * Test WP_Theme_JSON class.
 *
 * @package WordPress
 * @subpackage Theme
 *
 * @since 5.8.0
 *
 * @group themes
 *
 * @covers WP_Theme_JSON
 */
class Tests_Theme_wpThemeJson extends WP_UnitTestCase
{
    /**
     * Administrator ID.
     *
     * @var int
     */
    private static $administrator_id;

    /**
     * User ID.
     *
     * @var int
     */
    private static $user_id;

    public static function set_up_before_class()
    {
        parent::set_up_before_class();

        static::$administrator_id = self::factory()->user->create(
            [
                'role' => 'administrator',
            ]
        );

        if (is_multisite()) {
            grant_super_admin(self::$administrator_id);
        }

        static::$user_id = self::factory()->user->create();
    }

    /**
     * @ticket 52991
     * @ticket 54336
     */
    public function test_get_settings()
    {
        $theme_json = new WP_Theme_JSON(
            [
                'version'  => WP_Theme_JSON::LATEST_SCHEMA,
                'settings' => [
                    'color'       => [
                        'custom' => false,
                    ],
                    'layout'      => [
                        'contentSize' => 'value',
                        'invalid/key' => 'value',
                    ],
                    'invalid/key' => 'value',
                    'blocks'      => [
                        'core/group' => [
                            'color'       => [
                                'custom' => false,
                            ],
                            'invalid/key' => 'value',
                        ],
                    ],
                ],
                'styles'   => [
                    'elements' => [
                        'link' => [
                            'color' => [
                                'text' => '#111',
                            ],
                        ],
                    ],
                ],
            ]
        );

        $actual = $theme_json->get_settings();

        $expected = [
            'color'  => [
                'custom' => false,
            ],
            'layout' => [
                'contentSize' => 'value',
            ],
            'blocks' => [
                'core/group' => [
                    'color' => [
                        'custom' => false,
                    ],
                ],
            ],
        ];

        $this->assertEqualSetsWithIndex($expected, $actual);
    }

    /**
     * @ticket 53397
     */
    public function test_get_settings_presets_are_keyed_by_origin()
    {
        $default_origin = new WP_Theme_JSON(
            [
                'version'  => WP_Theme_JSON::LATEST_SCHEMA,
                'settings' => [
                    'color'       => [
                        'palette' => [
                            [
                                'slug'  => 'white',
                                'color' => 'white',
                            ],
                        ],
                    ],
                    'invalid/key' => 'value',
                    'blocks'      => [
                        'core/group' => [
                            'color' => [
                                'palette' => [
                                    [
                                        'slug'  => 'white',
                                        'color' => 'white',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'default'
        );
        $no_origin      = new WP_Theme_JSON(
            [
                'version'  => WP_Theme_JSON::LATEST_SCHEMA,
                'settings' => [
                    'color'       => [
                        'palette' => [
                            [
                                'slug'  => 'black',
                                'color' => 'black',
                            ],
                        ],
                    ],
                    'invalid/key' => 'value',
                    'blocks'      => [
                        'core/group' => [
                            'color' => [
                                'palette' => [
                                    [
                                        'slug'  => 'black',
                                        'color' => 'black',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );

        $actual_default   = $default_origin->get_raw_data();
        $actual_no_origin = $no_origin->get_raw_data();

        $expected_default   = [
            'version'  => WP_Theme_JSON::LATEST_SCHEMA,
            'settings' => [
                'color'  => [
                    'palette' => [
                        'default' => [
                            [
                                'slug'  => 'white',
                                'color' => 'white',
                            ],
                        ],
                    ],
                ],
                'blocks' => [
                    'core/group' => [
                        'color' => [
                            'palette' => [
                                'default' => [
                                    [
                                        'slug'  => 'white',
                                        'color' => 'white',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $expected_no_origin = [
            'version'  => WP_Theme_JSON::LATEST_SCHEMA,
            'settings' => [
                'color'  => [
                    'palette' => [
                        'theme' => [
                            [
                                'slug'  => 'black',
                                'color' => 'black',
                            ],
                        ],
                    ],
                ],
                'blocks' => [
                    'core/group' => [
                        'color' => [
                            'palette' => [
                                'theme' => [
                                    [
                                        'slug'  => 'black',
                                        'color' => 'black',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->assertEqualSetsWithIndex($expected_default, $actual_default);
        $this->assertEqualSetsWithIndex($expected_no_origin, $actual_no_origin);
    }

    public function test_get_settings_appearance_true_opts_in()
    {
        $theme_json = new WP_Theme_JSON(
            [
                'version'  => WP_Theme_JSON::LATEST_SCHEMA,
                'settings' => [
                    'appearanceTools' => true,
                    'spacing'         => [
                        'blockGap' => false, // This should override appearanceTools.
                    ],
                    'blocks'          => [
                        'core/paragraph' => [
                            'typography' => [
                                'lineHeight' => false,
                            ],
                        ],
                        'core/group'     => [
                            'appearanceTools' => true,
                            'typography'      => [
                                'lineHeight' => false, // This should override appearanceTools.
                            ],
                            'spacing'         => [
                                'blockGap' => null,
                            ],
                        ],
                    ],
                ],
            ]
        );

        $actual   = $theme_json->get_settings();
        $expected = [
            'background' => [
                'backgroundImage' => true,
                'backgroundSize'  => true,
            ],
            'border'     => [
                'width'  => true,
                'style'  => true,
                'radius' => true,
                'color'  => true,
            ],
            'color'      => [
                'link'    => true,
                'heading' => true,
                'button'  => true,
                'caption' => true,
            ],
            'dimensions' => [
                'aspectRatio' => true,
                'height'      => true,
                'minHeight'   => true,
                'width'       => true,
            ],
            'position'   => [
                'sticky' => true,
            ],
            'spacing'    => [
                'blockGap' => false,
                'margin'   => true,
                'padding'  => true,
            ],
            'typography' => [
                'lineHeight'  => true,
                'textColumns' => true,
            ],
            'blocks'     => [
                'core/paragraph' => [
                    'typography' => [
                        'lineHeight' => false,
                    ],
                ],
                'core/group'     => [
                    'background' => [
                        'backgroundImage' => true,
                        'backgroundSize'  => true,
                    ],
                    'border'     => [
                        'width'  => true,
                        'style'  => true,
                        'radius' => true,
                        'color'  => true,
                    ],
                    'color'      => [
                        'link'    => true,
                        'heading' => true,
                        'button'  => true,
                        'caption' => true,
                    ],
                    'dimensions' => [
                        'aspectRatio' => true,
                        'height'      => true,
                        'minHeight'   => true,
                        'width'       => true,
                    ],
                    'position'   => [
                        'sticky' => true,
                    ],
                    'spacing'    => [
                        'blockGap' => false,
                        'margin'   => true,
                        'padding'  => true,
                    ],
                    'typography' => [
                        'lineHeight'  => false,
                        'textColumns' => true,
                    ],
                ],
            ],
        ];

        $this->assertEqualSetsWithIndex($expected, $actual);
    }

    public function test_get_settings_appearance_false_does_not_opt_in()
    {
        $theme_json = new WP_Theme_JSON(
            [
                'version'  => WP_Theme_JSON::LATEST_SCHEMA,
                'settings' => [
                    'appearanceTools' => false,
                    'border'          => [
                        'width' => true,
                    ],
                    'blocks'          => [
                        'core/paragraph' => [
                            'typography' => [
                                'lineHeight' => false,
                            ],
                        ],
                        'core/group'     => [
                            'typography' => [
                                'lineHeight' => false,
                            ],
                        ],
                    ],
                ],
            ]
        );

        $actual   = $theme_json->get_settings();
        $expected = [
            'appearanceTools' => false,
            'border'          => [
                'width' => true,
            ],
            'blocks'          => [
                'core/paragraph' => [
                    'typography' => [
                        'lineHeight' => false,
                    ],
                ],
                'core/group'     => [
                    'typography' => [
                        'lineHeight' => false,
                    ],
                ],
            ],
        ];

        $this->assertEqualSetsWithIndex($expected, $actual);
    }

    /**
     * @ticket 53175
     * @ticket 54336
     * @ticket 56611
     * @ticket 58549
     * @ticket 58550
     * @ticket 60365
     * @ticket 60936
     * @ticket 61165
     * @ticket 61630
     * @ticket 61704
     * @ticket 63799
     */
    public function test_get_stylesheet()
    {
        $theme_json = new WP_Theme_JSON(
            [
                'version'  => WP_Theme_JSON::LATEST_SCHEMA,
                'settings' => [
                    'border'     => [
                        'radiusSizes' => [
                            [
                                'name' => 'Small',
                                'slug' => 'small',
                                'size' => '2px',
                            ],
                            [
                                'name' => 'Medium',
                                'slug' => 'medium',
                                'size' => '4px',
                            ],
                        ],
                    ],
                    'dimensions' => [
                        'dimensionSizes' => [
                            [
                                'name' => 'Small',
                                'slug' => 'small',
                                'size' => '100px',
                            ],
                            [
                                'name' => 'Large',
                                'slug' => 'large',
                                'size' => '200px',
                            ],
                        ],
                    ],
                    'color'      => [
                        'text'      => 'value',
                        'palette'   => [
                            [
                                'slug'  => 'grey',
                                'color' => 'grey',
                            ],
                        ],
                        'gradients' => [
                            [
                                'gradient' => 'linear-gradient(135deg,rgba(0,0,0) 0%,rgb(0,0,0) 100%)',
                                'name'     => 'Custom gradient',
                                'slug'     => 'custom-gradient',
                            ],
                        ],
                        'duotone'   => [
                            [
                                'colors' => [ '#333333', '#aaaaaa' ],
                                'name'   => 'Custom Duotone',
                                'slug'   => 'custom-duotone',
                            ],
                        ],
                    ],
                    'typography' => [
                        'fontFamilies' => [
                            [
                                'name'       => 'Arial',
                                'slug'       => 'arial',
                                'fontFamily' => 'Arial, serif',
                            ],
                        ],
                        'fontSizes'    => [
                            [
                                'slug' => 'small',
                                'size' => '14px',
                            ],
                            [
                                'slug' => 'big',
                                'size' => '41px',
                            ],
                        ],
                    ],
                    'misc'       => 'value',
                    'blocks'     => [
                        'core/group' => [
                            'custom' => [
                                'base-font'   => 16,
                                'line-height' => [
                                    'small'  => 1.2,
                                    'medium' => 1.4,
                                    'large'  => 1.8,
                                ],
                            ],
                        ],
                    ],
                ],
                'styles'   => [
                    'color'    => [
                        'text' => 'var:preset|color|grey',
                    ],
                    'misc'     => 'value',
                    'elements' => [
                        'link'   => [
                            'color' => [
                                'text'       => '#111',
                                'background' => '#333',
                            ],
                        ],
                        'button' => [
                            'shadow' => '10px 10px 5px 0px rgba(0,0,0,0.66)',
                        ],
                    ],
                    'blocks'   => [
                        'core/cover'        => [
                            'border'     => [
                                'radius' => 'var:preset|border-radius|small',
                            ],
                            'dimensions' => [
                                'aspectRatio' => '16/9',
                            ],
                        ],
                        'core/group'        => [
                            'color'      => [
                                'gradient' => 'var:preset|gradient|custom-gradient',
                            ],
                            'border'     => [
                                'radius' => '10px',
                            ],
                            'dimensions' => [
                                'minHeight' => '50vh',
                                'height'    => '500px',
                                'width'     => 'var:preset|dimension|large',
                            ],
                            'elements'   => [
                                'link' => [
                                    'color' => [
                                        'text' => '#111',
                                    ],
                                ],
                            ],
                            'spacing'    => [
                                'padding' => '24px',
                            ],
                        ],
                        'core/heading'      => [
                            'color'    => [
                                'text' => '#123456',
                            ],
                            'elements' => [
                                'link' => [
                                    'color'      => [
                                        'text'       => '#111',
                                        'background' => '#333',
                                    ],
                                    'typography' => [
                                        'fontSize' => '60px',
                                    ],
                                ],
                            ],
                        ],
                        'core/media-text'   => [
                            'typography' => [
                                'textAlign' => 'center',
                            ],
                        ],
                        'core/post-date'    => [
                            'color'    => [
                                'text' => '#123456',
                            ],
                            'elements' => [
                                'link' => [
                                    'color' => [
                                        'background' => '#777',
                                        'text'       => '#555',
                                    ],
                                ],
                            ],
                        ],
                        'core/post-excerpt' => [
                            'typography' => [
                                'textColumns' => 2,
                                'textIndent'  => '2em',
                            ],
                        ],
                        'core/image'        => [
                            'border'  => [
                                'radius' => [
                                    'topLeft'     => '10px',
                                    'bottomRight' => '1em',
                                ],
                            ],
                            'spacing' => [
                                'margin' => [
                                    'bottom' => '30px',
                                ],
                            ],
                            'filter'  => [
                                'duotone' => 'var:preset|duotone|custom-duotone',
                            ],
                        ],
                    ],
                    'spacing'  => [
                        'blockGap' => '24px',
                    ],
                ],
                'misc'     => 'value',
            ]
        );

        $variables = ':root{--wp--preset--color--grey: grey;--wp--preset--gradient--custom-gradient: linear-gradient(135deg,rgba(0,0,0) 0%,rgb(0,0,0) 100%);--wp--preset--font-size--small: 14px;--wp--preset--font-size--big: 41px;--wp--preset--font-family--arial: Arial, serif;--wp--preset--border-radius--small: 2px;--wp--preset--border-radius--medium: 4px;--wp--preset--dimension--small: 100px;--wp--preset--dimension--large: 200px;}.wp-block-group{--wp--custom--base-font: 16;--wp--custom--line-height--small: 1.2;--wp--custom--line-height--medium: 1.4;--wp--custom--line-height--large: 1.8;}';
        $styles    = ':where(body) { margin: 0; }.wp-site-blocks > .alignleft { float: left; margin-right: 2em; }.wp-site-blocks > .alignright { float: right; margin-left: 2em; }.wp-site-blocks > .aligncenter { justify-content: center; margin-left: auto; margin-right: auto; }:where(.is-layout-flex){gap: 0.5em;}:where(.is-layout-grid){gap: 0.5em;}.is-layout-flow > .alignleft{float: left;margin-inline-start: 0;margin-inline-end: 2em;}.is-layout-flow > .alignright{float: right;margin-inline-start: 2em;margin-inline-end: 0;}.is-layout-flow > .aligncenter{margin-left: auto !important;margin-right: auto !important;}.is-layout-constrained > .alignleft{float: left;margin-inline-start: 0;margin-inline-end: 2em;}.is-layout-constrained > .alignright{float: right;margin-inline-start: 2em;margin-inline-end: 0;}.is-layout-constrained > .aligncenter{margin-left: auto !important;margin-right: auto !important;}.is-layout-constrained > :where(:not(.alignleft):not(.alignright):not(.alignfull)){margin-left: auto !important;margin-right: auto !important;}body .is-layout-flex{display: flex;}.is-layout-flex{flex-wrap: wrap;align-items: center;}.is-layout-flex > :is(*, div){margin: 0;}body .is-layout-grid{display: grid;}.is-layout-grid > :is(*, div){margin: 0;}body{color: var(--wp--preset--color--grey);}a:where(:not(.wp-element-button)){background-color: #333;color: #111;}:root :where(.wp-element-button, .wp-block-button__link){box-shadow: 10px 10px 5px 0px rgba(0,0,0,0.66);}:root :where(.wp-block-cover){min-height: unset;aspect-ratio: 16/9;border-radius: var(--wp--preset--border-radius--small);}:root :where(.wp-block-group){background: var(--wp--preset--gradient--custom-gradient);border-radius: 10px;min-height: 50vh;padding: 24px;height: 500px;width: var(--wp--preset--dimension--large);}:root :where(.wp-block-group a:where(:not(.wp-element-button))){color: #111;}:root :where(.wp-block-heading){color: #123456;}:root :where(.wp-block-heading a:where(:not(.wp-element-button))){background-color: #333;color: #111;font-size: 60px;}:root :where(.wp-block-media-text){text-align: center;}:root :where(.wp-block-post-date){color: #123456;}:root :where(.wp-block-post-date a:where(:not(.wp-element-button))){background-color: #777;color: #555;}:root :where(.wp-block-post-excerpt){column-count: 2;text-indent: 2em;}:root :where(.wp-block-image){margin-bottom: 30px;}:root :where(.wp-block-image img, .wp-block-image .wp-block-image__crop-area, .wp-block-image .components-placeholder){border-top-left-radius: 10px;border-bottom-right-radius: 1em;}:root :where(.wp-block-image img, .wp-block-image .components-placeholder){filter: var(--wp--preset--duotone--custom-duotone);}';
        $presets   = '.has-grey-color{color: var(--wp--preset--color--grey) !important;}.has-grey-background-color{background-color: var(--wp--preset--color--grey) !important;}.has-grey-border-color{border-color: var(--wp--preset--color--grey) !important;}.has-custom-gradient-gradient-background{background: var(--wp--preset--gradient--custom-gradient) !important;}.has-small-font-size{font-size: var(--wp--preset--font-size--small) !important;}.has-big-font-size{font-size: var(--wp--preset--font-size--big) !important;}.has-arial-font-family{font-family: var(--wp--preset--font-family--arial) !important;}';
        $all       = $variables . $styles . $presets;

        $this->assertSame($variables, $theme_json->get_stylesheet([ 'variables' ]));
        $this->assertSame($styles, $theme_json->get_stylesheet([ 'styles' ]));
        $this->assertSame($presets, $theme_json->get_stylesheet([ 'presets' ]));
        $this->assertSame($all, $theme_json->get_stylesheet());
    }

    /**
     * @ticket 54336
     * @ticket 58550
     * @ticket 60936
     * @ticket 61165
     */
    public function test_get_styles_for_block_support_for_shorthand_and_longhand_values()
    {
        $theme_json = new WP_Theme_JSON(
            [
                'version' => WP_Theme_JSON::LATEST_SCHEMA,
                'styles'  => [
                    'blocks' => [
                        'core/group' => [
                            'border'  => [
                                'radius' => '10px',
                            ],
                            'spacing' => [
                                'padding' => '24px',
                                'margin'  => '1em',
                            ],
                        ],
                        'core/image' => [
                            'border'  => [
                                'radius' => [
                                    'topLeft'     => '10px',
                                    'bottomRight' => '1em',
                                ],
                            ],
                            'spacing' => [
                                'padding' => [
                                    'top' => '15px',
                                ],
                                'margin'  => [
                                    'bottom' => '30px',
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );

        $group_node = [
            'name'      => 'core/group',
            'path'      => [ 'styles', 'blocks', 'core/group' ],
            'selector'  => '.wp-block-group',
            'selectors' => [
                'root' => '.wp-block-group',
            ],
        ];
        $image_node = [
            'name'      => 'core/image',
            'path'      => [ 'styles', 'blocks', 'core/image' ],
            'selector'  => '.wp-block-image',
            'selectors' => [
                'root'   => '.wp-block-image',
                'border' => '.wp-block-image img, .wp-block-image .wp-block-image__crop-area, .wp-block-image .components-placeholder',
            ],
        ];

        $group_styles = ':root :where(.wp-block-group){border-radius: 10px;margin: 1em;padding: 24px;}';
        $image_styles = ':root :where(.wp-block-image){margin-bottom: 30px;padding-top: 15px;}:root :where(.wp-block-image img, .wp-block-image .wp-block-image__crop-area, .wp-block-image .components-placeholder){border-top-left-radius: 10px;border-bottom-right-radius: 1em;}';
        $this->assertSame($group_styles, $theme_json->get_styles_for_block($group_node));
        $this->assertSame($image_styles, $theme_json->get_styles_for_block($image_node));
    }

    /**
     * @ticket 54336
     * @ticket 58550
     * @ticket 60936
     * @ticket 61165
     */
    public function test_get_stylesheet_skips_disabled_protected_properties()
    {
        $theme_json = new WP_Theme_JSON(
            [
                'version'  => WP_Theme_JSON::LATEST_SCHEMA,
                'settings' => [
                    'spacing' => [
                        'blockGap' => null,
                    ],
                ],
                'styles'   => [
                    'spacing' => [
                        'blockGap' => '1em',
                    ],
                    'blocks'  => [
                        'core/columns' => [
                            'spacing' => [
                                'blockGap' => '24px',
                            ],
                        ],
                    ],
                ],
            ]
        );

        $expected = ':where(body) { margin: 0; }.wp-site-blocks > .alignleft { float: left; margin-right: 2em; }.wp-site-blocks > .alignright { float: right; margin-left: 2em; }.wp-site-blocks > .aligncenter { justify-content: center; margin-left: auto; margin-right: auto; }:where(.is-layout-flex){gap: 0.5em;}:where(.is-layout-grid){gap: 0.5em;}.is-layout-flow > .alignleft{float: left;margin-inline-start: 0;margin-inline-end: 2em;}.is-layout-flow > .alignright{float: right;margin-inline-start: 2em;margin-inline-end: 0;}.is-layout-flow > .aligncenter{margin-left: auto !important;margin-right: auto !important;}.is-layout-constrained > .alignleft{float: left;margin-inline-start: 0;margin-inline-end: 2em;}.is-layout-constrained > .alignright{float: right;margin-inline-start: 2em;margin-inline-end: 0;}.is-layout-constrained > .aligncenter{margin-left: auto !important;margin-right: auto !important;}.is-layout-constrained > :where(:not(.alignleft):not(.alignright):not(.alignfull)){margin-left: auto !important;margin-right: auto !important;}body .is-layout-flex{display: flex;}.is-layout-flex{flex-wrap: wrap;align-items: center;}.is-layout-flex > :is(*, div){margin: 0;}body .is-layout-grid{display: grid;}.is-layout-grid > :is(*, div){margin: 0;}:where(.wp-block-columns.is-layout-flex){gap: 2em;}:where(.wp-block-columns.is-layout-grid){gap: 2em;}';
        $this->assertSame($expected, $theme_json->get_stylesheet());
        $this->assertSame($expected, $theme_json->get_stylesheet([ 'styles' ]));
    }

    /**
     * @ticket 54336
     * @ticket 58548
     * @ticket 58550
     * @ticket 60936
     * @ticket 61165
     * @ticket 61829
     */
    public function test_get_stylesheet_renders_enabled_protected_properties()
    {
        $theme_json = new WP_Theme_JSON(
            [
                'version'  => WP_Theme_JSON::LATEST_SCHEMA,
                'settings' => [
                    'spacing' => [
                        'blockGap' => true,
                    ],
                ],
                'styles'   => [
                    'spacing' => [
                        'blockGap' => '1em',
                    ],
                ],
            ]
        );

        $expected = ':where(body) { margin: 0; }.wp-site-blocks > .alignleft { float: left; margin-right: 2em; }.wp-site-blocks > .alignright { float: right; margin-left: 2em; }.wp-site-blocks > .aligncenter { justify-content: center; margin-left: auto; margin-right: auto; }:where(.wp-site-blocks) > * { margin-block-start: 1em; margin-block-end: 0; }:where(.wp-site-blocks) > :first-child { margin-block-start: 0; }:where(.wp-site-blocks) > :last-child { margin-block-end: 0; }:root { --wp--style--block-gap: 1em; }:root :where(.is-layout-flow) > :first-child{margin-block-start: 0;}:root :where(.is-layout-flow) > :last-child{margin-block-end: 0;}:root :where(.is-layout-flow) > *{margin-block-start: 1em;margin-block-end: 0;}:root :where(.is-layout-constrained) > :first-child{margin-block-start: 0;}:root :where(.is-layout-constrained) > :last-child{margin-block-end: 0;}:root :where(.is-layout-constrained) > *{margin-block-start: 1em;margin-block-end: 0;}:root :where(.is-layout-flex){gap: 1em;}:root :where(.is-layout-grid){gap: 1em;}.is-layout-flow > .alignleft{float: left;margin-inline-start: 0;margin-inline-end: 2em;}.is-layout-flow > .alignright{float: right;margin-inline-start: 2em;margin-inline-end: 0;}.is-layout-flow > .aligncenter{margin-left: auto !important;margin-right: auto !important;}.is-layout-constrained > .alignleft{float: left;margin-inline-start: 0;margin-inline-end: 2em;}.is-layout-constrained > .alignright{float: right;margin-inline-start: 2em;margin-inline-end: 0;}.is-layout-constrained > .aligncenter{margin-left: auto !important;margin-right: auto !important;}.is-layout-constrained > :where(:not(.alignleft):not(.alignright):not(.alignfull)){margin-left: auto !important;margin-right: auto !important;}body .is-layout-flex{display: flex;}.is-layout-flex{flex-wrap: wrap;align-items: center;}.is-layout-flex > :is(*, div){margin: 0;}body .is-layout-grid{display: grid;}.is-layout-grid > :is(*, div){margin: 0;}';
        $this->assertSame($expected, $theme_json->get_stylesheet());
        $this->assertSame($expected, $theme_json->get_stylesheet([ 'styles' ]));
    }

    /**
     * @ticket 52991
     * @ticket 54336
     */
    public function test_get_stylesheet_preset_classes_work_with_compounded_selectors()
    {
        $theme_json = new WP_Theme_JSON(
            [
                'version'  => WP_Theme_JSON::LATEST_SCHEMA,
                'settings' => [
                    'blocks' => [
                        'core/heading' => [
                            'color' => [
                                'palette' => [
                                    [
                                        'slug'  => 'white',
                                        'color' => '#fff',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );

        $this->assertSame(
            '.wp-block-heading.has-white-color{color: var(--wp--preset--color--white) !important;}.wp-block-heading.has-white-background-color{background-color: var(--wp--preset--color--white) !important;}.wp-block-heading.has-white-border-color{border-color: var(--wp--preset--color--white) !important;}',
            $theme_json->get_stylesheet([ 'presets' ])
        );
    }

    /**
     * @ticket 53175
     * @ticket 54336
     * @ticket 58550
     * @ticket 60936
     * @ticket 61165
     */
    public function test_get_stylesheet_preset_rules_come_after_block_rules()
    {
        $theme_json = new WP_Theme_JSON(
            [
                'version'  => WP_Theme_JSON::LATEST_SCHEMA,
                'settings' => [
                    'blocks' => [
                        'core/group' => [
                            'color' => [
                                'palette' => [
                                    [
                                        'slug'  => 'grey',
                                        'color' => 'grey',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'styles'   => [
                    'blocks' => [
                        'core/group' => [
                            'color' => [
                                'text' => 'red',
                            ],
                        ],
                    ],
                ],
            ]
        );

        $styles    = ':root :where(.wp-block-group){color: red;}';
        $presets   = '.wp-block-group.has-grey-color{color: var(--wp--preset--color--grey) !important;}.wp-block-group.has-grey-background-color{background-color: var(--wp--preset--color--grey) !important;}.wp-block-group.has-grey-border-color{border-color: var(--wp--preset--color--grey) !important;}';
        $variables = '.wp-block-group{--wp--preset--color--grey: grey;}';

        $all = $variables . $styles . $presets;

        $this->assertSame($all, $theme_json->get_stylesheet([ 'styles', 'presets', 'variables' ], null, [ 'skip_root_layout_styles' => true ]));
        $this->assertSame($styles, $theme_json->get_stylesheet([ 'styles' ], null, [ 'skip_root_layout_styles' => true ]));
        $this->assertSame($presets, $theme_json->get_stylesheet([ 'presets' ]));
        $this->assertSame($variables, $theme_json->get_stylesheet([ 'variables' ]));
    }

    /**
     * @ticket 54336
     */
    public function test_get_stylesheet_generates_proper_classes_and_css_vars_from_slugs()
    {
        $theme_json = new WP_Theme_JSON(
            [
                'version'  => WP_Theme_JSON::LATEST_SCHEMA,
                'settings' => [
                    'color'  => [
                        'palette' => [
                            [
                                'slug'  => 'grey',
                                'color' => 'grey',
                            ],
                            [
                                'slug'  => 'dark grey',
                                'color' => 'grey',
                            ],
                            [
                                'slug'  => 'light-grey',
                                'color' => 'grey',
                            ],
                            [
                                'slug'  => 'white2black',
                                'color' => 'grey',
                            ],
                        ],
                    ],
                    'custom' => [
                        'white2black' => 'value',
                    ],
                ],
            ]
        );

        $this->assertSame(
            '.has-grey-color{color: var(--wp--preset--color--grey) !important;}.has-dark-grey-color{color: var(--wp--preset--color--dark-grey) !important;}.has-light-grey-color{color: var(--wp--preset--color--light-grey) !important;}.has-white-2-black-color{color: var(--wp--preset--color--white-2-black) !important;}.has-grey-background-color{background-color: var(--wp--preset--color--grey) !important;}.has-dark-grey-background-color{background-color: var(--wp--preset--color--dark-grey) !important;}.has-light-grey-background-color{background-color: var(--wp--preset--color--light-grey) !important;}.has-white-2-black-background-color{background-color: var(--wp--preset--color--white-2-black) !important;}.has-grey-border-color{border-color: var(--wp--preset--color--grey) !important;}.has-dark-grey-border-color{border-color: var(--wp--preset--color--dark-grey) !important;}.has-light-grey-border-color{border-color: var(--wp--preset--color--light-grey) !important;}.has-white-2-black-border-color{border-color: var(--wp--preset--color--white-2-black) !important;}',
            $theme_json->get_stylesheet([ 'presets' ])
        );
        $this->assertSame(
            ':root{--wp--preset--color--grey: grey;--wp--preset--color--dark-grey: grey;--wp--preset--color--light-grey: grey;--wp--preset--color--white-2-black: grey;--wp--custom--white-2-black: value;}',
            $theme_json->get_stylesheet([ 'variables' ])
        );
    }

    /**
     * @ticket 56467
     * @ticket 58550
     * @ticket 60936
     * @ticket 61165
     * @ticket 61630
     */
    public function test_get_styles_for_block_handles_whitelisted_element_pseudo_selectors()
    {
        $theme_json = new WP_Theme_JSON(
            [
                'version' => WP_Theme_JSON::LATEST_SCHEMA,
                'styles'  => [
                    'elements' => [
                        'link' => [
                            'color'  => [
                                'text'       => 'green',
                                'background' => 'red',
                            ],
                            ':hover' => [
                                'color'      => [
                                    'text'       => 'red',
                                    'background' => 'green',
                                ],
                                'typography' => [
                                    'textTransform' => 'uppercase',
                                    'fontSize'      => '10em',
                                ],
                            ],
                            ':focus' => [
                                'color' => [
                                    'text'       => 'yellow',
                                    'background' => 'black',
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );

        $link_node  = [
            'path'     => [ 'styles', 'elements', 'link' ],
            'selector' => 'a:where(:not(.wp-element-button))',
        ];
        $hover_node = [
            'path'     => [ 'styles', 'elements', 'link' ],
            'selector' => 'a:where(:not(.wp-element-button)):hover',
        ];
        $focus_node = [
            'path'     => [ 'styles', 'elements', 'link' ],
            'selector' => 'a:where(:not(.wp-element-button)):focus',
        ];

        $link_style  = 'a:where(:not(.wp-element-button)){background-color: red;color: green;}';
        $hover_style = ':root :where(a:where(:not(.wp-element-button)):hover){background-color: green;color: red;font-size: 10em;text-transform: uppercase;}';
        $focus_style = ':root :where(a:where(:not(.wp-element-button)):focus){background-color: black;color: yellow;}';

        $this->assertSame($link_style, $theme_json->get_styles_for_block($link_node));
        $this->assertSame($hover_style, $theme_json->get_styles_for_block($hover_node));
        $this->assertSame($focus_style, $theme_json->get_styles_for_block($focus_node));
    }

    /**
     * Tests that if an element has nothing but pseudo selector styles, they are still output by get_stylesheet.
     *
     * @ticket 56467
     * @ticket 58550
     * @ticket 60936
     * @ticket 61165
     */
    public function test_get_stylesheet_handles_only_pseudo_selector_rules_for_given_property()
    {
        $theme_json = new WP_Theme_JSON(
            [
                'version' => WP_Theme_JSON::LATEST_SCHEMA,
                'styles'  => [
                    'elements' => [
                        'link' => [
                            ':hover' => [
                                'color'      => [
                                    'text'       => 'red',
                                    'background' => 'green',
                                ],
                                'typography' => [
                                    'textTransform' => 'uppercase',
                                    'fontSize'      => '10em',
                                ],
                            ],
                            ':focus' => [
                                'color' => [
                                    'text'       => 'yellow',
                                    'background' => 'black',
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );

        $expected = ':root :where(a:where(:not(.wp-element-button)):hover){background-color: green;color: red;font-size: 10em;text-transform: uppercase;}:root :where(a:where(:not(.wp-element-button)):focus){background-color: black;color: yellow;}';

        $this->assertSame($expected, $theme_json->get_stylesheet([ 'styles' ], null, [ 'skip_root_layout_styles' => true ]));
    }

    /**
     * @ticket 56467
     * @ticket 58550
     * @ticket 60936
     * @ticket 61165
     * @ticket 61630
     */
    public function test_get_stylesheet_ignores_pseudo_selectors_on_non_whitelisted_elements()
    {
        $theme_json = new WP_Theme_JSON(
            [
                'version' => WP_Theme_JSON::LATEST_SCHEMA,
                'styles'  => [
                    'elements' => [
                        'h4' => [
                            'color'  => [
                                'text'       => 'green',
                                'background' => 'red',
                            ],
                            ':hover' => [
                                'color' => [
                                    'text'       => 'red',
                                    'background' => 'green',
                                ],
                            ],
                            ':focus' => [
                                'color' => [
                                    'text'       => 'yellow',
                                    'background' => 'black',
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );

        $expected = 'h4{background-color: red;color: green;}';

        $this->assertSame($expected, $theme_json->get_stylesheet([ 'styles' ], null, [ 'skip_root_layout_styles' => true ]));
    }

    /**
     * @ticket 56467
     * @ticket 58550
     * @ticket 60936
     * @ticket 61165
     * @ticket 61630
     */
    public function test_get_stylesheet_ignores_non_whitelisted_pseudo_selectors()
    {
        $theme_json = new WP_Theme_JSON(
            [
                'version' => WP_Theme_JSON::LATEST_SCHEMA,
                'styles'  => [
                    'elements' => [
                        'link' => [
                            'color'     => [
                                'text'       => 'green',
                                'background' => 'red',
                            ],
                            ':hover'    => [
                                'color' => [
                                    'text'       => 'red',
                                    'background' => 'green',
                                ],
                            ],
                            ':levitate' => [
                                'color' => [
                                    'text'       => 'yellow',
                                    'background' => 'black',
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );

        $expected = 'a:where(:not(.wp-element-button)){background-color: red;color: green;}:root :where(a:where(:not(.wp-element-button)):hover){background-color: green;color: red;}';

        $this->assertSame($expected, $theme_json->get_stylesheet([ 'styles' ], null, [ 'skip_root_layout_styles' => true ]));
        $this->assertStringNotContainsString('a:levitate{', $theme_json->get_stylesheet([ 'styles' ]));
    }

    /**
     * Tests that element pseudo selectors are output before block element pseudo selectors, and that whitelisted
     * block element pseudo selectors are output correctly.
     *
     * @ticket 56467
     * @ticket 58550
     * @ticket 60936
     * @ticket 61165
     * @ticket 61630
     */
    public function test_get_stylesheet_handles_priority_of_elements_vs_block_elements_pseudo_selectors()
    {
        $theme_json = new WP_Theme_JSON(
            [
                'version' => WP_Theme_JSON::LATEST_SCHEMA,
                'styles'  => [
                    'elements' => [
                        'link' => [
                            'color'  => [
                                'text'       => 'green',
                                'background' => 'red',
                            ],
                            ':hover' => [
                                'color' => [
                                    'text'       => 'red',
                                    'background' => 'green',
                                ],
                            ],
                        ],
                    ],
                    'blocks'   => [
                        'core/group' => [
                            'elements' => [
                                'link' => [
                                    ':hover' => [
                                        'color' => [
                                            'text'       => 'yellow',
                                            'background' => 'black',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );

        $expected = 'a:where(:not(.wp-element-button)){background-color: red;color: green;}:root :where(a:where(:not(.wp-element-button)):hover){background-color: green;color: red;}:root :where(.wp-block-group a:where(:not(.wp-element-button)):hover){background-color: black;color: yellow;}';

        $this->assertSame($expected, $theme_json->get_stylesheet([ 'styles' ], null, [ 'skip_root_layout_styles' => true ]));
    }

    /**
     * @ticket 56467
     * @ticket 58548
     * @ticket 58550
     * @ticket 60936
     * @ticket 61165
     * @ticket 61829
     */
    public function test_get_stylesheet_generates_layout_styles()
    {
        $theme_json = new WP_Theme_JSON(
            [
                'version'  => WP_Theme_JSON::LATEST_SCHEMA,
                'settings' => [
                    'layout'  => [
                        'contentSize' => '640px',
                        'wideSize'    => '1200px',
                    ],
                    'spacing' => [
                        'blockGap' => true,
                    ],
                ],
                'styles'   => [
                    'spacing' => [
                        'blockGap' => '1em',
                    ],
                ],
            ],
            'default'
        );

        // Results also include root site blocks styles.
        $this->assertSame(
            ':root { --wp--style--global--content-size: 640px;--wp--style--global--wide-size: 1200px; }:where(body) { margin: 0; }.wp-site-blocks > .alignleft { float: left; margin-right: 2em; }.wp-site-blocks > .alignright { float: right; margin-left: 2em; }.wp-site-blocks > .aligncenter { justify-content: center; margin-left: auto; margin-right: auto; }:where(.wp-site-blocks) > * { margin-block-start: 1em; margin-block-end: 0; }:where(.wp-site-blocks) > :first-child { margin-block-start: 0; }:where(.wp-site-blocks) > :last-child { margin-block-end: 0; }:root { --wp--style--block-gap: 1em; }:root :where(.is-layout-flow) > :first-child{margin-block-start: 0;}:root :where(.is-layout-flow) > :last-child{margin-block-end: 0;}:root :where(.is-layout-flow) > *{margin-block-start: 1em;margin-block-end: 0;}:root :where(.is-layout-constrained) > :first-child{margin-block-start: 0;}:root :where(.is-layout-constrained) > :last-child{margin-block-end: 0;}:root :where(.is-layout-constrained) > *{margin-block-start: 1em;margin-block-end: 0;}:root :where(.is-layout-flex){gap: 1em;}:root :where(.is-layout-grid){gap: 1em;}.is-layout-flow > .alignleft{float: left;margin-inline-start: 0;margin-inline-end: 2em;}.is-layout-flow > .alignright{float: right;margin-inline-start: 2em;margin-inline-end: 0;}.is-layout-flow > .aligncenter{margin-left: auto !important;margin-right: auto !important;}.is-layout-constrained > .alignleft{float: left;margin-inline-start: 0;margin-inline-end: 2em;}.is-layout-constrained > .alignright{float: right;margin-inline-start: 2em;margin-inline-end: 0;}.is-layout-constrained > .aligncenter{margin-left: auto !important;margin-right: auto !important;}.is-layout-constrained > :where(:not(.alignleft):not(.alignright):not(.alignfull)){max-width: var(--wp--style--global--content-size);margin-left: auto !important;margin-right: auto !important;}.is-layout-constrained > .alignwide{max-width: var(--wp--style--global--wide-size);}body .is-layout-flex{display: flex;}.is-layout-flex{flex-wrap: wrap;align-items: center;}.is-layout-flex > :is(*, div){margin: 0;}body .is-layout-grid{display: grid;}.is-layout-grid > :is(*, div){margin: 0;}',
            $theme_json->get_stylesheet([ 'styles' ])
        );
    }

    /**
     * @ticket 56467
     * @ticket 58548
     * @ticket 58550
     * @ticket 60936
     * @ticket 61165
     * @ticket 61829
     */
    public function test_get_stylesheet_generates_layout_styles_with_spacing_presets()
    {
        $theme_json = new WP_Theme_JSON(
            [
                'version'  => WP_Theme_JSON::LATEST_SCHEMA,
                'settings' => [
                    'layout'  => [
                        'contentSize' => '640px',
                        'wideSize'    => '1200px',
                    ],
                    'spacing' => [
                        'blockGap' => true,
                    ],
                ],
                'styles'   => [
                    'spacing' => [
                        'blockGap' => 'var:preset|spacing|60',
                    ],
                ],
            ],
            'default'
        );

        // Results also include root site blocks styles.
        $this->assertSame(
            ':root { --wp--style--global--content-size: 640px;--wp--style--global--wide-size: 1200px; }:where(body) { margin: 0; }.wp-site-blocks > .alignleft { float: left; margin-right: 2em; }.wp-site-blocks > .alignright { float: right; margin-left: 2em; }.wp-site-blocks > .aligncenter { justify-content: center; margin-left: auto; margin-right: auto; }:where(.wp-site-blocks) > * { margin-block-start: var(--wp--preset--spacing--60); margin-block-end: 0; }:where(.wp-site-blocks) > :first-child { margin-block-start: 0; }:where(.wp-site-blocks) > :last-child { margin-block-end: 0; }:root { --wp--style--block-gap: var(--wp--preset--spacing--60); }:root :where(.is-layout-flow) > :first-child{margin-block-start: 0;}:root :where(.is-layout-flow) > :last-child{margin-block-end: 0;}:root :where(.is-layout-flow) > *{margin-block-start: var(--wp--preset--spacing--60);margin-block-end: 0;}:root :where(.is-layout-constrained) > :first-child{margin-block-start: 0;}:root :where(.is-layout-constrained) > :last-child{margin-block-end: 0;}:root :where(.is-layout-constrained) > *{margin-block-start: var(--wp--preset--spacing--60);margin-block-end: 0;}:root :where(.is-layout-flex){gap: var(--wp--preset--spacing--60);}:root :where(.is-layout-grid){gap: var(--wp--preset--spacing--60);}.is-layout-flow > .alignleft{float: left;margin-inline-start: 0;margin-inline-end: 2em;}.is-layout-flow > .alignright{float: right;margin-inline-start: 2em;margin-inline-end: 0;}.is-layout-flow > .aligncenter{margin-left: auto !important;margin-right: auto !important;}.is-layout-constrained > .alignleft{float: left;margin-inline-start: 0;margin-inline-end: 2em;}.is-layout-constrained > .alignright{float: right;margin-inline-start: 2em;margin-inline-end: 0;}.is-layout-constrained > .aligncenter{margin-left: auto !important;margin-right: auto !important;}.is-layout-constrained > :where(:not(.alignleft):not(.alignright):not(.alignfull)){max-width: var(--wp--style--global--content-size);margin-left: auto !important;margin-right: auto !important;}.is-layout-constrained > .alignwide{max-width: var(--wp--style--global--wide-size);}body .is-layout-flex{display: flex;}.is-layout-flex{flex-wrap: wrap;align-items: center;}.is-layout-flex > :is(*, div){margin: 0;}body .is-layout-grid{display: grid;}.is-layout-grid > :is(*, div){margin: 0;}',
            $theme_json->get_stylesheet([ 'styles' ])
        );
    }

    /**
     * @ticket 56467
     * @ticket 58550
     * @ticket 60936
     * @ticket 61165
     */
    public function test_get_stylesheet_generates_fallback_gap_layout_styles()
    {
        $theme_json = new WP_Theme_JSON(
            [
                'version'  => WP_Theme_JSON::LATEST_SCHEMA,
                'settings' => [
                    'layout'  => [
                        'contentSize' => '640px',
                        'wideSize'    => '1200px',
                    ],
                    'spacing' => [
                        'blockGap' => null,
                    ],
                ],
                'styles'   => [
                    'spacing' => [
                        'blockGap' => '1em',
                    ],
                ],
            ],
            'default'
        );
        $stylesheet = $theme_json->get_stylesheet([ 'styles' ]);

        // Results also include root site blocks styles.
        $this->assertSame(
            ':root { --wp--style--global--content-size: 640px;--wp--style--global--wide-size: 1200px; }:where(body) { margin: 0; }.wp-site-blocks > .alignleft { float: left; margin-right: 2em; }.wp-site-blocks > .alignright { float: right; margin-left: 2em; }.wp-site-blocks > .aligncenter { justify-content: center; margin-left: auto; margin-right: auto; }:where(.is-layout-flex){gap: 0.5em;}:where(.is-layout-grid){gap: 0.5em;}.is-layout-flow > .alignleft{float: left;margin-inline-start: 0;margin-inline-end: 2em;}.is-layout-flow > .alignright{float: right;margin-inline-start: 2em;margin-inline-end: 0;}.is-layout-flow > .aligncenter{margin-left: auto !important;margin-right: auto !important;}.is-layout-constrained > .alignleft{float: left;margin-inline-start: 0;margin-inline-end: 2em;}.is-layout-constrained > .alignright{float: right;margin-inline-start: 2em;margin-inline-end: 0;}.is-layout-constrained > .aligncenter{margin-left: auto !important;margin-right: auto !important;}.is-layout-constrained > :where(:not(.alignleft):not(.alignright):not(.alignfull)){max-width: var(--wp--style--global--content-size);margin-left: auto !important;margin-right: auto !important;}.is-layout-constrained > .alignwide{max-width: var(--wp--style--global--wide-size);}body .is-layout-flex{display: flex;}.is-layout-flex{flex-wrap: wrap;align-items: center;}.is-layout-flex > :is(*, div){margin: 0;}body .is-layout-grid{display: grid;}.is-layout-grid > :is(*, div){margin: 0;}',
            $stylesheet
        );
    }

    /**
     * @ticket 56467
     * @ticket 58550
     * @ticket 60936
     * @ticket 60981
     * @ticket 61165
     */
    public function test_get_stylesheet_generates_base_fallback_gap_layout_styles()
    {
        $theme_json = new WP_Theme_JSON(
            [
                'version'  => WP_Theme_JSON::LATEST_SCHEMA,
                'settings' => [
                    'spacing' => [
                        'blockGap' => null,
                    ],
                ],
                'styles'   => [
                    'spacing' => [
                        'blockGap' => '1em',
                    ],
                ],
            ],
            'default'
        );
        // Set base_layout_styles to true to generate only base layout styles without alignment rules.
        $stylesheet = $theme_json->get_stylesheet([ 'styles' ], null, [ 'base_layout_styles' => true ]);

        // Verify that layout styles are still generated, but without .wp-site-blocks alignment rules and flow/constrained base styles.
        $this->assertSame(
            ':where(body) { margin: 0; }:where(.is-layout-flex){gap: 0.5em;}:where(.is-layout-grid){gap: 0.5em;}body .is-layout-flex{display: flex;}.is-layout-flex{flex-wrap: wrap;align-items: center;}.is-layout-flex > :is(*, div){margin: 0;}body .is-layout-grid{display: grid;}.is-layout-grid > :is(*, div){margin: 0;}',
            $stylesheet
        );
    }

    /**
     * @ticket 56467
     * @ticket 58550
     */
    public function test_get_stylesheet_skips_layout_styles()
    {
        add_theme_support('disable-layout-styles');
        $theme_json = new WP_Theme_JSON(
            [
                'version'  => WP_Theme_JSON::LATEST_SCHEMA,
                'settings' => [
                    'spacing' => [
                        'blockGap' => null,
                    ],
                ],
            ],
            'default'
        );
        $stylesheet = $theme_json->get_stylesheet([ 'styles' ], null);
        remove_theme_support('disable-layout-styles');

        // All Layout styles should be skipped when disable-layout-styles theme support is added.
        $this->assertSame(
            '',
            $stylesheet
        );
    }

    /**
     * @ticket 56467
     * @ticket 58550
     * @ticket 60936
     * @ticket 61165
     * @ticket 61829
     */
    public function test_get_stylesheet_generates_valid_block_gap_values_and_skips_null_or_false_values()
    {
        $theme_json = new WP_Theme_JSON(
            [
                'version'  => WP_Theme_JSON::LATEST_SCHEMA,
                'settings' => [
                    'layout'  => [
                        'contentSize' => '640px',
                        'wideSize'    => '1200px',
                    ],
                    'spacing' => [
                        'blockGap' => true,
                    ],
                ],
                'styles'   => [
                    'spacing' => [
                        'blockGap' => '1rem',
                    ],
                    'blocks'  => [
                        'core/post-content' => [
                            'color' => [
                                'text' => 'gray', // This value should not render block layout styles.
                            ],
                        ],
                        'core/social-links' => [
                            'spacing' => [
                                'blockGap' => '0', // This value should render block layout gap as zero.
                            ],
                        ],
                        'core/buttons'      => [
                            'spacing' => [
                                'blockGap' => 0, // This value should render block layout gap as zero.
                            ],
                        ],
                        'core/columns'      => [
                            'spacing' => [
                                'blockGap' => false, // This value should be ignored. The block will use the global layout value.
                            ],
                        ],
                    ],
                ],
            ],
            'default'
        );

        $this->assertSame(
            ':root { --wp--style--global--content-size: 640px;--wp--style--global--wide-size: 1200px; }:where(body) { margin: 0; }.wp-site-blocks > .alignleft { float: left; margin-right: 2em; }.wp-site-blocks > .alignright { float: right; margin-left: 2em; }.wp-site-blocks > .aligncenter { justify-content: center; margin-left: auto; margin-right: auto; }:where(.wp-site-blocks) > * { margin-block-start: 1rem; margin-block-end: 0; }:where(.wp-site-blocks) > :first-child { margin-block-start: 0; }:where(.wp-site-blocks) > :last-child { margin-block-end: 0; }:root { --wp--style--block-gap: 1rem; }:root :where(.is-layout-flow) > :first-child{margin-block-start: 0;}:root :where(.is-layout-flow) > :last-child{margin-block-end: 0;}:root :where(.is-layout-flow) > *{margin-block-start: 1rem;margin-block-end: 0;}:root :where(.is-layout-constrained) > :first-child{margin-block-start: 0;}:root :where(.is-layout-constrained) > :last-child{margin-block-end: 0;}:root :where(.is-layout-constrained) > *{margin-block-start: 1rem;margin-block-end: 0;}:root :where(.is-layout-flex){gap: 1rem;}:root :where(.is-layout-grid){gap: 1rem;}.is-layout-flow > .alignleft{float: left;margin-inline-start: 0;margin-inline-end: 2em;}.is-layout-flow > .alignright{float: right;margin-inline-start: 2em;margin-inline-end: 0;}.is-layout-flow > .aligncenter{margin-left: auto !important;margin-right: auto !important;}.is-layout-constrained > .alignleft{float: left;margin-inline-start: 0;margin-inline-end: 2em;}.is-layout-constrained > .alignright{float: right;margin-inline-start: 2em;margin-inline-end: 0;}.is-layout-constrained > .aligncenter{margin-left: auto !important;margin-right: auto !important;}.is-layout-constrained > :where(:not(.alignleft):not(.alignright):not(.alignfull)){max-width: var(--wp--style--global--content-size);margin-left: auto !important;margin-right: auto !important;}.is-layout-constrained > .alignwide{max-width: var(--wp--style--global--wide-size);}body .is-layout-flex{display: flex;}.is-layout-flex{flex-wrap: wrap;align-items: center;}.is-layout-flex > :is(*, div){margin: 0;}body .is-layout-grid{display: grid;}.is-layout-grid > :is(*, div){margin: 0;}:root :where(.wp-block-post-content){color: gray;}:root :where(.wp-block-social-links-is-layout-flow) > :first-child{margin-block-start: 0;}:root :where(.wp-block-social-links-is-layout-flow) > :last-child{margin-block-end: 0;}:root :where(.wp-block-social-links-is-layout-flow) > *{margin-block-start: 0;margin-block-end: 0;}:root :where(.wp-block-social-links-is-layout-constrained) > :first-child{margin-block-start: 0;}:root :where(.wp-block-social-links-is-layout-constrained) > :last-child{margin-block-end: 0;}:root :where(.wp-block-social-links-is-layout-constrained) > *{margin-block-start: 0;margin-block-end: 0;}:root :where(.wp-block-social-links-is-layout-flex){gap: 0;}:root :where(.wp-block-social-links-is-layout-grid){gap: 0;}:root :where(.wp-block-buttons-is-layout-flow) > :first-child{margin-block-start: 0;}:root :where(.wp-block-buttons-is-layout-flow) > :last-child{margin-block-end: 0;}:root :where(.wp-block-buttons-is-layout-flow) > *{margin-block-start: 0;margin-block-end: 0;}:root :where(.wp-block-buttons-is-layout-constrained) > :first-child{margin-block-start: 0;}:root :where(.wp-block-buttons-is-layout-constrained) > :last-child{margin-block-end: 0;}:root :where(.wp-block-buttons-is-layout-constrained) > *{margin-block-start: 0;margin-block-end: 0;}:root :where(.wp-block-buttons-is-layout-flex){gap: 0;}:root :where(.wp-block-buttons-is-layout-grid){gap: 0;}',
            $theme_json->get_stylesheet()
        );
    }

    /**
     * @ticket 57354
     * @ticket 58550
     * @ticket 60936
     * @ticket 61165
     */
    public function test_get_stylesheet_returns_outline_styles()
    {
        $theme_json = new WP_Theme_JSON(
            [
                'version' => WP_Theme_JSON::LATEST_SCHEMA,
                'styles'  => [
                    'elements' => [
                        'button' => [
                            'outline' => [
                                'offset' => '3px',
                                'width'  => '3px',
                                'style'  => 'dashed',
                                'color'  => 'red',
                            ],
                            ':hover'  => [
                                'outline' => [
                                    'offset' => '3px',
                                    'width'  => '3px',
                                    'style'  => 'solid',
                                    'color'  => 'blue',
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );

        $expected = ':root :where(.wp-element-button, .wp-block-button__link){outline-color: red;outline-offset: 3px;outline-style: dashed;outline-width: 3px;}:root :where(.wp-element-button:hover, .wp-block-button__link:hover){outline-color: blue;outline-offset: 3px;outline-style: solid;outline-width: 3px;}';

        $this->assertSame($expected, $theme_json->get_stylesheet([ 'styles' ], null, [ 'skip_root_layout_styles' => true ]));
    }

    /**
     * Tests that a custom root selector is correctly applied when generating a stylesheet.
     *
     * @ticket 60343
     * @ticket 61165
     */
    public function test_get_stylesheet_custom_root_selector()
    {
        $theme_json = new WP_Theme_JSON(
            [
                'version' => WP_Theme_JSON::LATEST_SCHEMA,
                'styles'  => [
                    'color' => [
                        'text' => 'teal',
                    ],
                ],
            ],
            'default'
        );

        // Custom root selector is unrelated to root layout styles so they don't need to be output for this test.
        $options = [
            'root_selector'           => '.custom',
            'skip_root_layout_styles' => true,
        ];
        $actual  = $theme_json->get_stylesheet([ 'styles' ], null, $options);

        $this->assertSame(
            ':root :where(.custom){color: teal;}',
            $actual
        );
    }

    /**
     * Tests that settings passed to WP_Theme_JSON override merged theme data.
     *
     * @ticket 61118
     * @ticket 61165
     * @ticket 61630
     * @ticket 61704
     */
    public function test_get_stylesheet_generates_fluid_typography_values()
    {
        register_block_type(
            'test/clamp-me',
            [
                'api_version' => 3,
            ]
        );
        $theme_json = new WP_Theme_JSON(
            [
                'version'  => WP_Theme_JSON::LATEST_SCHEMA,
                'settings' => [
                    'typography' => [
                        'fluid'     => true,
                        'fontSizes' => [
                            [
                                'size' => '16px',
                                'slug' => 'pickles',
                                'name' => 'Pickles',
                            ],
                            [
                                'size' => '22px',
                                'slug' => 'toast',
                                'name' => 'Toast',
                            ],
                        ],
                    ],
                ],
                'styles'   => [
                    'typography' => [
                        'fontSize' => '1em',
                    ],
                    'elements'   => [
                        'h1' => [
                            'typography' => [
                                'fontSize' => '100px',
                            ],
                        ],
                    ],
                    'blocks'     => [
                        'test/clamp-me' => [
                            'typography' => [
                                'fontSize' => '48px',
                            ],
                        ],
                    ],
                ],
            ],
            'default'
        );

        unregister_block_type('test/clamp-me');

        $this->assertSame(
            ':root{--wp--preset--font-size--pickles: clamp(14px, 0.875rem + ((1vw - 3.2px) * 0.156), 16px);--wp--preset--font-size--toast: clamp(14.642px, 0.915rem + ((1vw - 3.2px) * 0.575), 22px);}body{font-size: clamp(0.875em, 0.875rem + ((1vw - 0.2em) * 0.156), 1em);}h1{font-size: clamp(50.171px, 3.136rem + ((1vw - 3.2px) * 3.893), 100px);}:root :where(.wp-block-test-clamp-me){font-size: clamp(27.894px, 1.743rem + ((1vw - 3.2px) * 1.571), 48px);}.has-pickles-font-size{font-size: var(--wp--preset--font-size--pickles) !important;}.has-toast-font-size{font-size: var(--wp--preset--font-size--toast) !important;}',
            $theme_json->get_stylesheet([ 'styles', 'variables', 'presets' ], null, [ 'skip_root_layout_styles' => true ])
        );
    }

    public function test_allow_indirect_properties()
    {
        $actual = WP_Theme_JSON::remove_insecure_properties(
            [
                'version'  => WP_Theme_JSON::LATEST_SCHEMA,
                'styles'   => [
                    'blocks'  => [
                        'core/social-links' => [
                            'spacing' => [
                                'blockGap' => [
                                    'top'  => '1em',
                                    'left' => '2em',
                                ],
                            ],
                        ],
                    ],
                    'spacing' => [
                        'blockGap' => '3em',
                    ],
                ],
                'settings' => [
                    'layout' => [
                        'contentSize' => '800px',
                        'wideSize'    => '1000px',
                    ],
                ],
            ]
        );

        $expected = [
            'version'  => WP_Theme_JSON::LATEST_SCHEMA,
            'styles'   => [
                'blocks'  => [
                    'core/social-links' => [
                        'spacing' => [
                            'blockGap' => [
                                'top'  => '1em',
                                'left' => '2em',
                            ],
                        ],
                    ],
                ],
                'spacing' => [
                    'blockGap' => '3em',
                ],
            ],
            'settings' => [
                'layout' => [
                    'contentSize' => '800px',
                    'wideSize'    => '1000px',
                ],
            ],
        ];

        $this->assertEqualSetsWithIndex($expected, $actual);
    }

    /**
     * @ticket 52991
     * @ticket 54336
     */
    public function test_merge_incoming_data()
    {
        $theme_json = new WP_Theme_JSON(
            [
                'version'  => WP_Theme_JSON::LATEST_SCHEMA,
                'settings' => [
                    'color'  => [
                        'custom'  => false,
                        'palette' => [
                            [
                                'slug'  => 'red',
                                'color' => 'red',
                            ],
                            [
                                'slug'  => 'green',
                                'color' => 'green',
                            ],
                        ],
                    ],
                    'blocks' => [
                        'core/paragraph' => [
                            'color' => [
                                'custom' => false,
                            ],
                        ],
                    ],
                ],
                'styles'   => [
                    'typography' => [
                        'fontSize' => '12',
                    ],
                ],
            ]
        );

        $add_new_block = [
            'version'  => WP_Theme_JSON::LATEST_SCHEMA,
            'settings' => [
                'blocks' => [
                    'core/list' => [
                        'color' => [
                            'custom' => false,
                        ],
                    ],
                ],
            ],
            'styles'   => [
                'blocks' => [
                    'core/list' => [
                        'typography' => [
                            'fontSize' => '12',
                        ],
                        'color'      => [
                            'background' => 'brown',
                        ],
                    ],
                ],
            ],
        ];

        $add_key_in_settings = [
            'version'  => WP_Theme_JSON::LATEST_SCHEMA,
            'settings' => [
                'color' => [
                    'customGradient' => true,
                ],
            ],
        ];

        $update_key_in_settings = [
            'version'  => WP_Theme_JSON::LATEST_SCHEMA,
            'settings' => [
                'color' => [
                    'custom' => true,
                ],
            ],
        ];

        $add_styles = [
            'version' => WP_Theme_JSON::LATEST_SCHEMA,
            'styles'  => [
                'blocks' => [
                    'core/group' => [
                        'spacing' => [
                            'padding' => [
                                'top' => '12px',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $add_key_in_styles = [
            'version' => WP_Theme_JSON::LATEST_SCHEMA,
            'styles'  => [
                'blocks' => [
                    'core/group' => [
                        'spacing' => [
                            'padding' => [
                                'bottom' => '12px',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $add_invalid_context = [
            'version' => WP_Theme_JSON::LATEST_SCHEMA,
            'styles'  => [
                'blocks' => [
                    'core/para' => [
                        'typography' => [
                            'lineHeight' => '12',
                        ],
                    ],
                ],
            ],
        ];

        $update_presets = [
            'version'  => WP_Theme_JSON::LATEST_SCHEMA,
            'settings' => [
                'color'      => [
                    'palette'   => [
                        [
                            'slug'  => 'blue',
                            'color' => 'blue',
                        ],
                    ],
                    'gradients' => [
                        [
                            'slug'     => 'gradient',
                            'gradient' => 'gradient',
                        ],
                    ],
                ],
                'typography' => [
                    'fontSizes'    => [
                        [
                            'slug' => 'fontSize',
                            'size' => 'fontSize',
                        ],
                    ],
                    'fontFamilies' => [
                        [
                            'slug'       => 'fontFamily',
                            'fontFamily' => 'fontFamily',
                        ],
                    ],
                ],
            ],
        ];

        $expected = [
            'version'  => WP_Theme_JSON::LATEST_SCHEMA,
            'settings' => [
                'color'      => [
                    'custom'         => true,
                    'customGradient' => true,
                    'palette'        => [
                        'theme' => [
                            [
                                'slug'  => 'blue',
                                'color' => 'blue',
                            ],
                        ],
                    ],
                    'gradients'      => [
                        'theme' => [
                            [
                                'slug'     => 'gradient',
                                'gradient' => 'gradient',
                            ],
                        ],
                    ],
                ],
                'typography' => [
                    'fontSizes'    => [
                        'theme' => [
                            [
                                'slug' => 'fontSize',
                                'size' => 'fontSize',
                            ],
                        ],
                    ],
                    'fontFamilies' => [
                        'theme' => [
                            [
                                'slug'       => 'fontFamily',
                                'fontFamily' => 'fontFamily',
                            ],
                        ],
                    ],
                ],
                'blocks'     => [
                    'core/paragraph' => [
                        'color' => [
                            'custom' => false,
                        ],
                    ],
                    'core/list'      => [
                        'color' => [
                            'custom' => false,
                        ],
                    ],
                ],
            ],
            'styles'   => [
                'typography' => [
                    'fontSize' => '12',
                ],
                'blocks'     => [
                    'core/group' => [
                        'spacing' => [
                            'padding' => [
                                'top'    => '12px',
                                'bottom' => '12px',
                            ],
                        ],
                    ],
                    'core/list'  => [
                        'typography' => [
                            'fontSize' => '12',
                        ],
                        'color'      => [
                            'background' => 'brown',
                        ],
                    ],
                ],
            ],
        ];

        $theme_json->merge(new WP_Theme_JSON($add_new_block));
        $theme_json->merge(new WP_Theme_JSON($add_key_in_settings));
        $theme_json->merge(new WP_Theme_JSON($update_key_in_settings));
        $theme_json->merge(new WP_Theme_JSON($add_styles));
        $theme_json->merge(new WP_Theme_JSON($add_key_in_styles));
        $theme_json->merge(new WP_Theme_JSON($add_invalid_context));
        $theme_json->merge(new WP_Theme_JSON($update_presets));
        $actual = $theme_json->get_raw_data();

        $this->assertEqualSetsWithIndex($expected, $actual);
    }

    /**
     * @ticket 53175
     * @ticket 54336
     */
    public function test_merge_incoming_data_empty_presets()
    {
        $theme_json = new WP_Theme_JSON(
            [
                'version'  => WP_Theme_JSON::LATEST_SCHEMA,
                'settings' => [
                    'color'      => [
                        'duotone'   => [
                            [
                                'slug'   => 'value',
                                'colors' => [ 'red', 'green' ],
                            ],
                        ],
                        'gradients' => [
                            [
                                'slug'     => 'gradient',
                                'gradient' => 'gradient',
                            ],
                        ],
                        'palette'   => [
                            [
                                'slug'  => 'red',
                                'color' => 'red',
                            ],
                        ],
                    ],
                    'spacing'    => [
                        'units' => [ 'px', 'em' ],
                    ],
                    'typography' => [
                        'fontSizes' => [
                            [
                                'slug'  => 'size',
                                'value' => 'size',
                            ],
                        ],
                    ],
                ],
            ]
        );

        $theme_json->merge(
            new WP_Theme_JSON(
                [
                    'version'  => WP_Theme_JSON::LATEST_SCHEMA,
                    'settings' => [
                        'color'      => [
                            'duotone'   => [],
                            'gradients' => [],
                            'palette'   => [],
                        ],
                        'spacing'    => [
                            'units' => [],
                        ],
                        'typography' => [
                            'fontSizes' => [],
                        ],
                    ],
                ]
            )
        );

        $actual   = $theme_json->get_raw_data();
        $expected = [
            'version'  => WP_Theme_JSON::LATEST_SCHEMA,
            'settings' => [
                'color'      => [
                    'duotone'   => [
                        'theme' => [],
                    ],
                    'gradients' => [
                        'theme' => [],
                    ],
                    'palette'   => [
                        'theme' => [],
                    ],
                ],
                'spacing'    => [
                    'units' => [],
                ],
                'typography' => [
                    'fontSizes' => [
                        'theme' => [],
                    ],
                ],
            ],
        ];

        $this->assertEqualSetsWithIndex($expected, $actual);
    }

    /**
     * @ticket 53175
     * @ticket 54336
     */
    public function test_merge_incoming_data_null_presets()
    {
        $theme_json = new WP_Theme_JSON(
            [
                'version'  => WP_Theme_JSON::LATEST_SCHEMA,
                'settings' => [
                    'color'      => [
                        'duotone'   => [
                            [
                                'slug'   => 'value',
                                'colors' => [ 'red', 'green' ],
                            ],
                        ],
                        'gradients' => [
                            [
                                'slug'     => 'gradient',
                                'gradient' => 'gradient',
                            ],
                        ],
                        'palette'   => [
                            [
                                'slug'  => 'red',
                                'color' => 'red',
                            ],
                        ],
                    ],
                    'spacing'    => [
                        'units' => [ 'px', 'em' ],
                    ],
                    'typography' => [
                        'fontSizes' => [
                            [
                                'slug'  => 'size',
                                'value' => 'size',
                            ],
                        ],
                    ],
                ],
            ]
        );

        $theme_json->merge(
            new WP_Theme_JSON(
                [
                    'version'  => WP_Theme_JSON::LATEST_SCHEMA,
                    'settings' => [
                        'color'      => [
                            'custom' => false,
                        ],
                        'spacing'    => [
                            'margin' => false,
                        ],
                        'typography' => [
                            'lineHeight' => false,
                        ],
                    ],
                ]
            )
        );

        $actual   = $theme_json->get_raw_data();
        $expected = [
            'version'  => WP_Theme_JSON::LATEST_SCHEMA,
            'settings' => [
                'color'      => [
                    'custom'    => false,
                    'duotone'   => [
                        'theme' => [
                            [
                                'slug'   => 'value',
                                'colors' => [ 'red', 'green' ],
                            ],
                        ],
                    ],
                    'gradients' => [
                        'theme' => [
                            [
                                'slug'     => 'gradient',
                                'gradient' => 'gradient',
                            ],
                        ],
                    ],
                    'palette'   => [
                        'theme' => [
                            [
                                'slug'  => 'red',
                                'color' => 'red',
                            ],
                        ],
                    ],
                ],
                'spacing'    => [
                    'margin' => false,
                    'units'  => [ 'px', 'em' ],
                ],
                'typography' => [
                    'lineHeight' => false,
                    'fontSizes'  => [
                        'theme' => [
                            [
                                'slug'  => 'size',
                                'value' => 'size',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->assertEqualSetsWithIndex($expected, $actual);
    }

    public function test_merge_incoming_data_color_presets_with_same_slugs_as_default_are_removed()
    {
        $defaults = new WP_Theme_JSON(
            [
                'version'  => WP_Theme_JSON::LATEST_SCHEMA,
                'settings' => [
                    'color'  => [
                        'defaultPalette' => true,
                        'palette'        => [
                            [
                                'slug'  => 'red',
                                'color' => 'red',
                                'name'  => 'Red',
                            ],
                            [
                                'slug'  => 'green',
                                'color' => 'green',
                                'name'  => 'Green',
                            ],
                        ],
                    ],
                    'blocks' => [
                        'core/paragraph' => [
                            'color' => [
                                'palette' => [
                                    [
                                        'slug'  => 'blue',
                                        'color' => 'blue',
                                        'name'  => 'Blue',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'default'
        );
        $theme    = new WP_Theme_JSON(
            [
                'version'  => WP_Theme_JSON::LATEST_SCHEMA,
                'settings' => [
                    'color'  => [
                        'palette' => [
                            [
                                'slug'  => 'pink',
                                'color' => 'pink',
                                'name'  => 'Pink',
                            ],
                            [
                                'slug'  => 'green',
                                'color' => 'green',
                                'name'  => 'Greenish',
                            ],
                        ],
                    ],
                    'blocks' => [
                        'core/paragraph' => [
                            'color' => [
                                'palette' => [
                                    [
                                        'slug'  => 'blue',
                                        'color' => 'blue',
                                        'name'  => 'Bluish',
                                    ],
                                    [
                                        'slug'  => 'yellow',
                                        'color' => 'yellow',
                                        'name'  => 'Yellow',
                                    ],
                                    [
                                        'slug'  => 'green',
                                        'color' => 'green',
                                        'name'  => 'Block Green',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );

        $expected = [
            'version'  => WP_Theme_JSON::LATEST_SCHEMA,
            'settings' => [
                'color'  => [
                    'palette'        => [
                        'default' => [
                            [
                                'slug'  => 'red',
                                'color' => 'red',
                                'name'  => 'Red',
                            ],
                            [
                                'slug'  => 'green',
                                'color' => 'green',
                                'name'  => 'Green',
                            ],
                        ],
                        'theme'   => [
                            [
                                'slug'  => 'pink',
                                'color' => 'pink',
                                'name'  => 'Pink',
                            ],
                        ],
                    ],
                    'defaultPalette' => true,
                ],
                'blocks' => [
                    'core/paragraph' => [
                        'color' => [
                            'palette' => [
                                'default' => [
                                    [
                                        'slug'  => 'blue',
                                        'color' => 'blue',
                                        'name'  => 'Blue',
                                    ],
                                ],
                                'theme'   => [
                                    [
                                        'slug'  => 'yellow',
                                        'color' => 'yellow',
                                        'name'  => 'Yellow',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $defaults->merge($theme);
        $actual = $defaults->get_raw_data();

        $this->assertEqualSetsWithIndex($expected, $actual);
    }

    public function test_merge_incoming_data_color_presets_with_same_slugs_as_default_are_not_removed_if_defaults_are_disabled()
    {
        $defaults = new WP_Theme_JSON(
            [
                'version'  => WP_Theme_JSON::LATEST_SCHEMA,
                'settings' => [
                    'color'  => [
                        'defaultPalette' => true, // Emulate the defaults from core theme.json.
                        'palette'        => [
                            [
                                'slug'  => 'red',
                                'color' => 'red',
                                'name'  => 'Red',
                            ],
                            [
                                'slug'  => 'green',
                                'color' => 'green',
                                'name'  => 'Green',
                            ],
                        ],
                    ],
                    'blocks' => [
                        'core/paragraph' => [
                            'color' => [
                                'palette' => [
                                    [
                                        'slug'  => 'blue',
                                        'color' => 'blue',
                                        'name'  => 'Blue',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'default'
        );
        $theme    = new WP_Theme_JSON(
            [
                'version'  => WP_Theme_JSON::LATEST_SCHEMA,
                'settings' => [
                    'color'  => [
                        'defaultPalette' => false,
                        'palette'        => [
                            [
                                'slug'  => 'pink',
                                'color' => 'pink',
                                'name'  => 'Pink',
                            ],
                            [
                                'slug'  => 'green',
                                'color' => 'green',
                                'name'  => 'Greenish',
                            ],
                        ],
                    ],
                    'blocks' => [
                        'core/paragraph' => [
                            'color' => [
                                'palette' => [
                                    [
                                        'slug'  => 'blue',
                                        'color' => 'blue',
                                        'name'  => 'Bluish',
                                    ],
                                    [
                                        'slug'  => 'yellow',
                                        'color' => 'yellow',
                                        'name'  => 'Yellow',
                                    ],
                                    [
                                        'slug'  => 'green',
                                        'color' => 'green',
                                        'name'  => 'Block Green',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );

        $expected = [
            'version'  => WP_Theme_JSON::LATEST_SCHEMA,
            'settings' => [
                'color'  => [
                    'defaultPalette' => false,
                    'palette'        => [
                        'default' => [
                            [
                                'slug'  => 'red',
                                'color' => 'red',
                                'name'  => 'Red',
                            ],
                            [
                                'slug'  => 'green',
                                'color' => 'green',
                                'name'  => 'Green',
                            ],
                        ],
                        'theme'   => [
                            [
                                'slug'  => 'pink',
                                'color' => 'pink',
                                'name'  => 'Pink',
                            ],
                            [
                                'slug'  => 'green',
                                'color' => 'green',
                                'name'  => 'Greenish',
                            ],
                        ],
                    ],
                ],
                'blocks' => [
                    'core/paragraph' => [
                        'color' => [
                            'palette' => [
                                'default' => [
                                    [
                                        'slug'  => 'blue',
                                        'color' => 'blue',
                                        'name'  => 'Blue',
                                    ],
                                ],
                                'theme'   => [
                                    [
                                        'slug'  => 'blue',
                                        'color' => 'blue',
                                        'name'  => 'Bluish',
                                    ],
                                    [
                                        'slug'  => 'yellow',
                                        'color' => 'yellow',
                                        'name'  => 'Yellow',
                                    ],
                                    [
                                        'slug'  => 'green',
                                        'color' => 'green',
                                        'name'  => 'Block Green',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $defaults->merge($theme);
        $actual = $defaults->get_raw_data();

        $this->assertEqualSetsWithIndex($expected, $actual);
    }

    /**
     * @ticket 54640
     */
    public function test_merge_incoming_data_presets_use_default_names()
    {
        $defaults   = new WP_Theme_JSON(
            [
                'version'  => WP_Theme_JSON::LATEST_SCHEMA,
                'settings' => [
                    'typography' => [
                        'fontSizes' => [
                            [
                                'name' => 'Small',
                                'slug' => 'small',
                                'size' => '12px',
                            ],
                            [
                                'name' => 'Large',
                                'slug' => 'large',
                                'size' => '20px',
                            ],
                        ],
                    ],
                ],
            ],
            'default'
        );
        $theme_json = new WP_Theme_JSON(
            [
                'version'  => WP_Theme_JSON::LATEST_SCHEMA,
                'settings' => [
                    'typography' => [
                        'fontSizes' => [
                            [
                                'slug' => 'small',
                                'size' => '1.1rem',
                            ],
                            [
                                'slug' => 'large',
                                'size' => '1.75rem',
                            ],
                            [
                                'name' => 'Huge',
                                'slug' => 'huge',
                                'size' => '3rem',
                            ],
                        ],
                    ],
                ],
            ],
            'theme'
        );
        $expected   = [
            'version'  => WP_Theme_JSON::LATEST_SCHEMA,
            'settings' => [
                'typography' => [
                    'fontSizes' => [
                        'default' => [
                            [
                                'name' => 'Small',
                                'slug' => 'small',
                                'size' => '12px',
                            ],
                            [
                                'name' => 'Large',
                                'slug' => 'large',
                                'size' => '20px',
                            ],
                        ],
                        'theme'   => [
                            [
                                'slug' => 'small',
                                'size' => '1.1rem',
                                'name' => 'Small',
                            ],
                            [
                                'slug' => 'large',
                                'size' => '1.75rem',
                                'name' => 'Large',
                            ],
                            [
                                'name' => 'Huge',
                                'slug' => 'huge',
                                'size' => '3rem',
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $defaults->merge($theme_json);
        $actual = $defaults->get_raw_data();
        $this->assertSameSetsWithIndex($expected, $actual);
    }

    /**
     * @ticket 61858
     */
    public function test_merge_incoming_background_styles()
    {
        $theme_json = new WP_Theme_JSON(
            [
                'version' => WP_Theme_JSON::LATEST_SCHEMA,
                'styles'  => [
                    'background' => [
                        'backgroundImage' => [
                            'id'     => 'uploaded',
                            'source' => 'file',
                            'url'    => 'http://example.org/quote.png',
                        ],
                        'backgroundSize'  => 'cover',
                    ],
                    'blocks'     => [
                        'core/group' => [
                            'background' => [
                                'backgroundImage'      => [
                                    'ref' => 'styles.blocks.core/verse.background.backgroundImage',
                                ],
                                'backgroundAttachment' => 'fixed',
                            ],
                        ],
                        'core/quote' => [
                            'background' => [
                                'backgroundImage'      => [
                                    'url' => 'http://example.org/quote.png',
                                ],
                                'backgroundAttachment' => [
                                    'ref' => 'styles.blocks.core/group.background.backgroundAttachment',
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );

        $update_background_image_styles = [
            'version' => WP_Theme_JSON::LATEST_SCHEMA,
            'styles'  => [
                'background' => [
                    'backgroundImage' => [
                        'url' => 'http://example.org/site.png',
                    ],
                    'backgroundSize'  => 'contain',
                ],
                'blocks'     => [
                    'core/group' => [
                        'background' => [
                            'backgroundImage' => [
                                'url' => 'http://example.org/group.png',
                            ],
                        ],
                    ],
                    'core/quote' => [
                        'background' => [
                            'backgroundAttachment' => 'fixed',
                        ],
                    ],
                    'core/verse' => [
                        'background' => [
                            'backgroundImage' => [
                                'ref' => 'styles.blocks.core/group.background.backgroundImage',
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $expected                       = [
            'version' => WP_Theme_JSON::LATEST_SCHEMA,
            'styles'  => [
                'background' => [
                    'backgroundImage' => [
                        'url' => 'http://example.org/site.png',
                    ],
                    'backgroundSize'  => 'contain',
                ],
                'blocks'     => [
                    'core/group' => [
                        'background' => [
                            'backgroundImage'      => [
                                'url' => 'http://example.org/group.png',
                            ],
                            'backgroundAttachment' => 'fixed',
                        ],
                    ],
                    'core/quote' => [
                        'background' => [
                            'backgroundImage'      => [
                                'url' => 'http://example.org/quote.png',
                            ],
                            'backgroundAttachment' => 'fixed',
                        ],
                    ],
                    'core/verse' => [
                        'background' => [
                            'backgroundImage' => [
                                'ref' => 'styles.blocks.core/group.background.backgroundImage',
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $theme_json->merge(new WP_Theme_JSON($update_background_image_styles));
        $actual = $theme_json->get_raw_data();

        $this->assertEqualSetsWithIndex($expected, $actual);
    }

    /**
     * This test covers `get_block_nodes` with the `$include_node_paths_only` option.
     * When `true`, `$include_node_paths_only` should return only the paths of the block nodes.
     *
     * @ticket 61858
     */
    public function test_return_block_node_paths()
    {
        $theme_json = new ReflectionClass('WP_Theme_JSON');

        $func = $theme_json->getMethod('get_block_nodes');
        if (PHP_VERSION_ID < 80100) {
            $func->setAccessible(true);
        }

        $theme_json = [
            'version' => WP_Theme_JSON::LATEST_SCHEMA,
            'styles'  => [
                'typography' => [
                    'fontSize' => '16px',
                ],
                'blocks'     => [
                    'core/button' => [
                        'color' => [
                            'background' => 'red',
                        ],
                    ],
                    'core/group'  => [
                        'elements' => [
                            'link' => [
                                'color' => [
                                    'background' => 'blue',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $block_nodes = $func->invoke(null, $theme_json, [], [ 'include_node_paths_only' => true ]);

        $expected = [
            [
                'path' => [ 'styles', 'blocks', 'core/button' ],
            ],
            [
                'path' => [ 'styles', 'blocks', 'core/group' ],
            ],
            [
                'path' => [ 'styles', 'blocks', 'core/group', 'elements', 'link' ],
            ],
        ];

        $this->assertEquals($expected, $block_nodes);
    }

    /**
     * This test covers `get_block_nodes` with the `$include_node_paths_only`
     * and `include_block_style_variations` options.
     *
     * @ticket 62399
     */
    public function test_return_block_node_paths_with_variations()
    {
        $theme_json = new ReflectionClass('WP_Theme_JSON');

        $func = $theme_json->getMethod('get_block_nodes');
        if (PHP_VERSION_ID < 80100) {
            $func->setAccessible(true);
        }

        $theme_json = [
            'version' => WP_Theme_JSON::LATEST_SCHEMA,
            'styles'  => [
                'typography' => [
                    'fontSize' => '16px',
                ],
                'blocks'     => [
                    'core/button' => [
                        'color'      => [
                            'background' => 'red',
                        ],
                        'variations' => [
                            'cheese' => [
                                'color' => [
                                    'background' => 'cheese',
                                ],
                            ],
                        ],
                    ],
                    'core/group'  => [
                        'color'      => [
                            'background' => 'blue',
                        ],
                        'variations' => [
                            'apricot' => [
                                'color' => [
                                    'background' => 'apricot',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $block_nodes = $func->invoke(
            null,
            $theme_json,
            [],
            [
                'include_node_paths_only'        => true,
                'include_block_style_variations' => true,
            ]
        );

        $expected = [
            [
                'path'       => [ 'styles', 'blocks', 'core/button' ],
                'variations' => [
                    [
                        'path' => [ 'styles', 'blocks', 'core/button', 'variations', 'cheese' ],
                    ],
                ],
            ],
            [
                'path'       => [ 'styles', 'blocks', 'core/group' ],
                'variations' => [
                    [
                        'path' => [ 'styles', 'blocks', 'core/group', 'variations', 'apricot' ],
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, $block_nodes);
    }

    /**
     * @ticket 54336
     */
    public function test_remove_insecure_properties_removes_unsafe_styles()
    {
        $actual = WP_Theme_JSON::remove_insecure_properties(
            [
                'version' => WP_Theme_JSON::LATEST_SCHEMA,
                'styles'  => [
                    'color'    => [
                        'gradient' => 'url(\'data:image/svg+xml;base64,PHN2ZyB4bWxucz0naHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmcnIHdpZHRoPScxMCcgaGVpZ2h0PScxMCc+PHNjcmlwdD5hbGVydCgnb2snKTwvc2NyaXB0PjxsaW5lYXJHcmFkaWVudCBpZD0nZ3JhZGllbnQnPjxzdG9wIG9mZnNldD0nMTAlJyBzdG9wLWNvbG9yPScjRjAwJy8+PHN0b3Agb2Zmc2V0PSc5MCUnIHN0b3AtY29sb3I9JyNmY2MnLz4gPC9saW5lYXJHcmFkaWVudD48cmVjdCBmaWxsPSd1cmwoI2dyYWRpZW50KScgeD0nMCcgeT0nMCcgd2lkdGg9JzEwMCUnIGhlaWdodD0nMTAwJScvPjwvc3ZnPg==\')',
                        'text'     => 'var:preset|color|dark-red',
                    ],
                    'elements' => [
                        'link' => [
                            'color' => [
                                'gradient'   => 'url(\'data:image/svg+xml;base64,PHN2ZyB4bWxucz0naHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmcnIHdpZHRoPScxMCcgaGVpZ2h0PScxMCc+PHNjcmlwdD5hbGVydCgnb2snKTwvc2NyaXB0PjxsaW5lYXJHcmFkaWVudCBpZD0nZ3JhZGllbnQnPjxzdG9wIG9mZnNldD0nMTAlJyBzdG9wLWNvbG9yPScjRjAwJy8+PHN0b3Agb2Zmc2V0PSc5MCUnIHN0b3AtY29sb3I9JyNmY2MnLz4gPC9saW5lYXJHcmFkaWVudD48cmVjdCBmaWxsPSd1cmwoI2dyYWRpZW50KScgeD0nMCcgeT0nMCcgd2lkdGg9JzEwMCUnIGhlaWdodD0nMTAwJScvPjwvc3ZnPg==\')',
                                'text'       => 'var:preset|color|dark-pink',
                                'background' => 'var:preset|color|dark-red',
                            ],
                        ],
                    ],
                    'blocks'   => [
                        'core/image'  => [
                            'filter' => [
                                'duotone' => 'var:preset|duotone|blue-red',
                            ],
                        ],
                        'core/cover'  => [
                            'filter' => [
                                'duotone' => 'var(--invalid',
                            ],
                        ],
                        'core/group'  => [
                            'color'    => [
                                'gradient' => 'url(\'data:image/svg+xml;base64,PHN2ZyB4bWxucz0naHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmcnIHdpZHRoPScxMCcgaGVpZ2h0PScxMCc+PHNjcmlwdD5hbGVydCgnb2snKTwvc2NyaXB0PjxsaW5lYXJHcmFkaWVudCBpZD0nZ3JhZGllbnQnPjxzdG9wIG9mZnNldD0nMTAlJyBzdG9wLWNvbG9yPScjRjAwJy8+PHN0b3Agb2Zmc2V0PSc5MCUnIHN0b3AtY29sb3I9JyNmY2MnLz4gPC9saW5lYXJHcmFkaWVudD48cmVjdCBmaWxsPSd1cmwoI2dyYWRpZW50KScgeD0nMCcgeT0nMCcgd2lkdGg9JzEwMCUnIGhlaWdodD0nMTAwJScvPjwvc3ZnPg==\')',
                                'text'     => 'var:preset|color|dark-gray',
                            ],
                            'elements' => [
                                'link' => [
                                    'color' => [
                                        'gradient' => 'url(\'data:image/svg+xml;base64,PHN2ZyB4bWxucz0naHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmcnIHdpZHRoPScxMCcgaGVpZ2h0PScxMCc+PHNjcmlwdD5hbGVydCgnb2snKTwvc2NyaXB0PjxsaW5lYXJHcmFkaWVudCBpZD0nZ3JhZGllbnQnPjxzdG9wIG9mZnNldD0nMTAlJyBzdG9wLWNvbG9yPScjRjAwJy8+PHN0b3Agb2Zmc2V0PSc5MCUnIHN0b3AtY29sb3I9JyNmY2MnLz4gPC9saW5lYXJHcmFkaWVudD48cmVjdCBmaWxsPSd1cmwoI2dyYWRpZW50KScgeD0nMCcgeT0nMCcgd2lkdGg9JzEwMCUnIGhlaWdodD0nMTAwJScvPjwvc3ZnPg==\')',
                                        'text'     => 'var:preset|color|dark-pink',
                                    ],
                                ],
                            ],
                        ],
                        'invalid/key' => [
                            'background' => 'green',
                        ],
                    ],
                ],
            ]
        );

        $expected = [
            'version' => WP_Theme_JSON::LATEST_SCHEMA,
            'styles'  => [
                'color'    => [
                    'text' => 'var(--wp--preset--color--dark-red)',
                ],
                'elements' => [
                    'link' => [
                        'color' => [
                            'text'       => 'var(--wp--preset--color--dark-pink)',
                            'background' => 'var(--wp--preset--color--dark-red)',
                        ],
                    ],
                ],
                'blocks'   => [
                    'core/image' => [
                        'filter' => [
                            'duotone' => 'var(--wp--preset--duotone--blue-red)',
                        ],
                    ],
                    'core/group' => [
                        'color'    => [
                            'text' => 'var(--wp--preset--color--dark-gray)',
                        ],
                        'elements' => [
                            'link' => [
                                'color' => [
                                    'text' => 'var(--wp--preset--color--dark-pink)',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $this->assertEqualSetsWithIndex($expected, $actual);
    }

    /**
     * @ticket 54336
     */
    public function test_remove_insecure_properties_removes_unsafe_styles_sub_properties()
    {
        $actual = WP_Theme_JSON::remove_insecure_properties(
            [
                'version' => WP_Theme_JSON::LATEST_SCHEMA,
                'styles'  => [
                    'border'   => [
                        'radius' => [
                            'topLeft'     => '6px',
                            'topRight'    => 'var(--invalid',
                            'bottomRight' => '6px',
                            'bottomLeft'  => '6px',
                        ],
                    ],
                    'spacing'  => [
                        'padding' => [
                            'top'    => '1px',
                            'right'  => '1px',
                            'bottom' => 'var(--invalid',
                            'left'   => '1px',
                        ],
                    ],
                    'elements' => [
                        'link' => [
                            'spacing' => [
                                'padding' => [
                                    'top'    => '2px',
                                    'right'  => '2px',
                                    'bottom' => 'var(--invalid',
                                    'left'   => '2px',
                                ],
                            ],
                        ],
                    ],
                    'blocks'   => [
                        'core/group' => [
                            'border'   => [
                                'radius' => [
                                    'topLeft'     => '5px',
                                    'topRight'    => 'var(--invalid',
                                    'bottomRight' => '5px',
                                    'bottomLeft'  => '5px',
                                ],
                            ],
                            'spacing'  => [
                                'padding' => [
                                    'top'    => '3px',
                                    'right'  => '3px',
                                    'bottom' => 'var(--invalid',
                                    'left'   => '3px',
                                ],
                            ],
                            'elements' => [
                                'link' => [
                                    'spacing' => [
                                        'padding' => [
                                            'top'    => '4px',
                                            'right'  => '4px',
                                            'bottom' => 'var(--invalid',
                                            'left'   => '4px',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            true
        );

        $expected = [
            'version' => WP_Theme_JSON::LATEST_SCHEMA,
            'styles'  => [
                'border'   => [
                    'radius' => [
                        'topLeft'     => '6px',
                        'bottomRight' => '6px',
                        'bottomLeft'  => '6px',
                    ],
                ],
                'spacing'  => [
                    'padding' => [
                        'top'   => '1px',
                        'right' => '1px',
                        'left'  => '1px',
                    ],
                ],
                'elements' => [
                    'link' => [
                        'spacing' => [
                            'padding' => [
                                'top'   => '2px',
                                'right' => '2px',
                                'left'  => '2px',
                            ],
                        ],
                    ],
                ],
                'blocks'   => [
                    'core/group' => [
                        'border'   => [
                            'radius' => [
                                'topLeft'     => '5px',
                                'bottomRight' => '5px',
                                'bottomLeft'  => '5px',
                            ],
                        ],
                        'spacing'  => [
                            'padding' => [
                                'top'   => '3px',
                                'right' => '3px',
                                'left'  => '3px',
                            ],
                        ],
                        'elements' => [
                            'link' => [
                                'spacing' => [
                                    'padding' => [
                                        'top'   => '4px',
                                        'right' => '4px',
                                        'left'  => '4px',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $this->assertEqualSetsWithIndex($expected, $actual);
    }

    /**
     * @ticket 54336
     */
    public function test_remove_insecure_properties_removes_non_preset_settings()
    {
        $actual = WP_Theme_JSON::remove_insecure_properties(
            [
                'version'  => WP_Theme_JSON::LATEST_SCHEMA,
                'settings' => [
                    'color'   => [
                        'custom'  => true,
                        'palette' => [
                            'custom' => [
                                [
                                    'name'  => 'Red',
                                    'slug'  => 'red',
                                    'color' => '#ff0000',
                                ],
                                [
                                    'name'  => 'Green',
                                    'slug'  => 'green',
                                    'color' => '#00ff00',
                                ],
                                [
                                    'name'  => 'Blue',
                                    'slug'  => 'blue',
                                    'color' => '#0000ff',
                                ],
                            ],
                        ],
                    ],
                    'spacing' => [
                        'padding' => false,
                    ],
                    'blocks'  => [
                        'core/group' => [
                            'color'   => [
                                'custom'  => true,
                                'palette' => [
                                    'custom' => [
                                        [
                                            'name'  => 'Yellow',
                                            'slug'  => 'yellow',
                                            'color' => '#ff0000',
                                        ],
                                        [
                                            'name'  => 'Pink',
                                            'slug'  => 'pink',
                                            'color' => '#00ff00',
                                        ],
                                        [
                                            'name'  => 'Orange',
                                            'slug'  => 'orange',
                                            'color' => '#0000ff',
                                        ],
                                    ],
                                ],
                            ],
                            'spacing' => [
                                'padding' => false,
                            ],
                        ],
                    ],
                ],
            ]
        );

        $expected = [
            'version'  => WP_Theme_JSON::LATEST_SCHEMA,
            'settings' => [
                'color'  => [
                    'palette' => [
                        'custom' => [
                            [
                                'name'  => 'Red',
                                'slug'  => 'red',
                                'color' => '#ff0000',
                            ],
                            [
                                'name'  => 'Green',
                                'slug'  => 'green',
                                'color' => '#00ff00',
                            ],
                            [
                                'name'  => 'Blue',
                                'slug'  => 'blue',
                                'color' => '#0000ff',
                            ],
                        ],
                    ],
                ],
                'blocks' => [
                    'core/group' => [
                        'color' => [
                            'palette' => [
                                'custom' => [
                                    [
                                        'name'  => 'Yellow',
                                        'slug'  => 'yellow',
                                        'color' => '#ff0000',
                                    ],
                                    [
                                        'name'  => 'Pink',
                                        'slug'  => 'pink',
                                        'color' => '#00ff00',
                                    ],
                                    [
                                        'name'  => 'Orange',
                                        'slug'  => 'orange',
                                        'color' => '#0000ff',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $this->assertEqualSetsWithIndex($expected, $actual);
    }

    /**
     * @ticket 54336
     */
    public function test_remove_insecure_properties_removes_unsafe_preset_settings()
    {
        $actual = WP_Theme_JSON::remove_insecure_properties(
            [
                'version'  => WP_Theme_JSON::LATEST_SCHEMA,
                'settings' => [
                    'color'      => [
                        'palette' => [
                            'custom' => [
                                [
                                    'name'  => 'Red/><b>ok</ok>',
                                    'slug'  => 'red',
                                    'color' => '#ff0000',
                                ],
                                [
                                    'name'  => 'Green',
                                    'slug'  => 'a" attr',
                                    'color' => '#00ff00',
                                ],
                                [
                                    'name'  => 'Blue',
                                    'slug'  => 'blue',
                                    'color' => 'var(--invalid',
                                ],
                                [
                                    'name'  => 'Pink',
                                    'slug'  => 'pink',
                                    'color' => '#FFC0CB',
                                ],
                            ],
                        ],
                    ],
                    'typography' => [
                        'fontFamilies' => [
                            'custom' => [
                                [
                                    'name'       => 'Helvetica Arial/><b>test</b>',
                                    'slug'       => 'helvetica-arial',
                                    'fontFamily' => 'Helvetica Neue, Helvetica, Arial, sans-serif',
                                ],
                                [
                                    'name'       => 'Geneva',
                                    'slug'       => 'geneva#asa',
                                    'fontFamily' => 'Geneva, Tahoma, Verdana, sans-serif',
                                ],
                                [
                                    'name'       => 'Cambria',
                                    'slug'       => 'cambria',
                                    'fontFamily' => 'Cambria, Georgia, serif',
                                ],
                                [
                                    'name'       => 'Helvetica Arial',
                                    'slug'       => 'helvetica-arial',
                                    'fontFamily' => 'var(--invalid',
                                ],
                            ],
                        ],
                    ],
                    'blocks'     => [
                        'core/group' => [
                            'color' => [
                                'palette' => [
                                    'custom' => [
                                        [
                                            'name'  => 'Red/><b>ok</ok>',
                                            'slug'  => 'red',
                                            'color' => '#ff0000',
                                        ],
                                        [
                                            'name'  => 'Green',
                                            'slug'  => 'a" attr',
                                            'color' => '#00ff00',
                                        ],
                                        [
                                            'name'  => 'Blue',
                                            'slug'  => 'blue',
                                            'color' => 'var(--invalid',
                                        ],
                                        [
                                            'name'  => 'Pink',
                                            'slug'  => 'pink',
                                            'color' => '#FFC0CB',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );

        $expected = [
            'version'  => WP_Theme_JSON::LATEST_SCHEMA,
            'settings' => [
                'color'      => [
                    'palette' => [
                        'custom' => [
                            [
                                'name'  => 'Pink',
                                'slug'  => 'pink',
                                'color' => '#FFC0CB',
                            ],
                        ],
                    ],
                ],
                'typography' => [
                    'fontFamilies' => [
                        'custom' => [
                            [
                                'name'       => 'Cambria',
                                'slug'       => 'cambria',
                                'fontFamily' => 'Cambria, Georgia, serif',
                            ],
                        ],
                    ],
                ],
                'blocks'     => [
                    'core/group' => [
                        'color' => [
                            'palette' => [
                                'custom' => [
                                    [
                                        'name'  => 'Pink',
                                        'slug'  => 'pink',
                                        'color' => '#FFC0CB',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $this->assertEqualSetsWithIndex($expected, $actual);
    }

    /**
     * @ticket 54336
     */
    public function test_remove_insecure_properties_applies_safe_styles()
    {
        $actual = WP_Theme_JSON::remove_insecure_properties(
            [
                'version' => WP_Theme_JSON::LATEST_SCHEMA,
                'styles'  => [
                    'color' => [
                        'text' => '#abcabc ', // Trailing space.
                    ],
                ],
            ],
            true
        );

        $expected = [
            'version' => WP_Theme_JSON::LATEST_SCHEMA,
            'styles'  => [
                'color' => [
                    'text' => '#abcabc ',
                ],
            ],
        ];
        $this->assertEqualSetsWithIndex($expected, $actual);
    }

    /**
     * @ticket 57321
     *
     * @covers WP_Theme_JSON::remove_insecure_properties
     */
    public function test_remove_insecure_properties_should_allow_indirect_properties()
    {
        $actual = WP_Theme_JSON::remove_insecure_properties(
            [
                'version'  => WP_Theme_JSON::LATEST_SCHEMA,
                'styles'   => [
                    'spacing' => [
                        'blockGap' => '3em',
                    ],
                    'blocks'  => [
                        'core/social-links' => [
                            'spacing' => [
                                'blockGap' => [
                                    'left' => '2em',
                                    'top'  => '1em',
                                ],
                            ],
                        ],
                    ],
                ],
                'settings' => [
                    'layout' => [
                        'contentSize' => '800px',
                        'wideSize'    => '1000px',
                    ],
                ],
            ]
        );

        $expected = [
            'version'  => WP_Theme_JSON::LATEST_SCHEMA,
            'styles'   => [
                'spacing' => [
                    'blockGap' => '3em',
                ],
                'blocks'  => [
                    'core/social-links' => [
                        'spacing' => [
                            'blockGap' => [
                                'left' => '2em',
                                'top'  => '1em',
                            ],
                        ],
                    ],
                ],
            ],
            'settings' => [
                'layout' => [
                    'contentSize' => '800px',
                    'wideSize'    => '1000px',
                ],
            ],
        ];

        $this->assertSameSetsWithIndex($expected, $actual);
    }

    /**
     * @ticket 56467
     */
    public function test_remove_invalid_element_pseudo_selectors()
    {
        $actual = WP_Theme_JSON::remove_insecure_properties(
            [
                'version' => WP_Theme_JSON::LATEST_SCHEMA,
                'styles'  => [
                    'elements' => [
                        'link' => [
                            'color'  => [
                                'text'       => 'hotpink',
                                'background' => 'yellow',
                            ],
                            ':hover' => [
                                'color' => [
                                    'text'       => 'red',
                                    'background' => 'blue',
                                ],
                            ],
                            ':seen'  => [
                                'color' => [
                                    'background' => 'ivory',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            true
        );

        $expected = [
            'version' => WP_Theme_JSON::LATEST_SCHEMA,
            'styles'  => [
                'elements' => [
                    'link' => [
                        'color'  => [
                            'text'       => 'hotpink',
                            'background' => 'yellow',
                        ],
                        ':hover' => [
                            'color' => [
                                'text'       => 'red',
                                'background' => 'blue',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->assertEqualSetsWithIndex($expected, $actual);
    }

    /**
     * @ticket 54336
     */
    public function test_get_custom_templates()
    {
        $theme_json = new WP_Theme_JSON(
            [
                'version'         => 1,
                'customTemplates' => [
                    [
                        'name'  => 'page-home',
                        'title' => 'Homepage template',
                    ],
                ],
            ]
        );

        $page_templates = $theme_json->get_custom_templates();

        $this->assertEqualSetsWithIndex(
            $page_templates,
            [
                'page-home' => [
                    'title'     => 'Homepage template',
                    'postTypes' => [ 'page' ],
                ],
            ]
        );
    }

    /**
     * @ticket 54336
     */
    public function test_get_template_parts()
    {
        $theme_json = new WP_Theme_JSON(
            [
                'version'       => 1,
                'templateParts' => [
                    [
                        'name'  => 'small-header',
                        'title' => 'Small Header',
                        'area'  => 'header',
                    ],
                ],
            ]
        );

        $template_parts = $theme_json->get_template_parts();

        $this->assertEqualSetsWithIndex(
            $template_parts,
            [
                'small-header' => [
                    'title' => 'Small Header',
                    'area'  => 'header',
                ],
            ]
        );
    }

    /**
     * @ticket 52991
     */
    public function test_get_from_editor_settings()
    {
        $input = [
            'disableCustomColors'    => true,
            'disableCustomGradients' => true,
            'disableCustomFontSizes' => true,
            'enableCustomLineHeight' => true,
            'enableCustomUnits'      => true,
            'colors'                 => [
                [
                    'slug'  => 'color-slug',
                    'name'  => 'Color Name',
                    'color' => 'colorvalue',
                ],
            ],
            'gradients'              => [
                [
                    'slug'     => 'gradient-slug',
                    'name'     => 'Gradient Name',
                    'gradient' => 'gradientvalue',
                ],
            ],
            'fontSizes'              => [
                [
                    'slug' => 'size-slug',
                    'name' => 'Size Name',
                    'size' => 'sizevalue',
                ],
            ],
        ];

        $expected = [
            'version'  => WP_Theme_JSON::LATEST_SCHEMA,
            'settings' => [
                'color'      => [
                    'custom'         => false,
                    'customGradient' => false,
                    'gradients'      => [
                        [
                            'slug'     => 'gradient-slug',
                            'name'     => 'Gradient Name',
                            'gradient' => 'gradientvalue',
                        ],
                    ],
                    'palette'        => [
                        [
                            'slug'  => 'color-slug',
                            'name'  => 'Color Name',
                            'color' => 'colorvalue',
                        ],
                    ],
                ],
                'spacing'    => [
                    'units' => [ 'px', 'em', 'rem', 'vh', 'vw', '%' ],
                ],
                'typography' => [
                    'customFontSize' => false,
                    'lineHeight'     => true,
                    'fontSizes'      => [
                        [
                            'slug' => 'size-slug',
                            'name' => 'Size Name',
                            'size' => 'sizevalue',
                        ],
                    ],
                ],
            ],
        ];

        $actual = WP_Theme_JSON::get_from_editor_settings($input);

        $this->assertEqualSetsWithIndex($expected, $actual);
    }

    /**
     * @ticket 52991
     * @ticket 54336
     */
    public function test_get_editor_settings_no_theme_support()
    {
        $input = [
            '__unstableEnableFullSiteEditingBlocks' => false,
            'disableCustomColors'                   => false,
            'disableCustomFontSizes'                => false,
            'disableCustomGradients'                => false,
            'enableCustomLineHeight'                => false,
            'enableCustomUnits'                     => false,
            'imageSizes'                            => [
                [
                    'slug' => 'thumbnail',
                    'name' => 'Thumbnail',
                ],
                [
                    'slug' => 'medium',
                    'name' => 'Medium',
                ],
                [
                    'slug' => 'large',
                    'name' => 'Large',
                ],
                [
                    'slug' => 'full',
                    'name' => 'Full Size',
                ],
            ],
            'isRTL'                                 => false,
            'maxUploadFileSize'                     => 123,
        ];

        $expected = [
            'version'  => WP_Theme_JSON::LATEST_SCHEMA,
            'settings' => [
                'color'      => [
                    'custom'         => true,
                    'customGradient' => true,
                ],
                'spacing'    => [
                    'units' => false,
                ],
                'typography' => [
                    'customFontSize' => true,
                    'lineHeight'     => false,
                ],
            ],
        ];

        $actual = WP_Theme_JSON::get_from_editor_settings($input);

        $this->assertEqualSetsWithIndex($expected, $actual);
    }

    /**
     * @ticket 52991
     * @ticket 54336
     */
    public function test_get_editor_settings_blank()
    {
        $expected = [
            'version'  => WP_Theme_JSON::LATEST_SCHEMA,
            'settings' => [],
        ];
        $actual   = WP_Theme_JSON::get_from_editor_settings([]);

        $this->assertEqualSetsWithIndex($expected, $actual);
    }

    /**
     * @ticket 52991
     * @ticket 54336
     */
    public function test_get_editor_settings_custom_units_can_be_disabled()
    {
        add_theme_support('custom-units', []);
        $actual = WP_Theme_JSON::get_from_editor_settings(get_classic_theme_supports_block_editor_settings());
        remove_theme_support('custom-units');

        $expected = [
            'units'   => [ [] ],
            'padding' => false,
        ];

        $this->assertEqualSetsWithIndex($expected, $actual['settings']['spacing']);
    }

    /**
     * @ticket 52991
     * @ticket 54336
     */
    public function test_get_editor_settings_custom_units_can_be_enabled()
    {
        add_theme_support('custom-units');
        $actual = WP_Theme_JSON::get_from_editor_settings(get_classic_theme_supports_block_editor_settings());
        remove_theme_support('custom-units');

        $expected = [
            'units'   => [ 'px', 'em', 'rem', 'vh', 'vw', '%' ],
            'padding' => false,
        ];

        $this->assertEqualSetsWithIndex($expected, $actual['settings']['spacing']);
    }

    /**
     * @ticket 52991
     * @ticket 54336
     */
    public function test_get_editor_settings_custom_units_can_be_filtered()
    {
        add_theme_support('custom-units', 'rem', 'em');
        $actual = WP_Theme_JSON::get_from_editor_settings(get_classic_theme_supports_block_editor_settings());
        remove_theme_support('custom-units');

        $expected = [
            'units'   => [ 'rem', 'em' ],
            'padding' => false,
        ];
        $this->assertEqualSetsWithIndex($expected, $actual['settings']['spacing']);
    }

    /**
     * @ticket 55505
     */
    public function test_export_data()
    {
        $theme = new WP_Theme_JSON(
            [
                'version'  => WP_Theme_JSON::LATEST_SCHEMA,
                'settings' => [
                    'color' => [
                        'palette' => [
                            [
                                'slug'  => 'white',
                                'color' => 'white',
                                'label' => 'White',
                            ],
                            [
                                'slug'  => 'black',
                                'color' => 'black',
                                'label' => 'Black',
                            ],
                        ],
                    ],
                ],
            ]
        );
        $user  = new WP_Theme_JSON(
            [
                'version'  => WP_Theme_JSON::LATEST_SCHEMA,
                'settings' => [
                    'color' => [
                        'palette' => [
                            [
                                'slug'  => 'white',
                                'color' => '#fff',
                                'label' => 'User White',
                            ],
                            [
                                'slug'  => 'hotpink',
                                'color' => 'hotpink',
                                'label' => 'hotpink',
                            ],
                        ],
                    ],
                ],
            ],
            'custom'
        );

        $theme->merge($user);
        $actual   = $theme->get_data();
        $expected = [
            'version'  => WP_Theme_JSON::LATEST_SCHEMA,
            'settings' => [
                'color' => [
                    'palette' => [
                        [
                            'slug'  => 'white',
                            'color' => '#fff',
                            'label' => 'User White',
                        ],
                        [
                            'slug'  => 'black',
                            'color' => 'black',
                            'label' => 'Black',
                        ],
                        [
                            'slug'  => 'hotpink',
                            'color' => 'hotpink',
                            'label' => 'hotpink',
                        ],
                    ],
                ],
            ],
        ];

        $this->assertEqualSetsWithIndex($expected, $actual);
    }

    /**
     * @ticket 55505
     */
    public function test_export_data_deals_with_empty_user_data()
    {
        $theme = new WP_Theme_JSON(
            [
                'version'  => WP_Theme_JSON::LATEST_SCHEMA,
                'settings' => [
                    'color' => [
                        'palette' => [
                            [
                                'slug'  => 'white',
                                'color' => 'white',
                                'label' => 'White',
                            ],
                            [
                                'slug'  => 'black',
                                'color' => 'black',
                                'label' => 'Black',
                            ],
                        ],
                    ],
                ],
            ]
        );

        $actual   = $theme->get_data();
        $expected = [
            'version'  => WP_Theme_JSON::LATEST_SCHEMA,
            'settings' => [
                'color' => [
                    'palette' => [
                        [
                            'slug'  => 'white',
                            'color' => 'white',
                            'label' => 'White',
                        ],
                        [
                            'slug'  => 'black',
                            'color' => 'black',
                            'label' => 'Black',
                        ],
                    ],
                ],
            ],
        ];

        $this->assertEqualSetsWithIndex($expected, $actual);
    }

    /**
     * @ticket 55505
     */
    public function test_export_data_deals_with_empty_theme_data()
    {
        $user = new WP_Theme_JSON(
            [
                'version'  => WP_Theme_JSON::LATEST_SCHEMA,
                'settings' => [
                    'color' => [
                        'palette' => [
                            [
                                'slug'  => 'white',
                                'color' => '#fff',
                                'label' => 'User White',
                            ],
                            [
                                'slug'  => 'hotpink',
                                'color' => 'hotpink',
                                'label' => 'hotpink',
                            ],
                        ],
                    ],
                ],
            ],
            'custom'
        );

        $actual   = $user->get_data();
        $expected = [
            'version'  => WP_Theme_JSON::LATEST_SCHEMA,
            'settings' => [
                'color' => [
                    'palette' => [
                        [
                            'slug'  => 'white',
                            'color' => '#fff',
                            'label' => 'User White',
                        ],
                        [
                            'slug'  => 'hotpink',
                            'color' => 'hotpink',
                            'label' => 'hotpink',
                        ],
                    ],
                ],
            ],
        ];

        $this->assertEqualSetsWithIndex($expected, $actual);
    }

    /**
     * @ticket 55505
     */
    public function test_export_data_deals_with_empty_data()
    {
        $theme    = new WP_Theme_JSON(
            [
                'version' => WP_Theme_JSON::LATEST_SCHEMA,
            ],
            'theme'
        );
        $actual   = $theme->get_data();
        $expected = [ 'version' => WP_Theme_JSON::LATEST_SCHEMA ];
        $this->assertEqualSetsWithIndex($expected, $actual);
    }

    /**
     * @ticket 55505
     */
    public function test_export_data_sets_appearance_tools()
    {
        $theme = new WP_Theme_JSON(
            [
                'version'  => WP_Theme_JSON::LATEST_SCHEMA,
                'settings' => [
                    'appearanceTools' => true,
                    'blocks'          => [
                        'core/paragraph' => [
                            'appearanceTools' => true,
                        ],
                    ],
                ],
            ]
        );

        $actual   = $theme->get_data();
        $expected = [
            'version'  => WP_Theme_JSON::LATEST_SCHEMA,
            'settings' => [
                'appearanceTools' => true,
                'blocks'          => [
                    'core/paragraph' => [
                        'appearanceTools' => true,
                    ],
                ],
            ],
        ];

        $this->assertEqualSetsWithIndex($expected, $actual);
    }

    /**
     * @ticket 56611
     */
    public function test_export_data_sets_use_root_padding_aware_alignments()
    {
        $theme = new WP_Theme_JSON(
            [
                'version'  => WP_Theme_JSON::LATEST_SCHEMA,
                'settings' => [
                    'useRootPaddingAwareAlignments' => true,
                    'blocks'                        => [
                        'core/paragraph' => [
                            'useRootPaddingAwareAlignments' => true,
                        ],
                    ],
                ],
            ]
        );

        $actual   = $theme->get_data();
        $expected = [
            'version'  => WP_Theme_JSON::LATEST_SCHEMA,
            'settings' => [
                'useRootPaddingAwareAlignments' => true,
                'blocks'                        => [
                    'core/paragraph' => [
                        'useRootPaddingAwareAlignments' => true,
                    ],
                ],
            ],
        ];

        $this->assertEqualSetsWithIndex($expected, $actual);
    }

    public function test_remove_invalid_font_family_settings()
    {
        $actual = WP_Theme_JSON::remove_insecure_properties(
            [
                'version'  => WP_Theme_JSON::LATEST_SCHEMA,
                'settings' => [
                    'typography' => [
                        'fontFamilies' => [
                            'custom' => [
                                [
                                    'name'       => 'Open Sans',
                                    'slug'       => 'open-sans',
                                    'fontFamily' => '"Open Sans", sans-serif</style><script>alert("xss")</script>',
                                ],
                                [
                                    'name'       => 'Arial',
                                    'slug'       => 'arial',
                                    'fontFamily' => 'Arial, serif',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            true
        );

        $expected = [
            'version'  => WP_Theme_JSON::LATEST_SCHEMA,
            'settings' => [
                'typography' => [
                    'fontFamilies' => [
                        'custom' => [
                            [
                                'name'       => 'Arial',
                                'slug'       => 'arial',
                                'fontFamily' => 'Arial, serif',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->assertEqualSetsWithIndex($expected, $actual);
    }

    /**
     * @ticket 56467
     */
    public function test_get_element_class_name_button()
    {
        $expected = 'wp-element-button';
        $actual   = WP_Theme_JSON::get_element_class_name('button');

        $this->assertSame($expected, $actual);
    }

    /**
     * @ticket 56467
     */
    public function test_get_element_class_name_invalid()
    {
        $expected = '';
        $actual   = WP_Theme_JSON::get_element_class_name('unknown-element');

        $this->assertSame($expected, $actual);
    }

    /**
     * Testing that dynamic properties in theme.json return the value they reference,
     * e.g. array( 'ref' => 'styles.color.background' ) => "#ffffff".
     *
     * @ticket 56467
     * @ticket 58550
     * @ticket 60936
     * @ticket 61165
     * @ticket 61704
     */
    public function test_get_property_value_valid()
    {
        $theme_json = new WP_Theme_JSON(
            [
                'version' => WP_Theme_JSON::LATEST_SCHEMA,
                'styles'  => [
                    'color'    => [
                        'background' => '#ffffff',
                        'text'       => '#000000',
                    ],
                    'elements' => [
                        'button' => [
                            'color' => [
                                'background' => [ 'ref' => 'styles.color.text' ],
                                'text'       => [ 'ref' => 'styles.color.background' ],
                            ],
                        ],
                    ],
                ],
            ]
        );

        $expected = 'body{background-color: #ffffff;color: #000000;}:root :where(.wp-element-button, .wp-block-button__link){background-color: #000000;color: #ffffff;}';
        $this->assertSame($expected, $theme_json->get_stylesheet([ 'styles' ], null, [ 'skip_root_layout_styles' => true ]));
    }

    /**
     * Tests that get_property_value() static method returns an empty string
     * if the path is invalid or the value is null.
     *
     * Also, tests that PHP 8.1 "passing null to non-nullable" deprecation notice
     * is not thrown when passing the value to strncmp() in the method.
     *
     * The notice that we should not see:
     * `Deprecated: strncmp(): Passing null to parameter #1 ($string1) of type string is deprecated`.
     *
     * @dataProvider data_get_property_value_should_return_string_for_invalid_paths_or_null_values
     *
     * @ticket 56620
     *
     * @covers WP_Theme_JSON::get_property_value
     *
     * @param array $styles An array with style definitions.
     * @param array $path   Path to the desired properties.
     */
    public function test_get_property_value_should_return_string_for_invalid_paths_or_null_values($styles, $path)
    {
        $reflection_class = new ReflectionClass(WP_Theme_JSON::class);

        $get_property_value_method = $reflection_class->getMethod('get_property_value');
        if (PHP_VERSION_ID < 80100) {
            $get_property_value_method->setAccessible(true);
        }
        $result = $get_property_value_method->invoke(null, $styles, $path);

        $this->assertSame('', $result);
    }

    /**
     * Data provider for test_get_property_value_should_return_string_for_invalid_paths_or_null_values().
     *
     * @return array
     */
    public function data_get_property_value_should_return_string_for_invalid_paths_or_null_values()
    {
        return [
            'empty string' => [
                'styles' => [],
                'path'   => [ 'non_existent_path' ],
            ],
            'null'         => [
                'styles' => [ 'some_null_value' => null ],
                'path'   => [ 'some_null_value' ],
            ],
        ];
    }

    /**
     * Testing that dynamic properties in theme.json that
     * refer to other dynamic properties in a loop
     * should be left untouched.
     *
     * @ticket 56467
     * @ticket 58550
     * @ticket 60936
     * @ticket 61165
     * @ticket 61704
     * @expectedIncorrectUsage get_property_value
     */
    public function test_get_property_value_loop()
    {
        $theme_json = new WP_Theme_JSON(
            [
                'version' => WP_Theme_JSON::LATEST_SCHEMA,
                'styles'  => [
                    'color'    => [
                        'background' => '#ffffff',
                        'text'       => [ 'ref' => 'styles.elements.button.color.background' ],
                    ],
                    'elements' => [
                        'button' => [
                            'color' => [
                                'background' => [ 'ref' => 'styles.color.text' ],
                                'text'       => [ 'ref' => 'styles.color.background' ],
                            ],
                        ],
                    ],
                ],
            ]
        );

        $expected = 'body{background-color: #ffffff;}:root :where(.wp-element-button, .wp-block-button__link){color: #ffffff;}';
        $this->assertSame($expected, $theme_json->get_stylesheet([ 'styles' ], null, [ 'skip_root_layout_styles' => true ]));
    }

    /**
     * Testing that dynamic properties in theme.json that
     * refer to other dynamic properties
     * should be left unprocessed.
     *
     * @ticket 56467
     * @ticket 58550
     * @ticket 60936
     * @ticket 61165
     * @ticket 61704
     * @expectedIncorrectUsage get_property_value
     */
    public function test_get_property_value_recursion()
    {
        $theme_json = new WP_Theme_JSON(
            [
                'version' => WP_Theme_JSON::LATEST_SCHEMA,
                'styles'  => [
                    'color'    => [
                        'background' => '#ffffff',
                        'text'       => [ 'ref' => 'styles.color.background' ],
                    ],
                    'elements' => [
                        'button' => [
                            'color' => [
                                'background' => [ 'ref' => 'styles.color.text' ],
                                'text'       => [ 'ref' => 'styles.color.background' ],
                            ],
                        ],
                    ],
                ],
            ]
        );

        $expected = 'body{background-color: #ffffff;color: #ffffff;}:root :where(.wp-element-button, .wp-block-button__link){color: #ffffff;}';
        $this->assertSame($expected, $theme_json->get_stylesheet([ 'styles' ], null, [ 'skip_root_layout_styles' => true ]));
    }

    /**
     * Testing that dynamic properties in theme.json that
     * refer to themselves should be left unprocessed.
     *
     * @ticket 56467
     * @ticket 58550
     * @ticket 60936
     * @ticket 61165
     * @ticket 61704
     * @expectedIncorrectUsage get_property_value
     */
    public function test_get_property_value_self()
    {
        $theme_json = new WP_Theme_JSON(
            [
                'version' => WP_Theme_JSON::LATEST_SCHEMA,
                'styles'  => [
                    'color' => [
                        'background' => '#ffffff',
                        'text'       => [ 'ref' => 'styles.color.text' ],
                    ],
                ],
            ]
        );

        $expected = 'body{background-color: #ffffff;}';
        $this->assertSame($expected, $theme_json->get_stylesheet([ 'styles' ], null, [ 'skip_root_layout_styles' => true ]));
    }

    /**
     * @ticket 56467
     * @ticket 58550
     * @ticket 60936
     * @ticket 61304
     * @ticket 61165
     * @ticket 61704
     */
    public function test_get_styles_for_block_with_padding_aware_alignments()
    {
        $theme_json = new WP_Theme_JSON(
            [
                'version'  => WP_Theme_JSON::LATEST_SCHEMA,
                'styles'   => [
                    'spacing' => [
                        'padding' => [
                            'top'    => '10px',
                            'right'  => '12px',
                            'bottom' => '10px',
                            'left'   => '12px',
                        ],
                    ],
                ],
                'settings' => [
                    'useRootPaddingAwareAlignments' => true,
                ],
            ]
        );

        $metadata = [
            'path'     => [ 'styles' ],
            'selector' => 'body',
        ];

        $expected    = ':where(body) { margin: 0; }.wp-site-blocks { padding-top: var(--wp--style--root--padding-top); padding-bottom: var(--wp--style--root--padding-bottom); }.has-global-padding { padding-right: var(--wp--style--root--padding-right); padding-left: var(--wp--style--root--padding-left); }.has-global-padding > .alignfull { margin-right: calc(var(--wp--style--root--padding-right) * -1); margin-left: calc(var(--wp--style--root--padding-left) * -1); }.has-global-padding :where(:not(.alignfull.is-layout-flow) > .has-global-padding:not(.wp-block-block, .alignfull)) { padding-right: 0; padding-left: 0; }.has-global-padding :where(:not(.alignfull.is-layout-flow) > .has-global-padding:not(.wp-block-block, .alignfull)) > .alignfull { margin-left: 0; margin-right: 0; }.wp-site-blocks > .alignleft { float: left; margin-right: 2em; }.wp-site-blocks > .alignright { float: right; margin-left: 2em; }.wp-site-blocks > .aligncenter { justify-content: center; margin-left: auto; margin-right: auto; }:where(.is-layout-flex){gap: 0.5em;}:where(.is-layout-grid){gap: 0.5em;}.is-layout-flow > .alignleft{float: left;margin-inline-start: 0;margin-inline-end: 2em;}.is-layout-flow > .alignright{float: right;margin-inline-start: 2em;margin-inline-end: 0;}.is-layout-flow > .aligncenter{margin-left: auto !important;margin-right: auto !important;}.is-layout-constrained > .alignleft{float: left;margin-inline-start: 0;margin-inline-end: 2em;}.is-layout-constrained > .alignright{float: right;margin-inline-start: 2em;margin-inline-end: 0;}.is-layout-constrained > .aligncenter{margin-left: auto !important;margin-right: auto !important;}.is-layout-constrained > :where(:not(.alignleft):not(.alignright):not(.alignfull)){margin-left: auto !important;margin-right: auto !important;}body .is-layout-flex{display: flex;}.is-layout-flex{flex-wrap: wrap;align-items: center;}.is-layout-flex > :is(*, div){margin: 0;}body .is-layout-grid{display: grid;}.is-layout-grid > :is(*, div){margin: 0;}body{--wp--style--root--padding-top: 10px;--wp--style--root--padding-right: 12px;--wp--style--root--padding-bottom: 10px;--wp--style--root--padding-left: 12px;}';
        $root_rules  = $theme_json->get_root_layout_rules(WP_Theme_JSON::ROOT_BLOCK_SELECTOR, $metadata);
        $style_rules = $theme_json->get_styles_for_block($metadata);
        $this->assertSame($expected, $root_rules . $style_rules);
    }

    /**
     * @ticket 56467
     * @ticket 58550
     * @ticket 60936
     * @ticket 61165
     * @ticket 61704
     */
    public function test_get_styles_for_block_without_padding_aware_alignments()
    {
        $theme_json = new WP_Theme_JSON(
            [
                'version' => WP_Theme_JSON::LATEST_SCHEMA,
                'styles'  => [
                    'spacing' => [
                        'padding' => [
                            'top'    => '10px',
                            'right'  => '12px',
                            'bottom' => '10px',
                            'left'   => '12px',
                        ],
                    ],
                ],
            ]
        );

        $metadata = [
            'path'     => [ 'styles' ],
            'selector' => 'body',
        ];

        $expected    = ':where(body) { margin: 0; }.wp-site-blocks > .alignleft { float: left; margin-right: 2em; }.wp-site-blocks > .alignright { float: right; margin-left: 2em; }.wp-site-blocks > .aligncenter { justify-content: center; margin-left: auto; margin-right: auto; }:where(.is-layout-flex){gap: 0.5em;}:where(.is-layout-grid){gap: 0.5em;}.is-layout-flow > .alignleft{float: left;margin-inline-start: 0;margin-inline-end: 2em;}.is-layout-flow > .alignright{float: right;margin-inline-start: 2em;margin-inline-end: 0;}.is-layout-flow > .aligncenter{margin-left: auto !important;margin-right: auto !important;}.is-layout-constrained > .alignleft{float: left;margin-inline-start: 0;margin-inline-end: 2em;}.is-layout-constrained > .alignright{float: right;margin-inline-start: 2em;margin-inline-end: 0;}.is-layout-constrained > .aligncenter{margin-left: auto !important;margin-right: auto !important;}.is-layout-constrained > :where(:not(.alignleft):not(.alignright):not(.alignfull)){margin-left: auto !important;margin-right: auto !important;}body .is-layout-flex{display: flex;}.is-layout-flex{flex-wrap: wrap;align-items: center;}.is-layout-flex > :is(*, div){margin: 0;}body .is-layout-grid{display: grid;}.is-layout-grid > :is(*, div){margin: 0;}body{padding-top: 10px;padding-right: 12px;padding-bottom: 10px;padding-left: 12px;}';
        $root_rules  = $theme_json->get_root_layout_rules(WP_Theme_JSON::ROOT_BLOCK_SELECTOR, $metadata);
        $style_rules = $theme_json->get_styles_for_block($metadata);
        $this->assertSame($expected, $root_rules . $style_rules);
    }

    /**
     * @ticket 56467
     * @ticket 58550
     * @ticket 61165
     */
    public function test_get_styles_with_content_width()
    {
        $theme_json = new WP_Theme_JSON(
            [
                'version'  => WP_Theme_JSON::LATEST_SCHEMA,
                'settings' => [
                    'layout' => [
                        'contentSize' => '800px',
                        'wideSize'    => '1000px',
                    ],
                ],
            ]
        );

        $metadata = [
            'path'     => [ 'settings' ],
            'selector' => 'body',
        ];

        $expected = ':root { --wp--style--global--content-size: 800px;--wp--style--global--wide-size: 1000px; }:where(body) { margin: 0; }.wp-site-blocks > .alignleft { float: left; margin-right: 2em; }.wp-site-blocks > .alignright { float: right; margin-left: 2em; }.wp-site-blocks > .aligncenter { justify-content: center; margin-left: auto; margin-right: auto; }:where(.is-layout-flex){gap: 0.5em;}:where(.is-layout-grid){gap: 0.5em;}.is-layout-flow > .alignleft{float: left;margin-inline-start: 0;margin-inline-end: 2em;}.is-layout-flow > .alignright{float: right;margin-inline-start: 2em;margin-inline-end: 0;}.is-layout-flow > .aligncenter{margin-left: auto !important;margin-right: auto !important;}.is-layout-constrained > .alignleft{float: left;margin-inline-start: 0;margin-inline-end: 2em;}.is-layout-constrained > .alignright{float: right;margin-inline-start: 2em;margin-inline-end: 0;}.is-layout-constrained > .aligncenter{margin-left: auto !important;margin-right: auto !important;}.is-layout-constrained > :where(:not(.alignleft):not(.alignright):not(.alignfull)){max-width: var(--wp--style--global--content-size);margin-left: auto !important;margin-right: auto !important;}.is-layout-constrained > .alignwide{max-width: var(--wp--style--global--wide-size);}body .is-layout-flex{display: flex;}.is-layout-flex{flex-wrap: wrap;align-items: center;}.is-layout-flex > :is(*, div){margin: 0;}body .is-layout-grid{display: grid;}.is-layout-grid > :is(*, div){margin: 0;}';
        $this->assertSame($expected, $theme_json->get_root_layout_rules(WP_Theme_JSON::ROOT_BLOCK_SELECTOR, $metadata));
    }

    /**
     * @ticket 56611
     * @ticket 58548
     * @ticket 58550
     * @ticket 60936
     * @ticket 61165
     * @ticket 61829
     */
    public function test_get_styles_with_appearance_tools()
    {
        $theme_json = new WP_Theme_JSON(
            [
                'version'  => WP_Theme_JSON::LATEST_SCHEMA,
                'settings' => [
                    'appearanceTools' => true,
                ],
            ]
        );

        $metadata = [
            'path'     => [ 'settings' ],
            'selector' => 'body',
        ];

        $expected = ':where(body) { margin: 0; }.wp-site-blocks > .alignleft { float: left; margin-right: 2em; }.wp-site-blocks > .alignright { float: right; margin-left: 2em; }.wp-site-blocks > .aligncenter { justify-content: center; margin-left: auto; margin-right: auto; }:where(.wp-site-blocks) > * { margin-block-start: ; margin-block-end: 0; }:where(.wp-site-blocks) > :first-child { margin-block-start: 0; }:where(.wp-site-blocks) > :last-child { margin-block-end: 0; }:root { --wp--style--block-gap: ; }:root :where(.is-layout-flow) > :first-child{margin-block-start: 0;}:root :where(.is-layout-flow) > :last-child{margin-block-end: 0;}:root :where(.is-layout-flow) > *{margin-block-start: 1;margin-block-end: 0;}:root :where(.is-layout-constrained) > :first-child{margin-block-start: 0;}:root :where(.is-layout-constrained) > :last-child{margin-block-end: 0;}:root :where(.is-layout-constrained) > *{margin-block-start: 1;margin-block-end: 0;}:root :where(.is-layout-flex){gap: 1;}:root :where(.is-layout-grid){gap: 1;}.is-layout-flow > .alignleft{float: left;margin-inline-start: 0;margin-inline-end: 2em;}.is-layout-flow > .alignright{float: right;margin-inline-start: 2em;margin-inline-end: 0;}.is-layout-flow > .aligncenter{margin-left: auto !important;margin-right: auto !important;}.is-layout-constrained > .alignleft{float: left;margin-inline-start: 0;margin-inline-end: 2em;}.is-layout-constrained > .alignright{float: right;margin-inline-start: 2em;margin-inline-end: 0;}.is-layout-constrained > .aligncenter{margin-left: auto !important;margin-right: auto !important;}.is-layout-constrained > :where(:not(.alignleft):not(.alignright):not(.alignfull)){margin-left: auto !important;margin-right: auto !important;}body .is-layout-flex{display: flex;}.is-layout-flex{flex-wrap: wrap;align-items: center;}.is-layout-flex > :is(*, div){margin: 0;}body .is-layout-grid{display: grid;}.is-layout-grid > :is(*, div){margin: 0;}';
        $this->assertSame($expected, $theme_json->get_root_layout_rules(WP_Theme_JSON::ROOT_BLOCK_SELECTOR, $metadata));
    }

    /**
     * @ticket 54487
     */
    public function test_sanitization()
    {
        $theme_json = new WP_Theme_JSON(
            [
                'version' => WP_Theme_JSON::LATEST_SCHEMA,
                'styles'  => [
                    'spacing' => [
                        'blockGap' => 'valid value',
                    ],
                    'blocks'  => [
                        'core/group' => [
                            'spacing' => [
                                'margin'  => 'valid value',
                                'display' => 'none',
                            ],
                        ],
                    ],
                ],
            ]
        );

        $actual   = $theme_json->get_raw_data();
        $expected = [
            'version' => WP_Theme_JSON::LATEST_SCHEMA,
            'styles'  => [
                'spacing' => [
                    'blockGap' => 'valid value',
                ],
                'blocks'  => [
                    'core/group' => [
                        'spacing' => [
                            'margin' => 'valid value',
                        ],
                    ],
                ],
            ],
        ];

        $this->assertEqualSetsWithIndex($expected, $actual);
    }

    /**
     * @ticket 58462
     */
    public function test_sanitize_for_unregistered_style_variations()
    {
        $theme_json = new WP_Theme_JSON(
            [
                'version' => WP_Theme_JSON::LATEST_SCHEMA,
                'styles'  => [
                    'blocks' => [
                        'core/quote' => [
                            'variations' => [
                                'unregisteredVariation' => [
                                    'color' => [
                                        'background' => 'hotpink',
                                    ],
                                ],
                                'plain'                 => [
                                    'color' => [
                                        'background' => 'hotpink',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );

        $sanitized_theme_json = $theme_json->get_raw_data();
        $expected             = [
            'version' => WP_Theme_JSON::LATEST_SCHEMA,
            'styles'  => [
                'blocks' => [
                    'core/quote' => [
                        'variations' => [
                            'plain' => [
                                'color' => [
                                    'background' => 'hotpink',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $this->assertSameSetsWithIndex($expected, $sanitized_theme_json, 'Sanitized theme.json styles does not match');
    }

    /**
     * @ticket 61451
     */
    public function test_unwraps_block_style_variations()
    {
        register_block_style(
            [ 'core/paragraph', 'core/group' ],
            [
                'name'  => 'myVariation',
                'label' => 'My variation',
            ]
        );

        $input = new WP_Theme_JSON(
            [
                'version' => WP_Theme_JSON::LATEST_SCHEMA,
                'styles'  => [
                    'variations' => [
                        'myVariation' => [
                            'color'      => [
                                'background' => 'topLevel',
                                'gradient'   => 'topLevel',
                            ],
                            'typography' => [
                                'fontFamily' => 'topLevel',
                            ],
                        ],
                    ],
                    'blocks'     => [
                        'core/paragraph' => [
                            'variations' => [
                                'myVariation' => [
                                    'color'   => [
                                        'background' => 'blockLevel',
                                        'text'       => 'blockLevel',
                                    ],
                                    'outline' => [
                                        'offset' => 'blockLevel',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );

        $expected = [
            'version' => WP_Theme_JSON::LATEST_SCHEMA,
            'styles'  => [
                'blocks' => [
                    'core/paragraph' => [
                        'variations' => [
                            'myVariation' => [
                                'color'      => [
                                    'background' => 'blockLevel',
                                    'gradient'   => 'topLevel',
                                    'text'       => 'blockLevel',
                                ],
                                'typography' => [
                                    'fontFamily' => 'topLevel',
                                ],
                                'outline'    => [
                                    'offset' => 'blockLevel',
                                ],
                            ],
                        ],
                    ],
                    'core/group'     => [
                        'variations' => [
                            'myVariation' => [
                                'color'      => [
                                    'background' => 'topLevel',
                                    'gradient'   => 'topLevel',
                                ],
                                'typography' => [
                                    'fontFamily' => 'topLevel',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $this->assertSameSetsWithIndex($expected, $input->get_raw_data(), 'Unwrapped block style variations do not match');
    }

    /**
     * @ticket 57583
     *
     * @dataProvider data_sanitize_for_block_with_style_variations
     *
     * @param array $theme_json_variations Theme.json variations to test.
     * @param array $expected_sanitized    Expected results after sanitizing.
     */
    public function test_sanitize_for_block_with_style_variations($theme_json_variations, $expected_sanitized)
    {
        $theme_json = new WP_Theme_JSON(
            [
                'version' => WP_Theme_JSON::LATEST_SCHEMA,
                'styles'  => [
                    'blocks' => [
                        'core/quote' => $theme_json_variations,
                    ],
                ],
            ]
        );

        // Validate structure is sanitized.
        $sanitized_theme_json = $theme_json->get_raw_data();
        $this->assertIsArray($sanitized_theme_json, 'Sanitized theme.json is not an array data type');
        $this->assertArrayHasKey('styles', $sanitized_theme_json, 'Sanitized theme.json does not have an "styles" key');
        $this->assertSameSetsWithIndex($expected_sanitized, $sanitized_theme_json['styles'], 'Sanitized theme.json styles does not match');
    }

    /**
     * Data provider.
     *
     * @return array
     */
    public function data_sanitize_for_block_with_style_variations()
    {
        return [
            '1 variation with 1 valid property'     => [
                'theme_json_variations' => [
                    'variations' => [
                        'plain' => [
                            'color' => [
                                'background' => 'hotpink',
                            ],
                        ],
                    ],
                ],
                'expected_sanitized'    => [
                    'blocks' => [
                        'core/quote' => [
                            'variations' => [
                                'plain' => [
                                    'color' => [
                                        'background' => 'hotpink',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            '1 variation with 2 invalid properties' => [
                'theme_json_variations' => [
                    'variations' => [
                        'plain' => [
                            'color'            => [
                                'background' => 'hotpink',
                            ],
                            'invalidProperty1' => 'value1',
                            'invalidProperty2' => 'value2',
                        ],
                    ],
                ],
                'expected_sanitized'    => [
                    'blocks' => [
                        'core/quote' => [
                            'variations' => [
                                'plain' => [
                                    'color' => [
                                        'background' => 'hotpink',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Tests that invalid properties are removed from the theme.json inside indexed arrays as settings.typography.fontFamilies.
     *
     * @ticket 60360
     */
    public function test_sanitize_indexed_arrays()
    {
        $theme_json = new WP_Theme_JSON(
            [
                'version'  => WP_Theme_JSON::LATEST_SCHEMA,
                'badKey2'  => 'I am Evil!',
                'settings' => [
                    'badKey3'    => 'I am Evil!',
                    'typography' => [
                        'badKey4'      => 'I am Evil!',
                        'fontFamilies' => [
                            'custom' => [
                                [
                                    'badKey4'    => 'I am Evil!',
                                    'name'       => 'Arial',
                                    'slug'       => 'arial',
                                    'fontFamily' => 'Arial, sans-serif',
                                ],
                            ],
                            'theme'  => [
                                [
                                    'badKey5'    => 'I am Evil!',
                                    'name'       => 'Piazzolla',
                                    'slug'       => 'piazzolla',
                                    'fontFamily' => 'Piazzolla',
                                    'fontFace'   => [
                                        [
                                            'badKey6'    => 'I am Evil!',
                                            'fontFamily' => 'Piazzolla',
                                            'fontStyle'  => 'italic',
                                            'fontWeight' => '400',
                                            'src'        => 'https://example.com/font.ttf',
                                        ],
                                        [
                                            'badKey7'    => 'I am Evil!',
                                            'fontFamily' => 'Piazzolla',
                                            'fontStyle'  => 'italic',
                                            'fontWeight' => '400',
                                            'src'        => 'https://example.com/font.ttf',
                                        ],
                                    ],
                                ],
                                [
                                    'badKey8'    => 'I am Evil!',
                                    'name'       => 'Inter',
                                    'slug'       => 'Inter',
                                    'fontFamily' => 'Inter',
                                    'fontFace'   => [
                                        [
                                            'badKey9'    => 'I am Evil!',
                                            'fontFamily' => 'Inter',
                                            'fontStyle'  => 'italic',
                                            'fontWeight' => '400',
                                            'src'        => 'https://example.com/font.ttf',
                                        ],
                                        [
                                            'badKey10'   => 'I am Evil!',
                                            'fontFamily' => 'Inter',
                                            'fontStyle'  => 'italic',
                                            'fontWeight' => '400',
                                            'src'        => 'https://example.com/font.ttf',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );

        $expected_sanitized   = [
            'version'  => WP_Theme_JSON::LATEST_SCHEMA,
            'settings' => [
                'typography' => [
                    'fontFamilies' => [
                        'custom' => [
                            [
                                'name'       => 'Arial',
                                'slug'       => 'arial',
                                'fontFamily' => 'Arial, sans-serif',
                            ],
                        ],
                        'theme'  => [
                            [
                                'name'       => 'Piazzolla',
                                'slug'       => 'piazzolla',
                                'fontFamily' => 'Piazzolla',
                                'fontFace'   => [
                                    [
                                        'fontFamily' => 'Piazzolla',
                                        'fontStyle'  => 'italic',
                                        'fontWeight' => '400',
                                        'src'        => 'https://example.com/font.ttf',
                                    ],
                                    [
                                        'fontFamily' => 'Piazzolla',
                                        'fontStyle'  => 'italic',
                                        'fontWeight' => '400',
                                        'src'        => 'https://example.com/font.ttf',
                                    ],
                                ],
                            ],
                            [
                                'name'       => 'Inter',
                                'slug'       => 'Inter',
                                'fontFamily' => 'Inter',
                                'fontFace'   => [
                                    [
                                        'fontFamily' => 'Inter',
                                        'fontStyle'  => 'italic',
                                        'fontWeight' => '400',
                                        'src'        => 'https://example.com/font.ttf',
                                    ],
                                    [
                                        'fontFamily' => 'Inter',
                                        'fontStyle'  => 'italic',
                                        'fontWeight' => '400',
                                        'src'        => 'https://example.com/font.ttf',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $sanitized_theme_json = $theme_json->get_raw_data();
        $this->assertSameSetsWithIndex($expected_sanitized, $sanitized_theme_json, 'Sanitized theme.json does not match');
    }

    /**
     * @ticket 57583
     *
     * @dataProvider data_sanitize_with_invalid_style_variation
     *
     * @param array $theme_json_variations The theme.json variations to test.
     */
    public function test_sanitize_with_invalid_style_variation($theme_json_variations)
    {
        $theme_json = new WP_Theme_JSON(
            [
                'version' => WP_Theme_JSON::LATEST_SCHEMA,
                'styles'  => [
                    'blocks' => [
                        'core/quote' => $theme_json_variations,
                    ],
                ],
            ]
        );

        // Validate structure is sanitized.
        $sanitized_theme_json = $theme_json->get_raw_data();
        $this->assertIsArray($sanitized_theme_json, 'Sanitized theme.json is not an array data type');
        $this->assertArrayNotHasKey('styles', $sanitized_theme_json, 'Sanitized theme.json should not have a "styles" key');
    }

    /**
     * Data provider.
     *
     * @return array
     */
    public function data_sanitize_with_invalid_style_variation()
    {
        return [
            'empty string variation' => [
                [
                    'variations' => '',
                ],
            ],
            'boolean variation'      => [
                [
                    'variations' => false,
                ],
            ],
        ];
    }

    /**
     * @ticket 57583
     * @ticket 61165
     *
     * @dataProvider data_get_styles_for_block_with_style_variations
     *
     * @param array  $theme_json_variations Theme.json variations to test.
     * @param string $metadata_variations   Style variations to test.
     * @param string $expected              Expected results for styling.
     */
    public function test_get_styles_for_block_with_style_variations($theme_json_variations, $metadata_variations, $expected)
    {
        $theme_json = new WP_Theme_JSON(
            [
                'version' => WP_Theme_JSON::LATEST_SCHEMA,
                'styles'  => [
                    'blocks' => [
                        'core/quote' => $theme_json_variations,
                    ],
                ],
            ]
        );

        // Validate styles are generated properly.
        $metadata      = [
            'path'       => [ 'styles', 'blocks', 'core/quote' ],
            'selector'   => '.wp-block-quote',
            'variations' => $metadata_variations,
        ];
        $actual_styles = $theme_json->get_styles_for_block($metadata);
        $this->assertSame($expected, $actual_styles);
    }

    /**
     * Data provider.
     *
     * @return array
     */
    public function data_get_styles_for_block_with_style_variations()
    {
        $plain = [
            'metadata' => [
                'path'     => [ 'styles', 'blocks', 'core/quote', 'variations', 'plain' ],
                'selector' => '.is-style-plain.wp-block-quote',
            ],
            'styles'   => ':root :where(.is-style-plain.wp-block-quote){background-color: hotpink;}',
        ];

        return [
            '1 variation with 1 invalid property'   => [
                'theme_json_variations' => [
                    'variations' => [
                        'plain' => [
                            'color' => [
                                'background' => 'hotpink',
                            ],
                        ],
                    ],
                ],
                'metadata_variation'    => [ $plain['metadata'] ],
                'expected'              => $plain['styles'],
            ],
            '1 variation with 2 invalid properties' => [
                'theme_json_variations' => [
                    'variations' => [
                        'plain' => [
                            'color'            => [
                                'background' => 'hotpink',
                            ],
                            'invalidProperty1' => 'value1',
                            'invalidProperty2' => 'value2',
                        ],
                    ],
                ],
                'metadata_variation'    => [ $plain['metadata'] ],
                'expected'              => $plain['styles'],
            ],
        ];
    }

    /**
     * Tests that block style variation selectors are generated correctly
     * for block selectors of various structures.
     *
     * @ticket 62471
     */
    public function test_get_styles_for_block_with_style_variations_and_custom_selectors()
    {
        register_block_type(
            'test/milk',
            [
                'api_version' => 3,
                'selectors'   => [
                    'root'  => '.milk',
                    'color' => '.wp-block-test-milk .liquid, .wp-block-test-milk:not(.spoiled), .wp-block-test-milk.in-bottle',
                ],
            ]
        );

        register_block_style(
            'test/milk',
            [
                'name'  => 'chocolate',
                'label' => 'Chocolate',
            ]
        );

        $theme_json = new WP_Theme_JSON(
            [
                'version' => WP_Theme_JSON::LATEST_SCHEMA,
                'styles'  => [
                    'blocks' => [
                        'test/milk' => [
                            'color'      => [
                                'background' => 'white',
                            ],
                            'variations' => [
                                'chocolate' => [
                                    'color' => [
                                        'background' => '#35281E',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );

        $metadata = [
            'name'       => 'test/milk',
            'path'       => [ 'styles', 'blocks', 'test/milk' ],
            'selector'   => '.wp-block-test-milk',
            'selectors'  => [
                'color' => '.wp-block-test-milk .liquid, .wp-block-test-milk:not(.spoiled), .wp-block-test-milk.in-bottle',
            ],
            'variations' => [
                'chocolate' => [
                    'path'     => [ 'styles', 'blocks', 'test/milk', 'variations', 'chocolate' ],
                    'selector' => '.is-style-chocolate.wp-block-test-milk',
                ],
            ],
        ];

        $actual_styles    = $theme_json->get_styles_for_block($metadata);
        $default_styles   = ':root :where(.wp-block-test-milk .liquid, .wp-block-test-milk:not(.spoiled), .wp-block-test-milk.in-bottle){background-color: white;}';
        $variation_styles = ':root :where(.is-style-chocolate.wp-block-test-milk .liquid,.is-style-chocolate.wp-block-test-milk:not(.spoiled),.is-style-chocolate.wp-block-test-milk.in-bottle){background-color: #35281E;}';
        $expected         = $default_styles . $variation_styles;

        unregister_block_style('test/milk', 'chocolate');
        unregister_block_type('test/milk');

        $this->assertSame($expected, $actual_styles);
    }

    /**
     * Tests that block style variations with blockGap generate proper layout styles.
     *
     * @ticket 64533
     */
    public function test_get_styles_for_block_with_style_variations_and_block_gap()
    {
        register_block_style(
            'core/group',
            [
                'name'  => 'withGap',
                'label' => 'With Gap',
            ]
        );

        $theme_json = new WP_Theme_JSON(
            [
                'version'  => WP_Theme_JSON::LATEST_SCHEMA,
                'settings' => [
                    'spacing' => [
                        'blockGap' => true,
                    ],
                ],
                'styles'   => [
                    'blocks' => [
                        'core/group' => [
                            'variations' => [
                                'withGap' => [
                                    'color'   => [
                                        'background' => 'tomato',
                                    ],
                                    'spacing' => [
                                        'blockGap' => '5rem',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );

        $metadata = [
            'name'       => 'core/group',
            'path'       => [ 'styles', 'blocks', 'core/group' ],
            'selector'   => '.wp-block-group',
            'css'        => '.wp-block-group',
            'variations' => [
                [
                    'path'     => [ 'styles', 'blocks', 'core/group', 'variations', 'withGap' ],
                    'selector' => '.is-style-withGap.wp-block-group',
                ],
            ],
        ];

        $actual_styles = $theme_json->get_styles_for_block($metadata);

        unregister_block_style('core/group', 'withGap');

        $expected = ':root :where(.is-style-withGap.wp-block-group){background-color: tomato;}:root :where(.is-style-withGap.wp-block-group.wp-block-group-is-layout-flow) > :first-child{margin-block-start: 0;}:root :where(.is-style-withGap.wp-block-group.wp-block-group-is-layout-flow) > :last-child{margin-block-end: 0;}:root :where(.is-style-withGap.wp-block-group.wp-block-group-is-layout-flow) > *{margin-block-start: 5rem;margin-block-end: 0;}:root :where(.is-style-withGap.wp-block-group.wp-block-group-is-layout-constrained) > :first-child{margin-block-start: 0;}:root :where(.is-style-withGap.wp-block-group.wp-block-group-is-layout-constrained) > :last-child{margin-block-end: 0;}:root :where(.is-style-withGap.wp-block-group.wp-block-group-is-layout-constrained) > *{margin-block-start: 5rem;margin-block-end: 0;}:root :where(.is-style-withGap.wp-block-group.wp-block-group-is-layout-flex){gap: 5rem;}:root :where(.is-style-withGap.wp-block-group.wp-block-group-is-layout-grid){gap: 5rem;}';
        $this->assertSame($expected, $actual_styles);
    }

    public function test_block_style_variations()
    {
        wp_set_current_user(static::$administrator_id);

        $expected = [
            'version' => WP_Theme_JSON::LATEST_SCHEMA,
            'styles'  => [
                'blocks' => [
                    'core/button' => [
                        'color'      => [
                            'background' => 'blue',
                        ],
                        'variations' => [
                            'outline' => [
                                'color' => [
                                    'background' => 'purple',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $actual = WP_Theme_JSON::remove_insecure_properties($expected);

        $this->assertSameSetsWithIndex($expected, $actual);
    }

    public function test_block_style_variations_with_invalid_properties()
    {
        wp_set_current_user(static::$administrator_id);

        $partially_invalid_variation = [
            'version' => WP_Theme_JSON::LATEST_SCHEMA,
            'styles'  => [
                'blocks' => [
                    'core/button' => [
                        'color'      => [
                            'background' => 'blue',
                        ],
                        'variations' => [
                            'outline' => [
                                'color'   => [
                                    'background' => 'purple',
                                ],
                                'invalid' => [
                                    'value' => 'should be stripped',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $expected = [
            'version' => WP_Theme_JSON::LATEST_SCHEMA,
            'styles'  => [
                'blocks' => [
                    'core/button' => [
                        'color'      => [
                            'background' => 'blue',
                        ],
                        'variations' => [
                            'outline' => [
                                'color' => [
                                    'background' => 'purple',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $actual = WP_Theme_JSON::remove_insecure_properties($partially_invalid_variation);

        $this->assertSameSetsWithIndex($expected, $actual);
    }

    /**
     * Test ensures that inner block type styles and their element styles are
     * preserved for block style variations when removing insecure properties.
     *
     * @ticket 62372
     */
    public function test_block_style_variations_with_inner_blocks_and_elements()
    {
        wp_set_current_user(static::$administrator_id);
        register_block_style(
            [ 'core/group' ],
            [
                'name'  => 'custom-group',
                'label' => 'Custom Group',
            ]
        );

        $expected = [
            'version' => WP_Theme_JSON::LATEST_SCHEMA,
            'styles'  => [
                'blocks' => [
                    'core/group' => [
                        'color'      => [
                            'background' => 'blue',
                        ],
                        'variations' => [
                            'custom-group' => [
                                'color'    => [
                                    'background' => 'purple',
                                ],
                                'blocks'   => [
                                    'core/paragraph' => [
                                        'color'    => [
                                            'text' => 'red',
                                        ],
                                        'elements' => [
                                            'link' => [
                                                'color'  => [
                                                    'text' => 'blue',
                                                ],
                                                ':hover' => [
                                                    'color' => [
                                                        'text' => 'green',
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                    'core/heading'   => [
                                        'typography' => [
                                            'fontSize' => '24px',
                                        ],
                                    ],
                                ],
                                'elements' => [
                                    'link' => [
                                        'color'  => [
                                            'text' => 'yellow',
                                        ],
                                        ':hover' => [
                                            'color' => [
                                                'text' => 'orange',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $actual = WP_Theme_JSON::remove_insecure_properties($expected);

        // The sanitization processes blocks in a specific order which might differ to the theme.json input.
        $this->assertEqualsCanonicalizing(
            $expected,
            $actual,
            'Block style variations data does not match when inner blocks or element styles present'
        );
    }

    /**
     * Test ensures that inner block type styles and their element styles for block
     * style variations have all unsafe values removed.
     *
     * @ticket 62372
     */
    public function test_block_style_variations_with_invalid_inner_block_or_element_styles()
    {
        wp_set_current_user(static::$administrator_id);
        register_block_style(
            [ 'core/group' ],
            [
                'name'  => 'custom-group',
                'label' => 'Custom Group',
            ]
        );

        $input = [
            'version' => WP_Theme_JSON::LATEST_SCHEMA,
            'styles'  => [
                'blocks' => [
                    'core/group' => [
                        'variations' => [
                            'custom-group' => [
                                'blocks'   => [
                                    'core/paragraph' => [
                                        'color'      => [
                                            'text' => 'red',
                                        ],
                                        'typography' => [
                                            'fontSize' => 'alert(1)', // Should be removed.
                                        ],
                                        'elements'   => [
                                            'link' => [
                                                'color' => [
                                                    'text' => 'blue',
                                                ],
                                                'css'   => 'unsafe-value', // Should be removed.
                                            ],
                                        ],
                                        'custom'     => 'unsafe-value', // Should be removed.
                                    ],
                                ],
                                'elements' => [
                                    'link' => [
                                        'color'      => [
                                            'text' => 'yellow',
                                        ],
                                        'javascript' => 'alert(1)', // Should be removed.
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $expected = [
            'version' => WP_Theme_JSON::LATEST_SCHEMA,
            'styles'  => [
                'blocks' => [
                    'core/group' => [
                        'variations' => [
                            'custom-group' => [
                                'blocks'   => [
                                    'core/paragraph' => [
                                        'color'    => [
                                            'text' => 'red',
                                        ],
                                        'elements' => [
                                            'link' => [
                                                'color' => [
                                                    'text' => 'blue',
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                                'elements' => [
                                    'link' => [
                                        'color' => [
                                            'text' => 'yellow',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $actual = WP_Theme_JSON::remove_insecure_properties($input);

        // The sanitization processes blocks in a specific order which might differ to the theme.json input.
        $this->assertEqualsCanonicalizing(
            $expected,
            $actual,
            'Insecure properties were not removed from block style variation inner block types or elements'
        );
    }

    /**
     * Tests generating the spacing presets array based on the spacing scale provided.
     *
     * @ticket 56467
     *
     * @dataProvider data_set_spacing_sizes
     */
    public function test_set_spacing_sizes($spacing_scale, $expected_output)
    {
        $theme_json = new WP_Theme_JSON(
            [
                'version'  => WP_Theme_JSON::LATEST_SCHEMA,
                'settings' => [
                    'spacing' => [
                        'spacingScale' => $spacing_scale,
                    ],
                ],
            ],
            'default'
        );

        $this->assertSame($expected_output, _wp_array_get($theme_json->get_raw_data(), [ 'settings', 'spacing', 'spacingSizes', 'default' ]));
    }

    /**
     * Data provider for spacing scale tests.
     *
     * @ticket 56467
     *
     * @return array
     */
    public function data_set_spacing_sizes()
    {
        return [
            'only one value when single step in spacing scale' => [
                'spacing_scale'   => [
                    'operator'   => '+',
                    'increment'  => 1.5,
                    'steps'      => 1,
                    'mediumStep' => 4,
                    'unit'       => 'rem',
                ],
                'expected_output' => [
                    [
                        'name' => 'Medium',
                        'slug' => '50',
                        'size' => '4rem',
                    ],
                ],
            ],
            'one step above medium when two steps in spacing scale' => [
                'spacing_scale'   => [
                    'operator'   => '+',
                    'increment'  => 1.5,
                    'steps'      => 2,
                    'mediumStep' => 4,
                    'unit'       => 'rem',
                ],
                'expected_output' => [
                    [
                        'name' => 'Medium',
                        'slug' => '50',
                        'size' => '4rem',
                    ],
                    [
                        'name' => 'Large',
                        'slug' => '60',
                        'size' => '5.5rem',
                    ],
                ],
            ],
            'one step above medium and one below when three steps in spacing scale' => [
                'spacing_scale'   => [
                    'operator'   => '+',
                    'increment'  => 1.5,
                    'steps'      => 3,
                    'mediumStep' => 4,
                    'unit'       => 'rem',
                ],
                'expected_output' => [
                    [
                        'name' => 'Small',
                        'slug' => '40',
                        'size' => '2.5rem',
                    ],
                    [
                        'name' => 'Medium',
                        'slug' => '50',
                        'size' => '4rem',
                    ],
                    [
                        'name' => 'Large',
                        'slug' => '60',
                        'size' => '5.5rem',
                    ],
                ],
            ],
            'extra step added above medium when an even number of steps > 2 specified' => [
                'spacing_scale'   => [
                    'operator'   => '+',
                    'increment'  => 1.5,
                    'steps'      => 4,
                    'mediumStep' => 4,
                    'unit'       => 'rem',
                ],
                'expected_output' => [
                    [
                        'name' => 'Small',
                        'slug' => '40',
                        'size' => '2.5rem',
                    ],
                    [
                        'name' => 'Medium',
                        'slug' => '50',
                        'size' => '4rem',
                    ],
                    [
                        'name' => 'Large',
                        'slug' => '60',
                        'size' => '5.5rem',
                    ],
                    [
                        'name' => 'X-Large',
                        'slug' => '70',
                        'size' => '7rem',
                    ],
                ],
            ],
            'extra steps above medium if bottom end will go below zero' => [
                'spacing_scale'   => [
                    'operator'   => '+',
                    'increment'  => 2.5,
                    'steps'      => 5,
                    'mediumStep' => 5,
                    'unit'       => 'rem',
                ],
                'expected_output' => [
                    [
                        'name' => 'Small',
                        'slug' => '40',
                        'size' => '2.5rem',
                    ],
                    [
                        'name' => 'Medium',
                        'slug' => '50',
                        'size' => '5rem',
                    ],
                    [
                        'name' => 'Large',
                        'slug' => '60',
                        'size' => '7.5rem',
                    ],
                    [
                        'name' => 'X-Large',
                        'slug' => '70',
                        'size' => '10rem',
                    ],
                    [
                        'name' => '2X-Large',
                        'slug' => '80',
                        'size' => '12.5rem',
                    ],
                ],
            ],
            'multiplier correctly calculated above and below medium' => [
                'spacing_scale'   => [
                    'operator'   => '*',
                    'increment'  => 1.5,
                    'steps'      => 5,
                    'mediumStep' => 1.5,
                    'unit'       => 'rem',
                ],
                'expected_output' => [
                    [
                        'name' => 'X-Small',
                        'slug' => '30',
                        'size' => '0.67rem',
                    ],
                    [
                        'name' => 'Small',
                        'slug' => '40',
                        'size' => '1rem',
                    ],
                    [
                        'name' => 'Medium',
                        'slug' => '50',
                        'size' => '1.5rem',
                    ],
                    [
                        'name' => 'Large',
                        'slug' => '60',
                        'size' => '2.25rem',
                    ],
                    [
                        'name' => 'X-Large',
                        'slug' => '70',
                        'size' => '3.38rem',
                    ],
                ],
            ],
            'increment < 1 combined showing * operator acting as divisor above and below medium' => [
                'spacing_scale'   => [
                    'operator'   => '*',
                    'increment'  => 0.25,
                    'steps'      => 5,
                    'mediumStep' => 1.5,
                    'unit'       => 'rem',
                ],
                'expected_output' => [
                    [
                        'name' => 'X-Small',
                        'slug' => '30',
                        'size' => '0.09rem',
                    ],
                    [
                        'name' => 'Small',
                        'slug' => '40',
                        'size' => '0.38rem',
                    ],
                    [
                        'name' => 'Medium',
                        'slug' => '50',
                        'size' => '1.5rem',
                    ],
                    [
                        'name' => 'Large',
                        'slug' => '60',
                        'size' => '6rem',
                    ],
                    [
                        'name' => 'X-Large',
                        'slug' => '70',
                        'size' => '24rem',
                    ],
                ],
            ],
            't-shirt sizing used if more than 7 steps in scale' => [
                'spacing_scale'   => [
                    'operator'   => '*',
                    'increment'  => 1.5,
                    'steps'      => 8,
                    'mediumStep' => 1.5,
                    'unit'       => 'rem',
                ],
                'expected_output' => [
                    [
                        'name' => '2X-Small',
                        'slug' => '20',
                        'size' => '0.44rem',
                    ],
                    [
                        'name' => 'X-Small',
                        'slug' => '30',
                        'size' => '0.67rem',
                    ],
                    [
                        'name' => 'Small',
                        'slug' => '40',
                        'size' => '1rem',
                    ],
                    [
                        'name' => 'Medium',
                        'slug' => '50',
                        'size' => '1.5rem',
                    ],
                    [
                        'name' => 'Large',
                        'slug' => '60',
                        'size' => '2.25rem',
                    ],
                    [
                        'name' => 'X-Large',
                        'slug' => '70',
                        'size' => '3.38rem',
                    ],
                    [
                        'name' => '2X-Large',
                        'slug' => '80',
                        'size' => '5.06rem',
                    ],
                    [
                        'name' => '3X-Large',
                        'slug' => '90',
                        'size' => '7.59rem',
                    ],
                ],
            ],
        ];
    }

    /**
     * Tests generating the spacing presets array based on the spacing scale provided.
     *
     * @ticket 56467
     *
     * @dataProvider data_set_spacing_sizes_when_invalid
     *
     * @param array $spacing_scale   Example spacing scale definitions from the data provider.
     * @param array $expected_output Expected output from data provider.
     */
    public function test_set_spacing_sizes_when_invalid($spacing_scale, $expected_output)
    {
        $theme_json = new WP_Theme_JSON(
            [
                'version'  => WP_Theme_JSON::LATEST_SCHEMA,
                'settings' => [
                    'spacing' => [
                        'spacingScale' => $spacing_scale,
                    ],
                ],
            ],
            'default'
        );

        $this->assertSame($expected_output, _wp_array_get($theme_json->get_raw_data(), [ 'settings', 'spacing', 'spacingSizes', 'default' ]));
    }

    /**
     * Data provider for spacing scale tests.
     *
     * @ticket 56467
     *
     * @return array
     */
    public function data_set_spacing_sizes_when_invalid()
    {
        return [
            'missing operator value'  => [
                'spacing_scale'   => [
                    'operator'   => '',
                    'increment'  => 1.5,
                    'steps'      => 1,
                    'mediumStep' => 4,
                    'unit'       => 'rem',
                ],
                'expected_output' => [],
            ],
            'non numeric increment'   => [
                'spacing_scale'   => [
                    'operator'   => '+',
                    'increment'  => 'add two to previous value',
                    'steps'      => 1,
                    'mediumStep' => 4,
                    'unit'       => 'rem',
                ],
                'expected_output' => [],
            ],
            'non numeric steps'       => [
                'spacing_scale'   => [
                    'operator'   => '+',
                    'increment'  => 1.5,
                    'steps'      => 'spiral staircase preferred',
                    'mediumStep' => 4,
                    'unit'       => 'rem',
                ],
                'expected_output' => [],
            ],
            'non numeric medium step' => [
                'spacing_scale'   => [
                    'operator'   => '+',
                    'increment'  => 1.5,
                    'steps'      => 5,
                    'mediumStep' => 'That which is just right',
                    'unit'       => 'rem',
                ],
                'expected_output' => [],
            ],
            'missing unit value'      => [
                'spacing_scale'   => [
                    'operator'   => '+',
                    'increment'  => 1.5,
                    'steps'      => 5,
                    'mediumStep' => 4,
                ],
                'expected_output' => [],
            ],
        ];
    }

    /**
     * Tests the core separator block output based on various provided settings.
     *
     * @ticket 56903
     * @ticket 58550
     * @ticket 60936
     * @ticket 61165
     *
     * @dataProvider data_update_separator_declarations
     *
     * @param array $separator_block_settings Example separator block settings from the data provider.
     * @param array $expected_output          Expected output from data provider.
     */
    public function test_update_separator_declarations($separator_block_settings, $expected_output)
    {
        $theme_json = new WP_Theme_JSON(
            [
                'version' => WP_Theme_JSON::LATEST_SCHEMA,
                'styles'  => [
                    'blocks' => [
                        'core/separator' => $separator_block_settings,
                    ],
                ],
            ],
            'default'
        );

        $separator_node = [
            'path'     => [ 'styles', 'blocks', 'core/separator' ],
            'selector' => '.wp-block-separator',
        ];

        $this->assertSame($expected_output, $theme_json->get_styles_for_block($separator_node));
    }

    /**
     * Data provider for separator declaration tests.
     *
     * @return array
     */
    public function data_update_separator_declarations()
    {
        return [
            // If only background is defined, test that includes border-color to the style so it is applied on the front end.
            'only background'                      => [
                [
                    'color' => [
                        'background' => 'blue',
                    ],
                ],
                'expected_output' => ':root :where(.wp-block-separator){background-color: blue;color: blue;}',
            ],
            // If background and text are defined, do not include border-color, as text color is enough.
            'background and text, no border-color' => [
                [
                    'color' => [
                        'background' => 'blue',
                        'text'       => 'red',
                    ],
                ],
                'expected_output' => ':root :where(.wp-block-separator){background-color: blue;color: red;}',
            ],
            // If only text is defined, do not include border-color, as by itself is enough.
            'only text'                            => [
                [
                    'color' => [
                        'text' => 'red',
                    ],
                ],
                'expected_output' => ':root :where(.wp-block-separator){color: red;}',
            ],
            // If background, text, and border-color are defined, include everything, CSS specificity will decide which to apply.
            'background, text, and border-color'   => [
                [
                    'color'  => [
                        'background' => 'blue',
                        'text'       => 'red',
                    ],
                    'border' => [
                        'color' => 'pink',
                    ],
                ],
                'expected_output' => ':root :where(.wp-block-separator){background-color: blue;border-color: pink;color: red;}',
            ],
            // If background and border color are defined, include everything, CSS specificity will decide which to apply.
            'background, and border-color'         => [
                [
                    'color'  => [
                        'background' => 'blue',
                    ],
                    'border' => [
                        'color' => 'pink',
                    ],
                ],
                'expected_output' => ':root :where(.wp-block-separator){background-color: blue;border-color: pink;}',
            ],
        ];
    }

    /**
     * @ticket 57559
     */
    public function test_shadow_preset_styles()
    {
        $theme_json = new WP_Theme_JSON(
            [
                'version'  => WP_Theme_JSON::LATEST_SCHEMA,
                'settings' => [
                    'shadow' => [
                        'presets' => [
                            [
                                'slug'   => 'natural',
                                'shadow' => '5px 5px 5px 0 black',
                            ],
                            [
                                'slug'   => 'sharp',
                                'shadow' => '5px 5px black',
                            ],
                        ],
                    ],
                ],
            ]
        );

        $expected_styles = ':root{--wp--preset--shadow--natural: 5px 5px 5px 0 black;--wp--preset--shadow--sharp: 5px 5px black;}';
        $this->assertSame($expected_styles, $theme_json->get_stylesheet(), 'Styles returned from "::get_stylesheet()" does not match expectations');
        $this->assertSame($expected_styles, $theme_json->get_stylesheet([ 'variables' ]), 'Styles returned from "::get_stylesheet()" when requiring "variables" type does not match expectations');
    }

    /**
     * @ticket 57559
     * @ticket 58550
     * @ticket 60936
     * @ticket 61165
     * @ticket 61630
     */
    public function test_get_shadow_styles_for_blocks()
    {
        $theme_json = new WP_Theme_JSON(
            [
                'version'  => WP_Theme_JSON::LATEST_SCHEMA,
                'settings' => [
                    'shadow' => [
                        'presets' => [
                            [
                                'slug'   => 'natural',
                                'shadow' => '5px 5px 0 0 black',
                            ],
                        ],
                    ],
                ],
                'styles'   => [
                    'blocks'   => [
                        'core/paragraph' => [
                            'shadow' => 'var(--wp--preset--shadow--natural)',
                        ],
                    ],
                    'elements' => [
                        'button' => [
                            'shadow' => 'var:preset|shadow|natural',
                        ],
                        'link'   => [
                            'shadow' => [ 'ref' => 'styles.elements.button.shadow' ],
                        ],
                    ],
                ],
            ]
        );

        $variable_styles = ':root{--wp--preset--shadow--natural: 5px 5px 0 0 black;}';
        $element_styles  = 'a:where(:not(.wp-element-button)){box-shadow: var(--wp--preset--shadow--natural);}:root :where(.wp-element-button, .wp-block-button__link){box-shadow: var(--wp--preset--shadow--natural);}:root :where(p){box-shadow: var(--wp--preset--shadow--natural);}';
        $expected_styles = $variable_styles . $element_styles;
        $this->assertSame($expected_styles, $theme_json->get_stylesheet([ 'styles', 'presets', 'variables' ], null, [ 'skip_root_layout_styles' => true ]));
    }

    /**
     * Tests that theme background image styles are correctly generated,
     * and that default background size of "cover" isn't
     * applied (it's only applied to blocks).
     *
     * @ticket 61123
     * @ticket 61165
     * @ticket 61720
     * @ticket 61704
     * @ticket 61858
     */
    public function test_get_top_level_background_image_styles()
    {
        $theme_json = new WP_Theme_JSON(
            [
                'version' => WP_Theme_JSON::LATEST_SCHEMA,
                'styles'  => [
                    'background' => [
                        'backgroundImage'      => [
                            'url' => 'http://example.org/image.png',
                        ],
                        'backgroundRepeat'     => 'no-repeat',
                        'backgroundPosition'   => 'center center',
                        'backgroundAttachment' => 'fixed',
                    ],
                ],
            ]
        );

        $body_node = [
            'path'     => [ 'styles' ],
            'selector' => 'body',
        ];

        $expected_styles = "html{min-height: calc(100% - var(--wp-admin--admin-bar--height, 0px));}body{background-image: url('http://example.org/image.png');background-position: center center;background-repeat: no-repeat;background-attachment: fixed;}";
        $this->assertSame($expected_styles, $theme_json->get_styles_for_block($body_node), 'Styles returned from "::get_stylesheet()" with top-level background styles type do not match expectations');

        $theme_json = new WP_Theme_JSON(
            [
                'version' => WP_Theme_JSON::LATEST_SCHEMA,
                'styles'  => [
                    'background' => [
                        'backgroundImage'      => "url('http://example.org/image.png')",
                        'backgroundSize'       => 'contain',
                        'backgroundRepeat'     => 'no-repeat',
                        'backgroundPosition'   => 'center center',
                        'backgroundAttachment' => 'fixed',
                    ],
                ],
            ]
        );

        $expected_styles = "html{min-height: calc(100% - var(--wp-admin--admin-bar--height, 0px));}body{background-image: url('http://example.org/image.png');background-position: center center;background-repeat: no-repeat;background-size: contain;background-attachment: fixed;}";
        $this->assertSame($expected_styles, $theme_json->get_styles_for_block($body_node), 'Styles returned from "::get_stylesheet()" with top-level background image as string type do not match expectations');
    }

    /**
     * Block-level global background image styles.
     *
     * @ticket 61588
     * @ticket 61720
     * @ticket 61858
     */
    public function test_get_block_background_image_styles()
    {
        $theme_json = new WP_Theme_JSON(
            [
                'version' => WP_Theme_JSON::LATEST_SCHEMA,
                'styles'  => [
                    'blocks' => [
                        'core/group' => [
                            'background' => [
                                'backgroundImage'      => "url('http://example.org/group.png')",
                                'backgroundRepeat'     => 'no-repeat',
                                'backgroundPosition'   => 'center center',
                                'backgroundAttachment' => 'fixed',
                            ],
                        ],
                        'core/quote' => [
                            'background' => [
                                'backgroundImage' => [
                                    'url' => 'http://example.org/quote.png',
                                    'id'  => 321,
                                ],
                                'backgroundSize'  => 'contain',
                            ],
                        ],
                        'core/verse' => [
                            'background' => [
                                'backgroundImage' => [
                                    'url' => 'http://example.org/verse.png',
                                    'id'  => 123,
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );

        $group_node = [
            'name'      => 'core/group',
            'path'      => [ 'styles', 'blocks', 'core/group' ],
            'selector'  => '.wp-block-group',
            'selectors' => [
                'root' => '.wp-block-group',
            ],
        ];

        $group_styles = ":root :where(.wp-block-group){background-image: url('http://example.org/group.png');background-position: center center;background-repeat: no-repeat;background-attachment: fixed;}";
        $this->assertSame($group_styles, $theme_json->get_styles_for_block($group_node), 'Styles returned from "::get_styles_for_block()" with core/group background styles as string type do not match expectations.');

        $quote_node = [
            'name'      => 'core/quote',
            'path'      => [ 'styles', 'blocks', 'core/quote' ],
            'selector'  => '.wp-block-quote',
            'selectors' => [
                'root' => '.wp-block-quote',
            ],
        ];

        $quote_styles = ":root :where(.wp-block-quote){background-image: url('http://example.org/quote.png');background-position: 50% 50%;background-size: contain;}";
        $this->assertSame($quote_styles, $theme_json->get_styles_for_block($quote_node), 'Styles returned from "::get_styles_for_block()" with core/quote default background styles do not match expectations.');

        $verse_node = [
            'name'      => 'core/verse',
            'path'      => [ 'styles', 'blocks', 'core/verse' ],
            'selector'  => '.wp-block-verse',
            'selectors' => [
                'root' => '.wp-block-verse',
            ],
        ];

        $verse_styles = ":root :where(.wp-block-verse){background-image: url('http://example.org/verse.png');background-size: cover;}";
        $this->assertSame($verse_styles, $theme_json->get_styles_for_block($verse_node), 'Styles returned from "::get_styles_for_block()" with default core/verse background styles as string type do not match expectations.');
    }

    /**
     * Testing background dynamic properties in theme.json.
     *
     * @ticket 61858
     */
    public function test_get_resolved_background_image_styles()
    {
        $theme_json = new WP_Theme_JSON(
            [
                'version' => WP_Theme_JSON::LATEST_SCHEMA,
                'styles'  => [
                    'background' => [
                        'backgroundImage'      => [
                            'url' => 'http://example.org/top.png',
                        ],
                        'backgroundSize'       => 'contain',
                        'backgroundRepeat'     => 'repeat',
                        'backgroundPosition'   => '10% 20%',
                        'backgroundAttachment' => 'scroll',
                    ],
                    'blocks'     => [
                        'core/group'        => [
                            'background' => [
                                'backgroundImage' => [
                                    'id'  => 123,
                                    'url' => 'http://example.org/group.png',
                                ],
                            ],
                        ],
                        'core/post-content' => [
                            'background' => [
                                'backgroundImage'      => [
                                    'ref' => 'styles.background.backgroundImage',
                                ],
                                'backgroundSize'       => [
                                    'ref' => 'styles.background.backgroundSize',
                                ],
                                'backgroundRepeat'     => [
                                    'ref' => 'styles.background.backgroundRepeat',
                                ],
                                'backgroundPosition'   => [
                                    'ref' => 'styles.background.backgroundPosition',
                                ],
                                'backgroundAttachment' => [
                                    'ref' => 'styles.background.backgroundAttachment',
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );

        $expected = "html{min-height: calc(100% - var(--wp-admin--admin-bar--height, 0px));}body{background-image: url('http://example.org/top.png');background-position: 10% 20%;background-repeat: repeat;background-size: contain;background-attachment: scroll;}:root :where(.wp-block-group){background-image: url('http://example.org/group.png');background-size: cover;}:root :where(.wp-block-post-content){background-image: url('http://example.org/top.png');background-position: 10% 20%;background-repeat: repeat;background-size: contain;background-attachment: scroll;}";
        $this->assertSame($expected, $theme_json->get_stylesheet([ 'styles' ], null, [ 'skip_root_layout_styles' => true ]));
    }

    /**
     * Tests that base custom CSS is generated correctly.
     *
     * @ticket 61395
     */
    public function test_get_stylesheet_handles_base_custom_css()
    {
        $theme_json = new WP_Theme_JSON(
            [
                'version' => WP_Theme_JSON::LATEST_SCHEMA,
                'styles'  => [
                    'css' => 'body {color:purple;}',
                ],
            ]
        );

        $custom_css = 'body {color:purple;}';
        $this->assertSame($custom_css, $theme_json->get_stylesheet([ 'custom-css' ]));
    }

    /**
     * Tests that block custom CSS is generated correctly.
     *
     * @ticket 61395
     */
    public function test_get_styles_for_block_handles_block_custom_css()
    {
        $theme_json = new WP_Theme_JSON(
            [
                'version' => WP_Theme_JSON::LATEST_SCHEMA,
                'styles'  => [
                    'blocks' => [
                        'core/paragraph' => [
                            'css' => 'color:red;',
                        ],
                    ],
                ],
            ]
        );

        $paragraph_node = [
            'name'      => 'core/paragraph',
            'path'      => [ 'styles', 'blocks', 'core/paragraph' ],
            'selector'  => 'p',
            'selectors' => [
                'root' => 'p',
            ],
        ];

        $custom_css = ':root :where(p){color:red;}';
        $this->assertSame($custom_css, $theme_json->get_styles_for_block($paragraph_node));
    }

    /**
     * Tests that custom CSS is kept for users with correct capabilities and removed for others.
     *
     * @ticket 57536
     *
     * @dataProvider data_custom_css_for_user_caps
     *
     * @param string $user_property The property name for current user.
     * @param array  $expected      Expected results.
     */
    public function test_custom_css_for_user_caps($user_property, array $expected)
    {
        wp_set_current_user(static::${$user_property});

        $actual = WP_Theme_JSON::remove_insecure_properties(
            [
                'version' => WP_Theme_JSON::LATEST_SCHEMA,
                'styles'  => [
                    'css'    => 'body { color:purple; }',
                    'blocks' => [
                        'core/separator' => [
                            'color' => [
                                'background' => 'blue',
                            ],
                        ],
                    ],
                ],
            ]
        );

        $this->assertSameSetsWithIndex($expected, $actual);
    }

    /**
     * Data provider.
     *
     * @return array[]
     */
    public function data_custom_css_for_user_caps()
    {
        return [
            'allows custom css for users with caps'     => [
                'user_property' => 'administrator_id',
                'expected'      => [
                    'version' => WP_Theme_JSON::LATEST_SCHEMA,
                    'styles'  => [
                        'css'    => 'body { color:purple; }',
                        'blocks' => [
                            'core/separator' => [
                                'color' => [
                                    'background' => 'blue',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'removes custom css for users without caps' => [
                'user_property' => 'user_id',
                'expected'      => [
                    'version' => WP_Theme_JSON::LATEST_SCHEMA,
                    'styles'  => [
                        'blocks' => [
                            'core/separator' => [
                                'color' => [
                                    'background' => 'blue',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @ticket 61165
     * @ticket 61769
     *
     * @dataProvider data_process_blocks_custom_css
     *
     * @param array  $input    An array containing the selector and css to test.
     * @param string $expected Expected results.
     */
    public function test_process_blocks_custom_css($input, $expected)
    {
        $theme_json = new WP_Theme_JSON(
            [
                'version' => WP_Theme_JSON::LATEST_SCHEMA,
                'styles'  => [],
            ]
        );
        $reflection = new ReflectionMethod($theme_json, 'process_blocks_custom_css');
        if (PHP_VERSION_ID < 80100) {
            $reflection->setAccessible(true);
        }

        $this->assertSame($expected, $reflection->invoke($theme_json, $input['css'], $input['selector']));
    }

    /**
     * Data provider.
     *
     * @return array[]
     */
    public function data_process_blocks_custom_css()
    {
        return [
            // Simple CSS without any nested selectors.
            'empty css'                    => [
                'input'    => [
                    'selector' => '.foo',
                    'css'      => '',
                ],
                'expected' => '',
            ],
            'no nested selectors'          => [
                'input'    => [
                    'selector' => '.foo',
                    'css'      => 'color: red; margin: auto;',
                ],
                'expected' => ':root :where(.foo){color: red; margin: auto;}',
            ],
            // CSS with nested selectors.
            'with nested selector'         => [
                'input'    => [
                    'selector' => '.foo',
                    'css'      => 'color: red; margin: auto; &.one{color: blue;} & .two{color: green;}',
                ],
                'expected' => ':root :where(.foo){color: red; margin: auto;}:root :where(.foo.one){color: blue;}:root :where(.foo .two){color: green;}',
            ],
            'no root styles'               => [
                'input'    => [
                    'selector' => '.foo',
                    'css'      => '&::before{color: red;}',
                ],
                'expected' => ':root :where(.foo)::before{color: red;}',
            ],
            // CSS with pseudo elements.
            'with pseudo elements'         => [
                'input'    => [
                    'selector' => '.foo',
                    'css'      => 'color: red; margin: auto; &::before{color: blue;} & ::before{color: green;}  &.one::before{color: yellow;} & .two::before{color: purple;}',
                ],
                'expected' => ':root :where(.foo){color: red; margin: auto;}:root :where(.foo)::before{color: blue;}:root :where(.foo) ::before{color: green;}:root :where(.foo.one)::before{color: yellow;}:root :where(.foo .two)::before{color: purple;}',
            ],
            // CSS with multiple root selectors.
            'with multiple root selectors' => [
                'input'    => [
                    'selector' => '.foo, .bar',
                    'css'      => 'color: red; margin: auto; &.one{color: blue;} & .two{color: green;} &::before{color: yellow;} & ::before{color: purple;}  &.three::before{color: orange;} & .four::before{color: skyblue;}',
                ],
                'expected' => ':root :where(.foo, .bar){color: red; margin: auto;}:root :where(.foo.one, .bar.one){color: blue;}:root :where(.foo .two, .bar .two){color: green;}:root :where(.foo, .bar)::before{color: yellow;}:root :where(.foo, .bar) ::before{color: purple;}:root :where(.foo.three, .bar.three)::before{color: orange;}:root :where(.foo .four, .bar .four)::before{color: skyblue;}',
            ],
        ];
    }

    public function test_internal_syntax_is_converted_to_css_variables()
    {
        $result = new WP_Theme_JSON(
            [
                'version' => WP_Theme_JSON::LATEST_SCHEMA,
                'styles'  => [
                    'color'    => [
                        'background' => 'var:preset|color|primary',
                        'text'       => 'var(--wp--preset--color--secondary)',
                    ],
                    'elements' => [
                        'link' => [
                            'color' => [
                                'background' => 'var:preset|color|pri',
                                'text'       => 'var(--wp--preset--color--sec)',
                            ],
                        ],
                    ],
                    'blocks'   => [
                        'core/post-terms' => [
                            'typography' => [ 'fontSize' => 'var(--wp--preset--font-size--small)' ],
                            'color'      => [ 'background' => 'var:preset|color|secondary' ],
                        ],
                        'core/navigation' => [
                            'elements' => [
                                'link' => [
                                    'color' => [
                                        'background' => 'var:preset|color|p',
                                        'text'       => 'var(--wp--preset--color--s)',
                                    ],
                                ],
                            ],
                        ],
                        'core/quote'      => [
                            'typography' => [ 'fontSize' => 'var(--wp--preset--font-size--d)' ],
                            'color'      => [ 'background' => 'var:preset|color|d' ],
                            'variations' => [
                                'plain' => [
                                    'typography' => [ 'fontSize' => 'var(--wp--preset--font-size--s)' ],
                                    'color'      => [ 'background' => 'var:preset|color|s' ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );
        $styles = $result->get_raw_data()['styles'];

        $this->assertSame('var(--wp--preset--color--primary)', $styles['color']['background'], 'Top level: Assert the originally correct values are still correct.');
        $this->assertSame('var(--wp--preset--color--secondary)', $styles['color']['text'], 'Top level: Assert the originally correct values are still correct.');

        $this->assertSame('var(--wp--preset--color--pri)', $styles['elements']['link']['color']['background'], 'Element top level: Assert the originally correct values are still correct.');
        $this->assertSame('var(--wp--preset--color--sec)', $styles['elements']['link']['color']['text'], 'Element top level: Assert the originally correct values are still correct.');

        $this->assertSame('var(--wp--preset--font-size--small)', $styles['blocks']['core/post-terms']['typography']['fontSize'], 'Top block level: Assert the originally correct values are still correct.');
        $this->assertSame('var(--wp--preset--color--secondary)', $styles['blocks']['core/post-terms']['color']['background'], 'Top block level: Assert the internal variables are convert to CSS custom variables.');

        $this->assertSame('var(--wp--preset--color--p)', $styles['blocks']['core/navigation']['elements']['link']['color']['background'], 'Elements block level: Assert the originally correct values are still correct.');
        $this->assertSame('var(--wp--preset--color--s)', $styles['blocks']['core/navigation']['elements']['link']['color']['text'], 'Elements block level: Assert the originally correct values are still correct.');

        $this->assertSame('var(--wp--preset--font-size--s)', $styles['blocks']['core/quote']['variations']['plain']['typography']['fontSize'], 'Style variations: Assert the originally correct values are still correct.');
        $this->assertSame('var(--wp--preset--color--s)', $styles['blocks']['core/quote']['variations']['plain']['color']['background'], 'Style variations: Assert the internal variables are convert to CSS custom variables.');
    }

    /**
     * Tests that the theme.json file is correctly parsed and the variables are resolved.
     *
     * @ticket 58588
     * @ticket 60613
     *
     * @covers WP_Theme_JSON::resolve_variables
     * @covers WP_Theme_JSON::convert_variables_to_value
     */
    public function test_resolve_variables()
    {
        $primary_color   = '#9DFF20';
        $secondary_color = '#9DFF21';
        $contrast_color  = '#000';
        $raw_color_value = '#efefef';
        $large_font      = '18px';
        $small_font      = '12px';
        $spacing         = 'clamp(1.5rem, 5vw, 2rem)';
        $theme_json      = new WP_Theme_JSON(
            [
                'version'  => WP_Theme_JSON::LATEST_SCHEMA,
                'settings' => [
                    'color'      => [
                        'palette' => [
                            'theme' => [
                                [
                                    'color' => $primary_color,
                                    'name'  => 'Primary',
                                    'slug'  => 'primary',
                                ],
                                [
                                    'color' => $secondary_color,
                                    'name'  => 'Secondary',
                                    'slug'  => 'secondary',
                                ],
                                [
                                    'color' => $contrast_color,
                                    'name'  => 'Contrast',
                                    'slug'  => 'contrast',
                                ],
                            ],
                        ],
                    ],
                    'typography' => [
                        'fontSizes' => [
                            [
                                'size' => $small_font,
                                'name' => 'Font size small',
                                'slug' => 'small',
                            ],
                            [
                                'size' => $large_font,
                                'name' => 'Font size large',
                                'slug' => 'large',
                            ],
                        ],
                    ],
                    'spacing'    => [
                        'spacingSizes' => [
                            [
                                'size' => $spacing,
                                'name' => '100',
                                'slug' => '100',
                            ],
                        ],
                    ],
                ],
                'styles'   => [
                    'color'    => [
                        'background' => 'var(--wp--preset--color--primary)',
                        'text'       => $raw_color_value,
                    ],
                    'elements' => [
                        'button' => [
                            'color'      => [
                                'text' => 'var(--wp--preset--color--contrast)',
                            ],
                            'typography' => [
                                'fontSize' => 'var(--wp--preset--font-size--small)',
                            ],
                        ],
                    ],
                    'blocks'   => [
                        'core/post-terms'      => [
                            'typography' => [ 'fontSize' => 'var(--wp--preset--font-size--small)' ],
                            'color'      => [ 'background' => $raw_color_value ],
                        ],
                        'core/more'            => [
                            'typography' => [ 'fontSize' => 'var(--undefined--font-size--small)' ],
                            'color'      => [ 'background' => 'linear-gradient(90deg, var(--wp--preset--color--primary) 0%, var(--wp--preset--color--secondary) 35%, var(--wp--undefined--color--secondary) 100%)' ],
                        ],
                        'core/comment-content' => [
                            'typography' => [ 'fontSize' => 'calc(var(--wp--preset--font-size--small, 12px) + 20px)' ],
                            'color'      => [
                                'text'       => 'var(--wp--preset--color--primary, red)',
                                'background' => 'var(--wp--preset--color--primary, var(--wp--preset--font-size--secondary))',
                                'link'       => 'var(--undefined--color--primary, var(--wp--preset--font-size--secondary))',
                            ],
                        ],
                        'core/comments'        => [
                            'color' => [
                                'text'       => 'var(--undefined--color--primary, var(--wp--preset--font-size--small))',
                                'background' => 'var(--wp--preset--color--primary, var(--undefined--color--primary))',
                            ],
                        ],
                        'core/navigation'      => [
                            'elements' => [
                                'link' => [
                                    'color'      => [
                                        'background' => 'var(--wp--preset--color--primary)',
                                        'text'       => 'var(--wp--preset--color--secondary)',
                                    ],
                                    'typography' => [
                                        'fontSize' => 'var(--wp--preset--font-size--large)',
                                    ],
                                ],
                            ],
                        ],
                        'core/quote'           => [
                            'typography' => [ 'fontSize' => 'var(--wp--preset--font-size--large)' ],
                            'color'      => [ 'background' => 'var(--wp--preset--color--primary)' ],
                            'variations' => [
                                'plain' => [
                                    'typography' => [ 'fontSize' => 'var(--wp--preset--font-size--small)' ],
                                    'color'      => [ 'background' => 'var(--wp--preset--color--secondary)' ],
                                ],
                            ],
                        ],
                        'core/post-template'   => [
                            'spacing' => [
                                'blockGap' => null,
                            ],
                        ],
                        'core/columns'         => [
                            'spacing' => [
                                'blockGap' => 'var(--wp--preset--spacing--100)',
                            ],
                        ],
                    ],
                ],
            ]
        );

        $styles = $theme_json::resolve_variables($theme_json)->get_raw_data()['styles'];

        $this->assertSame($primary_color, $styles['color']['background'], 'Top level: Assert values are converted');
        $this->assertSame($raw_color_value, $styles['color']['text'], 'Top level: Assert raw values stay intact');

        $this->assertSame($contrast_color, $styles['elements']['button']['color']['text'], 'Elements: color');
        $this->assertSame($small_font, $styles['elements']['button']['typography']['fontSize'], 'Elements: font-size');

        $this->assertSame($large_font, $styles['blocks']['core/quote']['typography']['fontSize'], 'Blocks: font-size');
        $this->assertSame($primary_color, $styles['blocks']['core/quote']['color']['background'], 'Blocks: color');
        $this->assertSame($raw_color_value, $styles['blocks']['core/post-terms']['color']['background'], 'Blocks: Raw color value stays intact');
        $this->assertSame($small_font, $styles['blocks']['core/post-terms']['typography']['fontSize'], 'Block core/post-terms: font-size');
        $this->assertSame(
            "linear-gradient(90deg, $primary_color 0%, $secondary_color 35%, var(--wp--undefined--color--secondary) 100%)",
            $styles['blocks']['core/more']['color']['background'],
            'Blocks: multiple colors and undefined color'
        );
        $this->assertSame('var(--undefined--font-size--small)', $styles['blocks']['core/more']['typography']['fontSize'], 'Blocks: undefined font-size ');
        $this->assertSame("calc($small_font + 20px)", $styles['blocks']['core/comment-content']['typography']['fontSize'], 'Blocks: font-size in random place');
        $this->assertSame($primary_color, $styles['blocks']['core/comment-content']['color']['text'], 'Blocks: text color with fallback');
        $this->assertSame($primary_color, $styles['blocks']['core/comment-content']['color']['background'], 'Blocks: background color with var as fallback');
        $this->assertSame($primary_color, $styles['blocks']['core/navigation']['elements']['link']['color']['background'], 'Block element: background color');
        $this->assertSame($secondary_color, $styles['blocks']['core/navigation']['elements']['link']['color']['text'], 'Block element: text color');
        $this->assertSame($large_font, $styles['blocks']['core/navigation']['elements']['link']['typography']['fontSize'], 'Block element: font-size');

        $this->assertSame(
            "var(--undefined--color--primary, $small_font)",
            $styles['blocks']['core/comments']['color']['text'],
            'Blocks: text color with undefined var and fallback'
        );
        $this->assertSame(
            $primary_color,
            $styles['blocks']['core/comments']['color']['background'],
            'Blocks: background color with variable and undefined fallback'
        );

        $this->assertSame($small_font, $styles['blocks']['core/quote']['variations']['plain']['typography']['fontSize'], 'Block variations: font-size');
        $this->assertSame($secondary_color, $styles['blocks']['core/quote']['variations']['plain']['color']['background'], 'Block variations: color');
        /*
         * As with wp_get_global_styles(), WP_Theme_JSON::resolve_variables may be called with merged data from
         * WP_Theme_JSON_Resolver. WP_Theme_JSON_Resolver::get_block_data() sets blockGap for supported blocks to `null` if the value is not defined.
         */
        $this->assertNull(
            $styles['blocks']['core/post-template']['spacing']['blockGap'],
            'Blocks: Post Template spacing.blockGap should be null'
        );
        $this->assertSame(
            $spacing,
            $styles['blocks']['core/columns']['spacing']['blockGap'],
            'Blocks: Columns spacing.blockGap should match'
        );
    }

    /**
     * Tests the correct application of a block style variation's selector to
     * a block's selector.
     *
     * @ticket 60453
     *
     * @dataProvider data_get_block_style_variation_selector
     *
     * @param string $selector  CSS selector.
     * @param string $expected  Expected block style variation CSS selector.
     */
    public function test_get_block_style_variation_selector($selector, $expected)
    {
        $theme_json = new ReflectionClass('WP_Theme_JSON');

        $func = $theme_json->getMethod('get_block_style_variation_selector');
        if (PHP_VERSION_ID < 80100) {
            $func->setAccessible(true);
        }

        $actual = $func->invoke(null, 'custom', $selector);

        $this->assertSame($expected, $actual);
    }

    /**
     * Data provider for generating block style variation selectors.
     *
     * @return array[]
     */
    public function data_get_block_style_variation_selector()
    {
        return [
            'empty block selector'     => [
                'selector' => '',
                'expected' => '.is-style-custom',
            ],
            'class selector'           => [
                'selector' => '.wp-block',
                'expected' => '.wp-block.is-style-custom',
            ],
            'id selector'              => [
                'selector' => '#wp-block',
                'expected' => '#wp-block.is-style-custom',
            ],
            'element tag selector'     => [
                'selector' => 'p',
                'expected' => 'p.is-style-custom',
            ],
            'attribute selector'       => [
                'selector' => '[style*="color"]',
                'expected' => '[style*="color"].is-style-custom',
            ],
            'descendant selector'      => [
                'selector' => '.wp-block .inner',
                'expected' => '.wp-block.is-style-custom .inner',
            ],
            'comma separated selector' => [
                'selector' => '.wp-block .inner, .wp-block .alternative',
                'expected' => '.wp-block.is-style-custom .inner, .wp-block.is-style-custom .alternative',
            ],
            'pseudo selector'          => [
                'selector' => 'div:first-child',
                'expected' => 'div.is-style-custom:first-child',
            ],
            ':is selector'             => [
                'selector' => '.wp-block:is(.outer .inner:first-child)',
                'expected' => '.wp-block.is-style-custom:is(.outer .inner:first-child)',
            ],
            ':not selector'            => [
                'selector' => '.wp-block:not(.outer .inner:first-child)',
                'expected' => '.wp-block.is-style-custom:not(.outer .inner:first-child)',
            ],
            ':has selector'            => [
                'selector' => '.wp-block:has(.outer .inner:first-child)',
                'expected' => '.wp-block.is-style-custom:has(.outer .inner:first-child)',
            ],
            ':where selector'          => [
                'selector' => '.wp-block:where(.outer .inner:first-child)',
                'expected' => '.wp-block.is-style-custom:where(.outer .inner:first-child)',
            ],
            'wrapping :where selector' => [
                'selector' => ':where(.outer .inner:first-child)',
                'expected' => ':where(.outer.is-style-custom .inner:first-child)',
            ],
            'complex'                  => [
                'selector' => '.wp:where(.something):is(.test:not(.nothing p)):has(div[style]) .content, .wp:where(.nothing):not(.test:is(.something div)):has(span[style]) .inner',
                'expected' => '.wp.is-style-custom:where(.something):is(.test:not(.nothing p)):has(div[style]) .content, .wp.is-style-custom:where(.nothing):not(.test:is(.something div)):has(span[style]) .inner',
            ],
        ];
    }

    /**
     * Tests the correct scoping of selectors for a style node.
     *
     * @ticket 61119
     */
    public function test_scope_style_node_selectors()
    {
        $theme_json = new ReflectionClass('WP_Theme_JSON');

        $func = $theme_json->getMethod('scope_style_node_selectors');
        if (PHP_VERSION_ID < 80100) {
            $func->setAccessible(true);
        }

        $node = [
            'name'      => 'core/image',
            'path'      => [ 'styles', 'blocks', 'core/image' ],
            'selector'  => '.wp-block-image',
            'selectors' => [
                'root'       => '.wp-block-image',
                'border'     => '.wp-block-image img, .wp-block-image .wp-block-image__crop-area, .wp-block-image .components-placeholder',
                'typography' => [
                    'textDecoration' => '.wp-block-image caption',
                ],
                'filter'     => [
                    'duotone' => '.wp-block-image img, .wp-block-image .components-placeholder',
                ],
            ],
        ];

        $actual   = $func->invoke(null, '.custom-scope', $node);
        $expected = [
            'name'      => 'core/image',
            'path'      => [ 'styles', 'blocks', 'core/image' ],
            'selector'  => '.custom-scope .wp-block-image',
            'selectors' => [
                'root'       => '.custom-scope .wp-block-image',
                'border'     => '.custom-scope .wp-block-image img, .custom-scope .wp-block-image .wp-block-image__crop-area, .custom-scope .wp-block-image .components-placeholder',
                'typography' => [
                    'textDecoration' => '.custom-scope .wp-block-image caption',
                ],
                'filter'     => [
                    'duotone' => '.custom-scope .wp-block-image img, .custom-scope .wp-block-image .components-placeholder',
                ],
            ],
        ];

        $this->assertSame($expected, $actual);
    }

    /**
     * Block style variations styles aren't generated by default. This test covers
     * the `get_block_nodes` does not include variations by default, preventing
     * the inclusion of their styles.
     *
     * @ticket 61443
     */
    public function test_opt_out_of_block_style_variations_by_default()
    {
        $theme_json = new ReflectionClass('WP_Theme_JSON');

        $func = $theme_json->getMethod('get_block_nodes');
        if (PHP_VERSION_ID < 80100) {
            $func->setAccessible(true);
        }

        $theme_json = [
            'version' => WP_Theme_JSON::LATEST_SCHEMA,
            'styles'  => [
                'blocks' => [
                    'core/button' => [
                        'variations' => [
                            'outline' => [
                                'color' => [
                                    'background' => 'red',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $selectors  = [];

        $block_nodes       = $func->invoke(null, $theme_json, $selectors);
        $button_variations = $block_nodes[0]['variations'] ?? [];

        $this->assertSame([], $button_variations);
    }

    /**
     * Block style variations styles aren't generated by default. This test ensures
     * variations are included by `get_block_nodes` when requested.
     *
     * @ticket 61443
     */
    public function test_opt_in_to_block_style_variations()
    {
        $theme_json = new ReflectionClass('WP_Theme_JSON');

        $func = $theme_json->getMethod('get_block_nodes');
        if (PHP_VERSION_ID < 80100) {
            $func->setAccessible(true);
        }

        $theme_json = [
            'version' => WP_Theme_JSON::LATEST_SCHEMA,
            'styles'  => [
                'blocks' => [
                    'core/button' => [
                        'variations' => [
                            'outline' => [
                                'color' => [
                                    'background' => 'red',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $selectors  = [];
        $options    = [ 'include_block_style_variations' => true ];

        $block_nodes       = $func->invoke(null, $theme_json, $selectors, $options);
        $button_variations = $block_nodes[0]['variations'] ?? [];

        $expected = [
            [
                'path'     => [ 'styles', 'blocks', 'core/button', 'variations', 'outline' ],
                'selector' => '.wp-block-button.is-style-outline .wp-block-button__link',
            ],
        ];

        $this->assertSame($expected, $button_variations);
    }

    /**
     * Tests that block-level settings inherit global default settings when not explicitly set.
     *
     * When a block doesn't have its own default presets setting, it should inherit
     * the global setting from the theme. This affects whether default presets
     * are filtered out during merging.
     *
     * @ticket 64195
     */
    public function test_merge_incoming_data_block_level_inherits_global_default_setting()
    {
        $defaults = new WP_Theme_JSON(
            [
                'version'  => WP_Theme_JSON::LATEST_SCHEMA,
                'settings' => [
                    'color' => [
                        'defaultDuotone' => true,
                        'duotone'        => [
                            [
                                'slug'   => 'dark-grayscale',
                                'colors' => [ '#000000', '#7f7f7f' ],
                                'name'   => 'Default Dark grayscale',
                            ],
                        ],
                    ],
                ],
            ],
            'default'
        );
        $theme    = new WP_Theme_JSON(
            [
                'version'  => WP_Theme_JSON::LATEST_SCHEMA,
                'settings' => [
                    'color'  => [
                        'defaultDuotone' => false,
                    ],
                    'blocks' => [
                        'core/image' => [
                            'color' => [
                                // No defaultDuotone setting - should inherit global (false) set by theme.
                                'duotone' => [
                                    [
                                        'slug'   => 'dark-grayscale',
                                        'colors' => [ '#000000', '#7f7f7f' ],
                                        'name'   => 'Theme Dark grayscale',
                                    ],
                                ],
                            ],
                        ],
                        'core/cover' => [
                            'color' => [
                                'defaultDuotone' => true, // Explicitly enabled at block level
                                'duotone'        => [
                                    [
                                        'slug'   => 'dark-grayscale',
                                        'colors' => [ '#000000', '#7f7f7f' ],
                                        'name'   => 'Cover Dark grayscale',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );

        $expected = [
            'version'  => WP_Theme_JSON::LATEST_SCHEMA,
            'settings' => [
                'color'  => [
                    'defaultDuotone' => false,
                    'duotone'        => [
                        'default' => [
                            [
                                'slug'   => 'dark-grayscale',
                                'colors' => [ '#000000', '#7f7f7f' ],
                                'name'   => 'Default Dark grayscale',
                            ],
                        ],
                    ],
                ],
                'blocks' => [
                    'core/image' => [
                        'color' => [
                            'duotone' => [
                                'theme' => [
                                    [
                                        'slug'   => 'dark-grayscale',
                                        'colors' => [ '#000000', '#7f7f7f' ],
                                        'name'   => 'Theme Dark grayscale',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'core/cover' => [
                        'color' => [
                            'defaultDuotone' => true,
                            'duotone'        => [
                                'theme' => [
                                    // Should be filtered out because block-level defaults are enabled
                                    // and slug matches default
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $defaults->merge($theme);
        $actual = $defaults->get_raw_data();

        $this->assertEqualSetsWithIndex($expected, $actual);
    }

    /**
     * Tests that presets with unique slugs are preserved during merging.
     *
     * When merging theme presets, any preset with a slug that doesn't match
     * a default preset should always be preserved, regardless of default
     * preset settings. Only presets with matching slugs should be filtered out
     * when defaults are enabled.
     *
     * @ticket 64195
     */
    public function test_merge_incoming_data_unique_slugs_always_preserved()
    {
        $defaults = new WP_Theme_JSON(
            [
                'version'  => WP_Theme_JSON::LATEST_SCHEMA,
                'settings' => [
                    'color' => [
                        'defaultDuotone' => true, // Defaults enabled
                        'duotone'        => [
                            [
                                'slug'   => 'dark-grayscale',
                                'colors' => [ '#000000', '#7f7f7f' ],
                                'name'   => 'Default Dark grayscale',
                            ],
                        ],
                    ],
                ],
            ],
            'default'
        );
        $theme    = new WP_Theme_JSON(
            [
                'version'  => WP_Theme_JSON::LATEST_SCHEMA,
                'settings' => [
                    'blocks' => [
                        'core/image' => [
                            'color' => [
                                'defaultDuotone' => true, // Block-level defaults enabled
                                'duotone'        => [
                                    [
                                        'slug'   => 'custom-unique',
                                        'colors' => [ '#ff0000', '#00ff00' ],
                                        'name'   => 'Custom Unique',
                                    ],
                                    [
                                        'slug'   => 'dark-grayscale', // Matches default slug
                                        'colors' => [ '#111111', '#888888' ],
                                        'name'   => 'Theme Dark grayscale',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );

        $expected = [
            'version'  => WP_Theme_JSON::LATEST_SCHEMA,
            'settings' => [
                'color'  => [
                    'defaultDuotone' => true,
                    'duotone'        => [
                        'default' => [
                            [
                                'slug'   => 'dark-grayscale',
                                'colors' => [ '#000000', '#7f7f7f' ],
                                'name'   => 'Default Dark grayscale',
                            ],
                        ],
                    ],
                ],
                'blocks' => [
                    'core/image' => [
                        'color' => [
                            'defaultDuotone' => true,
                            'duotone'        => [
                                'theme' => [
                                    [
                                        'slug'   => 'custom-unique', // Should always be preserved
                                        'colors' => [ '#ff0000', '#00ff00' ],
                                        'name'   => 'Custom Unique',
                                    ],
                                    // 'dark-grayscale' should be filtered out due to slug match
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $defaults->merge($theme);
        $actual = $defaults->get_raw_data();

        $this->assertEqualSetsWithIndex($expected, $actual);
    }

    /**
     * Test that block pseudo selectors are processed correctly.
     */
    public function test_block_pseudo_selectors_are_processed()
    {
        $theme_json = new WP_Theme_JSON(
            [
                'version' => WP_Theme_JSON::LATEST_SCHEMA,
                'styles'  => [
                    'blocks' => [
                        'core/button' => [
                            'color'  => [
                                'text'       => 'white',
                                'background' => 'blue',
                            ],
                            ':hover' => [
                                'color' => [
                                    'text'       => 'blue',
                                    'background' => 'white',
                                ],
                            ],
                            ':focus' => [
                                'color' => [
                                    'text'       => 'red',
                                    'background' => 'yellow',
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );

        $expected = ':root :where(.wp-block-button .wp-block-button__link){background-color: blue;color: white;}:root :where(.wp-block-button .wp-block-button__link:hover){background-color: white;color: blue;}:root :where(.wp-block-button .wp-block-button__link:focus){background-color: yellow;color: red;}';
        $this->assertSame($expected, $theme_json->get_stylesheet([ 'styles' ], null, [ 'skip_root_layout_styles' => true ]));
    }

    /**
     * Test that block pseudo selectors are processed correctly within variations.
     */
    public function test_block_variation_pseudo_selectors_are_processed()
    {
        register_block_style(
            'core/button',
            [
                'name'  => 'outline',
                'label' => 'Outline',
            ]
        );

        $theme_json = new WP_Theme_JSON(
            [
                'version' => WP_Theme_JSON::LATEST_SCHEMA,
                'styles'  => [
                    'blocks' => [
                        'core/button' => [
                            'color'      => [
                                'text'       => 'white',
                                'background' => 'blue',
                            ],
                            'variations' => [
                                'outline' => [
                                    'color'  => [
                                        'text'       => 'currentColor',
                                        'background' => 'transparent',
                                    ],
                                    'border' => [
                                        'color' => 'currentColor',
                                        'width' => '1px',
                                        'style' => 'solid',
                                    ],
                                    ':hover' => [
                                        'color' => [
                                            'text'       => 'white',
                                            'background' => 'red',
                                        ],
                                    ],
                                    ':focus' => [
                                        'color' => [
                                            'text'       => 'black',
                                            'background' => 'yellow',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );

        $expected = ':root :where(.wp-block-button .wp-block-button__link){background-color: blue;color: white;}:root :where(.wp-block-button.is-style-outline .wp-block-button__link){background-color: transparent;border-color: currentColor;border-width: 1px;border-style: solid;color: currentColor;}:root :where(.wp-block-button.is-style-outline .wp-block-button__link:hover){background-color: red;color: white;}:root :where(.wp-block-button.is-style-outline .wp-block-button__link:focus){background-color: yellow;color: black;}';
        $actual   = $theme_json->get_stylesheet(
            [ 'styles' ],
            null,
            [
                'skip_root_layout_styles'        => true,
                'include_block_style_variations' => true,
            ]
        );

        unregister_block_style('core/button', 'outline');

        $this->assertSame($expected, $actual);
    }

    /**
     * Test that non-whitelisted pseudo selectors are ignored for blocks.
     */
    public function test_block_pseudo_selectors_ignores_non_whitelisted()
    {
        $theme_json = new WP_Theme_JSON(
            [
                'version' => WP_Theme_JSON::LATEST_SCHEMA,
                'styles'  => [
                    'blocks' => [
                        'core/button' => [
                            'color'     => [
                                'text'       => 'white',
                                'background' => 'blue',
                            ],
                            ':hover'    => [
                                'color' => [
                                    'text'       => 'blue',
                                    'background' => 'white',
                                ],
                            ],
                            ':levitate' => [
                                'color' => [
                                    'text'       => 'yellow',
                                    'background' => 'black',
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );

        $expected = ':root :where(.wp-block-button .wp-block-button__link){background-color: blue;color: white;}:root :where(.wp-block-button .wp-block-button__link:hover){background-color: white;color: blue;}';
        $this->assertSame($expected, $theme_json->get_stylesheet([ 'styles' ], null, [ 'skip_root_layout_styles' => true ]));
        $this->assertStringNotContainsString('.wp-block-button .wp-block-button__link:levitate{', $theme_json->get_stylesheet([ 'styles' ]));
    }

    /**
     * Test that blocks without pseudo selector support ignore pseudo selectors.
     */
    public function test_blocks_without_pseudo_support_ignore_pseudo_selectors()
    {
        $theme_json = new WP_Theme_JSON(
            [
                'version' => WP_Theme_JSON::LATEST_SCHEMA,
                'styles'  => [
                    'blocks' => [
                        'core/paragraph' => [
                            'color'  => [
                                'text' => 'black',
                            ],
                            ':hover' => [
                                'color' => [
                                    'text' => 'red',
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );

        $expected = ':root :where(p){color: black;}';
        $this->assertSame($expected, $theme_json->get_stylesheet([ 'styles' ], null, [ 'skip_root_layout_styles' => true ]));
        $this->assertStringNotContainsString('p:hover{', $theme_json->get_stylesheet([ 'styles' ]));
    }

    /**
     * Test that block pseudo selectors work with elements within blocks.
     */
    public function test_block_pseudo_selectors_with_elements()
    {
        $theme_json = new WP_Theme_JSON(
            [
                'version' => WP_Theme_JSON::LATEST_SCHEMA,
                'styles'  => [
                    'blocks' => [
                        'core/button' => [
                            'color'    => [
                                'text'       => 'white',
                                'background' => 'blue',
                            ],
                            ':hover'   => [
                                'color' => [
                                    'text'       => 'blue',
                                    'background' => 'white',
                                ],
                            ],
                            'elements' => [
                                'button' => [
                                    'color'  => [
                                        'text' => 'green',
                                    ],
                                    ':hover' => [
                                        'color' => [
                                            'text' => 'orange',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );

        $expected = ':root :where(.wp-block-button .wp-block-button__link){background-color: blue;color: white;}:root :where(.wp-block-button .wp-block-button__link:hover){background-color: white;color: blue;}:root :where(.wp-block-button .wp-block-button__link .wp-element-button,.wp-block-button .wp-block-button__link  .wp-block-button__link){color: green;}:root :where(.wp-block-button .wp-block-button__link .wp-element-button:hover,.wp-block-button .wp-block-button__link  .wp-block-button__link:hover){color: orange;}';
        $this->assertSame($expected, $theme_json->get_stylesheet([ 'styles' ], null, [ 'skip_root_layout_styles' => true ]));
    }

    /**
     * @covers WP_Theme_JSON::sanitize
     * @covers WP_Theme_JSON::remove_keys_not_in_schema
     *
     * @ticket 64280
     */
    public function test_sanitize_preserves_boolean_values_when_schema_expects_boolean()
    {
        $theme_json = new WP_Theme_JSON(
            [
                'version'  => WP_Theme_JSON::LATEST_SCHEMA,
                'settings' => [
                    'lightbox' => [
                        'enabled'      => true,
                        'allowEditing' => false,
                    ],
                ],
            ]
        );

        $settings = $theme_json->get_settings();
        $this->assertTrue($settings['lightbox']['enabled'], 'Enabled should be true');
        $this->assertFalse($settings['lightbox']['allowEditing'], 'Allow editing should be false');
    }

    /**
     * @covers WP_Theme_JSON::sanitize
     * @covers WP_Theme_JSON::remove_keys_not_in_schema
     *
     * @ticket 64280
     */
    public function test_sanitize_removes_non_boolean_values_when_schema_expects_boolean()
    {
        $theme_json = new WP_Theme_JSON(
            [
                'version'  => WP_Theme_JSON::LATEST_SCHEMA,
                'settings' => [
                    'lightbox' => [
                        'enabled'      => 'not-a-boolean',
                        'allowEditing' => 123,
                    ],
                ],
            ]
        );

        $settings = $theme_json->get_settings();
        $this->assertArrayNotHasKey('enabled', $settings['lightbox'] ?? [], 'Enabled should be removed');
        $this->assertArrayNotHasKey('allowEditing', $settings['lightbox'] ?? [], 'Allow editing should be removed');
    }

    /**
     * @covers WP_Theme_JSON::sanitize
     * @covers WP_Theme_JSON::remove_keys_not_in_schema
     *
     * @ticket 64280
     */
    public function test_sanitize_preserves_boolean_values_in_block_settings()
    {
        $theme_json = new WP_Theme_JSON(
            [
                'version'  => WP_Theme_JSON::LATEST_SCHEMA,
                'settings' => [
                    'blocks' => [
                        'core/image' => [
                            'lightbox' => [
                                'enabled'      => true,
                                'allowEditing' => false,
                            ],
                        ],
                    ],
                ],
            ]
        );

        $settings = $theme_json->get_settings();
        $this->assertTrue($settings['blocks']['core/image']['lightbox']['enabled'], 'Enabled should be true');
        $this->assertFalse($settings['blocks']['core/image']['lightbox']['allowEditing'], 'Allow editing should be false');
    }

    /**
     * @covers WP_Theme_JSON::sanitize
     * @covers WP_Theme_JSON::remove_keys_not_in_schema
     *
     * @ticket 64280
     */
    public function test_sanitize_removes_non_boolean_values_in_block_settings()
    {
        $theme_json = new WP_Theme_JSON(
            [
                'version'  => WP_Theme_JSON::LATEST_SCHEMA,
                'settings' => [
                    'blocks' => [
                        'core/image' => [
                            'lightbox' => [
                                'enabled'      => 'string-value',
                                'allowEditing' => [ 'not', 'a', 'boolean' ],
                            ],
                        ],
                    ],
                ],
            ]
        );

        $settings = $theme_json->get_settings();
        $lightbox = $settings['blocks']['core/image']['lightbox'] ?? [];
        $this->assertArrayNotHasKey('enabled', $lightbox, 'Enabled should be removed');
        $this->assertArrayNotHasKey('allowEditing', $lightbox, 'Allow editing should be removed');
    }

    /**
     * @covers WP_Theme_JSON::sanitize
     * @covers WP_Theme_JSON::remove_keys_not_in_schema
     *
     * @ticket 64280
     */
    public function test_sanitize_preserves_null_schema_behavior()
    {
        // Test that settings with null in schema (no type validation) still accept any type.
        $theme_json = new WP_Theme_JSON(
            [
                'version'  => WP_Theme_JSON::LATEST_SCHEMA,
                'settings' => [
                    'appearanceTools' => 'string-value', // null in schema, should accept any type.
                    'custom'          => [ 'nested' => 'value' ], // null in schema, should accept any type.
                ],
            ]
        );

        $settings = $theme_json->get_settings();
        $this->assertSame('string-value', $settings['appearanceTools'], 'Appearance tools should be string value');
        $this->assertSame([ 'nested' => 'value' ], $settings['custom'], 'Custom should be array value');
    }
}
