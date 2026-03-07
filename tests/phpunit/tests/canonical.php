<?php

declare(strict_types=1);
/**
 * Tests Canonical redirections.
 *
 * In the process of doing so, it also tests WP, WP_Rewrite and WP_Query, A fail here may show a bug in any one of these areas.
 *
 * @group canonical
 * @group rewrite
 * @group query
 */
class Tests_Canonical extends WP_Canonical_UnitTestCase
{
    public static $private_cpt_post;

    public static function wpSetUpBeforeClass(WP_UnitTest_Factory $factory)
    {
        // Set up fixtures in WP_Canonical_UnitTestCase.
        parent::wpSetUpBeforeClass($factory);

        self::set_up_custom_post_types();
        self::$private_cpt_post = $factory->post->create(
            [
                'post_type'  => 'wp_tests_private',
                'post_title' => 'private-cpt-post',
            ]
        );
    }

    public function set_up()
    {
        parent::set_up();
        wp_set_current_user(self::$author_id);
        self::set_up_custom_post_types();

        update_option('wp_attachment_pages_enabled', 1);
    }

    /**
     * Register custom post type for tests.
     *
     * Register non publicly queryable post type with public set to true.
     *
     * These arguments are intentionally contradictory for the test associated
     * with ticket #59795.
     */
    public static function set_up_custom_post_types()
    {
        register_post_type(
            'wp_tests_private',
            [
                'public'             => true,
                'publicly_queryable' => false,
            ]
        );
    }

    /**
     * @dataProvider data_canonical
     */
    public function test_canonical($test_url, $expected, $ticket = 0, $expected_doing_it_wrong = [])
    {

        if (false !== strpos($test_url, '%d')) {
            if (false !== strpos($test_url, '/?author=%d')) {
                $test_url = sprintf($test_url, self::$author_id);
            }
            if (false !== strpos($test_url, '?cat=%d')) {
                $test_url = sprintf($test_url, self::$terms[ $expected['url'] ]);
            }
        }

        $this->assertCanonical($test_url, $expected, $ticket, $expected_doing_it_wrong);
    }

    public function data_canonical()
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

        // Please Note: A few test cases are commented out below, look at the test case following it.
        // In most cases it's simply showing 2 options for the "proper" redirect.
        return [
            // Categories.
            [ '?cat=%d', [ 'url' => '/category/parent/' ], 15256 ],
            [ '?cat=%d', [ 'url' => '/category/parent/child-1/' ], 15256 ],
            [ '?cat=%d', [ 'url' => '/category/parent/child-1/child-2/' ] ], // No children.
            [
                '/category/uncategorized/',
                [
                    'url' => '/category/uncategorized/',
                    'qv'  => [ 'category_name' => 'uncategorized' ],
                ],
            ],
            [
                '/category/uncategorized/page/2/',
                [
                    'url' => '/category/uncategorized/page/2/',
                    'qv'  => [
                        'category_name' => 'uncategorized',
                        'paged'         => 2,
                    ],
                ],
            ],
            [
                '/category/uncategorized/?paged=2',
                [
                    'url' => '/category/uncategorized/page/2/',
                    'qv'  => [
                        'category_name' => 'uncategorized',
                        'paged'         => 2,
                    ],
                ],
            ],
            [
                '/category/uncategorized/?paged=2&category_name=uncategorized',
                [
                    'url' => '/category/uncategorized/page/2/',
                    'qv'  => [
                        'category_name' => 'uncategorized',
                        'paged'         => 2,
                    ],
                ],
                17174,
            ],

            // Categories & intersections with other vars.
            [
                '/category/uncategorized/?tag=post-formats',
                [
                    'url' => '/category/uncategorized/?tag=post-formats',
                    'qv'  => [
                        'category_name' => 'uncategorized',
                        'tag'           => 'post-formats',
                    ],
                ],
            ],
            [
                '/?category_name=cat-a,cat-b',
                [
                    'url' => '/?category_name=cat-a,cat-b',
                    'qv'  => [ 'category_name' => 'cat-a,cat-b' ],
                ],
            ],

            // Taxonomies with extra query vars.
            [ '/category/cat-a/page/1/?test=one%20two', '/category/cat-a/?test=one%20two', 18086 ], // Extra query vars should stay encoded.

            // Categories with dates.
            [
                '/2008/04/?cat=1',
                [
                    'url' => '/2008/04/?cat=1',
                    'qv'  => [
                        'cat'      => '1',
                        'year'     => '2008',
                        'monthnum' => '04',
                    ],
                ],
                17661,
            ],
            /*
            array(
                '/2008/?category_name=cat-a',
                    array(
                        'url' => '/2008/?category_name=cat-a',
                        'qv'  => array(
                            'category_name' => 'cat-a',
                            'year'          => '2008'
                        )
                    )
            ),
            */

            // Pages.
            [ '/child-page-1/', '/parent-page/child-page-1/' ],
            [ '/?page_id=144', '/parent-page/child-page-1/' ],
            [ '/abo', '/about/' ],
            [ '/parent/child1/grandchild/', '/parent/child1/grandchild/' ],
            [ '/parent/child2/grandchild/', '/parent/child2/grandchild/' ],

            // Posts.
            [ '?p=587', '/2008/06/02/post-format-test-audio/' ],
            [ '/?name=images-test', '/2008/09/03/images-test/' ],
            // Incomplete slug should resolve and remove the ?name= parameter.
            [ '/?name=images-te', '/2008/09/03/images-test/', 20374 ],
            // Page slug should resolve to post slug and remove the ?pagename= parameter.
            [ '/?pagename=images-test', '/2008/09/03/images-test/', 20374 ],

            [ '/2008/06/02/post-format-test-au/', '/2008/06/02/post-format-test-audio/' ],
            [ '/2008/06/post-format-test-au/', '/2008/06/02/post-format-test-audio/' ],
            [ '/2008/post-format-test-au/', '/2008/06/02/post-format-test-audio/' ],
            [ '/2010/post-format-test-au/', '/2008/06/02/post-format-test-audio/' ], // A year the post is not in.
            [ '/post-format-test-au/', '/2008/06/02/post-format-test-audio/' ],

            // Pagination.
            [
                '/2008/09/03/multipage-post-test/3/',
                [
                    'url' => '/2008/09/03/multipage-post-test/3/',
                    'qv'  => [
                        'name'     => 'multipage-post-test',
                        'year'     => '2008',
                        'monthnum' => '09',
                        'day'      => '03',
                        'page'     => '3',
                    ],
                ],
            ],
            [ '/2008/09/03/multipage-post-test/?page=3', '/2008/09/03/multipage-post-test/3/' ],
            [ '/2008/09/03/multipage-post-te?page=3', '/2008/09/03/multipage-post-test/3/' ],

            [ '/2008/09/03/non-paged-post-test/3/', '/2008/09/03/non-paged-post-test/' ],
            [ '/2008/09/03/non-paged-post-test/?page=3', '/2008/09/03/non-paged-post-test/' ],

            // Comments.
            [ '/2008/03/03/comment-test/?cpage=2', '/2008/03/03/comment-test/comment-page-2/' ],

            // Attachments.
            [ '/?attachment_id=611', '/2008/06/10/post-format-test-gallery/canola2/' ],
            [ '/2008/06/10/post-format-test-gallery/?attachment_id=611', '/2008/06/10/post-format-test-gallery/canola2/' ],

            // Dates.
            [ '/?m=2008', '/2008/' ],
            [ '/?m=200809', '/2008/09/' ],
            [ '/?m=20080905', '/2008/09/05/' ],

            [ '/2008/?day=05', '/2008/?day=05' ], // No redirect.
            [ '/2008/09/?day=05', '/2008/09/05/' ],
            [ '/2008/?monthnum=9', '/2008/09/' ],

            [ '/?year=2008', '/2008/' ],

            [ '/2012/13/', '/2012/' ],
            [ '/2012/11/51/', '/2012/11/', 0, [ 'WP_Date_Query' ] ],

            // Authors.
            [ '/?author=%d', '/author/canonical-author/' ],
            // array( '/?author=%d&year=2008', '/2008/?author=3'),
            // array( '/author/canonical-author/?year=2008', '/2008/?author=3'), // Either or, see previous testcase.
            [ '/author/canonical-author/?author[1]=hello', '/author/canonical-author/?author[1]=hello', 60059 ],

            // Feeds.
            [ '/?feed=atom', '/feed/atom/' ],
            [ '/?feed=rss2', '/feed/' ],
            [ '/?feed=comments-rss2', '/comments/feed/' ],
            [ '/?feed=comments-atom', '/comments/feed/atom/' ],

            // Feeds (per-post).
            [ '/2008/03/03/comment-test/?feed=comments-atom', '/2008/03/03/comment-test/feed/atom/' ],
            [ '/?p=149&feed=comments-atom', '/2008/03/03/comment-test/feed/atom/' ],

            // Index.
            [ '/?paged=1', '/' ],
            [ '/page/1/', '/' ],
            [ '/page1/', '/' ],
            [ '/?paged=2', '/page/2/' ],
            [ '/page2/', '/page/2/' ],

            // Misc.
            [ '/2008%20', '/2008' ],
            [ '//2008////', '/2008/' ],

            // @todo Endpoints (feeds, trackbacks, etc). More fuzzed mixed query variables, comment paging, Home page (static).
        ];
    }

    /**
     * @ticket 16557
     */
    public function test_do_redirect_guess_404_permalink()
    {
        // Test disable do_redirect_guess_404_permalink().
        add_filter('do_redirect_guess_404_permalink', '__return_false');
        $this->go_to('/child-page-1');
        $this->assertFalse(redirect_guess_404_permalink());
    }

    /**
     * @ticket 16557
     */
    public function test_pre_redirect_guess_404_permalink()
    {
        // Test short-circuit filter.
        add_filter(
            'pre_redirect_guess_404_permalink',
            static function () {
                return 'wp';
            }
        );
        $this->go_to('/child-page-1');
        $this->assertSame('wp', redirect_guess_404_permalink());
    }

    /**
     * @ticket 16557
     */
    public function test_strict_redirect_guess_404_permalink()
    {
        $post = self::factory()->post->create(
            [
                'post_title' => 'strict-redirect-guess-404-permalink',
            ]
        );

        $this->go_to('strict-redirect');

        // Test default 'non-strict' redirect guess.
        $this->assertSame(get_permalink($post), redirect_guess_404_permalink());

        // Test 'strict' redirect guess.
        add_filter('strict_redirect_guess_404_permalink', '__return_true');
        $this->assertFalse(redirect_guess_404_permalink());
    }

    /**
     * Ensure public posts with custom public statuses are guessed.
     *
     * @ticket 47911
     * @dataProvider data_redirect_guess_404_permalink_with_custom_statuses
     *
     * @covers ::redirect_guess_404_permalink
     */
    public function test_redirect_guess_404_permalink_with_custom_statuses($status_args, $redirects)
    {
        register_post_status('custom', $status_args);

        $post = self::factory()->post->create(
            [
                'post_title'  => 'custom-status-public-guess-404-permalink',
                'post_status' => 'custom',
            ]
        );

        $this->go_to('custom-status-public-guess-404-permalink');

        $expected = $redirects ? get_permalink($post) : false;

        $this->assertSame($expected, redirect_guess_404_permalink());
    }

    /**
     * Data provider for test_redirect_guess_404_permalink_with_custom_statuses().
     *
     * return array[] {
     *    array Arguments used to register custom status
     *    bool  Whether the 404 link is expected to redirect
     * }
     */
    public function data_redirect_guess_404_permalink_with_custom_statuses()
    {
        return [
            'public status'                      => [
                'status_args' => [ 'public' => true ],
                'redirects'   => true,
            ],
            'private status'                     => [
                'status_args' => [ 'public' => false ],
                'redirects'   => false,
            ],
            'internal status'                    => [
                'status_args' => [ 'internal' => true ],
                'redirects'   => false,
            ],
            'protected status'                   => [
                'status_args' => [ 'protected' => true ],
                'redirects'   => false,
            ],
            'protected status flagged as public' => [
                'status_args' => [
                    'protected' => true,
                    'public'    => true,
                ],
                'redirects'   => false,
            ],
        ];
    }

    /**
     * Ensure multiple post types do not throw a notice.
     *
     * @ticket 43056
     * @ticket 59795
     *
     * @dataProvider data_redirect_guess_404_permalink_post_types
     */
    public function test_redirect_guess_404_permalink_post_types($original_url, $expected)
    {
        $this->assertCanonical($original_url, $expected);
    }

    /**
     * Data provider for test_redirect_guess_404_permalink_post_types().
     *
     * In the original URLs the post names are intentionally misspelled
     * to test the redirection.
     *
     * Please do not correct the apparent typos.
     *
     * @return array[]
     */
    public function data_redirect_guess_404_permalink_post_types()
    {
        return [
            'single string formatted post type'    => [
                'original_url' => '/?name=sample-pag&post_type=page',
                'expected'     => '/sample-page/',
            ],
            'single array formatted post type'     => [
                'original_url' => '/?name=sample-pag&post_type[]=page',
                'expected'     => '/sample-page/',
            ],
            'multiple array formatted post type'   => [
                'original_url' => '/?name=sample-pag&post_type[]=page&post_type[]=post',
                'expected'     => '/sample-page/',
            ],
            'do not redirect to private post type' => [
                'original_url' => '/?name=private-cpt-po&post_type[]=wp_tests_private',
                'expected'     => '/?name=private-cpt-po&post_type[]=wp_tests_private',
            ],
        ];
    }

    /**
     * @ticket 43745
     */
    public function test_utf8_query_keys_canonical()
    {
        $p = self::factory()->post->create(
            [
                'post_type' => 'page',
            ]
        );
        update_option('show_on_front', 'page');
        update_option('page_on_front', $p);

        $this->go_to(get_permalink($p));

        $redirect = redirect_canonical(add_query_arg('%D0%BA%D0%BE%D0%BA%D0%BE%D0%BA%D0%BE', 1, site_url('/')), false);

        delete_option('page_on_front');

        $this->assertNull($redirect);
    }

    /**
     * Ensure NOT EXISTS queries do not trigger not-countable or undefined array key errors.
     *
     * @ticket 55955
     */
    public function test_feed_canonical_with_not_exists_query()
    {
        // Set a NOT EXISTS tax_query on the global query.
        $global_query        = $GLOBALS['wp_query'];
        $GLOBALS['wp_query'] = new WP_Query(
            [
                'post_type' => 'post',
                'tax_query' => [
                    [
                        'taxonomy' => 'post_format',
                        'operator' => 'NOT EXISTS',
                    ],
                ],
            ]
        );

        $redirect = redirect_canonical(get_term_feed_link(self::$terms['/category/parent/']), false);

        // Restore original global.
        $GLOBALS['wp_query'] = $global_query;

        $this->assertNull($redirect);
    }

    /**
     * Test canonical redirects for attachment pages when the option is disabled.
     *
     * @ticket 57913
     * @ticket 59866
     *
     * @dataProvider data_canonical_attachment_page_redirect_with_option_disabled
     */
    public function test_canonical_attachment_page_redirect_with_option_disabled($expected, $user = null, $parent_post_status = '')
    {
        update_option('wp_attachment_pages_enabled', 0);

        if ('' !== $parent_post_status) {
            $parent_post_id = self::factory()->post->create(
                [
                    'post_status' => $parent_post_status,
                ]
            );
        } else {
            $parent_post_id = 0;
        }

        $filename = DIR_TESTDATA . '/images/test-image.jpg';
        $contents = file_get_contents($filename);
        $upload   = wp_upload_bits(wp_basename($filename), null, $contents);

        $attachment_id   = $this->_make_attachment($upload, $parent_post_id);
        $attachment_url  = wp_get_attachment_url($attachment_id);
        $attachment_page = get_permalink($attachment_id);

        // Set as anonymous/logged out user.
        if (null !== $user) {
            wp_set_current_user($user);
        }

        $this->go_to($attachment_page);

        $url = redirect_canonical($attachment_page, false);
        if (is_string($expected)) {
            $expected = str_replace('%%attachment_url%%', $attachment_url, $expected);
        }

        $this->assertSame($expected, $url);
    }

    /**
     * Data provider for test_canonical_attachment_page_redirect_with_option_disabled().
     *
     * @return array[]
     */
    public function data_canonical_attachment_page_redirect_with_option_disabled()
    {
        return [
            'logged out user, no parent'      => [
                '%%attachment_url%%',
                0,
            ],
            'logged in user, no parent'       => [
                '%%attachment_url%%',
            ],
            'logged out user, private parent' => [
                null,
                0,
                'private',
            ],
            'logged in user, private parent'  => [
                '%%attachment_url%%',
                null,
                'private',
            ],
            'logged out user, public parent'  => [
                '%%attachment_url%%',
                0,
                'publish',
            ],
            'logged in user, public parent'   => [
                '%%attachment_url%%',
                null,
                'publish',
            ],
        ];
    }
}
