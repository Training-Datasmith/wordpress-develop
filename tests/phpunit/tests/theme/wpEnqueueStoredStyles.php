<?php

declare(strict_types=1);

require_once __DIR__ . '/base.php';

/**
 * Tests wp_enqueue_stored_styles().
 *
 * @group themes
 *
 * @covers ::wp_enqueue_stored_styles
 */
class Tests_Themes_WpEnqueueStoredStyles extends WP_Theme_UnitTestCase
{
    /**
     * @var WP_Scripts|null
     */
    protected $original_wp_scripts;

    /**
     * @var WP_Styles|null
     */
    protected $original_wp_styles;

    public function set_up()
    {
        parent::set_up();

        global $wp_scripts, $wp_styles;
        $this->original_wp_scripts = $wp_scripts;
        $this->original_wp_styles  = $wp_styles;
        $wp_scripts                = null;
        $wp_styles                 = null;
        wp_scripts();
        wp_styles();
    }

    public function tear_down()
    {
        global $wp_scripts, $wp_styles;
        $wp_scripts = $this->original_wp_scripts;
        $wp_styles  = $this->original_wp_styles;

        parent::tear_down();
    }

    /**
     * Tests that stored CSS is enqueued.
     *
     * @ticket 56467
     *
     * @covers ::wp_style_engine_get_stylesheet_from_css_rules
     * @covers ::wp_enqueue_stored_styles
     */
    public function test_should_enqueue_stored_styles()
    {
        $core_styles_to_enqueue = [
            [
                'selector'     => '.saruman',
                'declarations' => [
                    'color'        => 'white',
                    'height'       => '100px',
                    'border-style' => 'solid',
                ],
            ],
        ];

        // Enqueues a block supports (core styles).
        wp_style_engine_get_stylesheet_from_css_rules(
            $core_styles_to_enqueue,
            [
                'context' => 'block-supports',
            ]
        );

        $my_styles_to_enqueue = [
            [
                'selector'     => '.gandalf',
                'declarations' => [
                    'color'        => 'grey',
                    'height'       => '90px',
                    'border-style' => 'dotted',
                ],
            ],
        ];

        // Enqueues some other styles.
        wp_style_engine_get_stylesheet_from_css_rules(
            $my_styles_to_enqueue,
            [
                'context' => 'my-styles',
            ]
        );

        wp_enqueue_stored_styles([ 'prettify' => false ]);

        $this->assertSame(
            [ '.saruman{color:white;height:100px;border-style:solid;}' ],
            wp_styles()->registered['core-block-supports']->extra['after'],
            'Registered styles with handle of "core-block-supports" do not match expected value from Style Engine store.'
        );

        $this->assertSame(
            [ '.gandalf{color:grey;height:90px;border-style:dotted;}' ],
            wp_styles()->registered['wp-style-engine-my-styles']->extra['after'],
            'Registered styles with handle of "wp-style-engine-my-styles" do not match expected value from the Style Engine store.'
        );
    }
}
