<?php

declare(strict_types=1);
/**
 * Tests for block supports related to layout.
 *
 * @package WordPress
 * @subpackage Block Supports
 * @since 6.0.0
 *
 * @group block-supports
 *
 * @covers ::wp_restore_image_outer_container
 */
class Tests_Block_Supports_Layout extends WP_UnitTestCase
{
    /**
     * Theme root directory.
     *
     * @var string
     */
    private $theme_root;

    /**
     * Original theme directory.
     *
     * @var string
     */
    private $orig_theme_dir;

    public function set_up()
    {
        parent::set_up();
        $this->theme_root     = realpath(DIR_TESTDATA . '/themedir1');
        $this->orig_theme_dir = $GLOBALS['wp_theme_directories'];

        // /themes is necessary as theme.php functions assume /themes is the root if there is only one root.
        $GLOBALS['wp_theme_directories'] = [ WP_CONTENT_DIR . '/themes', $this->theme_root ];

        // Set up the new root.
        add_filter('theme_root', [ $this, 'filter_set_theme_root' ]);
        add_filter('stylesheet_root', [ $this, 'filter_set_theme_root' ]);
        add_filter('template_root', [ $this, 'filter_set_theme_root' ]);

        // Clear caches.
        wp_clean_themes_cache();
        unset($GLOBALS['wp_themes']);

        /*
         * Register a style variation with a custom blockGap value for testing.
         */
        register_block_style(
            'core/group',
            [
                'name'       => 'custom-gap',
                'label'      => 'Custom Gap',
                'style_data' => [
                    'spacing' => [
                        'blockGap' => '99px',
                    ],
                ],
            ]
        );
    }

    public function tear_down()
    {
        $GLOBALS['wp_theme_directories'] = $this->orig_theme_dir;

        // Clear up the filters to modify the theme root.
        remove_filter('theme_root', [ $this, 'filter_set_theme_root' ]);
        remove_filter('stylesheet_root', [ $this, 'filter_set_theme_root' ]);
        remove_filter('template_root', [ $this, 'filter_set_theme_root' ]);

        wp_clean_themes_cache();
        unset($GLOBALS['wp_themes']);

        // Clean up variation test data.
        unregister_block_style('core/group', 'custom-gap');
        WP_Theme_JSON_Resolver::clean_cached_data();

        parent::tear_down();
    }

    public function filter_set_theme_root()
    {
        return $this->theme_root;
    }

    /**
     * @ticket 55505
     */
    public function test_outer_container_not_restored_for_non_aligned_image_block_with_non_themejson_theme()
    {
        // The "default" theme doesn't have theme.json support.
        switch_theme('default');
        $block         = [
            'blockName' => 'core/image',
            'attrs'     => [],
        ];
        $block_content = '<figure class="wp-block-image size-full"><img src="/my-image.jpg"/></figure>';
        $expected      = '<figure class="wp-block-image size-full"><img src="/my-image.jpg"/></figure>';

        $this->assertEqualHTML($expected, wp_restore_image_outer_container($block_content, $block));
    }

    /**
     * @ticket 55505
     */
    public function test_outer_container_restored_for_aligned_image_block_with_non_themejson_theme()
    {
        // The "default" theme doesn't have theme.json support.
        switch_theme('default');
        $block         = [
            'blockName' => 'core/image',
            'attrs'     => [],
        ];
        $block_content = '<figure class="wp-block-image alignright size-full"><img src="/my-image.jpg"/></figure>';
        $expected      = '<div class="wp-block-image"><figure class="alignright size-full"><img src="/my-image.jpg"/></figure></div>';

        $this->assertEqualHTML($expected, wp_restore_image_outer_container($block_content, $block));
    }

    /**
     * @ticket 55505
     *
     * @dataProvider data_block_image_html_restored_outer_container
     *
     * @param string $block_image_html The block image HTML passed to `wp_restore_image_outer_container`.
     * @param string $expected         The expected block image HTML.
     */
    public function test_additional_styles_moved_to_restored_outer_container_for_aligned_image_block_with_non_themejson_theme($block_image_html, $expected)
    {
        // The "default" theme doesn't have theme.json support.
        switch_theme('default');
        $block = [
            'blockName' => 'core/image',
            'attrs'     => [
                'className' => 'is-style-round my-custom-classname',
            ],
        ];

        $this->assertEqualHTML($expected, wp_restore_image_outer_container($block_image_html, $block));
    }

    /**
     * Data provider for test_additional_styles_moved_to_restored_outer_container_for_aligned_image_block_with_non_themejson_theme().
     *
     * @return array {
     *     @type array {
     *         @type string $block_image_html The block image HTML passed to `wp_restore_image_outer_container`.
     *         @type string $expected         The expected block image HTML.
     *     }
     * }
     */
    public function data_block_image_html_restored_outer_container()
    {
        $expected = '<div class="wp-block-image is-style-round my-custom-classname"><figure class="alignright size-full"><img src="/my-image.jpg"/></figure></div>';

        return [
            [
                '<figure class="wp-block-image alignright size-full is-style-round my-custom-classname"><img src="/my-image.jpg"/></figure>',
                $expected,
            ],
            [
                '<figure class="is-style-round my-custom-classname wp-block-image alignright size-full"><img src="/my-image.jpg"/></figure>',
                $expected,
            ],
            [
                '<figure class="wp-block-image is-style-round my-custom-classname alignright size-full"><img src="/my-image.jpg"/></figure>',
                $expected,
            ],
            [
                '<figure class="is-style-round wp-block-image alignright my-custom-classname size-full"><img src="/my-image.jpg"/></figure>',
                $expected,
            ],
            [
                '<figure style="color: red" class=\'is-style-round wp-block-image alignright my-custom-classname size-full\' data-random-tag=">"><img src="/my-image.jpg"/></figure>',
                '<div class="wp-block-image is-style-round my-custom-classname"><figure style="color: red" class=\'alignright size-full\' data-random-tag=">"><img src="/my-image.jpg"/></figure></div>',
            ],
        ];
    }

    /**
     * @ticket 55505
     */
    public function test_outer_container_not_restored_for_aligned_image_block_with_themejson_theme()
    {
        switch_theme('block-theme');
        $block         = [
            'blockName' => 'core/image',
            'attrs'     => [
                'className' => 'is-style-round my-custom-classname',
            ],
        ];
        $block_content = '<figure class="wp-block-image alignright size-full is-style-round my-custom-classname"><img src="/my-image.jpg"/></figure>';
        $expected      = '<figure class="wp-block-image alignright size-full is-style-round my-custom-classname"><img src="/my-image.jpg"/></figure>';

        $this->assertEqualHTML($expected, wp_restore_image_outer_container($block_content, $block));
    }

    /**
     * @ticket 57584
     * @ticket 58548
     * @ticket 60292
     * @ticket 61111
     *
     * @dataProvider data_layout_support_flag_renders_classnames_on_wrapper
     *
     * @covers ::wp_render_layout_support_flag
     *
     * @param array  $args            Dataset to test.
     * @param string $expected_output The expected output.
     */
    public function test_layout_support_flag_renders_classnames_on_wrapper($args, $expected_output)
    {
        switch_theme('default');
        $actual_output = wp_render_layout_support_flag($args['block_content'], $args['block']);
        $this->assertEqualHTML($expected_output, $actual_output);
    }

    /**
     * Data provider for test_layout_support_flag_renders_classnames_on_wrapper.
     *
     * @return array
     */
    public function data_layout_support_flag_renders_classnames_on_wrapper()
    {
        return [
            'single wrapper block layout with flow type'   => [
                'args'            => [
                    'block_content' => '<div class="wp-block-group"></div>',
                    'block'         => [
                        'blockName'    => 'core/group',
                        'attrs'        => [
                            'layout' => [
                                'type' => 'default',
                            ],
                        ],
                        'innerBlocks'  => [],
                        'innerHTML'    => '<div class="wp-block-group"></div>',
                        'innerContent' => [
                            '<div class="wp-block-group"></div>',
                        ],
                    ],
                ],
                'expected_output' => '<div class="wp-block-group is-layout-flow wp-block-group-is-layout-flow"></div>',
            ],
            'single wrapper block layout with constrained type' => [
                'args'            => [
                    'block_content' => '<div class="wp-block-group"></div>',
                    'block'         => [
                        'blockName'    => 'core/group',
                        'attrs'        => [
                            'layout' => [
                                'type' => 'constrained',
                            ],
                        ],
                        'innerBlocks'  => [],
                        'innerHTML'    => '<div class="wp-block-group"></div>',
                        'innerContent' => [
                            '<div class="wp-block-group"></div>',
                        ],
                    ],
                ],
                'expected_output' => '<div class="wp-block-group is-layout-constrained wp-block-group-is-layout-constrained"></div>',
            ],
            'multiple wrapper block layout with flow type' => [
                'args'            => [
                    'block_content' => '<div class="wp-block-group"><div class="wp-block-group__inner-wrapper"></div></div>',
                    'block'         => [
                        'blockName'    => 'core/group',
                        'attrs'        => [
                            'layout' => [
                                'type' => 'default',
                            ],
                        ],
                        'innerBlocks'  => [],
                        'innerHTML'    => '<div class="wp-block-group"><div class="wp-block-group__inner-wrapper"></div></div>',
                        'innerContent' => [
                            '<div class="wp-block-group"><div class="wp-block-group__inner-wrapper">',
                            ' ',
                            ' </div></div>',
                        ],
                    ],
                ],
                'expected_output' => '<div class="wp-block-group"><div class="wp-block-group__inner-wrapper is-layout-flow wp-block-group-is-layout-flow"></div></div>',
            ],
            'block with child layout'                      => [
                'args'            => [
                    'block_content' => '<p>Some text.</p>',
                    'block'         => [
                        'blockName'    => 'core/paragraph',
                        'attrs'        => [
                            'style' => [
                                'layout' => [
                                    'columnSpan' => '2',
                                ],
                            ],
                        ],
                        'innerBlocks'  => [],
                        'innerHTML'    => '<p>Some text.</p>',
                        'innerContent' => [
                            '<p>Some text.</p>',
                        ],
                    ],
                ],
                'expected_output' => '<p class="wp-container-content-b7aa651c">Some text.</p>',
            ],
            'single wrapper block layout with flex type'   => [
                'args'            => [
                    'block_content' => '<div class="wp-block-group"></div>',
                    'block'         => [
                        'blockName'    => 'core/group',
                        'attrs'        => [
                            'layout' => [
                                'type'        => 'flex',
                                'orientation' => 'horizontal',
                                'flexWrap'    => 'nowrap',
                            ],
                        ],
                        'innerBlocks'  => [],
                        'innerHTML'    => '<div class="wp-block-group"></div>',
                        'innerContent' => [
                            '<div class="wp-block-group"></div>',
                        ],
                    ],
                ],
                'expected_output' => '<div class="wp-block-group is-horizontal is-nowrap is-layout-flex wp-container-core-group-is-layout-ee7b5020 wp-block-group-is-layout-flex"></div>',
            ],
            'single wrapper block layout with grid type'   => [
                'args'            => [
                    'block_content' => '<div class="wp-block-group"></div>',
                    'block'         => [
                        'blockName'    => 'core/group',
                        'attrs'        => [
                            'layout' => [
                                'type' => 'grid',
                            ],
                        ],
                        'innerBlocks'  => [],
                        'innerHTML'    => '<div class="wp-block-group"></div>',
                        'innerContent' => [
                            '<div class="wp-block-group"></div>',
                        ],
                    ],
                ],
                'expected_output' => '<div class="wp-block-group is-layout-grid wp-container-core-group-is-layout-9d260ee2 wp-block-group-is-layout-grid"></div>',
            ],
            'skip classname output if block does not support layout and there are no child layout classes to be output' => [
                'args'            => [
                    'block_content' => '<p>A paragraph</p>',
                    'block'         => [
                        'blockName'    => 'core/paragraph',
                        'attrs'        => [
                            'style' => [
                                'layout' => [
                                    'selfStretch' => 'fit',
                                ],
                            ],
                        ],
                        'innerBlocks'  => [],
                        'innerHTML'    => '<p>A paragraph</p>',
                        'innerContent' => [ '<p>A paragraph</p>' ],
                    ],
                ],
                'expected_output' => '<p>A paragraph</p>',
            ],
        ];
    }

    /**
     * Check that wp_restore_group_inner_container() restores the legacy inner container on the Group block.
     *
     * @ticket 60130
     *
     * @covers ::wp_restore_group_inner_container
     *
     * @dataProvider data_restore_group_inner_container
     *
     * @param array  $args            Dataset to test.
     * @param string $expected_output The expected output.
     */
    public function test_restore_group_inner_container($args, $expected_output)
    {
        $actual_output = wp_restore_group_inner_container($args['block_content'], $args['block']);
        $this->assertSame($expected_output, $actual_output);
    }

    /**
     * Data provider for test_restore_group_inner_container.
     *
     * @return array
     */
    public function data_restore_group_inner_container()
    {
        return [
            'group block with existing inner container'    => [
                'args'            => [
                    'block_content' => '<div class="wp-block-group"><div class="wp-block-group__inner-container"></div></div>',
                    'block'         => [
                        'blockName'    => 'core/group',
                        'attrs'        => [
                            'layout' => [
                                'type' => 'default',
                            ],
                        ],
                        'innerBlocks'  => [],
                        'innerHTML'    => '<div class="wp-block-group"><div class="wp-block-group__inner-container"></div></div>',
                        'innerContent' => [
                            '<div class="wp-block-group"><div class="wp-block-group__inner-container">',
                            ' ',
                            ' </div></div>',
                        ],
                    ],
                ],
                'expected_output' => '<div class="wp-block-group"><div class="wp-block-group__inner-container"></div></div>',
            ],
            'group block with no existing inner container' => [
                'args'            => [
                    'block_content' => '<div class="wp-block-group"></div>',
                    'block'         => [
                        'blockName'    => 'core/group',
                        'attrs'        => [
                            'layout' => [
                                'type' => 'default',
                            ],
                        ],
                        'innerBlocks'  => [],
                        'innerHTML'    => '<div class="wp-block-group"></div>',
                        'innerContent' => [
                            '<div class="wp-block-group">',
                            ' ',
                            ' </div>',
                        ],
                    ],
                ],
                'expected_output' => '<div class="wp-block-group"><div class="wp-block-group__inner-container"></div></div>',
            ],
            'group block with layout classnames'           => [
                'args'            => [
                    'block_content' => '<div class="wp-block-group is-layout-constrained wp-block-group-is-layout-constrained"></div>',
                    'block'         => [
                        'blockName'    => 'core/group',
                        'attrs'        => [
                            'layout' => [
                                'type' => 'default',
                            ],
                        ],
                        'innerBlocks'  => [],
                        'innerHTML'    => '<div class="wp-block-group"></div>',
                        'innerContent' => [
                            '<div class="wp-block-group">',
                            ' ',
                            ' </div>',
                        ],
                    ],
                ],
                'expected_output' => '<div class="wp-block-group"><div class="wp-block-group__inner-container is-layout-constrained wp-block-group-is-layout-constrained"></div></div>',
            ],
        ];
    }

    /**
     * Checks that `wp_add_parent_layout_to_parsed_block` adds the parent layout attribute to the block object.
     *
     * @ticket 61111
     *
     * @covers ::wp_add_parent_layout_to_parsed_block
     *
     * @dataProvider data_wp_add_parent_layout_to_parsed_block
     *
     * @param array    $block        The block object.
     * @param WP_Block $parent_block The parent block object.
     * @param array    $expected     The expected block object.
     */
    public function test_wp_add_parent_layout_to_parsed_block($block, $parent_block, $expected)
    {
        $actual = wp_add_parent_layout_to_parsed_block($block, [], $parent_block);
        $this->assertSame($expected, $actual);
    }

    /**
     * Data provider for test_wp_add_parent_layout_to_parsed_block.
     *
     * @return array
     */
    public function data_wp_add_parent_layout_to_parsed_block()
    {
        return [
            'block with no parent layout' => [
                'block'        => [
                    'blockName' => 'core/group',
                    'attrs'     => [
                        'layout' => [
                            'type' => 'default',
                        ],
                    ],
                ],
                'parent_block' => [],
                'expected'     => [
                    'blockName' => 'core/group',
                    'attrs'     => [
                        'layout' => [
                            'type' => 'default',
                        ],
                    ],
                ],
            ],
            'block with parent layout'    => [
                'block'        => [
                    'blockName' => 'core/group',
                    'attrs'     => [
                        'layout' => [
                            'type' => 'default',
                        ],
                    ],
                ],
                'parent_block' => new WP_Block(
                    [
                        'blockName' => 'core/group',
                        'attrs'     => [
                            'layout' => [
                                'type' => 'grid',
                            ],
                        ],
                    ]
                ),
                'expected'     => [
                    'blockName'    => 'core/group',
                    'attrs'        => [
                        'layout' => [
                            'type' => 'default',
                        ],
                    ],
                    'parentLayout' => [
                        'type' => 'grid',
                    ],
                ],
            ],
        ];
    }

    /**
     * Check that wp_render_layout_support_flag() renders consistent hashes
     * for the container class when the relevant layout properties are the same.
     *
     * @dataProvider data_layout_support_flag_renders_consistent_container_hash
     *
     * @covers ::wp_render_layout_support_flag
     *
     * @param array $block_attrs     Dataset to test.
     * @param array $expected_class  Class generated for the passed dataset.
     */
    public function test_layout_support_flag_renders_consistent_container_hash($block_attrs, $expected_class)
    {
        switch_theme('default');

        $block_content = '<div class="wp-block-group"></div>';
        $block         = [
            'blockName'    => 'core/group',
            'innerBlocks'  => [],
            'innerHTML'    => '<div class="wp-block-group"></div>',
            'innerContent' => [
                '<div class="wp-block-group"></div>',
            ],
            'attrs'        => $block_attrs,
        ];

        /*
         * The `appearance-tools` theme support is temporarily added to ensure
         * that the block gap support is enabled during rendering, which is
         * necessary to compute styles for layouts with block gap values.
         */
        add_theme_support('appearance-tools');
        $output = wp_render_layout_support_flag($block_content, $block);
        remove_theme_support('appearance-tools');

        // Process the output and look for the expected class in the first rendered element.
        $processor = new WP_HTML_Tag_Processor($output);
        $processor->next_tag();

        // Extract the actual container class from the output for better error messages.
        $actual_class = '';
        foreach ($processor->class_list() as $class_name) {
            if (str_starts_with($class_name, 'wp-container-core-group-is-layout-')) {
                $actual_class = $class_name;
                break;
            }
        }

        $this->assertEquals(
            $expected_class,
            $actual_class,
            'Expected class not found in the rendered output, probably because of a different hash.'
        );
    }

    /**
     * Data provider for test_layout_support_flag_renders_consistent_container_hash.
     *
     * @return array
     */
    public function data_layout_support_flag_renders_consistent_container_hash()
    {
        return [
            'default type block gap 12px'      => [
                'block_attributes' => [
                    'layout' => [
                        'type' => 'default',
                    ],
                    'style'  => [
                        'spacing' => [
                            'blockGap' => '12px',
                        ],
                    ],
                ],
                'expected_class'   => 'wp-container-core-group-is-layout-a6248535',
            ],
            'default type block gap 24px'      => [
                'block_attributes' => [
                    'layout' => [
                        'type' => 'default',
                    ],
                    'style'  => [
                        'spacing' => [
                            'blockGap' => '24px',
                        ],
                    ],
                ],
                'expected_class'   => 'wp-container-core-group-is-layout-61b496ee',
            ],
            'constrained type justified left'  => [
                'block_attributes' => [
                    'layout' => [
                        'type'           => 'constrained',
                        'justifyContent' => 'left',
                    ],
                ],
                'expected_class'   => 'wp-container-core-group-is-layout-54d22900',
            ],
            'constrained type justified right' => [
                'block_attributes' => [
                    'layout' => [
                        'type'           => 'constrained',
                        'justifyContent' => 'right',
                    ],
                ],
                'expected_class'   => 'wp-container-core-group-is-layout-2910ada7',
            ],
            'flex type horizontal'             => [
                'block_attributes' => [
                    'layout' => [
                        'type'        => 'flex',
                        'orientation' => 'horizontal',
                        'flexWrap'    => 'nowrap',
                    ],
                ],
                'expected_class'   => 'wp-container-core-group-is-layout-f5d79bea',
            ],
            'flex type vertical'               => [
                'block_attributes' => [
                    'layout' => [
                        'type'        => 'flex',
                        'orientation' => 'vertical',
                    ],
                ],
                'expected_class'   => 'wp-container-core-group-is-layout-2c90304e',
            ],
            'grid type'                        => [
                'block_attributes' => [
                    'layout' => [
                        'type' => 'grid',
                    ],
                ],
                'expected_class'   => 'wp-container-core-group-is-layout-5a23bf8e',
            ],
            'grid type 3 columns'              => [
                'block_attributes' => [
                    'layout' => [
                        'type'        => 'grid',
                        'columnCount' => 3,
                    ],
                ],
                'expected_class'   => 'wp-container-core-group-is-layout-cda6dc4f',
            ],
        ];
    }

    /**
     * Tests that custom blocks include namespace in layout classnames.
     *
     * When layout support is enabled for custom blocks, the generated
     * layout classname should include the full block namespace to ensure
     * that CSS selectors match correctly.
     *
     * @ticket 63839
     * @covers ::wp_render_layout_support_flag
     *
     * @dataProvider data_layout_classname_with_custom_blocks
     */
    public function test_layout_classname_includes_namespace_for_custom_blocks($block_name, $layout_type, $expected_class, $should_not_contain)
    {
        switch_theme('default');

        register_block_type(
            $block_name,
            [
                'supports' => [
                    'layout' => true,
                ],
            ]
        );

        $block_content = '<div class="wp-block-test"><p>Content</p></div>';
        $block         = [
            'blockName' => $block_name,
            'attrs'     => [
                'layout' => [
                    'type' => $layout_type,
                ],
            ],
        ];

        $output = wp_render_layout_support_flag($block_content, $block);

        // Assert that the expected class is present.
        $this->assertStringContainsString($expected_class, $output);

        // Assert that the old buggy class is not present.
        $this->assertStringNotContainsString($should_not_contain, $output);

        // Clean up the registered block type.
        unregister_block_type($block_name);
    }

    /**
     * Data provider for test_layout_classname_includes_namespace_for_custom_blocks.
     *
     * @return array
     */
    public function data_layout_classname_with_custom_blocks()
    {
        return [
            'custom block with constrained layout' => [
                'block_name'         => 'foo/bar',
                'layout_type'        => 'constrained',
                'expected_class'     => 'wp-block-foo-bar-is-layout-constrained',
                'should_not_contain' => 'wp-block-bar-is-layout-constrained',
            ],
            'custom block with default layout'     => [
                'block_name'         => 'foo/bar',
                'layout_type'        => 'default',
                'expected_class'     => 'wp-block-foo-bar-is-layout-flow',
                'should_not_contain' => 'wp-block-bar-is-layout-flow',
            ],
            'custom block with flex layout'        => [
                'block_name'         => 'foo/bar',
                'layout_type'        => 'flex',
                'expected_class'     => 'wp-block-foo-bar-is-layout-flex',
                'should_not_contain' => 'wp-block-bar-is-layout-flex',
            ],
            'custom block with grid layout'        => [
                'block_name'         => 'foo/bar',
                'layout_type'        => 'grid',
                'expected_class'     => 'wp-block-foo-bar-is-layout-grid',
                'should_not_contain' => 'wp-block-bar-is-layout-grid',
            ],
        ];
    }

    /**
     * Tests that block style variations with blockGap values are applied to layout styles.
     *
     * @ticket 64624
     * @covers ::wp_render_layout_support_flag
     */
    public function test_layout_support_flag_uses_variation_block_gap_value()
    {
        switch_theme('block-theme');

        $block_content = '<div class="wp-block-group is-style-custom-gap"></div>';
        $block         = [
            'blockName'    => 'core/group',
            'attrs'        => [
                'className' => 'is-style-custom-gap',
                'layout'    => [
                    'type'               => 'grid',
                    'columnCount'        => 3,
                    'minimumColumnWidth' => '12rem',
                ],
            ],
            'innerBlocks'  => [],
            'innerHTML'    => '<div class="wp-block-group is-style-custom-gap"></div>',
            'innerContent' => [
                '<div class="wp-block-group is-style-custom-gap"></div>',
            ],
        ];

        wp_render_layout_support_flag($block_content, $block);

        // Get the generated CSS from the style engine.
        $actual_stylesheet = wp_style_engine_get_stylesheet_from_context('block-supports', [ 'prettify' => false ]);

        // The CSS grid declaration should contain the variation's blockGap value of 99px.
        $this->assertStringContainsString(
            'grid-template-columns:repeat(auto-fill, minmax(max(min(12rem, 100%), (100% - (99px * (3 - 1))) /3), 1fr))',
            $actual_stylesheet,
            'Generated CSS should contain the variation blockGap value of 99px.'
        );
    }

    /**
     * Tests that wp_get_block_style_variation_name_from_registered_style correctly extracts variation names from class strings.
     *
     * @ticket 64624
     * @covers ::wp_get_block_style_variation_name_from_registered_style
     *
     * @dataProvider data_get_block_style_variation_name_from_registered_style
     *
     * @param string      $class_name        CSS class string to test.
     * @param array       $registered_styles Registered block styles.
     * @param string|null $expected_result   Expected variation name or null.
     */
    public function test_get_block_style_variation_name_from_registered_style($class_name, $registered_styles, $expected_result)
    {
        $result = wp_get_block_style_variation_name_from_registered_style($class_name, $registered_styles);
        $this->assertSame($expected_result, $result);
    }

    /**
     * Data provider for test_get_block_style_variation_name_from_registered_style.
     *
     * @return array
     */
    public function data_get_block_style_variation_name_from_registered_style()
    {
        return [
            'empty class name'                             => [
                'class_name'        => '',
                'registered_styles' => [],
                'expected_result'   => null,
            ],
            'no matching registered styles'                => [
                'class_name'        => 'is-style-shadowed wp-block-button',
                'registered_styles' => [
                    [ 'name' => 'rounded' ],
                    [ 'name' => 'outlined' ],
                ],
                'expected_result'   => null,
            ],
            'single matching variation found'              => [
                'class_name'        => 'wp-block-button is-style-rounded',
                'registered_styles' => [
                    [ 'name' => 'rounded' ],
                    [ 'name' => 'outlined' ],
                ],
                'expected_result'   => 'rounded',
            ],
            'ignores default style only'                   => [
                'class_name'        => 'is-style-default wp-block-button',
                'registered_styles' => [
                    [ 'name' => 'default' ],
                    [ 'name' => 'rounded' ],
                ],
                'expected_result'   => null,
            ],
            'ignores default and returns next variation'   => [
                'class_name'        => 'is-style-default is-style-rounded wp-block-button',
                'registered_styles' => [
                    [ 'name' => 'default' ],
                    [ 'name' => 'rounded' ],
                    [ 'name' => 'outlined' ],
                ],
                'expected_result'   => 'rounded',
            ],
            'returns first matching variation when multiple present' => [
                'class_name'        => 'is-style-shadowed is-style-rounded',
                'registered_styles' => [
                    [ 'name' => 'rounded' ],
                    [ 'name' => 'outlined' ],
                    [ 'name' => 'shadowed' ],
                ],
                'expected_result'   => 'shadowed',
            ],
            'empty registered styles array'                => [
                'class_name'        => 'is-style-rounded',
                'registered_styles' => [],
                'expected_result'   => null,
            ],
            'registered styles with missing name property' => [
                'class_name'        => 'is-style-outlined wp-block-button',
                'registered_styles' => [
                    [ 'label' => 'Rounded' ],
                    [ 'name' => 'outlined' ],
                ],
                'expected_result'   => 'outlined',
            ],
        ];
    }
}
