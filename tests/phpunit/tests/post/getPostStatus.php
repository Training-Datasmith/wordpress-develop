<?php

declare(strict_types=1);

/**
 * @group post
 */
class Tests_Post_GetPostStatus extends WP_UnitTestCase
{
    /**
     * Array of post IDs.
     *
     * @var int[]
     */
    public static $post_ids;

    /**
     * Create shared fixtures.
     */
    public static function wpSetUpBeforeClass(WP_UnitTest_Factory $factory)
    {
        $post_statuses = [ 'publish', 'future', 'draft', 'auto-draft', 'trash', 'private', 'delete' ];
        foreach ($post_statuses as $post_status) {
            $date          = '';
            $actual_status = $post_status;
            if ('future' === $post_status) {
                $date = date_format(date_create('+1 year'), 'Y-m-d H:i:s');
            } elseif (in_array($post_status, [ 'trash', 'delete' ], true)) {
                $actual_status = 'publish';
            }

            self::$post_ids[ $post_status ] = $factory->post->create(
                [
                    'post_status' => $actual_status,
                    'post_date'   => $date,
                    'post_name'   => "$post_status-post",
                ]
            );

            // Attachments without parent or media.
            self::$post_ids[ "$post_status-attachment-no-parent" ] = $factory->attachment->create_object(
                [
                    'post_status' => $actual_status,
                    'post_name'   => "$post_status-attachment-no-parent",
                    'post_date'   => $date,
                ]
            );

            // Attachments without media.
            self::$post_ids[ "$post_status-attachment" ] = $factory->attachment->create_object(
                [
                    'post_parent' => self::$post_ids[ $post_status ],
                    'post_status' => 'inherit',
                    'post_name'   => "$post_status-attachment",
                    'post_date'   => $date,
                ]
            );
        }

        // Attachment with incorrect parent ID.
        self::$post_ids['badly-parented-attachment'] = $factory->attachment->create_object(
            [
                'post_parent' => PHP_INT_MAX, // Impossibly large number.
                'post_status' => 'inherit',
                'post_name'   => "$post_status-attachment",
                'post_date'   => $date,
            ]
        );

        // Trash the trash post and attachment.
        wp_trash_post(self::$post_ids['trash']);
        wp_trash_post(self::$post_ids['trash-attachment-no-parent']);

        // Force delete parent and unattached post objects.
        wp_delete_post(self::$post_ids['delete'], true);
        wp_delete_post(self::$post_ids['delete-attachment-no-parent'], true);
    }

    /**
     * Ensure `get_post_status()` resolves correctly for posts and attachments.
     *
     * @ticket 52326
     * @dataProvider data_get_post_status_resolves
     *
     * @param string $post_key The post key in self::$post_ids.
     * @param string $expected The expected get_post_status() return value.
     */
    public function test_get_post_status_resolves($post_key, $expected)
    {
        $this->assertSame($expected, get_post_status(self::$post_ids[ $post_key ]));
    }

    /**
     * Data provider for test_get_post_status_resolves().
     *
     * @return array[] {
     *     @type string $post_key The post key in self::$post_ids.
     *     @type string $expected The expected get_post_status() return value.
     * }
     */
    public function data_get_post_status_resolves()
    {
        return [
            [ 'publish', 'publish' ],
            [ 'future', 'future' ],
            [ 'draft', 'draft' ],
            [ 'auto-draft', 'auto-draft' ],
            [ 'trash', 'trash' ],
            [ 'private', 'private' ],
            [ 'delete', false ],

            // Attachment with `inherit` status from parent.
            [ 'publish-attachment', 'publish' ],
            [ 'future-attachment', 'future' ],
            [ 'draft-attachment', 'draft' ],
            [ 'auto-draft-attachment', 'auto-draft' ],
            [ 'trash-attachment', 'publish' ],
            [ 'private-attachment', 'private' ],
            [ 'delete-attachment', 'publish' ],

            // Attachment with native status (rather than inheriting from parent).
            [ 'publish-attachment-no-parent', 'publish' ],
            [ 'future-attachment-no-parent', 'publish' ], // Attachments can't have future status.
            [ 'draft-attachment-no-parent', 'publish' ],  // Attachments can't have draft status.
            [ 'auto-draft-attachment-no-parent', 'auto-draft' ],
            [ 'trash-attachment-no-parent', 'trash' ],
            [ 'private-attachment-no-parent', 'private' ],
            [ 'delete-attachment-no-parent', false ],

            // Attachment attempting to inherit from an invalid parent number.
            [ 'badly-parented-attachment', 'publish' ],
        ];
    }

    /**
     * Ensure post status resolves after trashing parent posts.
     *
     * @ticket 52326
     * @dataProvider data_get_post_status_after_trashing
     *
     * @param string $post_to_test  The post key in self::$post_ids.
     * @param string $post_to_trash The post key to trash then delete in self::$post_ids.
     * @param string $expected      The expected result after trashing the post.
     */
    public function test_get_post_status_after_trashing($post_to_test, $post_to_trash, $expected)
    {
        wp_trash_post(self::$post_ids[ $post_to_trash ]);
        $this->assertSame($expected, get_post_status(self::$post_ids[ $post_to_test ]));

        // Now delete the post, expect publish.
        wp_delete_post(self::$post_ids[ $post_to_trash ], true);
        $this->assertSame('publish', get_post_status(self::$post_ids[ $post_to_test ]));
    }

    /**
     * Data provider for test_get_post_status_after_trashing().
     * @return array[] {
     *     @type string $post_to_test  The post key in self::$post_ids.
     *     @type string $post_to_trash The post key to trash then delete in self::$post_ids.
     *     @type string $expected      The expected result after trashing the post.
     * }
     */
    public function data_get_post_status_after_trashing()
    {
        return [
            [ 'publish-attachment', 'publish', 'publish' ],
            [ 'future-attachment', 'future', 'future' ],
            [ 'draft-attachment', 'draft', 'draft' ],
            [ 'auto-draft-attachment', 'auto-draft', 'auto-draft' ],
            [ 'private-attachment', 'private', 'private' ],
            [ 'delete-attachment', 'publish', 'publish' ],
        ];
    }

    /**
     * Ensure the `post_states_html` filter works to modify post state output.
     *
     * @ticket 51403
     *
     * @dataProvider data_filter_post_states_html_should_enable_post_state_html_output_modification
     *
     * @covers ::_post_states
     *
     * @param string $post_state The post state to test.
     */
    public function test_filter_post_states_html_should_enable_post_state_html_output_modification($post_state)
    {
        $post = get_post(self::$post_ids[ $post_state ]);

        $original_output = _post_states($post, false);

        if (count(get_post_states($post)) === 0) {
            $text_to_append = '&mdash; <span class="post-state">Sample state</span>';
        } else {
            $text_to_append = '<span class="post-state">, Sample state</span>';
        }

        add_filter(
            'post_states_html',
            function ($post_states_html, $post_states, $filtered_post) use ($text_to_append, $post) {
                $this->assertIsString($post_states_html, 'Expected first filter arg to be a string.');
                $this->assertIsArray($post_states, 'Expected second filter arg to be an array.');
                $this->assertInstanceOf(WP_Post::class, $filtered_post, 'Expected third filter arg to be a WP_Post');
                $this->assertSame($post->ID, $filtered_post->ID, 'Expected the third filter arg to be the same as the current post.');
                return $post_states_html . $text_to_append;
            },
            10,
            3
        );

        $output = _post_states($post, false);

        $this->assertSame($original_output . $text_to_append, $output, 'Expected text to be appended to the original output.');
    }

    /**
     * Data provider for test_filter_post_states_html_should_enable_post_state_html_output_modification().
     *
     * @return array[] {
     *     @type string $post_state The post state to test.
     * }
     */
    public static function data_filter_post_states_html_should_enable_post_state_html_output_modification()
    {
        return [
            [ 'publish' ],
            [ 'future' ],
            [ 'draft' ],
            [ 'auto-draft' ],
            [ 'trash' ],
            [ 'private' ],
        ];
    }
}
