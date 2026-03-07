<?php

declare(strict_types=1);
/**
 * Test WP_Font_Collection::get_data.
 *
 * @package WordPress
 * @subpackage Font Library
 *
 * @group fonts
 * @group font-library
 *
 * @covers WP_Font_Collection::get_data
 */
class Tests_Fonts_WpFontCollection_GetData extends WP_UnitTestCase
{
    private static $mock_collection_data;

    /**
     * @dataProvider data_create_font_collection
     *
     * @param string $slug          Font collection slug.
     * @param array  $config        Font collection config.
     * @param array  $expected_data Expected collection data.
     */
    public function test_should_get_data_from_config_array($slug, $config, $expected_data)
    {
        $collection = new WP_Font_Collection($slug, $config);
        $data       = $collection->get_data();

        $this->assertSame($slug, $collection->slug, 'The slug should match.');
        $this->assertSame($expected_data, $data, 'The collection data should match.');
    }

    /**
     * @dataProvider data_create_font_collection
     *
     * @param string $slug          Font collection slug.
     * @param array  $config        Font collection config.
     * @param array  $expected_data Expected collection data.
     */
    public function test_should_get_data_from_json_file($slug, $config, $expected_data)
    {
        $mock_file = wp_tempnam('my-collection-data-');
        file_put_contents($mock_file, wp_json_encode($config));

        $collection = new WP_Font_Collection(
            $slug,
            array_merge(
                $config,
                [ 'font_families' => $mock_file ]
            )
        );
        $data       = $collection->get_data();

        $this->assertSame($slug, $collection->slug, 'The slug should match.');
        $this->assertEqualSetsWithIndex($expected_data, $data, 'The collection data should match.');
    }

    /**
     * @dataProvider data_create_font_collection
     *
     * @param string $slug          Font collection slug.
     * @param array  $config        Font collection config.
     * @param array  $expected_data Expected collection data.
     */
    public function test_should_get_data_from_json_url($slug, $config, $expected_data)
    {
        add_filter('pre_http_request', [ $this, 'mock_request' ], 10, 3);

        self::$mock_collection_data = $config;
        $collection                 = new WP_Font_Collection(
            $slug,
            array_merge(
                $config,
                [
                    'font_families' => 'https://example.com/fonts/mock-font-collection.json',
                ]
            )
        );
        $data                       = $collection->get_data();

        remove_filter('pre_http_request', [ $this, 'mock_request' ]);

        $this->assertSame($slug, $collection->slug, 'The slug should match.');
        $this->assertEqualSetsWithIndex($expected_data, $data, 'The collection data should match.');
    }

    /**
     * Data provider.
     *
     * @return array
     */
    public function data_create_font_collection()
    {
        return [
            'font collection with required data' => [
                'slug'          => 'my-collection',
                'config'        => [
                    'name'          => 'My Collection',
                    'font_families' => [ [] ],
                ],
                'expected_data' => [
                    'description'   => '',
                    'categories'    => [],
                    'name'          => 'My Collection',
                    'font_families' => [ [] ],
                ],
            ],

            'font collection with all data'      => [
                'slug'          => 'my-collection',
                'config'        => [
                    'name'          => 'My Collection',
                    'description'   => 'My collection description',
                    'font_families' => [ [] ],
                    'categories'    => [],
                ],
                'expected_data' => [
                    'description'   => 'My collection description',
                    'categories'    => [],
                    'name'          => 'My Collection',
                    'font_families' => [ [] ],
                ],
            ],

            'font collection with risky data'    => [
                'slug'          => 'my-collection',
                'config'        => [
                    'name'              => 'My Collection<script>alert("xss")</script>',
                    'description'       => 'My collection description<script>alert("xss")</script>',
                    'font_families'     => [
                        [
                            'font_family_settings' => [
                                'fontFamily'        => 'Open Sans, sans-serif<script>alert("xss")</script>',
                                'slug'              => 'open-sans',
                                'name'              => 'Open Sans<script>alert("xss")</script>',
                                'fontFace'          => [
                                    [
                                        'fontFamily' => 'Open Sans',
                                        'fontStyle'  => 'normal',
                                        'fontWeight' => '400',
                                        'src'        => 'https://example.com/src-as-string.ttf?a=<script>alert("xss")</script>',
                                    ],
                                    [
                                        'fontFamily' => 'Open Sans',
                                        'fontStyle'  => 'normal',
                                        'fontWeight' => '400',
                                        'src'        => [
                                            'https://example.com/src-as-array.woff2?a=<script>alert("xss")</script>',
                                            'https://example.com/src-as-array.ttf',
                                        ],
                                    ],
                                ],
                                'unwanted_property' => 'potentially evil value',
                            ],
                            'categories'           => [ 'sans-serif<script>alert("xss")</script>' ],
                        ],
                    ],
                    'categories'        => [
                        [
                            'name'              => 'Mock col<script>alert("xss")</script>',
                            'slug'              => 'mock-col<script>alert("xss")</script>',
                            'unwanted_property' => 'potentially evil value',
                        ],
                    ],
                    'unwanted_property' => 'potentially evil value',
                ],
                'expected_data' => [
                    'description'   => 'My collection description',
                    'categories'    => [
                        [
                            'name' => 'Mock col',
                            'slug' => 'mock-colalertxss',
                        ],
                    ],
                    'name'          => 'My Collection',
                    'font_families' => [
                        [
                            'font_family_settings' => [
                                'fontFamily' => '"Open Sans", sans-serif',
                                'slug'       => 'open-sans',
                                'name'       => 'Open Sans',
                                'fontFace'   => [
                                    [
                                        'fontFamily' => 'Open Sans',
                                        'fontStyle'  => 'normal',
                                        'fontWeight' => '400',
                                        'src'        => 'https://example.com/src-as-string.ttf?a=',
                                    ],
                                    [
                                        'fontFamily' => 'Open Sans',
                                        'fontStyle'  => 'normal',
                                        'fontWeight' => '400',
                                        'src'        => [
                                            'https://example.com/src-as-array.woff2?a=',
                                            'https://example.com/src-as-array.ttf',
                                        ],
                                    ],
                                ],
                            ],
                            'categories'           => [ 'sans-serifalertxss' ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider data_should_error_when_missing_properties
     *
     * @param array $config Font collection config.
     */
    public function test_should_error_when_missing_properties($config)
    {
        $this->setExpectedIncorrectUsage('WP_Font_Collection::sanitize_and_validate_data');

        $collection = new WP_Font_Collection('my-collection', $config);
        $data       = $collection->get_data();

        $this->assertWPError($data, 'Error is not returned when property is missing or invalid.');
        $this->assertSame(
            'font_collection_missing_property',
            $data->get_error_code(),
            'Incorrect error code when property is missing or invalid.'
        );
    }

    /**
     * Data provider.
     *
     * @return array
     */
    public function data_should_error_when_missing_properties()
    {
        return [
            'missing name'          => [
                'config' => [
                    'font_families' => [ 'mock' ],
                ],
            ],
            'empty name'            => [
                'config' => [
                    'name'          => '',
                    'font_families' => [ 'mock' ],
                ],
            ],
            'missing font_families' => [
                'config' => [
                    'name' => 'My Collection',
                ],
            ],
            'empty font_families'   => [
                'config' => [
                    'name'          => 'My Collection',
                    'font_families' => [],
                ],
            ],
        ];
    }

    public function test_should_error_with_invalid_json_file_path()
    {
        $this->setExpectedIncorrectUsage('WP_Font_Collection::load_from_json');

        $collection = new WP_Font_Collection(
            'my-collection',
            [
                'name'          => 'My collection',
                'font_families' => 'non-existing.json',
            ]
        );
        $data       = $collection->get_data();

        $this->assertWPError($data, 'Error is not returned when invalid file path is provided.');
        $this->assertSame(
            'font_collection_json_missing',
            $data->get_error_code(),
            'Incorrect error code when invalid file path is provided.'
        );
    }

    public function test_should_error_with_invalid_json_from_file()
    {
        $mock_file = wp_tempnam('my-collection-data-');
        file_put_contents($mock_file, 'invalid-json');

        $collection = new WP_Font_Collection(
            'my-collection',
            [
                'name'          => 'Invalid collection',
                'font_families' => $mock_file,
            ]
        );

        // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- Testing error response returned by `load_from_json`, not the underlying error from `wp_json_file_decode`.
        $data = @$collection->get_data();

        $this->assertWPError($data, 'Error is not returned with invalid json file contents.');
        $this->assertSame(
            'font_collection_decode_error',
            $data->get_error_code(),
            'Incorrect error code with invalid json file contents.'
        );
    }

    public function test_should_error_with_invalid_url()
    {
        $this->setExpectedIncorrectUsage('WP_Font_Collection::load_from_json');

        $collection = new WP_Font_Collection(
            'my-collection',
            [
                'name'          => 'Invalid collection',
                'font_families' => 'not-a-url',
            ]
        );
        $data       = $collection->get_data();

        $this->assertWPError($data, 'Error is not returned when invalid url is provided.');
        $this->assertSame(
            'font_collection_json_missing',
            $data->get_error_code(),
            'Incorrect error code when invalid url is provided.'
        );
    }

    public function test_should_error_with_unsuccessful_response_status()
    {
        add_filter('pre_http_request', [ $this, 'mock_request_unsuccessful_response' ], 10, 3);

        $collection = new WP_Font_Collection(
            'my-collection',
            [
                'name'          => 'Missing collection',
                'font_families' => 'https://example.com/fonts/missing-collection.json',
            ]
        );
        $data       = $collection->get_data();

        remove_filter('pre_http_request', [ $this, 'mock_request_unsuccessful_response' ]);

        $this->assertWPError($data, 'Error is not returned when response is unsuccessful.');
        $this->assertSame(
            'font_collection_request_error',
            $data->get_error_code(),
            'Incorrect error code when response is unsuccessful.'
        );
    }

    public function test_should_error_with_invalid_json_from_url()
    {
        add_filter('pre_http_request', [ $this, 'mock_request_invalid_json' ], 10, 3);

        $collection = new WP_Font_Collection(
            'my-collection',
            [
                'name'          => 'Invalid collection',
                'font_families' => 'https://example.com/fonts/invalid-collection.json',
            ]
        );
        $data       = $collection->get_data();

        remove_filter('pre_http_request', [ $this, 'mock_request_invalid_json' ]);

        $this->assertWPError($data, 'Error is not returned when response is invalid json.');
        $this->assertSame(
            'font_collection_decode_error',
            $data->get_error_code(),
            'Incorrect error code when response is invalid json.'
        );
    }

    public function mock_request($preempt, $args, $url)
    {
        if ('https://example.com/fonts/mock-font-collection.json' !== $url) {
            return false;
        }

        return [
            'body'     => wp_json_encode(self::$mock_collection_data),
            'response' => [
                'code' => 200,
            ],
        ];
    }

    public function mock_request_unsuccessful_response($preempt, $args, $url)
    {
        if ('https://example.com/fonts/missing-collection.json' !== $url) {
            return false;
        }

        return [
            'body'     => '',
            'response' => [
                'code' => 404,
            ],
        ];
    }

    public function mock_request_invalid_json($preempt, $args, $url)
    {
        if ('https://example.com/fonts/invalid-collection.json' !== $url) {
            return false;
        }

        return [
            'body'     => 'invalid',
            'response' => [
                'code' => 200,
            ],
        ];
    }
}
