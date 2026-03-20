<?php

declare(strict_types=1);

/**
 * Test site query functionality in multisite.
 *
 * @group ms-required
 * @group ms-site
 * @group multisite
 */
class Tests_Multisite_wpSiteQuery extends WP_UnitTestCase
{
    protected static $network_ids;
    protected static $site_ids;

    public static function wpSetUpBeforeClass(WP_UnitTest_Factory $factory)
    {
        self::$network_ids = [
            'wordpress.org/'      => [
                'domain' => 'wordpress.org',
                'path'   => '/',
            ],
            'make.wordpress.org/' => [
                'domain' => 'make.wordpress.org',
                'path'   => '/',
            ],
            'www.wordpress.net/'  => [
                'domain' => 'www.wordpress.net',
                'path'   => '/',
            ],
        ];

        foreach (self::$network_ids as &$id) {
            $id = $factory->network->create($id);
        }
        unset($id);

        self::$site_ids = [
            'wordpress.org/'          => [
                'domain'     => 'wordpress.org',
                'path'       => '/',
                'network_id' => self::$network_ids['wordpress.org/'],
            ],
            'wordpress.org/foo/'      => [
                'domain'     => 'wordpress.org',
                'path'       => '/foo/',
                'network_id' => self::$network_ids['wordpress.org/'],
            ],
            'wordpress.org/foo/bar/'  => [
                'domain'     => 'wordpress.org',
                'path'       => '/foo/bar/',
                'network_id' => self::$network_ids['wordpress.org/'],
            ],
            'make.wordpress.org/'     => [
                'domain'     => 'make.wordpress.org',
                'path'       => '/',
                'network_id' => self::$network_ids['make.wordpress.org/'],
            ],
            'make.wordpress.org/foo/' => [
                'domain'     => 'make.wordpress.org',
                'path'       => '/foo/',
                'network_id' => self::$network_ids['make.wordpress.org/'],
            ],
            'www.w.org/'              => [
                'domain' => 'www.w.org',
                'path'   => '/',
            ],
            'www.w.org/foo/'          => [
                'domain' => 'www.w.org',
                'path'   => '/foo/',
            ],
            'www.w.org/foo/bar/'      => [
                'domain' => 'www.w.org',
                'path'   => '/foo/bar/',
            ],
            'www.w.org/make/'         => [
                'domain'  => 'www.w.org',
                'path'    => '/make/',
                'public'  => 1,
                'lang_id' => 1,
            ],
        ];

        foreach (self::$site_ids as &$id) {
            $id = $factory->blog->create($id);
        }
        unset($id);
    }

    public static function wpTearDownAfterClass()
    {
        global $wpdb;

        foreach (self::$site_ids as $id) {
            wp_delete_site($id);
        }

        foreach (self::$network_ids as $id) {
            $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->sitemeta} WHERE site_id = %d", $id));
            $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->site} WHERE id= %d", $id));
        }

        wp_update_network_site_counts();
    }

    public function test_wp_site_query_by_ID()
    {
        $q     = new WP_Site_Query();
        $found = $q->query(
            [
                'fields' => 'ids',
                'ID'     => self::$site_ids['www.w.org/'],
            ]
        );

        $this->assertSameSets([ self::$site_ids['www.w.org/'] ], $found);
    }

    public function test_wp_site_query_by_number()
    {
        $q     = new WP_Site_Query();
        $found = $q->query(
            [
                'fields' => 'ids',
                'number' => 3,
            ]
        );

        $this->assertCount(3, $found);
    }

    public function test_wp_site_query_by_site__in_with_single_id()
    {
        $expected = [ self::$site_ids['wordpress.org/foo/'] ];

        $q     = new WP_Site_Query();
        $found = $q->query(
            [
                'fields'   => 'ids',
                'site__in' => $expected,
            ]
        );

        $this->assertSameSets($expected, $found);
    }

    public function test_wp_site_query_by_site__in_with_multiple_ids()
    {
        $expected = [ self::$site_ids['wordpress.org/'], self::$site_ids['wordpress.org/foo/'] ];

        $q     = new WP_Site_Query();
        $found = $q->query(
            [
                'fields'   => 'ids',
                'site__in' => $expected,
            ]
        );

        $this->assertSameSets($expected, $found);
    }

    /**
     * Test the `count` query var
     */
    public function test_wp_site_query_by_site__in_and_count_with_multiple_ids()
    {
        $expected = [ self::$site_ids['wordpress.org/'], self::$site_ids['wordpress.org/foo/'] ];

        $q     = new WP_Site_Query();
        $found = $q->query(
            [
                'fields'   => 'ids',
                'count'    => true,
                'site__in' => $expected,
            ]
        );

        $this->assertSame(2, $found);
    }

    public function test_wp_site_query_by_site__not_in_with_single_id()
    {
        $excluded = [ self::$site_ids['wordpress.org/foo/'] ];
        $expected = array_diff(self::$site_ids, $excluded);

        // Exclude main site since we don't have control over it here.
        $excluded[] = 1;

        $q     = new WP_Site_Query();
        $found = $q->query(
            [
                'fields'       => 'ids',
                'site__not_in' => $excluded,
            ]
        );

        $this->assertSameSets($expected, $found);
    }

    public function test_wp_site_query_by_site__not_in_with_multiple_ids()
    {
        $excluded = [ self::$site_ids['wordpress.org/'], self::$site_ids['wordpress.org/foo/'] ];
        $expected = array_diff(self::$site_ids, $excluded);

        // Exclude main site since we don't have control over it here.
        $excluded[] = 1;

        $q     = new WP_Site_Query();
        $found = $q->query(
            [
                'fields'       => 'ids',
                'site__not_in' => $excluded,
            ]
        );

        $this->assertSameSets($expected, $found);
    }

    public function test_wp_site_query_by_network_id_with_order()
    {
        $q     = new WP_Site_Query();
        $found = $q->query(
            [
                'fields'     => 'ids',
                'network_id' => self::$network_ids['wordpress.org/'],
                'number'     => 3,
                'order'      => 'ASC',
            ]
        );

        $expected = [
            self::$site_ids['wordpress.org/'],
            self::$site_ids['wordpress.org/foo/'],
            self::$site_ids['wordpress.org/foo/bar/'],
        ];

        $this->assertSame($expected, $found);

        $found = $q->query(
            [
                'fields'     => 'ids',
                'network_id' => self::$network_ids['wordpress.org/'],
                'number'     => 3,
                'order'      => 'DESC',
            ]
        );

        $this->assertSame(array_reverse($expected), $found);
    }

    public function test_wp_site_query_by_network_id_with_existing_sites()
    {
        $q     = new WP_Site_Query();
        $found = $q->query(
            [
                'fields'     => 'ids',
                'network_id' => self::$network_ids['make.wordpress.org/'],
            ]
        );

        $expected = [
            self::$site_ids['make.wordpress.org/'],
            self::$site_ids['make.wordpress.org/foo/'],
        ];

        $this->assertSameSets($expected, $found);
    }

    public function test_wp_site_query_by_network_id_with_no_existing_sites()
    {
        $q     = new WP_Site_Query();
        $found = $q->query(
            [
                'fields'     => 'ids',
                'network_id' => self::$network_ids['www.wordpress.net/'],
            ]
        );

        $this->assertEmpty($found);
    }

    public function test_wp_site_query_by_domain()
    {
        $q     = new WP_Site_Query();
        $found = $q->query(
            [
                'fields' => 'ids',
                'domain' => 'www.w.org',
            ]
        );

        $expected = [
            self::$site_ids['www.w.org/'],
            self::$site_ids['www.w.org/foo/'],
            self::$site_ids['www.w.org/foo/bar/'],
            self::$site_ids['www.w.org/make/'],
        ];

        $this->assertSameSets($expected, $found);
    }

    public function test_wp_site_query_by_domain_and_offset()
    {
        $q     = new WP_Site_Query();
        $found = $q->query(
            [
                'fields' => 'ids',
                'domain' => 'www.w.org',
                'offset' => 1,
            ]
        );

        $expected = [
            self::$site_ids['www.w.org/foo/'],
            self::$site_ids['www.w.org/foo/bar/'],
            self::$site_ids['www.w.org/make/'],
        ];

        $this->assertSameSets($expected, $found);
    }

    public function test_wp_site_query_by_domain_and_number_and_offset()
    {
        $q     = new WP_Site_Query();
        $found = $q->query(
            [
                'fields' => 'ids',
                'domain' => 'www.w.org',
                'number' => 2,
                'offset' => 1,
            ]
        );

        $expected = [
            self::$site_ids['www.w.org/foo/'],
            self::$site_ids['www.w.org/foo/bar/'],
        ];

        $this->assertSameSets($expected, $found);
    }

    public function test_wp_site_query_by_domain__in_with_single_domain()
    {
        $q     = new WP_Site_Query();
        $found = $q->query(
            [
                'fields'     => 'ids',
                'domain__in' => [ 'make.wordpress.org' ],
            ]
        );

        $expected = [
            self::$site_ids['make.wordpress.org/'],
            self::$site_ids['make.wordpress.org/foo/'],
        ];

        $this->assertSameSets($expected, $found);
    }

    public function test_wp_site_query_by_domain__in_with_multiple_domains()
    {
        $q     = new WP_Site_Query();
        $found = $q->query(
            [
                'fields'     => 'ids',
                'domain__in' => [ 'wordpress.org', 'make.wordpress.org' ],
            ]
        );

        $expected = [
            self::$site_ids['wordpress.org/'],
            self::$site_ids['wordpress.org/foo/'],
            self::$site_ids['wordpress.org/foo/bar/'],
            self::$site_ids['make.wordpress.org/'],
            self::$site_ids['make.wordpress.org/foo/'],
        ];

        $this->assertSameSets($expected, $found);
    }

    public function test_wp_site_query_by_domain__not_in_with_single_domain()
    {
        $q     = new WP_Site_Query();
        $found = $q->query(
            [
                'fields'         => 'ids',
                'domain__not_in' => [ 'www.w.org' ],
            ]
        );

        $expected = [
            get_current_blog_id(), // Account for the initial site added by the test suite.
            self::$site_ids['wordpress.org/'],
            self::$site_ids['wordpress.org/foo/'],
            self::$site_ids['wordpress.org/foo/bar/'],
            self::$site_ids['make.wordpress.org/'],
            self::$site_ids['make.wordpress.org/foo/'],
        ];

        $this->assertSameSets($expected, $found);
    }

    public function test_wp_site_query_by_domain__not_in_with_multiple_domains()
    {
        $q     = new WP_Site_Query();
        $found = $q->query(
            [
                'fields'         => 'ids',
                'domain__not_in' => [ 'wordpress.org', 'www.w.org' ],
            ]
        );

        $expected = [
            get_current_blog_id(), // Account for the initial site added by the test suite.
            self::$site_ids['make.wordpress.org/'],
            self::$site_ids['make.wordpress.org/foo/'],
        ];

        $this->assertSameSets($expected, $found);
    }

    public function test_wp_site_query_by_path_with_expected_results()
    {
        $q     = new WP_Site_Query();
        $found = $q->query(
            [
                'fields' => 'ids',
                'path'   => '/foo/bar/',
            ]
        );

        $expected = [
            self::$site_ids['wordpress.org/foo/bar/'],
            self::$site_ids['www.w.org/foo/bar/'],
        ];

        $this->assertSameSets($expected, $found);
    }

    public function test_wp_site_query_by_path_with_no_expected_results()
    {
        $q     = new WP_Site_Query();
        $found = $q->query(
            [
                'fields' => 'ids',
                'path'   => '/foo/bar/foo/',
            ]
        );

        $this->assertEmpty($found);
    }

    // archived, mature, spam, deleted, public.

    public function test_wp_site_query_by_archived()
    {
        $q     = new WP_Site_Query();
        $found = $q->query(
            [
                'fields'       => 'ids',
                // Exclude main site since we don't have control over it here.
                'site__not_in' => [ 1 ],
                'archived'     => '0',
            ]
        );

        $this->assertSameSets(array_values(self::$site_ids), $found);
    }

    public function test_wp_site_query_by_mature()
    {
        $q     = new WP_Site_Query();
        $found = $q->query(
            [
                'fields'       => 'ids',
                // Exclude main site since we don't have control over it here.
                'site__not_in' => [ 1 ],
                'mature'       => '0',
            ]
        );

        $this->assertSameSets(array_values(self::$site_ids), $found);
    }

    public function test_wp_site_query_by_spam()
    {
        $q     = new WP_Site_Query();
        $found = $q->query(
            [
                'fields'       => 'ids',
                // Exclude main site since we don't have control over it here.
                'site__not_in' => [ 1 ],
                'spam'         => '0',
            ]
        );

        $this->assertSameSets(array_values(self::$site_ids), $found);
    }

    public function test_wp_site_query_by_deleted()
    {
        $q     = new WP_Site_Query();
        $found = $q->query(
            [
                'fields'       => 'ids',
                // Exclude main site since we don't have control over it here.
                'site__not_in' => [ 1 ],
                'deleted'      => '0',
            ]
        );

        $this->assertSameSets(array_values(self::$site_ids), $found);
    }

    public function test_wp_site_query_by_deleted_with_no_results()
    {
        $q     = new WP_Site_Query();
        $found = $q->query(
            [
                'fields'  => 'ids',
                'deleted' => '1',
            ]
        );

        $this->assertEmpty($found);
    }

    public function test_wp_site_query_by_public()
    {
        $q     = new WP_Site_Query();
        $found = $q->query(
            [
                'fields'       => 'ids',
                // Exclude main site since we don't have control over it here.
                'site__not_in' => [ 1 ],
                'public'       => '1',
            ]
        );

        $this->assertSameSets(array_values(self::$site_ids), $found);
    }

    public function test_wp_site_query_by_lang_id_with_zero()
    {
        $q     = new WP_Site_Query();
        $found = $q->query(
            [
                'fields'       => 'ids',
                // Exclude main site since we don't have control over it here.
                'site__not_in' => [ 1 ],
                'lang_id'      => 0,
            ]
        );

        $this->assertSameSets(array_diff(array_values(self::$site_ids), [ self::$site_ids['www.w.org/make/'] ]), $found);
    }

    public function test_wp_site_query_by_lang_id()
    {
        $q     = new WP_Site_Query();
        $found = $q->query(
            [
                'fields'  => 'ids',
                'lang_id' => 1,
            ]
        );

        $expected = [
            self::$site_ids['www.w.org/make/'],
        ];

        $this->assertSameSets($expected, $found);
    }

    public function test_wp_site_query_by_lang_id_with_no_results()
    {
        $q     = new WP_Site_Query();
        $found = $q->query(
            [
                'fields'  => 'ids',
                'lang_id' => 2,
            ]
        );

        $this->assertEmpty($found);
    }

    public function test_wp_site_query_by_lang__in()
    {
        $q     = new WP_Site_Query();
        $found = $q->query(
            [
                'fields'   => 'ids',
                'lang__in' => [ 1 ],
            ]
        );

        $expected = [
            self::$site_ids['www.w.org/make/'],
        ];

        $this->assertSameSets($expected, $found);
    }

    public function test_wp_site_query_by_lang__in_with_multiple_ids()
    {
        $q     = new WP_Site_Query();
        $found = $q->query(
            [
                'fields'       => 'ids',
                // Exclude main site since we don't have control over it here.
                'site__not_in' => [ 1 ],
                'lang__in'     => [ 0, 1 ],
            ]
        );

        $this->assertSameSets(array_values(self::$site_ids), $found);
    }

    public function test_wp_site_query_by_lang__not_in()
    {
        $q     = new WP_Site_Query();
        $found = $q->query(
            [
                'fields'       => 'ids',
                'lang__not_in' => [ 0 ],
            ]
        );

        $expected = [
            self::$site_ids['www.w.org/make/'],
        ];

        $this->assertSameSets($expected, $found);
    }

    public function test_wp_site_query_by_lang__not_in_with_multiple_ids()
    {
        $q     = new WP_Site_Query();
        $found = $q->query(
            [
                'fields'       => 'ids',
                'lang__not_in' => [ 0, 1 ],
            ]
        );

        $this->assertEmpty($found);
    }

    public function test_wp_site_query_by_search_with_text_in_domain()
    {
        $q     = new WP_Site_Query();
        $found = $q->query(
            [
                'fields' => 'ids',
                'search' => 'ke.wordp',
            ]
        );

        $expected = [
            self::$site_ids['make.wordpress.org/'],
            self::$site_ids['make.wordpress.org/foo/'],
        ];

        $this->assertSameSets($expected, $found);
    }

    public function test_wp_site_query_by_search_with_text_in_path()
    {
        $q     = new WP_Site_Query();
        $found = $q->query(
            [
                'fields' => 'ids',
                'search' => 'foo',
            ]
        );

        $expected = [
            self::$site_ids['wordpress.org/foo/'],
            self::$site_ids['wordpress.org/foo/bar/'],
            self::$site_ids['make.wordpress.org/foo/'],
            self::$site_ids['www.w.org/foo/'],
            self::$site_ids['www.w.org/foo/bar/'],
        ];

        $this->assertSameSets($expected, $found);
    }

    public function test_wp_site_query_by_search_with_text_in_path_and_domain()
    {
        $q     = new WP_Site_Query();
        $found = $q->query(
            [
                'fields' => 'ids',
                'search' => 'make',
            ]
        );

        $expected = [
            self::$site_ids['make.wordpress.org/'],
            self::$site_ids['make.wordpress.org/foo/'],
            self::$site_ids['www.w.org/make/'],
        ];

        $this->assertSameSets($expected, $found);
    }

    public function test_wp_site_query_by_search_with_text_in_path_and_domain_order_by_domain_desc()
    {
        $q     = new WP_Site_Query();
        $found = $q->query(
            [
                'fields'  => 'ids',
                'search'  => 'make',
                'order'   => 'DESC',
                'orderby' => 'domain',
            ]
        );

        $expected = [
            self::$site_ids['www.w.org/make/'],
            self::$site_ids['make.wordpress.org/'],
            self::$site_ids['make.wordpress.org/foo/'],
        ];

        $this->assertSame($expected, $found);
    }

    public function test_wp_site_query_by_search_with_text_in_path_exclude_domain_from_search()
    {
        $q     = new WP_Site_Query();
        $found = $q->query(
            [
                'fields'         => 'ids',
                'search'         => 'make',
                'search_columns' => [ 'path' ],
            ]
        );

        $expected = [
            self::$site_ids['www.w.org/make/'],
        ];

        $this->assertSame($expected, $found);
    }

    public function test_wp_site_query_by_search_with_text_in_domain_exclude_path_from_search()
    {
        $q     = new WP_Site_Query();
        $found = $q->query(
            [
                'fields'         => 'ids',
                'search'         => 'make',
                'search_columns' => [ 'domain' ],
            ]
        );

        $expected = [
            self::$site_ids['make.wordpress.org/'],
            self::$site_ids['make.wordpress.org/foo/'],
        ];

        $this->assertSame($expected, $found);
    }

    public function test_wp_site_query_by_search_with_wildcard_in_text()
    {
        $q     = new WP_Site_Query();
        $found = $q->query(
            [
                'fields' => 'ids',
                'search' => 'm*ke',
            ]
        );

        $expected = [
            self::$site_ids['www.w.org/make/'],
            self::$site_ids['make.wordpress.org/'],
            self::$site_ids['make.wordpress.org/foo/'],
        ];

        $this->assertSameSets($expected, $found);
    }

    public function test_wp_site_query_by_search_with_wildcard_in_text_exclude_path_from_search()
    {
        $q     = new WP_Site_Query();
        $found = $q->query(
            [
                'fields'         => 'ids',
                'search'         => 'm*ke',
                'search_columns' => [ 'domain' ],
            ]
        );

        $expected = [
            self::$site_ids['make.wordpress.org/'],
            self::$site_ids['make.wordpress.org/foo/'],
        ];

        $this->assertSameSets($expected, $found);
    }

    public function test_wp_site_query_by_search_with_wildcard_in_text_exclude_domain_from_search()
    {
        $q     = new WP_Site_Query();
        $found = $q->query(
            [
                'fields'         => 'ids',
                'search'         => 'm*ke',
                'search_columns' => [ 'path' ],
            ]
        );

        $expected = [
            self::$site_ids['www.w.org/make/'],
        ];

        $this->assertSameSets($expected, $found);
    }

    /**
     * @ticket 41197
     */
    public function test_wp_site_query_cache_with_different_fields_no_count()
    {
        $q                 = new WP_Site_Query();
        $query_1           = $q->query(
            [
                'fields'     => 'all',
                'network_id' => self::$network_ids['wordpress.org/'],
                'number'     => 3,
                'order'      => 'ASC',
            ]
        );
        $number_of_queries = get_num_queries();

        $query_2 = $q->query(
            [
                'fields'     => 'ids',
                'network_id' => self::$network_ids['wordpress.org/'],
                'number'     => 3,
                'order'      => 'ASC',
            ]
        );

        $this->assertSame($number_of_queries, get_num_queries());
    }

    /**
     * @ticket 41197
     */
    public function test_wp_site_query_cache_with_different_fields_active_count()
    {
        $q = new WP_Site_Query();

        $query_1           = $q->query(
            [
                'fields'     => 'all',
                'network_id' => self::$network_ids['wordpress.org/'],
                'number'     => 3,
                'order'      => 'ASC',
                'count'      => true,
            ]
        );
        $number_of_queries = get_num_queries();

        $query_2 = $q->query(
            [
                'fields'     => 'ids',
                'network_id' => self::$network_ids['wordpress.org/'],
                'number'     => 3,
                'order'      => 'ASC',
                'count'      => true,
            ]
        );
        $this->assertSame($number_of_queries, get_num_queries());
    }

    /**
     * @ticket 41197
     */
    public function test_wp_site_query_cache_with_same_fields_different_count()
    {
        $q = new WP_Site_Query();

        $query_1 = $q->query(
            [
                'fields'     => 'ids',
                'network_id' => self::$network_ids['wordpress.org/'],
                'number'     => 3,
                'order'      => 'ASC',
            ]
        );

        $number_of_queries = get_num_queries();

        $query_2 = $q->query(
            [
                'fields'     => 'ids',
                'network_id' => self::$network_ids['wordpress.org/'],
                'number'     => 3,
                'order'      => 'ASC',
                'count'      => true,
            ]
        );
        $this->assertSame($number_of_queries + 1, get_num_queries());
    }

    /**
     * @ticket 55462
     */
    public function test_wp_site_query_cache_with_same_fields_same_cache_fields()
    {
        $q = new WP_Site_Query();

        $query_1 = $q->query(
            [
                'fields'                 => 'ids',
                'network_id'             => self::$network_ids['wordpress.org/'],
                'number'                 => 3,
                'order'                  => 'ASC',
                'update_site_cache'      => true,
                'update_site_meta_cache' => true,
            ]
        );

        $number_of_queries = get_num_queries();

        $query_2 = $q->query(
            [
                'fields'                 => 'ids',
                'network_id'             => self::$network_ids['wordpress.org/'],
                'number'                 => 3,
                'order'                  => 'ASC',
                'update_site_cache'      => true,
                'update_site_meta_cache' => true,
            ]
        );
        $this->assertSame($number_of_queries, get_num_queries());
    }

    /**
     * @ticket 55462
     */
    public function test_wp_site_query_cache_with_same_fields_different_cache_fields()
    {
        $q = new WP_Site_Query();

        $query_1 = $q->query(
            [
                'fields'                 => 'ids',
                'network_id'             => self::$network_ids['wordpress.org/'],
                'number'                 => 3,
                'order'                  => 'ASC',
                'update_site_cache'      => true,
                'update_site_meta_cache' => true,
            ]
        );

        $number_of_queries = get_num_queries();

        $query_2 = $q->query(
            [
                'fields'                 => 'ids',
                'network_id'             => self::$network_ids['wordpress.org/'],
                'number'                 => 3,
                'order'                  => 'ASC',
                'update_site_cache'      => false,
                'update_site_meta_cache' => false,
            ]
        );
        $this->assertSame($number_of_queries, get_num_queries());
    }

    /**
     * @ticket 40229
     * @dataProvider data_wp_site_query_meta_query
     */
    public function test_wp_site_query_meta_query($query, $expected, $strict)
    {
        if (! is_site_meta_supported()) {
            $this->markTestSkipped('Test only runs with the blogmeta database table installed.');
        }

        add_site_meta(self::$site_ids['wordpress.org/'], 'foo', 'foo');
        add_site_meta(self::$site_ids['wordpress.org/foo/'], 'foo', 'bar');
        add_site_meta(self::$site_ids['wordpress.org/foo/bar/'], 'foo', 'baz');
        add_site_meta(self::$site_ids['make.wordpress.org/'], 'bar', 'baz');
        add_site_meta(self::$site_ids['wordpress.org/'], 'numberfoo', 1);
        add_site_meta(self::$site_ids['wordpress.org/foo/'], 'numberfoo', 2);

        $query['fields'] = 'ids';

        $q     = new WP_Site_Query();
        $found = $q->query($query);

        foreach ($expected as $index => $domain_path) {
            $expected[ $index ] = self::$site_ids[ $domain_path ];
        }

        if ($strict) {
            $this->assertSame($expected, $found);
        } else {
            $this->assertSameSets($expected, $found);
        }
    }

    public function data_wp_site_query_meta_query()
    {
        return [
            [
                [
                    'meta_key' => 'foo',
                ],
                [
                    'wordpress.org/',
                    'wordpress.org/foo/',
                    'wordpress.org/foo/bar/',
                ],
                false,
            ],
            [
                [
                    'meta_key'   => 'foo',
                    'meta_value' => 'bar',
                ],
                [
                    'wordpress.org/foo/',
                ],
                false,
            ],
            [
                [
                    'meta_key'     => 'foo',
                    'meta_value'   => [ 'bar', 'baz' ],
                    'meta_compare' => 'IN',
                ],
                [
                    'wordpress.org/foo/',
                    'wordpress.org/foo/bar/',
                ],
                false,
            ],
            [
                [
                    'meta_query' => [
                        [
                            'key'   => 'foo',
                            'value' => 'bar',
                        ],
                        [
                            'key'   => 'numberfoo',
                            'value' => 2,
                            'type'  => 'NUMERIC',
                        ],
                    ],
                ],
                [
                    'wordpress.org/foo/',
                ],
                false,
            ],
            [
                [
                    'meta_key' => 'foo',
                    'orderby'  => 'meta_value',
                    'order'    => 'ASC',
                ],
                [
                    'wordpress.org/foo/',
                    'wordpress.org/foo/bar/',
                    'wordpress.org/',
                ],
                true,
            ],
            [
                [
                    'meta_key' => 'foo',
                    'orderby'  => 'foo',
                    'order'    => 'ASC',
                ],
                [
                    'wordpress.org/foo/',
                    'wordpress.org/foo/bar/',
                    'wordpress.org/',
                ],
                true,
            ],
            [
                [
                    'meta_key' => 'numberfoo',
                    'orderby'  => 'meta_value_num',
                    'order'    => 'DESC',
                ],
                [
                    'wordpress.org/foo/',
                    'wordpress.org/',
                ],
                true,
            ],
            [
                [
                    'meta_query' => [
                        [
                            'key'     => 'foo',
                            'value'   => [ 'foo', 'bar' ],
                            'compare' => 'IN',
                        ],
                        [
                            'key' => 'numberfoo',
                        ],
                    ],
                    'orderby'    => [ 'meta_value' => 'ASC' ],
                ],
                [
                    'wordpress.org/foo/',
                    'wordpress.org/',
                ],
                true,
            ],
            [
                [
                    'meta_query' => [
                        [
                            'key'     => 'foo',
                            'value'   => [ 'foo', 'bar' ],
                            'compare' => 'IN',
                        ],
                        [
                            'key' => 'numberfoo',
                        ],
                    ],
                    'orderby'    => [ 'foo' => 'ASC' ],
                ],
                [
                    'wordpress.org/foo/',
                    'wordpress.org/',
                ],
                true,
            ],
            [
                [
                    'meta_query' => [
                        [
                            'key'     => 'foo',
                            'value'   => [ 'foo', 'bar' ],
                            'compare' => 'IN',
                        ],
                        'my_subquery' => [
                            'key' => 'numberfoo',
                        ],
                    ],
                    'orderby'    => [ 'my_subquery' => 'DESC' ],
                ],
                [
                    'wordpress.org/foo/',
                    'wordpress.org/',
                ],
                true,
            ],
        ];
    }

    /**
     * @ticket 45749
     * @ticket 47599
     */
    public function test_sites_pre_query_filter_should_bypass_database_query()
    {
        add_filter('sites_pre_query', [ __CLASS__, 'filter_sites_pre_query' ], 10, 2);

        $num_queries = get_num_queries();

        $q       = new WP_Site_Query();
        $results = $q->query([]);

        remove_filter('sites_pre_query', [ __CLASS__, 'filter_sites_pre_query' ], 10, 2);

        // Make sure no queries were executed.
        $this->assertSame($num_queries, get_num_queries());

        // We manually inserted a non-existing site and overrode the results with it.
        $this->assertSame([ 555 ], $results);

        // Make sure manually setting found_sites doesn't get overwritten.
        $this->assertSame(1, $q->found_sites);
    }

    public static function filter_sites_pre_query($sites, $query)
    {
        $query->found_sites = 1;

        return [ 555 ];
    }

    /**
     * @ticket 51333
     */
    public function test_sites_pre_query_filter_should_set_sites_property()
    {
        add_filter('sites_pre_query', [ __CLASS__, 'filter_sites_pre_query_and_set_sites' ], 10, 2);

        $q       = new WP_Site_Query();
        $results = $q->query([]);

        remove_filter('sites_pre_query', [ __CLASS__, 'filter_sites_pre_query_and_set_sites' ], 10);

        // Make sure the sites property is the same as the results.
        $this->assertSame($results, $q->sites);

        // Make sure the site domain is `wordpress.org`.
        $this->assertSame('wordpress.org', $q->sites[0]->domain);
    }

    public static function filter_sites_pre_query_and_set_sites($sites, $query)
    {
        return [ get_site(self::$site_ids['wordpress.org/']) ];
    }

    /**
     * @ticket 56841
     */
    public function test_wp_site_query_does_not_have_leading_whitespace()
    {
        $q = new WP_Site_Query();

        $q->query(
            [
                'fields'                 => 'ids',
                'network_id'             => self::$network_ids['wordpress.org/'],
                'number'                 => 3,
                'order'                  => 'ASC',
                'update_site_cache'      => true,
                'update_site_meta_cache' => true,
            ]
        );

        $this->assertSame(ltrim($q->request), $q->request, 'The query has leading whitespace');
    }
}
