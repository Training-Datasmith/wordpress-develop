<?php

declare(strict_types=1);
/**
 * Customizer settings for this theme.
 *
 * @package WordPress
 * @subpackage Twenty_Twenty_One
 * @since Twenty Twenty-One 1.0
 */

if (! class_exists('Twenty_Twenty_One_Customize')) {
    /**
     * Customizer Settings.
     *
     * @since Twenty Twenty-One 1.0
     */
    class Twenty_Twenty_One_Customize
    {
        /**
         * Constructor. Instantiates the object.
         *
         * @since Twenty Twenty-One 1.0
         */
        public function __construct()
        {
            add_action('customize_register', [ $this, 'register' ]);
        }

        /**
         * Registers customizer options.
         *
         * @since Twenty Twenty-One 1.0
         *
         * @param WP_Customize_Manager $wp_customize Theme Customizer object.
         */
        public function register($wp_customize): void
        {

            // Change site-title & description to postMessage.
            foreach ([ 'blogname', 'blogdescription' ] as $setting_id) {
                $setting = $wp_customize->get_setting($setting_id);
                if ($setting) {
                    $setting->transport = 'postMessage';
                }
            }

            // Add partial for blogname.
            $wp_customize->selective_refresh->add_partial(
                'blogname',
                [
                    'selector'        => '.site-title',
                    'render_callback' => [ $this, 'partial_blogname' ],
                ]
            );

            // Add partial for blogdescription.
            $wp_customize->selective_refresh->add_partial(
                'blogdescription',
                [
                    'selector'        => '.site-description',
                    'render_callback' => [ $this, 'partial_blogdescription' ],
                ]
            );

            // Add "display_title_and_tagline" setting for displaying the site-title & tagline.
            $wp_customize->add_setting(
                'display_title_and_tagline',
                [
                    'capability'        => 'edit_theme_options',
                    'default'           => true,
                    'sanitize_callback' => [ self::class, 'sanitize_checkbox' ],
                ]
            );

            // Add control for the "display_title_and_tagline" setting.
            $wp_customize->add_control(
                'display_title_and_tagline',
                [
                    'type'    => 'checkbox',
                    'section' => 'title_tagline',
                    'label'   => esc_html__('Display Site Title & Tagline', 'twentytwentyone'),
                ]
            );

            /**
             * Add excerpt or full text selector to customizer
             */
            $wp_customize->add_section(
                'excerpt_settings',
                [
                    'title'    => esc_html__('Excerpt Settings', 'twentytwentyone'),
                    'priority' => 120,
                ]
            );

            $wp_customize->add_setting(
                'display_excerpt_or_full_post',
                [
                    'capability'        => 'edit_theme_options',
                    'default'           => 'excerpt',
                    'sanitize_callback' => static fn ($value) => 'excerpt' === $value || 'full' === $value ? $value : 'excerpt',
                ]
            );

            $wp_customize->add_control(
                'display_excerpt_or_full_post',
                [
                    'type'    => 'radio',
                    'section' => 'excerpt_settings',
                    'label'   => esc_html__('On Archive Pages, posts show:', 'twentytwentyone'),
                    'choices' => [
                        'excerpt' => esc_html__('Summary', 'twentytwentyone'),
                        'full'    => esc_html__('Full text', 'twentytwentyone'),
                    ],
                ]
            );

            // Background color.
            // Include the custom control class.
            require_once get_theme_file_path('classes/class-twenty-twenty-one-customize-color-control.php');

            // Register the custom control.
            $wp_customize->register_control_type('Twenty_Twenty_One_Customize_Color_Control');

            // Get the palette from theme-supports.
            $palette = get_theme_support('editor-color-palette');

            // Build the colors array from theme-support.
            $colors = [];
            if (isset($palette[0]) && is_array($palette[0])) {
                foreach ($palette[0] as $palette_color) {
                    $colors[] = $palette_color['color'];
                }
            }

            // Add the control. Overrides the default background-color control.
            $wp_customize->add_control(
                new Twenty_Twenty_One_Customize_Color_Control(
                    $wp_customize,
                    'background_color',
                    [
                        'label'   => esc_html_x('Background color', 'Customizer control', 'twentytwentyone'),
                        'section' => 'colors',
                        'palette' => $colors,
                    ]
                )
            );
        }

        /**
         * Sanitizes a boolean for checkbox.
         *
         * @since Twenty Twenty-One 1.0
         *
         * @param bool $checked Whether or not a box is checked.
         */
        public static function sanitize_checkbox($checked = null): bool
        {
            return isset($checked) && true === $checked;
        }

        /**
         * Renders the site title for the selective refresh partial.
         *
         * @since Twenty Twenty-One 1.0
         */
        public function partial_blogname(): void
        {
            bloginfo('name');
        }

        /**
         * Renders the site tagline for the selective refresh partial.
         *
         * @since Twenty Twenty-One 1.0
         */
        public function partial_blogdescription(): void
        {
            bloginfo('description');
        }
    }
}
