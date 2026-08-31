<?php

declare(strict_types=1);
/**
 * Customize API: WP_Customize_Color_Control class
 *
 * @package WordPress
 * @subpackage Twenty_Twenty_One
 * @since Twenty Twenty-One 1.0
 */

/**
 * Customize Color Control class.
 *
 * @since Twenty Twenty-One 1.0
 *
 * @see WP_Customize_Control
 */
class Twenty_Twenty_One_Customize_Color_Control extends WP_Customize_Color_Control
{
    /**
     * The control type.
     *
     * @since Twenty Twenty-One 1.0
     *
     * @var string
     */
    public $type = 'twenty-twenty-one-color';

    /**
     * Colorpicker palette.
     *
     * @since Twenty Twenty-One 1.0
     *
     * @var array
     */
    public $palette;

    /**
     * Enqueues control related scripts/styles.
     *
     * @since Twenty Twenty-One 1.0
     */
    public function enqueue(): void
    {
        parent::enqueue();

        // Enqueue the script.
        wp_enqueue_script(
            'twentytwentyone-control-color',
            get_theme_file_uri('assets/js/palette-colorpicker.js'),
            [ 'customize-controls', 'jquery', 'customize-base', 'wp-color-picker' ],
            wp_get_theme()->get('Version'),
            false
        );
    }

    /**
     * Refreshes the parameters passed to the JavaScript via JSON.
     *
     * @since Twenty Twenty-One 1.0
     *
     * @uses WP_Customize_Control::to_json()
     */
    public function to_json(): void
    {
        parent::to_json();
        $this->json['palette'] = $this->palette;
    }
}
