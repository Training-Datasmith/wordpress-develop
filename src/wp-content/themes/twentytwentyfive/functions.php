<?php

declare(strict_types=1);
/**
 * Twenty Twenty-Five functions and definitions.
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package WordPress
 * @subpackage Twenty_Twenty_Five
 * @since Twenty Twenty-Five 1.0
 */

if (! function_exists('twentytwentyfive_post_format_setup')) :
    /**
     * Adds theme support for post formats.
     *
     * @since Twenty Twenty-Five 1.0
     */
    function twentytwentyfive_post_format_setup(): void
    {
        add_theme_support('post-formats', [ 'aside', 'audio', 'chat', 'gallery', 'image', 'link', 'quote', 'status', 'video' ]);
    }
endif;
add_action('after_setup_theme', 'twentytwentyfive_post_format_setup');

if (! function_exists('twentytwentyfive_editor_style')) :
    /**
     * Enqueues editor-style.css in the editors.
     *
     * @since Twenty Twenty-Five 1.0
     */
    function twentytwentyfive_editor_style(): void
    {
        add_editor_style('assets/css/editor-style.css');
    }
endif;
add_action('after_setup_theme', 'twentytwentyfive_editor_style');

if (! function_exists('twentytwentyfive_enqueue_styles')) :
    /**
     * Enqueues the theme stylesheet on the front.
     *
     * @since Twenty Twenty-Five 1.0
     */
    function twentytwentyfive_enqueue_styles(): void
    {
        $suffix = SCRIPT_DEBUG ? '' : '.min';
        $src    = 'style' . $suffix . '.css';

        wp_enqueue_style(
            'twentytwentyfive-style',
            get_parent_theme_file_uri($src),
            [],
            wp_get_theme()->get('Version')
        );
        wp_style_add_data(
            'twentytwentyfive-style',
            'path',
            get_parent_theme_file_path($src)
        );
    }
endif;
add_action('wp_enqueue_scripts', 'twentytwentyfive_enqueue_styles');

if (! function_exists('twentytwentyfive_block_styles')) :
    /**
     * Registers custom block styles.
     *
     * @since Twenty Twenty-Five 1.0
     */
    function twentytwentyfive_block_styles(): void
    {
        register_block_style(
            'core/list',
            [
                'name'         => 'checkmark-list',
                'label'        => __('Checkmark', 'twentytwentyfive'),
                'inline_style' => '
				ul.is-style-checkmark-list {
					list-style-type: "\2713";
				}

				ul.is-style-checkmark-list li {
					padding-inline-start: 1ch;
				}',
            ]
        );
    }
endif;
add_action('init', 'twentytwentyfive_block_styles');

if (! function_exists('twentytwentyfive_pattern_categories')) :
    /**
     * Registers pattern categories.
     *
     * @since Twenty Twenty-Five 1.0
     */
    function twentytwentyfive_pattern_categories(): void
    {

        register_block_pattern_category(
            'twentytwentyfive_page',
            [
                'label'       => __('Pages', 'twentytwentyfive'),
                'description' => __('A collection of full page layouts.', 'twentytwentyfive'),
            ]
        );

        register_block_pattern_category(
            'twentytwentyfive_post-format',
            [
                'label'       => __('Post formats', 'twentytwentyfive'),
                'description' => __('A collection of post format patterns.', 'twentytwentyfive'),
            ]
        );
    }
endif;
add_action('init', 'twentytwentyfive_pattern_categories');

if (! function_exists('twentytwentyfive_register_block_bindings')) :
    /**
     * Registers the post format block binding source.
     *
     * @since Twenty Twenty-Five 1.0
     */
    function twentytwentyfive_register_block_bindings(): void
    {
        register_block_bindings_source(
            'twentytwentyfive/format',
            [
                'label'              => _x('Post format name', 'Label for the block binding placeholder in the editor', 'twentytwentyfive'),
                'get_value_callback' => 'twentytwentyfive_format_binding',
            ]
        );
    }
endif;
add_action('init', 'twentytwentyfive_register_block_bindings');

if (! function_exists('twentytwentyfive_format_binding')) :
    /**
     * Callback function for the post format name block binding source.
     *
     * @since Twenty Twenty-Five 1.0
     *
     * @return string|void Post format name, or nothing if the format is 'standard'.
     */
    function twentytwentyfive_format_binding()
    {
        $post_format_slug = get_post_format();

        if ($post_format_slug && 'standard' !== $post_format_slug) {
            return get_post_format_string($post_format_slug);
        }
    }
endif;
