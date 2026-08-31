<?php

declare(strict_types=1);

/**
 * Test WP_Theme_JSON_Schema class.
 *
 * @package WordPress
 * @subpackage Theme
 *
 * @since 5.9.0
 *
 * @group themes
 */
class Tests_Theme_wpThemeJsonSchema extends WP_UnitTestCase
{
    /**
     * @ticket 54336
     */
    public function test_migrate_v1_to_latest()
    {
        $theme_json_v1 = [
            'version'  => 1,
            'settings' => [
                'color'      => [
                    'palette' => [
                        [
                            'name'  => 'Pale Pink',
                            'slug'  => 'pale-pink',
                            'color' => '#f78da7',
                        ],
                        [
                            'name'  => 'Vivid Red',
                            'slug'  => 'vivid-red',
                            'color' => '#cf2e2e',
                        ],
                    ],
                    'custom'  => false,
                    'link'    => true,
                ],
                'border'     => [
                    'color'        => false,
                    'customRadius' => false,
                    'style'        => false,
                    'width'        => false,
                ],
                'typography' => [
                    'fontSizes'      => [
                        [
                            'name' => 'Small',
                            'slug' => 'small',
                            'size' => 12,
                        ],
                        [
                            'name' => 'Normal',
                            'slug' => 'normal',
                            'size' => 16,
                        ],
                    ],
                    'fontStyle'      => false,
                    'fontWeight'     => false,
                    'letterSpacing'  => false,
                    'textDecoration' => false,
                    'textTransform'  => false,
                ],
                'blocks'     => [
                    'core/group' => [
                        'border'     => [
                            'color'        => true,
                            'customRadius' => true,
                            'style'        => true,
                            'width'        => true,
                        ],
                        'typography' => [
                            'fontStyle'      => true,
                            'fontWeight'     => true,
                            'letterSpacing'  => true,
                            'textDecoration' => true,
                            'textTransform'  => true,
                        ],
                    ],
                ],
            ],
            'styles'   => [
                'color'    => [
                    'background' => 'purple',
                ],
                'blocks'   => [
                    'core/group' => [
                        'color'    => [
                            'background' => 'red',
                        ],
                        'spacing'  => [
                            'padding' => [
                                'top' => '10px',
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
                'elements' => [
                    'link' => [
                        'color' => [
                            'text' => 'red',
                        ],
                    ],
                ],
            ],
        ];

        $actual = WP_Theme_JSON_Schema::migrate($theme_json_v1);

        $expected = [
            'version'  => WP_Theme_JSON::LATEST_SCHEMA,
            'settings' => [
                'color'      => [
                    'palette' => [
                        [
                            'name'  => 'Pale Pink',
                            'slug'  => 'pale-pink',
                            'color' => '#f78da7',
                        ],
                        [
                            'name'  => 'Vivid Red',
                            'slug'  => 'vivid-red',
                            'color' => '#cf2e2e',
                        ],
                    ],
                    'custom'  => false,
                    'link'    => true,
                ],
                'border'     => [
                    'color'  => false,
                    'radius' => false,
                    'style'  => false,
                    'width'  => false,
                ],
                'typography' => [
                    'defaultFontSizes' => false,
                    'fontSizes'        => [
                        [
                            'name' => 'Small',
                            'slug' => 'small',
                            'size' => 12,
                        ],
                        [
                            'name' => 'Normal',
                            'slug' => 'normal',
                            'size' => 16,
                        ],
                    ],
                    'fontStyle'        => false,
                    'fontWeight'       => false,
                    'letterSpacing'    => false,
                    'textDecoration'   => false,
                    'textTransform'    => false,
                ],
                'blocks'     => [
                    'core/group' => [
                        'border'     => [
                            'color'  => true,
                            'radius' => true,
                            'style'  => true,
                            'width'  => true,
                        ],
                        'typography' => [
                            'fontStyle'      => true,
                            'fontWeight'     => true,
                            'letterSpacing'  => true,
                            'textDecoration' => true,
                            'textTransform'  => true,
                        ],
                    ],
                ],
            ],
            'styles'   => [
                'color'    => [
                    'background' => 'purple',
                ],
                'blocks'   => [
                    'core/group' => [
                        'color'    => [
                            'background' => 'red',
                        ],
                        'spacing'  => [
                            'padding' => [
                                'top' => '10px',
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
                'elements' => [
                    'link' => [
                        'color' => [
                            'text' => 'red',
                        ],
                    ],
                ],
            ],
        ];

        $this->assertEqualSetsWithIndex($expected, $actual);
    }

    public function test_migrate_v2_to_latest()
    {
        $theme_json_v2 = [
            'version'  => 2,
            'settings' => [
                'typography' => [
                    'fontSizes' => [
                        [
                            'name' => 'Small',
                            'slug' => 'small',
                            'size' => 12,
                        ],
                        [
                            'name' => 'Normal',
                            'slug' => 'normal',
                            'size' => 16,
                        ],
                    ],
                ],
                'spacing'    => [
                    'spacingSizes' => [
                        [
                            'name' => 'Small',
                            'slug' => 20,
                            'size' => '20px',
                        ],
                        [
                            'name' => 'Large',
                            'slug' => 80,
                            'size' => '80px',
                        ],
                    ],
                ],
            ],
        ];

        $actual = WP_Theme_JSON_Schema::migrate($theme_json_v2);

        $expected = [
            'version'  => WP_Theme_JSON::LATEST_SCHEMA,
            'settings' => [
                'typography' => [
                    'defaultFontSizes' => false,
                    'fontSizes'        => [
                        [
                            'name' => 'Small',
                            'slug' => 'small',
                            'size' => 12,
                        ],
                        [
                            'name' => 'Normal',
                            'slug' => 'normal',
                            'size' => 16,
                        ],
                    ],
                ],
                'spacing'    => [
                    'defaultSpacingSizes' => false,
                    'spacingSizes'        => [
                        [
                            'name' => 'Small',
                            'slug' => 20,
                            'size' => '20px',
                        ],
                        [
                            'name' => 'Large',
                            'slug' => 80,
                            'size' => '80px',
                        ],
                    ],
                ],
            ],
        ];

        $this->assertEqualSetsWithIndex($expected, $actual);
    }
}
