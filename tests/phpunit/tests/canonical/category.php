<?php

declare(strict_types=1);

/**
 * @group canonical
 * @group rewrite
 * @group query
 */
class Tests_Canonical_Category extends WP_Canonical_UnitTestCase
{
    public $structure = '/%category%/%postname%/';

    public static $posts = [];
    public static $cats  = [];

    public static function wpSetUpBeforeClass(WP_UnitTest_Factory $factory)
    {

        self::$posts[0] = $factory->post->create([ 'post_name' => 'post0' ]);
        self::$posts[1] = $factory->post->create([ 'post_name' => 'post1' ]);
        self::$cats[0]  = $factory->category->create([ 'slug' => 'cat0' ]);
        self::$cats[1]  = $factory->category->create([ 'slug' => 'cat1' ]);
        self::$cats[2]  = $factory->category->create([ 'slug' => 'cat2' ]);

        wp_set_post_categories(self::$posts[0], self::$cats[2]);
        wp_set_post_categories(self::$posts[0], self::$cats[0]);
        wp_set_post_categories(self::$posts[1], self::$cats[1]);
    }

    /**
     * @dataProvider data_canonical_category
     */
    public function test_canonical_category($test_url, $expected, $ticket = 0, $expected_doing_it_wrong = [])
    {
        $this->assertCanonical($test_url, $expected, $ticket, $expected_doing_it_wrong);
    }

    public function data_canonical_category()
    {
        /*
         * Data format:
         * [0]: Test URL.
         * [1]: Expected results: Any of the following can be used.
         *      array( 'url': expected redirection location, 'qv': expected query vars to be set via the rewrite AND $_GET );
         *      array( expected query vars to be set, same as 'qv' above )
         *      (string) expected redirect location
         * [2]: (optional) The ticket the test refers to, Can be skipped if unknown.
         * [3]: (optional) Array of class/function names expected to throw `_doing_it_wrong()` notices.
         */

        return [
            // Valid category.
            [
                '/cat0/post0/',
                [
                    'url' => '/cat0/post0/',
                    'qv'  => [
                        'category_name' => 'cat0',
                        'name'          => 'post0',
                        'page'          => '',
                    ],
                ],
            ],

            // Category other than the first one will redirect to first "canonical" category.
            [
                '/cat2/post0/',
                [
                    'url' => '/cat0/post0/',
                    'qv'  => [
                        'category_name' => 'cat0',
                        'name'          => 'post0',
                        'page'          => '',
                    ],
                ],
            ],

            // Incorrect category will redirect to correct one.
            [
                '/cat1/post0/',
                [
                    'url' => '/cat0/post0/',
                    'qv'  => [
                        'category_name' => 'cat0',
                        'name'          => 'post0',
                        'page'          => '',
                    ],
                ],
            ],

            // Nonexistent category will redirect to correct one.
            [
                '/foo/post0/',
                [
                    'url' => '/cat0/post0/',
                    'qv'  => [
                        'category_name' => 'cat0',
                        'name'          => 'post0',
                        'page'          => '',
                    ],
                ],
            ],

            // Embed URLs should not redirect to post permalinks.
            [
                '/cat0/post0/embed/',
                [
                    'url' => '/cat0/post0/embed/',
                    'qv'  => [
                        'category_name' => 'cat0',
                        'name'          => 'post0',
                        'embed'         => 'true',
                    ],
                ],
            ],
        ];
    }
}
