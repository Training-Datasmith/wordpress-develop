<?php

declare(strict_types=1);
/**
 * Test WP_Font_Utils::sanitize_from_schema().
 *
 * @package WordPress
 * @subpackage Font Library
 *
 * @group fonts
 * @group font-library
 *
 * @covers WP_Font_Utils::sanitize_from_schema
 */
class Tests_Fonts_WpFontUtils_SanitizeFromSchema extends WP_UnitTestCase
{
    /**
     * @dataProvider data_sanitize_from_schema
     *
     * @param array $data     Data to sanitize.
     * @param array $schema   Schema to use for sanitization.
     * @param array $expected Expected result.
     */
    public function test_sanitize_from_schema($data, $schema, $expected)
    {
        $result = WP_Font_Utils::sanitize_from_schema($data, $schema);

        $this->assertSame($result, $expected);
    }

    public function data_sanitize_from_schema()
    {
        return [
            'One level associative array'  => [
                'data'     => [
                    'slug'       => 'open      -       sans</style><script>alert("xss")</script>',
                    'fontFamily' => 'Open Sans, sans-serif</style><script>alert("xss")</script>',
                    'src'        => 'https://wordpress.org/example.json</style><script>alert("xss")</script>',
                ],
                'schema'   => [
                    'slug'       => 'sanitize_title',
                    'fontFamily' => 'sanitize_text_field',
                    'src'        => 'sanitize_url',
                ],
                'expected' => [
                    'slug'       => 'open-sansalertxss',
                    'fontFamily' => 'Open Sans, sans-serif',
                    'src'        => 'https://wordpress.org/example.json/stylescriptalert(xss)/script',
                ],
            ],

            'Nested associative arrays'    => [
                'data'     => [
                    'slug'       => 'open      -       sans</style><script>alert("xss")</script>',
                    'fontFamily' => 'Open Sans, sans-serif</style><script>alert("xss")</script>',
                    'src'        => 'https://wordpress.org/example.json</style><script>alert("xss")</script>',
                    'nested'     => [
                        'key1'    => 'value1</style><script>alert("xss")</script>',
                        'key2'    => 'value2</style><script>alert("xss")</script>',
                        'nested2' => [
                            'key3' => 'value3</style><script>alert("xss")</script>',
                            'key4' => 'value4</style><script>alert("xss")</script>',
                        ],
                    ],
                ],
                'schema'   => [
                    'slug'       => 'sanitize_title',
                    'fontFamily' => 'sanitize_text_field',
                    'src'        => 'sanitize_url',
                    'nested'     => [
                        'key1'    => 'sanitize_text_field',
                        'key2'    => 'sanitize_text_field',
                        'nested2' => [
                            'key3' => 'sanitize_text_field',
                            'key4' => 'sanitize_text_field',
                        ],
                    ],
                ],
                'expected' => [
                    'slug'       => 'open-sansalertxss',
                    'fontFamily' => 'Open Sans, sans-serif',
                    'src'        => 'https://wordpress.org/example.json/stylescriptalert(xss)/script',
                    'nested'     => [
                        'key1'    => 'value1',
                        'key2'    => 'value2',
                        'nested2' => [
                            'key3' => 'value3',
                            'key4' => 'value4',
                        ],
                    ],
                ],
            ],

            'Indexed arrays'               => [
                'data'     => [
                    'slug' => 'oPeN SaNs',
                    'enum' => [
                        'value1<script>alert("xss")</script>',
                        'value2<script>alert("xss")</script>',
                        'value3<script>alert("xss")</script>',
                    ],
                ],
                'schema'   => [
                    'slug' => 'sanitize_title',
                    'enum' => [ 'sanitize_text_field' ],
                ],
                'expected' => [
                    'slug' => 'open-sans',
                    'enum' => [ 'value1', 'value2', 'value3' ],
                ],
            ],

            'Nested indexed arrays'        => [
                'data'     => [
                    'slug'     => 'OPEN-SANS',
                    'name'     => 'Open Sans</style><script>alert("xss")</script>',
                    'fontFace' => [
                        [
                            'fontFamily' => 'Open Sans, sans-serif</style><script>alert("xss")</script>',
                            'src'        => 'https://wordpress.org/example.json/stylescriptalert(xss)/script',
                        ],
                        [
                            'fontFamily' => 'Open Sans, sans-serif</style><script>alert("xss")</script>',
                            'src'        => 'https://wordpress.org/example.json/stylescriptalert(xss)/script',
                        ],
                    ],
                ],
                'schema'   => [
                    'slug'     => 'sanitize_title',
                    'name'     => 'sanitize_text_field',
                    'fontFace' => [
                        [
                            'fontFamily' => 'sanitize_text_field',
                            'src'        => 'sanitize_url',
                        ],
                    ],
                ],
                'expected' => [
                    'slug'     => 'open-sans',
                    'name'     => 'Open Sans',
                    'fontFace' => [
                        [
                            'fontFamily' => 'Open Sans, sans-serif',
                            'src'        => 'https://wordpress.org/example.json/stylescriptalert(xss)/script',
                        ],
                        [
                            'fontFamily' => 'Open Sans, sans-serif',
                            'src'        => 'https://wordpress.org/example.json/stylescriptalert(xss)/script',
                        ],
                    ],
                ],
            ],

            'Custom sanitization function' => [
                'data'     => [
                    'key1' => 'abc123edf456ghi789',
                    'key2' => 'value2',
                ],
                'schema'   => [
                    'key1' => function ($value) {
                        // Remove the six first character.
                        return substr($value, 6);
                    },
                    'key2' => function ($value) {
                        // Capitalize the value.
                        return strtoupper($value);
                    },
                ],
                'expected' => [
                    'key1' => 'edf456ghi789',
                    'key2' => 'VALUE2',
                ],
            ],

            'Null as schema value'         => [
                'data'     => [
                    'key1'   => 'value1<script>alert("xss")</script>',
                    'key2'   => 'value2',
                    'nested' => [
                        'key3' => 'value3',
                        'key4' => 'value4',
                    ],
                ],
                'schema'   => [
                    'key1'   => null,
                    'key2'   => 'sanitize_text_field',
                    'nested' => null,
                ],
                'expected' => [
                    'key1'   => 'value1<script>alert("xss")</script>',
                    'key2'   => 'value2',
                    'nested' => [
                        'key3' => 'value3',
                        'key4' => 'value4',
                    ],
                ],
            ],

            'Keys to remove'               => [
                'data'     => [
                    'key1'              => 'value1',
                    'key2'              => 'value2',
                    'unwanted1'         => 'value',
                    'unwanted2'         => 'value',
                    'nestedAssociative' => [
                        'key5'      => 'value5',
                        'unwanted3' => 'value',
                    ],
                    'nestedIndexed'     => [
                        [
                            'key6'      => 'value7',
                            'unwanted4' => 'value',
                        ],
                        [
                            'key6'      => 'value7',
                            'unwanted5' => 'value',
                        ],
                    ],

                ],
                'schema'   => [
                    'key1'              => 'sanitize_text_field',
                    'key2'              => 'sanitize_text_field',
                    'nestedAssociative' => [
                        'key5' => 'sanitize_text_field',
                    ],
                    'nestedIndexed'     => [
                        [
                            'key6' => 'sanitize_text_field',
                        ],
                    ],
                ],
                'expected' => [
                    'key1'              => 'value1',
                    'key2'              => 'value2',
                    'nestedAssociative' => [
                        'key5' => 'value5',
                    ],
                    'nestedIndexed'     => [
                        [
                            'key6' => 'value7',
                        ],
                        [
                            'key6' => 'value7',
                        ],
                    ],
                ],
            ],

            'With empty structure'         => [
                'data'     => [
                    'slug'   => 'open-sans',
                    'nested' => [
                        'key1'    => 'value</style><script>alert("xss")</script>',
                        'nested2' => [
                            'key2'    => 'value</style><script>alert("xss")</script>',
                            'nested3' => [
                                'nested4' => [],
                            ],
                        ],
                    ],
                ],
                'schema'   => [
                    'slug'   => 'sanitize_title',
                    'nested' => [
                        'key1'    => 'sanitize_text_field',
                        'nested2' => [
                            'key2'    => 'sanitize_text_field',
                            'nested3' => [
                                'key3'    => 'sanitize_text_field',
                                'nested4' => [
                                    'key4' => 'sanitize_text_field',
                                ],
                            ],
                        ],
                    ],
                ],
                'expected' => [
                    'slug'   => 'open-sans',
                    'nested' => [
                        'key1'    => 'value',
                        'nested2' => [
                            'key2' => 'value',
                        ],
                    ],
                ],
            ],
        ];
    }

    public function test_sanitize_from_schema_with_invalid_data()
    {
        $data   = 'invalid data';
        $schema = [
            'key1' => 'sanitize_text_field',
            'key2' => 'sanitize_text_field',
        ];

        $result = WP_Font_Utils::sanitize_from_schema($data, $schema);

        $this->assertSame($result, []);
    }

    public function test_sanitize_from_schema_with_invalid_schema()
    {
        $data   = [
            'key1' => 'value1',
            'key2' => 'value2',
        ];
        $schema = 'invalid schema';

        $result = WP_Font_Utils::sanitize_from_schema($data, $schema);

        $this->assertSame($result, []);
    }
}
