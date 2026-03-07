<?php

declare(strict_types=1);

/**
 * @group term
 * @group slashes
 * @ticket 21767
 */
class Tests_Term_Slashes extends WP_Ajax_UnitTestCase
{
    /*
     * It is important to test with both even and odd numbered slashes,
     * as KSES does a strip-then-add slashes in some of its function calls.
     */

    public const SLASH_1 = 'String with 1 slash \\';
    public const SLASH_2 = 'String with 2 slashes \\\\';
    public const SLASH_3 = 'String with 3 slashes \\\\\\';
    public const SLASH_4 = 'String with 4 slashes \\\\\\\\';
    public const SLASH_5 = 'String with 5 slashes \\\\\\\\\\';
    public const SLASH_6 = 'String with 6 slashes \\\\\\\\\\\\';
    public const SLASH_7 = 'String with 7 slashes \\\\\\\\\\\\\\';

    protected static $author_id;

    public static function wpSetUpBeforeClass(WP_UnitTest_Factory $factory)
    {
        self::$author_id = $factory->user->create([ 'role' => 'administrator' ]);
    }

    public function set_up()
    {
        parent::set_up();

        wp_set_current_user(self::$author_id);
    }

    /**
     * Tests the model function that expects slashed data.
     */
    public function test_wp_insert_term()
    {
        $taxonomies = [
            'category',
            'post_tag',
        ];
        foreach ($taxonomies as $taxonomy) {
            $insert = wp_insert_term(
                self::SLASH_1,
                $taxonomy,
                [
                    'slug'        => 'slash_test_1_' . $taxonomy,
                    'description' => self::SLASH_3,
                ]
            );
            $term   = get_term($insert['term_id'], $taxonomy);
            $this->assertSame(wp_unslash(self::SLASH_1), $term->name);
            $this->assertSame(wp_unslash(self::SLASH_3), $term->description);

            $insert = wp_insert_term(
                self::SLASH_3,
                $taxonomy,
                [
                    'slug'        => 'slash_test_2_' . $taxonomy,
                    'description' => self::SLASH_5,
                ]
            );
            $term   = get_term($insert['term_id'], $taxonomy);
            $this->assertSame(wp_unslash(self::SLASH_3), $term->name);
            $this->assertSame(wp_unslash(self::SLASH_5), $term->description);

            $insert = wp_insert_term(
                self::SLASH_2,
                $taxonomy,
                [
                    'slug'        => 'slash_test_3_' . $taxonomy,
                    'description' => self::SLASH_4,
                ]
            );
            $term   = get_term($insert['term_id'], $taxonomy);
            $this->assertSame(wp_unslash(self::SLASH_2), $term->name);
            $this->assertSame(wp_unslash(self::SLASH_4), $term->description);
        }
    }

    /**
     * Tests the model function that expects slashed data.
     */
    public function test_wp_update_term()
    {
        $taxonomies = [
            'category',
            'post_tag',
        ];
        foreach ($taxonomies as $taxonomy) {
            $term_id = self::factory()->term->create(
                [
                    'taxonomy' => $taxonomy,
                ]
            );

            $update = wp_update_term(
                $term_id,
                $taxonomy,
                [
                    'name'        => self::SLASH_1,
                    'description' => self::SLASH_3,
                ]
            );

            $term = get_term($term_id, $taxonomy);
            $this->assertSame(wp_unslash(self::SLASH_1), $term->name);
            $this->assertSame(wp_unslash(self::SLASH_3), $term->description);

            $update = wp_update_term(
                $term_id,
                $taxonomy,
                [
                    'name'        => self::SLASH_3,
                    'description' => self::SLASH_5,
                ]
            );
            $term   = get_term($term_id, $taxonomy);
            $this->assertSame(wp_unslash(self::SLASH_3), $term->name);
            $this->assertSame(wp_unslash(self::SLASH_5), $term->description);

            $update = wp_update_term(
                $term_id,
                $taxonomy,
                [
                    'name'        => self::SLASH_2,
                    'description' => self::SLASH_4,
                ]
            );
            $term   = get_term($term_id, $taxonomy);
            $this->assertSame(wp_unslash(self::SLASH_2), $term->name);
            $this->assertSame(wp_unslash(self::SLASH_4), $term->description);
        }
    }
}
