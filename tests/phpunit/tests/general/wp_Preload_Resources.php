<?php

declare(strict_types=1);

/**
 * @group general
 * @group template
 * @ticket 42438
 * @covers ::wp_preload_resources
 */
class Tests_General_wpPreloadResources extends WP_UnitTestCase
{
    /**
     * @dataProvider data_preload_resources
     *
     * @ticket 42438
     */
    public function test_preload_resources($expected, $preload_resources)
    {
        $callback = static function () use ($preload_resources) {
            return $preload_resources;
        };

        add_filter('wp_preload_resources', $callback, 10);
        $actual = get_echo('wp_preload_resources');
        remove_filter('wp_preload_resources', $callback);

        $this->assertSame($expected, $actual);
    }

    /**
     * Test provider for all preload link possible combinations.
     *
     * @return array[]
     */
    public function data_preload_resources()
    {
        return [
            'basic_preload'          => [
                'expected' => "<link rel='preload' href='https://example.com/style.css' as='style' />\n",
                'urls'     => [
                    [
                        'href' => 'https://example.com/style.css',
                        'as'   => 'style',
                    ],
                ],
            ],
            'multiple_links'         => [
                'expected' => "<link rel='preload' href='https://example.com/style.css' as='style' />\n" .
                            "<link rel='preload' href='https://example.com/main.js' as='script' />\n",
                'urls'     => [
                    [
                        'href' => 'https://example.com/style.css',
                        'as'   => 'style',
                    ],
                    [
                        'href' => 'https://example.com/main.js',
                        'as'   => 'script',
                    ],
                ],
            ],
            'MIME_types'             => [
                'expected' => "<link rel='preload' href='https://example.com/style.css' as='style' />\n" .
                            "<link rel='preload' href='https://example.com/video.mp4' as='video' type='video/mp4' />\n" .
                            "<link rel='preload' href='https://example.com/main.js' as='script' />\n",
                'urls'     => [
                    [
                        // Should ignore not valid attributes.
                        'not'  => 'valid',
                        'href' => 'https://example.com/style.css',
                        'as'   => 'style',
                    ],
                    [
                        'href' => 'https://example.com/video.mp4',
                        'as'   => 'video',
                        'type' => 'video/mp4',
                    ],
                    [
                        'href' => 'https://example.com/main.js',
                        'as'   => 'script',
                    ],
                ],
            ],
            'CORS'                   => [
                'expected' => "<link rel='preload' href='https://example.com/style.css' as='style' crossorigin='anonymous' />\n" .
                            "<link rel='preload' href='https://example.com/video.mp4' as='video' type='video/mp4' />\n" .
                            "<link rel='preload' href='https://example.com/main.js' as='script' />\n" .
                            "<link rel='preload' href='https://example.com/font.woff2' as='font' type='font/woff2' crossorigin />\n",
                'urls'     => [
                    [
                        'href'        => 'https://example.com/style.css',
                        'as'          => 'style',
                        'crossorigin' => 'anonymous',
                    ],
                    [
                        'href' => 'https://example.com/video.mp4',
                        'as'   => 'video',
                        'type' => 'video/mp4',
                    ],
                    [
                        'href' => 'https://example.com/main.js',
                        'as'   => 'script',
                    ],
                    [
                        // Should ignore not valid attributes.
                        'ignore' => 'ignore',
                        'href'   => 'https://example.com/font.woff2',
                        'as'     => 'font',
                        'type'   => 'font/woff2',
                        'crossorigin',
                    ],
                ],
            ],
            'media'                  => [
                'expected' => "<link rel='preload' href='https://example.com/style.css' as='style' crossorigin='anonymous' />\n" .
                            "<link rel='preload' href='https://example.com/video.mp4' as='video' type='video/mp4' />\n" .
                            "<link rel='preload' href='https://example.com/main.js' as='script' />\n" .
                            "<link rel='preload' href='https://example.com/font.woff2' as='font' type='font/woff2' crossorigin />\n" .
                            "<link rel='preload' href='https://example.com/image-narrow.png' as='image' media='(max-width: 600px)' />\n" .
                            "<link rel='preload' href='https://example.com/image-wide.png' as='image' media='(min-width: 601px)' />\n",
                'urls'     => [
                    [
                        'href'        => 'https://example.com/style.css',
                        'as'          => 'style',
                        'crossorigin' => 'anonymous',
                    ],
                    [
                        'href' => 'https://example.com/video.mp4',
                        'as'   => 'video',
                        'type' => 'video/mp4',
                    ],
                    // Duplicated href should be ignored.
                    [
                        'href' => 'https://example.com/video.mp4',
                        'as'   => 'video',
                        'type' => 'video/mp4',
                    ],
                    [
                        'href' => 'https://example.com/main.js',
                        'as'   => 'script',
                    ],
                    [
                        'href' => 'https://example.com/font.woff2',
                        'as'   => 'font',
                        'type' => 'font/woff2',
                        'crossorigin',
                    ],
                    [
                        'href'  => 'https://example.com/image-narrow.png',
                        'as'    => 'image',
                        'media' => '(max-width: 600px)',
                    ],
                    [
                        'href'  => 'https://example.com/image-wide.png',
                        'as'    => 'image',
                        'media' => '(min-width: 601px)',
                    ],

                ],
            ],
            'media_extra_attributes' => [
                'expected' => "<link rel='preload' href='https://example.com/style.css' as='style' crossorigin='anonymous' />\n" .
                            "<link rel='preload' href='https://example.com/video.mp4' as='video' type='video/mp4' />\n" .
                            "<link rel='preload' href='https://example.com/main.js' as='script' />\n" .
                            "<link rel='preload' href='https://example.com/font.woff2' as='font' type='font/woff2' crossorigin />\n" .
                            "<link rel='preload' href='https://example.com/image-640.png' as='image' imagesrcset='640.png 640w, 800.png 800w, 1024.png 1024w' imagesizes='100vw' />\n" .
                            "<link rel='preload' as='image' imagesrcset='640.png 640w, 800.png 800w, 1024.png 1024w' imagesizes='100vw' />\n" .
                            "<link rel='preload' href='https://example.com/image-wide.png' as='image' media='(min-width: 601px)' />\n" .
                            "<link rel='preload' href='https://example.com/image-800.png' as='image' imagesrcset='640.png 640w, 800.png 800w, 1024.png 1024w' />\n",
                'urls'     => [
                    [
                        'href'        => 'https://example.com/style.css',
                        'as'          => 'style',
                        'crossorigin' => 'anonymous',
                    ],
                    [
                        'href' => 'https://example.com/video.mp4',
                        'as'   => 'video',
                        'type' => 'video/mp4',
                    ],
                    [
                        'href' => 'https://example.com/main.js',
                        'as'   => 'script',
                    ],
                    [
                        'href' => 'https://example.com/font.woff2',
                        'as'   => 'font',
                        'type' => 'font/woff2',
                        'crossorigin',
                    ],
                    // imagesrcset only possible when using image, ignore.
                    [
                        'href'        => 'https://example.com/font.woff2',
                        'as'          => 'font',
                        'type'        => 'font/woff2',
                        'imagesrcset' => '640.png 640w, 800.png 800w, 1024.png 1024w',
                    ],
                    // imagesizes only possible when using image, ignore.
                    [
                        'href'       => 'https://example.com/font.woff2',
                        'as'         => 'font',
                        'type'       => 'font/woff2',
                        'imagesizes' => '100vw',
                    ],
                    // Duplicated href should be ignored.
                    [
                        'href' => 'https://example.com/font.woff2',
                        'as'   => 'font',
                        'type' => 'font/woff2',
                        'crossorigin',
                    ],
                    [
                        'href'        => 'https://example.com/image-640.png',
                        'as'          => 'image',
                        'imagesrcset' => '640.png 640w, 800.png 800w, 1024.png 1024w',
                        'imagesizes'  => '100vw',
                    ],
                    // Omit href so that unsupporting browsers won't request a useless image.
                    [
                        'as'          => 'image',
                        'imagesrcset' => '640.png 640w, 800.png 800w, 1024.png 1024w',
                        'imagesizes'  => '100vw',
                    ],
                    // Duplicated imagesrcset should be ignored.
                    [
                        'as'          => 'image',
                        'imagesrcset' => '640.png 640w, 800.png 800w, 1024.png 1024w',
                        'imagesizes'  => '100vw',
                    ],
                    [
                        'href'  => 'https://example.com/image-wide.png',
                        'as'    => 'image',
                        'media' => '(min-width: 601px)',
                    ],
                    // No href but not imagesrcset, should be ignored.
                    [
                        'as'    => 'image',
                        'media' => '(min-width: 601px)',
                    ],
                    // imagesizes is optional.
                    [
                        'href'        => 'https://example.com/image-800.png',
                        'as'          => 'image',
                        'imagesrcset' => '640.png 640w, 800.png 800w, 1024.png 1024w',
                    ],
                    // imagesizes should be ignored since imagesrcset not present.
                    [
                        'href'       => 'https://example.com/image-640.png',
                        'as'         => 'image',
                        'imagesizes' => '100vw',
                    ],
                ],
            ],
            'fetchpriority'          => [
                'expected'  => "<link rel='preload' href='https://example.com/image.jpg' as='image' fetchpriority='high' />\n",
                'resources' => [
                    [
                        'href'          => 'https://example.com/image.jpg',
                        'as'            => 'image',
                        'fetchpriority' => 'high',
                    ],
                ],
            ],
        ];
    }
}
