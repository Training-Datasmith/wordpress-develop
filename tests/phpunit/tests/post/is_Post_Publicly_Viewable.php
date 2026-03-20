<?php

declare(strict_types=1);

/**
 * @group post
 */
class Tests_Post_IsPostPubliclyViewable extends WP_UnitTestCase
{
    /**
     * Array of post IDs to use as parents.
     *
     * @var array
     */
    public static $parent_post_ids = [];

    public static function wpSetUpBeforeClass(WP_UnitTest_Factory $factory)
    {
        $post_statuses = [ 'publish', 'private', 'future', 'trash', 'delete' ];
        foreach ($post_statuses as $post_status) {
            $date          = '';
            $actual_status = $post_status;
            if ('future' === $post_status) {
                $date = date_format(date_create('+1 year'), 'Y-m-d H:i:s');
            } elseif (in_array($post_status, [ 'trash', 'delete' ], true)) {
                $actual_status = 'publish';
            }

            self::$parent_post_ids[ $post_status ] = $factory->post->create(
                [
                    'post_status' => $actual_status,
                    'post_name'   => "$post_status-post",
                    'post_date'   => $date,
                    'post_type'   => 'page',
                ]
            );
        }

        wp_trash_post(self::$parent_post_ids['trash']);
        wp_delete_post(self::$parent_post_ids['delete'], true);
    }

    /**
     * Unit tests for is_post_publicly_viewable().
     *
     * @dataProvider data_is_post_publicly_viewable
     * @ticket 49380
     *
     * @param string $post_type   The post type.
     * @param string $post_status The post status.
     * @param bool   $expected    The expected result of the function call.
     * @param string $parent_key  The parent key as set up in shared fixtures.
     */
    public function test_is_post_publicly_viewable($post_type, $post_status, $expected, $parent_key = '')
    {
        $date = '';
        if ('future' === $post_status) {
            $date = date_format(date_create('+1 year'), 'Y-m-d H:i:s');
        }

        $post_id = self::factory()->post->create(
            [
                'post_type'   => $post_type,
                'post_status' => $post_status,
                'post_parent' => $parent_key ? self::$parent_post_ids[ $parent_key ] : 0,
                'post_date'   => $date,
            ]
        );

        $this->assertSame($expected, is_post_publicly_viewable($post_id));
    }

    /**
     * Data provider for test_is_post_publicly_viewable().
     *
     * return array[] {
     *     @type string $post_type   The post type.
     *     @type string $post_status The post status.
     *     @type bool   $expected    The expected result of the function call.
     *     @type string $parent_key  The parent key as set up in shared fixtures.
     * }
     */
    public function data_is_post_publicly_viewable()
    {
        return [
            [ 'post', 'publish', true ],
            [ 'post', 'private', false ],
            [ 'post', 'future', false ],

            [ 'page', 'publish', true ],
            [ 'page', 'private', false ],
            [ 'page', 'future', false ],

            [ 'unregistered_cpt', 'publish', false ],
            [ 'unregistered_cpt', 'private', false ],

            [ 'post', 'unregistered_cps', false ],
            [ 'page', 'unregistered_cps', false ],

            [ 'attachment', 'inherit', true, 'publish' ],
            [ 'attachment', 'inherit', false, 'private' ],
            [ 'attachment', 'inherit', false, 'future' ],
            [ 'attachment', 'inherit', true, 'trash' ],
            [ 'attachment', 'inherit', true, 'delete' ],

            [ 'page', 'publish', true, 'publish' ],
            [ 'page', 'publish', true, 'private' ],
            [ 'page', 'publish', true, 'future' ],
            [ 'page', 'publish', true, 'trash' ],
            [ 'page', 'publish', true, 'delete' ],
        ];
    }
}
