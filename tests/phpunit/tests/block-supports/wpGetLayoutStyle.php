<?php

declare(strict_types=1);

/**
 * @group block-supports
 * @covers ::wp_get_layout_style
 */
class Tests_Block_Supports_WpGetLayoutStyle extends WP_UnitTestCase
{
    public const ARGS_DEFAULTS = [
        'selector'                      => null,
        'layout'                        => null,
        'has_block_gap_support'         => false,
        'gap_value'                     => null,
        'should_skip_gap_serialization' => false,
        'fallback_gap_value'            => '0.5em',
        'block_spacing'                 => null,
    ];

    /**
     * @dataProvider data_wp_get_layout_style
     * @ticket       56467
     * @ticket       61165
     *
     * @param array  $args            Dataset to test.
     * @param string $expected_output The expected output.
     */
    public function test_wp_get_layout_style(array $args, $expected_output)
    {
        $args          = array_merge(static::ARGS_DEFAULTS, $args);
        $layout_styles = wp_get_layout_style(
            $args['selector'],
            $args['layout'],
            $args['has_block_gap_support'],
            $args['gap_value'],
            $args['should_skip_gap_serialization'],
            $args['fallback_gap_value'],
            $args['block_spacing']
        );

        $this->assertSame($expected_output, $layout_styles);
    }

    /**
     * Data provider.
     *
     * @return array
     */
    public function data_wp_get_layout_style()
    {
        return [
            'no args should return empty value'            => [
                'args'            => [],
                'expected_output' => '',
            ],
            'nulled args should return empty value'        => [
                'args'            => [
                    'selector'                      => null,
                    'layout'                        => null,
                    'has_block_gap_support'         => null,
                    'gap_value'                     => null,
                    'should_skip_gap_serialization' => null,
                    'fallback_gap_value'            => null,
                    'block_spacing'                 => null,
                ],
                'expected_output' => '',
            ],
            'only selector should return empty value'      => [
                'args'            => [
                    'selector' => '.wp-layout',
                ],
                'expected_output' => '',
            ],
            'default layout and block gap support'         => [
                'args'            => [
                    'selector'              => '.wp-layout',
                    'has_block_gap_support' => true,
                    'gap_value'             => '1em',
                ],
                'expected_output' => '.wp-layout > *{margin-block-start:0;margin-block-end:0;}.wp-layout > * + *{margin-block-start:1em;margin-block-end:0;}',
            ],
            'skip serialization should return empty value' => [
                'args'            => [
                    'selector'                      => '.wp-layout',
                    'has_block_gap_support'         => true,
                    'gap_value'                     => '1em',
                    'should_skip_gap_serialization' => true,
                ],
                'expected_output' => '',
            ],
            'default layout and axial block gap support'   => [
                'args'            => [
                    'selector'              => '.wp-layout',
                    'has_block_gap_support' => true,
                    'gap_value'             => [ 'top' => '1em' ],
                ],
                'expected_output' => '.wp-layout > *{margin-block-start:0;margin-block-end:0;}.wp-layout > * + *{margin-block-start:1em;margin-block-end:0;}',
            ],
            'constrained layout with sizes'                => [
                'args'            => [
                    'selector' => '.wp-layout',
                    'layout'   => [
                        'type'        => 'constrained',
                        'contentSize' => '800px',
                        'wideSize'    => '1200px',
                    ],
                ],
                'expected_output' => '.wp-layout > :where(:not(.alignleft):not(.alignright):not(.alignfull)){max-width:800px;margin-left:auto !important;margin-right:auto !important;}.wp-layout > .alignwide{max-width:1200px;}.wp-layout .alignfull{max-width:none;}',
            ],
            'constrained layout with sizes and block spacing' => [
                'args'            => [
                    'selector'      => '.wp-layout',
                    'layout'        => [
                        'type'        => 'constrained',
                        'contentSize' => '800px',
                        'wideSize'    => '1200px',
                    ],
                    'block_spacing' => [
                        'padding' => [
                            'left'  => '20px',
                            'right' => '10px',
                        ],
                    ],
                ],
                'expected_output' => '.wp-layout > :where(:not(.alignleft):not(.alignright):not(.alignfull)){max-width:800px;margin-left:auto !important;margin-right:auto !important;}.wp-layout > .alignwide{max-width:1200px;}.wp-layout .alignfull{max-width:none;}.wp-layout > .alignfull{margin-right:calc(10px * -1);margin-left:calc(20px * -1);}',
            ],
            'constrained layout with block gap support'    => [
                'args'            => [
                    'selector'              => '.wp-layout',
                    'layout'                => [
                        'type' => 'constrained',
                    ],
                    'has_block_gap_support' => true,
                    'gap_value'             => '2.5rem',
                ],
                'expected_output' => '.wp-layout > *{margin-block-start:0;margin-block-end:0;}.wp-layout > * + *{margin-block-start:2.5rem;margin-block-end:0;}',
            ],
            'constrained layout with axial block gap support' => [
                'args'            => [
                    'selector'              => '.wp-layout',
                    'layout'                => [
                        'type' => 'constrained',
                    ],
                    'has_block_gap_support' => true,
                    'gap_value'             => [ 'top' => '2.5rem' ],
                ],
                'expected_output' => '.wp-layout > *{margin-block-start:0;margin-block-end:0;}.wp-layout > * + *{margin-block-start:2.5rem;margin-block-end:0;}',
            ],
            'constrained layout with block gap support and spacing preset' => [
                'args'            => [
                    'selector'              => '.wp-layout',
                    'layout'                => [
                        'type' => 'constrained',
                    ],
                    'has_block_gap_support' => true,
                    'gap_value'             => 'var:preset|spacing|50',
                ],
                'expected_output' => '.wp-layout > *{margin-block-start:0;margin-block-end:0;}.wp-layout > * + *{margin-block-start:var(--wp--preset--spacing--50);margin-block-end:0;}',
            ],
            'flex layout with no args should return empty value' => [
                'args'            => [
                    'selector' => '.wp-layout',
                    'layout'   => [
                        'type' => 'flex',
                    ],
                ],
                'expected_output' => '',
            ],
            'horizontal flex layout should return empty value' => [
                'args'            => [
                    'selector' => '.wp-layout',
                    'layout'   => [
                        'type'        => 'flex',
                        'orientation' => 'horizontal',
                    ],
                ],
                'expected_output' => '',
            ],
            'flex layout with properties'                  => [
                'args'            => [
                    'selector' => '.wp-layout',
                    'layout'   => [
                        'type'              => 'flex',
                        'orientation'       => 'horizontal',
                        'flexWrap'          => 'nowrap',
                        'justifyContent'    => 'left',
                        'verticalAlignment' => 'bottom',
                    ],
                ],
                'expected_output' => '.wp-layout{flex-wrap:nowrap;justify-content:flex-start;align-items:flex-end;}',
            ],
            'flex layout with properties and block gap'    => [
                'args'            => [
                    'selector'              => '.wp-layout',
                    'layout'                => [
                        'type'              => 'flex',
                        'orientation'       => 'horizontal',
                        'flexWrap'          => 'nowrap',
                        'justifyContent'    => 'left',
                        'verticalAlignment' => 'bottom',
                    ],
                    'has_block_gap_support' => true,
                    'gap_value'             => '29px',
                ],
                'expected_output' => '.wp-layout{flex-wrap:nowrap;gap:29px;justify-content:flex-start;align-items:flex-end;}',
            ],
            'flex layout with properties and axial block gap' => [
                'args'            => [
                    'selector'              => '.wp-layout',
                    'layout'                => [
                        'type'              => 'flex',
                        'orientation'       => 'horizontal',
                        'flexWrap'          => 'nowrap',
                        'justifyContent'    => 'left',
                        'verticalAlignment' => 'bottom',
                    ],
                    'has_block_gap_support' => true,
                    'gap_value'             => [
                        'top'  => '1px',
                        'left' => '2px',
                    ],
                ],
                'expected_output' => '.wp-layout{flex-wrap:nowrap;gap:1px 2px;justify-content:flex-start;align-items:flex-end;}',
            ],
            'flex layout with properties and axial block gap using spacing preset' => [
                'args'            => [
                    'selector'              => '.wp-layout',
                    'layout'                => [
                        'type'              => 'flex',
                        'orientation'       => 'horizontal',
                        'flexWrap'          => 'nowrap',
                        'justifyContent'    => 'left',
                        'verticalAlignment' => 'bottom',
                    ],
                    'has_block_gap_support' => true,
                    'gap_value'             => [
                        'left' => 'var:preset|spacing|40',
                    ],
                    'fallback_gap_value'    => '11px',
                ],
                'expected_output' => '.wp-layout{flex-wrap:nowrap;gap:11px var(--wp--preset--spacing--40);justify-content:flex-start;align-items:flex-end;}',
            ],
            'vertical flex layout with properties'         => [
                'args'            => [
                    'selector' => '.wp-layout',
                    'layout'   => [
                        'type'              => 'flex',
                        'orientation'       => 'vertical',
                        'flexWrap'          => 'nowrap',
                        'justifyContent'    => 'left',
                        'verticalAlignment' => 'bottom',
                    ],
                ],
                'expected_output' => '.wp-layout{flex-wrap:nowrap;flex-direction:column;align-items:flex-start;justify-content:flex-end;}',
            ],
            'default grid layout'                          => [
                'args'            => [
                    'selector' => '.wp-layout',
                    'layout'   => [
                        'type' => 'grid',
                    ],
                ],
                'expected_output' => '.wp-layout{grid-template-columns:repeat(auto-fill, minmax(min(12rem, 100%), 1fr));container-type:inline-size;}',
            ],
            'grid layout with columnCount'                 => [
                'args'            => [
                    'selector' => '.wp-layout',
                    'layout'   => [
                        'type'        => 'grid',
                        'columnCount' => 3,
                    ],
                ],
                'expected_output' => '.wp-layout{grid-template-columns:repeat(3, minmax(0, 1fr));}',
            ],
            'default layout with blockGap to verify converting gap value into valid CSS' => [
                'args'            => [
                    'selector'              => '.wp-block-group.wp-container-6',
                    'layout'                => [
                        'type' => 'default',
                    ],
                    'has_block_gap_support' => true,
                    'gap_value'             => 'var:preset|spacing|70',
                    'block_spacing'         => [
                        'blockGap' => 'var(--wp--preset--spacing--70)',
                    ],
                ],
                'expected_output' => '.wp-block-group.wp-container-6 > *{margin-block-start:0;margin-block-end:0;}.wp-block-group.wp-container-6 > * + *{margin-block-start:var(--wp--preset--spacing--70);margin-block-end:0;}',
            ],
        ];
    }
}
