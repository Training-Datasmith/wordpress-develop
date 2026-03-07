<?php

declare(strict_types=1);

/**
 * @group taxonomy
 * @group category
 */
class Tests_Term extends WP_UnitTestCase
{
    protected $taxonomy        = 'category';
    protected static $post_ids = [];

    public static function wpSetUpBeforeClass(WP_UnitTest_Factory $factory)
    {
        self::$post_ids = $factory->post->create_many(5);
    }

    /**
     * @ticket 29911
     */
    public function test_wp_delete_term_should_invalidate_cache_for_child_terms()
    {
        register_taxonomy(
            'wptests_tax',
            'post',
            [
                'hierarchical' => true,
            ]
        );

        $parent = self::factory()->term->create(
            [
                'taxonomy' => 'wptests_tax',
            ]
        );

        $child = self::factory()->term->create(
            [
                'taxonomy' => 'wptests_tax',
                'parent'   => $parent,
                'slug'     => 'foo',
            ]
        );

        // Prime the cache.
        $child_term = get_term($child, 'wptests_tax');
        $this->assertSame($parent, $child_term->parent);

        wp_delete_term($parent, 'wptests_tax');
        $child_term = get_term($child, 'wptests_tax');
        $this->assertSame(0, $child_term->parent);
    }

    /**
     * @ticket 5381
     */
    public function test_is_term_type()
    {
        // Insert a term.
        $term = 'term_new';
        $t    = wp_insert_term($term, $this->taxonomy);
        $this->assertIsArray($t);
        $term_obj = get_term_by('name', $term, $this->taxonomy);

        $exists = term_exists($term_obj->slug);
        // Clean up.
        $deleted = wp_delete_term($t['term_id'], $this->taxonomy);

        $this->assertEquals($t['term_id'], $exists);
        $this->assertTrue($deleted);
    }

    /**
     * @ticket 15919
     */
    public function test_wp_count_terms()
    {
        $count = wp_count_terms(
            [
                'hide_empty' => true,
                'taxonomy'   => 'category',
            ]
        );
        // There are 5 posts, all Uncategorized.
        $this->assertSame('1', $count);
    }

    /**
     * @ticket 36399
     */
    public function test_wp_count_terms_legacy_interoperability()
    {
        self::factory()->tag->create_many(5);

        // Counts all terms (1 default category, 5 tags).
        $count = wp_count_terms();
        $this->assertSame('6', $count);

        // Counts only tags (5), with both current and legacy signature.
        // Legacy usage should not trigger deprecated notice.
        $count        = wp_count_terms([ 'taxonomy' => 'post_tag' ]);
        $legacy_count = wp_count_terms('post_tag');
        $this->assertSame('5', $count);
        $this->assertSame($count, $legacy_count);
    }

    /**
     * @ticket 15475
     */
    public function test_wp_add_remove_object_terms()
    {
        $posts = self::$post_ids;
        $tags  = self::factory()->tag->create_many(5);

        $tt = wp_add_object_terms($posts[0], $tags[1], 'post_tag');
        $this->assertCount(1, $tt);
        $this->assertSame([ $tags[1] ], wp_get_object_terms($posts[0], 'post_tag', [ 'fields' => 'ids' ]));

        $three_tags = [ $tags[0], $tags[1], $tags[2] ];
        $tt         = wp_add_object_terms($posts[1], $three_tags, 'post_tag');
        $this->assertCount(3, $tt);
        $this->assertSame($three_tags, wp_get_object_terms($posts[1], 'post_tag', [ 'fields' => 'ids' ]));

        $this->assertTrue(wp_remove_object_terms($posts[0], $tags[1], 'post_tag'));
        $this->assertFalse(wp_remove_object_terms($posts[0], $tags[0], 'post_tag'));
        $this->assertInstanceOf('WP_Error', wp_remove_object_terms($posts[0], $tags[1], 'non_existing_taxonomy'));
        $this->assertTrue(wp_remove_object_terms($posts[1], $three_tags, 'post_tag'));
        $this->assertCount(0, wp_get_object_terms($posts[1], 'post_tag'));

        foreach ($tags as $term_id) {
            $this->assertTrue(wp_delete_term($term_id, 'post_tag'));
        }

        foreach ($posts as $post_id) {
            $this->assertInstanceOf('WP_Post', wp_delete_post($post_id));
        }
    }

    public function test_term_is_ancestor_of()
    {
        $term  = rand_str();
        $term2 = rand_str();

        $t = wp_insert_term($term, 'category');
        $this->assertIsArray($t);
        $t2 = wp_insert_term($term, 'category', [ 'parent' => $t['term_id'] ]);
        $this->assertIsArray($t2);

        $this->assertTrue(term_is_ancestor_of($t['term_id'], $t2['term_id'], 'category'));
        $this->assertFalse(term_is_ancestor_of($t2['term_id'], $t['term_id'], 'category'));

        $this->assertTrue(cat_is_ancestor_of($t['term_id'], $t2['term_id']));
        $this->assertFalse(cat_is_ancestor_of($t2['term_id'], $t['term_id']));

        wp_delete_term($t['term_id'], 'category');
        wp_delete_term($t2['term_id'], 'category');
    }

    public function test_wp_insert_delete_category()
    {
        $term = rand_str();
        $this->assertNull(category_exists($term));

        $initial_count = wp_count_terms([ 'taxonomy' => 'category' ]);

        $t = wp_insert_category([ 'cat_name' => $term ]);
        $this->assertIsNumeric($t);
        $this->assertNotWPError($t);
        $this->assertGreaterThan(0, $t);
        $this->assertSame((string) ($initial_count + 1), wp_count_terms([ 'taxonomy' => 'category' ]));

        // Make sure the term exists.
        $this->assertGreaterThan(0, term_exists($term));
        $this->assertGreaterThan(0, term_exists($t));

        // Now delete it.
        $this->assertTrue(wp_delete_category($t));
        $this->assertNull(term_exists($term));
        $this->assertNull(term_exists($t));
        $this->assertSame($initial_count, wp_count_terms([ 'taxonomy' => 'category' ]));
    }

    /**
     * @ticket 16550
     */
    public function test_wp_set_post_categories()
    {
        $post_id = self::$post_ids[0];
        $post    = get_post($post_id);

        $this->assertIsArray($post->post_category);
        $this->assertCount(1, $post->post_category);
        $this->assertEquals(get_option('default_category'), $post->post_category[0]);

        $term1 = wp_insert_term('Foo', 'category');
        $term2 = wp_insert_term('Bar', 'category');
        $term3 = wp_insert_term('Baz', 'category');

        wp_set_post_categories($post_id, [ $term1['term_id'], $term2['term_id'] ]);
        $this->assertCount(2, $post->post_category);
        $this->assertSame([ $term2['term_id'], $term1['term_id'] ], $post->post_category);

        wp_set_post_categories($post_id, $term3['term_id'], true);
        $this->assertSame([ $term2['term_id'], $term3['term_id'], $term1['term_id'] ], $post->post_category);

        $term4 = wp_insert_term('Burrito', 'category');

        wp_set_post_categories($post_id, $term4['term_id']);
        $this->assertSame([ $term4['term_id'] ], $post->post_category);

        wp_set_post_categories($post_id, [ $term1['term_id'], $term2['term_id'] ], true);
        $this->assertSame([ $term2['term_id'], $term4['term_id'], $term1['term_id'] ], $post->post_category);

        wp_set_post_categories($post_id, [], true);
        $this->assertCount(1, $post->post_category);
        $this->assertEquals(get_option('default_category'), $post->post_category[0]);

        wp_set_post_categories($post_id, []);
        $this->assertCount(1, $post->post_category);
        $this->assertEquals(get_option('default_category'), $post->post_category[0]);
    }

    /**
     * @ticket 43516
     */
    public function test_wp_set_post_categories_sets_default_category_for_custom_post_types()
    {
        add_filter('default_category_post_types', [ $this, 'filter_default_category_post_types' ]);

        register_post_type('cpt', [ 'taxonomies' => [ 'category' ] ]);

        $post_id = self::factory()->post->create([ 'post_type' => 'cpt' ]);
        $post    = get_post($post_id);

        $this->assertEquals(get_option('default_category'), $post->post_category[0]);

        $term = wp_insert_term('Foo', 'category');

        wp_set_post_categories($post_id, $term['term_id']);
        $this->assertSame($term['term_id'], $post->post_category[0]);

        wp_set_post_categories($post_id, []);
        $this->assertEquals(get_option('default_category'), $post->post_category[0]);

        remove_filter('default_category_post_types', [ $this, 'filter_default_category_post_types' ]);
    }

    public function filter_default_category_post_types($post_types)
    {
        $post_types[] = 'cpt';
        return $post_types;
    }

    /**
     * @ticket 25852
     */
    public function test_sanitize_term_field()
    {
        $term = wp_insert_term('foo', $this->taxonomy);

        $this->assertSame(0, sanitize_term_field('parent', 0, $term['term_id'], $this->taxonomy, 'raw'));
        $this->assertSame(1, sanitize_term_field('parent', 1, $term['term_id'], $this->taxonomy, 'raw'));
        $this->assertSame(0, sanitize_term_field('parent', -1, $term['term_id'], $this->taxonomy, 'raw'));
        $this->assertSame(0, sanitize_term_field('parent', '', $term['term_id'], $this->taxonomy, 'raw'));
    }

    /**
     * @ticket 53152
     * @dataProvider data_wp_set_term_objects_finds_term_name_with_special_characters
     *
     * @param string $name  A term name containing special characters.
     */
    public function test_wp_set_term_objects_finds_term_name_with_special_characters($name)
    {
        $post_id  = self::$post_ids[0];
        $expected = wp_set_object_terms($post_id, $name, 'category', false);
        $actual   = wp_set_object_terms($post_id, $name, 'category', false);
        $this->assertEquals($expected, $actual);
    }

    /**
     * Data provider.
     *
     * @return array
     */
    public function data_wp_set_term_objects_finds_term_name_with_special_characters()
    {
        return [
            'ampersand'               => [ 'name' => 'Foo & Bar' ],
            'ndash and mdash'         => [ 'name' => 'Foo – Bar' ],
            'trademark'               => [ 'name' => 'Foo Bar™' ],
            'copyright'               => [ 'name' => 'Foo Bar©' ],
            'registered'              => [ 'name' => 'Foo Bar®' ],
            'degree'                  => [ 'name' => 'Foo ° Bar' ],
            'forward slash'           => [ 'name' => 'Fo/o Ba/r' ],
            'back slash'              => [ 'name' => 'F\oo \Bar' ],
            'multiply'                => [ 'name' => 'Foo × Bar' ],
            'standalone diacritic'    => [ 'name' => 'Foo Bāáǎàr' ],
            'acute accents'           => [ 'name' => 'ááa´aˊ' ],
            'iexcel and iquest'       => [ 'name' => '¡Foo ¿Bar' ],
            'angle quotes'            => [ 'name' => '‹Foo« »Bar›' ],
            'curly quotes'            => [ 'name' => '“F‘o„o‚ „ ‟ ‛B“a’r”' ],
            'bullet'                  => [ 'name' => 'Foo • Bar' ],
            'unencoded percent'       => [ 'name' => 'Foo % Bar' ],
            'encoded ampersand'       => [ 'name' => 'Foo &amp; Bar' ],
            'encoded ndash and mdash' => [ 'name' => 'Foo &mdash; &ndash; Bar' ],
            'encoded trademark'       => [ 'name' => 'Foo Bar &trade;' ],
            'encoded copyright'       => [ 'name' => 'Foo Bar &copy;' ],
            'encoded registered'      => [ 'name' => 'Foo Bar &reg;' ],
            'encoded bullet'          => [ 'name' => 'Foo &bullet; Bar' ],
        ];
    }

    /**
     * @ticket 19205
     */
    public function test_orphan_category()
    {
        $cat_id1 = self::factory()->category->create();

        wp_delete_category($cat_id1);

        $cat_id2 = self::factory()->category->create([ 'parent' => $cat_id1 ]);
        $this->assertWPError($cat_id2);
    }

    /**
     * @ticket 58329
     *
     * @covers ::get_term
     *
     */
    public function test_get_term_sanitize_once()
    {
        $cat_id1 = self::factory()->category->create();
        $_term   = get_term($cat_id1, '', OBJECT, 'edit');

        $filter = new MockAction();
        add_filter('edit_term_slug', [ $filter, 'filter' ]);

        $term = get_term($_term, '', OBJECT, 'edit');

        $this->assertSame(0, $filter->get_call_count(), 'The term was filtered more than once');
        $this->assertSame($_term, $term, 'Both terms should match');
    }

    /**
     * @ticket 58329
     *
     * @covers ::get_term
     *
     * @dataProvider data_get_term_filter
     *
     * @param string $filter How to sanitize term fields.
     */
    public function test_get_term_should_set_term_filter_property_to_filter_argument($filter)
    {
        $cat_id1 = self::factory()->category->create();

        $term = get_term($cat_id1, '', OBJECT, $filter);

        $this->assertSame($filter, $term->filter, "The term's 'filter' property should be set to '$filter'.");
    }

    /**
     * @ticket 58329
     *
     * @covers ::get_term
     *
     * @dataProvider data_get_term_filter
     *
     * @param string $filter How to sanitize term fields.
     */
    public function test_get_term_filtered($filter)
    {
        $cat_id1 = self::factory()->category->create();
        $cat     = self::factory()->category->create_and_get();
        add_filter(
            'get_term',
            static function () use ($cat) {
                return $cat;
            }
        );

        $term = get_term($cat_id1, '', OBJECT, $filter);

        $this->assertSame($filter, $term->filter, "The term's 'filter' property should be set to '$filter'.");
        $this->assertSame($term, $cat, 'The returned term should match the filtered term');
    }

    /**
     * Data provider.
     *
     * @return array[]
     */
    public function data_get_term_filter()
    {
        return self::text_array_to_dataprovider([ 'edit', 'db', 'display', 'attribute', 'js', 'rss', 'raw' ]);
    }
}
