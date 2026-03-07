<?php

declare(strict_types=1);

/**
 * @group user
 */
class Tests_User_CountUsers extends WP_UnitTestCase
{
    /**
     * @ticket 22993
     *
     * @dataProvider data_count_users_strategies
     * @group ms-excluded
     */
    public function test_count_users_is_accurate($strategy)
    {
        // Setup users.
        $admin       = self::factory()->user->create(
            [
                'role' => 'administrator',
            ]
        );
        $editor      = self::factory()->user->create(
            [
                'role' => 'editor',
            ]
        );
        $author      = self::factory()->user->create(
            [
                'role' => 'author',
            ]
        );
        $contributor = self::factory()->user->create(
            [
                'role' => 'contributor',
            ]
        );
        $subscriber  = self::factory()->user->create(
            [
                'role' => 'subscriber',
            ]
        );
        $none        = self::factory()->user->create(
            [
                'role' => '',
            ]
        );
        $nobody      = self::factory()->user->create(
            [
                'role' => '',
            ]
        );

        // Test user counts.
        $count = count_users($strategy);

        $this->assertSame(8, $count['total_users']);
        $this->assertSameSetsWithIndex(
            [
                'administrator' => 2,
                'editor'        => 1,
                'author'        => 1,
                'contributor'   => 1,
                'subscriber'    => 1,
                'none'          => 2,
            ],
            $count['avail_roles']
        );
    }

    /**
     * @ticket 22993
     * @ticket 36196
     * @group multisite
     * @group ms-required
     *
     * @dataProvider data_count_users_strategies
     */
    public function test_count_users_multisite_is_accurate($strategy)
    {
        // Setup users.
        $admin       = self::factory()->user->create(
            [
                'role' => 'administrator',
            ]
        );
        $editor      = self::factory()->user->create(
            [
                'role' => 'editor',
            ]
        );
        $author      = self::factory()->user->create(
            [
                'role' => 'author',
            ]
        );
        $contributor = self::factory()->user->create(
            [
                'role' => 'contributor',
            ]
        );
        $subscriber  = self::factory()->user->create(
            [
                'role' => 'subscriber',
            ]
        );
        $none        = self::factory()->user->create(
            [
                'role' => '',
            ]
        );
        $nobody      = self::factory()->user->create(
            [
                'role' => '',
            ]
        );

        // Setup blogs.
        $blog_1 = (int) self::factory()->blog->create(
            [
                'user_id' => $editor,
            ]
        );
        $blog_2 = (int) self::factory()->blog->create(
            [
                'user_id' => $author,
            ]
        );

        // Add users to blogs.
        add_user_to_blog($blog_1, $subscriber, 'editor');
        add_user_to_blog($blog_2, $none, 'contributor');

        // Test users counts on root site.
        $count = count_users($strategy);

        $this->assertSame(8, $count['total_users']);
        $this->assertSameSetsWithIndex(
            [
                'administrator' => 2,
                'editor'        => 1,
                'author'        => 1,
                'contributor'   => 1,
                'subscriber'    => 1,
                'none'          => 2,
            ],
            $count['avail_roles']
        );

        // Test users counts on blog 1.
        switch_to_blog($blog_1);
        $count = count_users($strategy);
        restore_current_blog();

        $this->assertSame(2, $count['total_users']);
        $this->assertSameSetsWithIndex(
            [
                'administrator' => 1,
                'editor'        => 1,
                'none'          => 0,
            ],
            $count['avail_roles']
        );

        // Test users counts on blog 2.
        switch_to_blog($blog_2);
        $count = count_users($strategy);
        restore_current_blog();

        $this->assertSame(2, $count['total_users']);
        $this->assertSameSetsWithIndex(
            [
                'administrator' => 1,
                'contributor'   => 1,
                'none'          => 0,
            ],
            $count['avail_roles']
        );
    }

    /**
     * @ticket 42014
     * @group multisite
     * @group ms-required
     *
     * @dataProvider data_count_users_strategies
     */
    public function test_count_users_multisite_queries_correct_roles($strategy)
    {
        $site_id = (int) self::factory()->blog->create();

        switch_to_blog($site_id);
        wp_roles()->add_role('tester', 'Tester', [ 'test' => true ]);
        $user_id = self::factory()->user->create(
            [
                'role' => 'tester',
            ]
        );
        restore_current_blog();

        $count = count_users($strategy, $site_id);
        $this->assertSameSetsWithIndex(
            [
                'tester' => 1,
                'none'   => 0,
            ],
            $count['avail_roles']
        );
    }

    /**
     * @ticket 34495
     *
     * @dataProvider data_count_users_strategies
     */
    public function test_count_users_is_accurate_with_multiple_roles($strategy)
    {

        // Setup users.
        $admin  = self::factory()->user->create(
            [
                'role' => 'administrator',
            ]
        );
        $editor = self::factory()->user->create(
            [
                'role' => 'editor',
            ]
        );

        get_userdata($editor)->add_role('author');

        $this->assertSame(
            [
                'editor',
                'author',
            ],
            get_userdata($editor)->roles
        );

        // Test user counts.
        $count = count_users($strategy);

        $this->assertSame(3, $count['total_users']);
        $this->assertSameSetsWithIndex(
            [
                'administrator' => 2,
                'editor'        => 1,
                'author'        => 1,
                'none'          => 0,
            ],
            $count['avail_roles']
        );
    }

    /**
     * @ticket 29785
     *
     * @dataProvider data_count_users_strategies
     */
    public function test_count_users_should_not_count_users_who_are_not_in_posts_table($strategy)
    {
        global $wpdb;

        // Get a 'before' count for comparison.
        $count = count_users($strategy);

        $u = self::factory()->user->create(
            [
                'role' => 'editor',
            ]
        );

        // Manually delete the user, but leave the capabilities usermeta.
        $wpdb->delete(
            $wpdb->users,
            [
                'ID' => $u,
            ]
        );

        $count2 = count_users($strategy);

        $this->assertSameSets($count, $count2);
    }

    public function data_count_users_strategies()
    {
        return [
            [
                'time',
            ],
            [
                'memory',
            ],
        ];
    }
}
