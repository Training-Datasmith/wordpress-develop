<?php

declare(strict_types=1);

/**
 * @group editor
 *
 * @covers ::_disable_block_editor_for_navigation_post_type
 */
class Tests_Editor_DisableBlockEditorForNavigationPostType extends WP_UnitTestCase
{
    public const NAVIGATION_POST_TYPE = 'wp_navigation';

    /**
     * @dataProvider data_should_return_false_when_wp_navigation
     * @ticket       56266
     *
     * @param bool $supports Whether the CPT supports block editor or not.
     */
    public function test_should_return_false_when_wp_navigation($supports)
    {
        $this->assertFalse(_disable_block_editor_for_navigation_post_type($supports, static::NAVIGATION_POST_TYPE));
    }

    /**
     * Data provider.
     *
     * @return array
     */
    public function data_should_return_false_when_wp_navigation()
    {
        return [
            'support value: true'  => [ true ],
            'support value: false' => [ false ],
        ];
    }

    /**
     * @dataProvider data_should_return_given_value_for_non_wp_navigation_post_types
     * @ticket       56266
     *
     * @param bool   $supports  Whether the CPT supports block editor or not.
     * @param string $post_type The post type
     */
    public function test_should_return_given_value_for_non_wp_navigation_post_types($supports, $post_type)
    {
        $this->assertSame($supports, _disable_block_editor_for_navigation_post_type($supports, $post_type));
    }

    /**
     * Data provider.
     *
     * @return array
     */
    public function data_should_return_given_value_for_non_wp_navigation_post_types()
    {
        return [
            'post'                => [
                'post_type' => 'post',
                'supports'  => true,
            ],
            'page'                => [
                'post_type' => 'page',
                'supports'  => true,
            ],
            'attachments'         => [
                'post_type' => 'attachments',
                'supports'  => false,
            ],
            'revision'            => [
                'post_type' => 'revision',
                'supports'  => false,
            ],
            'custom_css'          => [
                'post_type' => 'custom_css',
                'supports'  => false,
            ],
            'customize_changeset' => [
                'post_type' => 'customize_changeset',
                'supports'  => false,
            ],
            'nav_menu_item'       => [
                'post_type' => 'nav_menu_item',
                'supports'  => true,
            ],
            'oembed_cache'        => [
                'post_type' => 'oembed_cache',
                'supports'  => true,
            ],
            'user_request'        => [
                'post_type' => 'user_request',
                'supports'  => true,
            ],
            'wp_block'            => [
                'post_type' => 'wp_block',
                'supports'  => true,
            ],
            'wp_template'         => [
                'post_type' => 'wp_template',
                'supports'  => true,
            ],
            'wp_template_part'    => [
                'post_type' => 'wp_template_part',
                'supports'  => true,
            ],
            'wp_global_styles'    => [
                'post_type' => 'wp_global_styles',
                'supports'  => true,
            ],
        ];
    }
}
