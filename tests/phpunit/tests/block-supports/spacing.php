<?php

declare(strict_types=1);
/**
 * @group block-supports
 *
 * @covers ::wp_apply_spacing_support
 */
class Tests_Block_Supports_Spacing extends WP_UnitTestCase
{
    /**
     * @var string|null
     */
    private $test_block_name;

    public function set_up()
    {
        parent::set_up();
        $this->test_block_name = null;
    }

    public function tear_down()
    {
        unregister_block_type($this->test_block_name);
        $this->test_block_name = null;
        parent::tear_down();
    }

    /**
     * @ticket 55505
     */
    public function test_spacing_style_is_applied()
    {
        $this->test_block_name = 'test/spacing-style-is-applied';
        register_block_type(
            $this->test_block_name,
            [
                'api_version' => 2,
                'attributes'  => [
                    'style' => [
                        'type' => 'object',
                    ],
                ],
                'supports'    => [
                    'spacing' => [
                        'margin'   => true,
                        'padding'  => true,
                        'blockGap' => true,
                    ],
                ],
            ]
        );
        $registry   = WP_Block_Type_Registry::get_instance();
        $block_type = $registry->get_registered($this->test_block_name);
        $block_atts = [
            'style' => [
                'spacing' => [
                    'margin'   => [
                        'top'    => '1px',
                        'right'  => '2px',
                        'bottom' => '3px',
                        'left'   => '4px',
                    ],
                    'padding'  => '111px',
                    'blockGap' => '2em',
                ],
            ],
        ];

        $actual   = wp_apply_spacing_support($block_type, $block_atts);
        $expected = [
            'style' => 'padding:111px;margin-top:1px;margin-right:2px;margin-bottom:3px;margin-left:4px;',
        ];

        $this->assertSame($expected, $actual);
    }

    /**
     * @ticket 55505
     */
    public function test_spacing_with_skipped_serialization_block_supports()
    {
        $this->test_block_name = 'test/spacing-with-skipped-serialization-block-supports';
        register_block_type(
            $this->test_block_name,
            [
                'api_version' => 2,
                'attributes'  => [
                    'style' => [
                        'type' => 'object',
                    ],
                ],
                'supports'    => [
                    'spacing' => [
                        'margin'                          => true,
                        'padding'                         => true,
                        'blockGap'                        => true,
                        '__experimentalSkipSerialization' => true,
                    ],
                ],
            ]
        );
        $registry   = WP_Block_Type_Registry::get_instance();
        $block_type = $registry->get_registered($this->test_block_name);
        $block_atts = [
            'style' => [
                'spacing' => [
                    'margin'   => [
                        'top'    => '1px',
                        'right'  => '2px',
                        'bottom' => '3px',
                        'left'   => '4px',
                    ],
                    'padding'  => '111px',
                    'blockGap' => '2em',
                ],
            ],
        ];

        $actual   = wp_apply_spacing_support($block_type, $block_atts);
        $expected = [];

        $this->assertSame($expected, $actual);
    }

    /**
     * @ticket 55505
     */
    public function test_margin_with_individual_skipped_serialization_block_supports()
    {
        $this->test_block_name = 'test/margin-with-individual-skipped-serialization-block-supports';
        register_block_type(
            $this->test_block_name,
            [
                'api_version' => 2,
                'attributes'  => [
                    'style' => [
                        'type' => 'object',
                    ],
                ],
                'supports'    => [
                    'spacing' => [
                        'margin'                          => true,
                        'padding'                         => true,
                        'blockGap'                        => true,
                        '__experimentalSkipSerialization' => [ 'margin' ],
                    ],
                ],
            ]
        );
        $registry   = WP_Block_Type_Registry::get_instance();
        $block_type = $registry->get_registered($this->test_block_name);
        $block_atts = [
            'style' => [
                'spacing' => [
                    'padding'  => [
                        'top'    => '1px',
                        'right'  => '2px',
                        'bottom' => '3px',
                        'left'   => '4px',
                    ],
                    'margin'   => '111px',
                    'blockGap' => '2em',
                ],
            ],
        ];

        $actual   = wp_apply_spacing_support($block_type, $block_atts);
        $expected = [
            'style' => 'padding-top:1px;padding-right:2px;padding-bottom:3px;padding-left:4px;',
        ];

        $this->assertSame($expected, $actual);
    }
}
