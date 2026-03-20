<?php

declare(strict_types=1);

/**
 * @group ms-required
 * @group ms-site
 * @group multisite
 */
class Tests_Multisite_wpGetSites extends WP_UnitTestCase
{
    protected static $site_ids;

    public static function wpSetUpBeforeClass(WP_UnitTest_Factory $factory)
    {
        self::$site_ids = [
            'w.org/'      => [
                'domain'     => 'w.org',
                'path'       => '/',
                'network_id' => 2,
            ],
            'wp.org/'     => [
                'domain'     => 'wp.org',
                'path'       => '/',
                'network_id' => 2,
                'public'     => 0,
            ],
            'wp.org/foo/' => [
                'domain'     => 'wp.org',
                'path'       => '/foo/',
                'network_id' => 1,
                'public'     => 0,
            ],
            'wp.org/oof/' => [
                'domain' => 'wp.org',
                'path'   => '/oof/',
            ],
        ];

        foreach (self::$site_ids as &$id) {
            $id = $factory->blog->create($id);
        }
        unset($id);
    }

    public static function wpTearDownAfterClass()
    {
        foreach (self::$site_ids as $id) {
            wp_delete_site($id);
        }

        wp_update_network_site_counts();
    }

    /**
     * @expectedDeprecated wp_get_sites
     */
    public function test_wp_get_sites_site_is_expected_array()
    {

        $keys  = [
            'blog_id',
            'site_id',
            'domain',
            'path',
            'registered',
            'last_updated',
            'public',
            'archived',
            'mature',
            'spam',
            'deleted',
            'lang_id',
        ];
        $sites = wp_get_sites();

        $missing_keys = array_diff_key(array_flip($keys), $sites[0]);

        $this->assertSame([], $missing_keys, 'Keys are missing from site arrays.');
    }

    /**
     * @expectedDeprecated wp_get_sites
     * @dataProvider data_wp_get_sites
     *
     * @param $expected
     * @param $args
     * @param $error
     */
    public function test_wp_get_sites($expected, $args, $error)
    {
        $this->assertCount($expected, wp_get_sites($args), $error);
    }

    /**
     * @return array
     */
    public function data_wp_get_sites()
    {
        return [
            [ 3, [], 'Default arguments should return all sites from the current network.' ],
            [ 0, [ 'network_id' => 999 ], 'No sites should match a query with an invalid network ID.' ],
            [ 5, [ 'network_id' => null ], 'A network ID of null should return all sites on all networks.' ],
            [ 2, [ 'network_id' => 2 ], 'Only sites on a specified network ID should be returned.' ],
            [ 5, [ 'network_id' => [ 1, 2 ] ], 'If multiple network IDs are specified, sites from both should be returned.' ],
            [
                3,
                [
                    'public'     => 1,
                    'network_id' => null,
                ],
                'Public sites on all networks.',
            ],
            [
                2,
                [
                    'public'     => 0,
                    'network_id' => null,
                ],
                'Non public sites on all networks.',
            ],
            [
                2,
                [
                    'public'     => 1,
                    'network_id' => 1,
                ],
                'Public sites on a single network.',
            ],
            [
                1,
                [
                    'public'     => 1,
                    'network_id' => 2,
                ],
                'Public sites on a second network.',
            ],
            [ 2, [ 'limit' => 2 ], 'Provide only a limit argument.' ],
            [
                1,
                [
                    'limit'  => 2,
                    'offset' => 2,
                ],
                'Provide both limit and offset arguments.',
            ],
            [ 2, [ 'offset' => 1 ], 'Provide only an offset argument.' ],
            [ 0, [ 'offset' => 20 ], 'Expect 0 sites when using an offset larger than the total number of sites.' ],
        ];
    }
}
