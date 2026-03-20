<?php

declare(strict_types=1);

/**
 * @group taxonomy
 * @covers ::wp_delete_object_term_relationships
 */
class Tests_Term_WpDeleteObjectTermRelationships extends WP_UnitTestCase
{
    public function test_single_taxonomy()
    {
        register_taxonomy('wptests_tax1', 'post');
        register_taxonomy('wptests_tax2', 'post');

        $t1 = self::factory()->term->create([ 'taxonomy' => 'wptests_tax1' ]);
        $t2 = self::factory()->term->create([ 'taxonomy' => 'wptests_tax2' ]);

        $object_id = 567;

        wp_set_object_terms($object_id, [ $t1 ], 'wptests_tax1');
        wp_set_object_terms($object_id, [ $t2 ], 'wptests_tax2');

        // Confirm the setup.
        $terms = wp_get_object_terms($object_id, [ 'wptests_tax1', 'wptests_tax2' ], [ 'fields' => 'ids' ]);
        $this->assertSameSets([ $t1, $t2 ], $terms);

        // wp_delete_object_term_relationships() doesn't have a return value.
        wp_delete_object_term_relationships($object_id, 'wptests_tax2');
        $terms = wp_get_object_terms($object_id, [ 'wptests_tax1', 'wptests_tax2' ], [ 'fields' => 'ids' ]);

        $this->assertSameSets([ $t1 ], $terms);
    }

    public function test_array_of_taxonomies()
    {
        register_taxonomy('wptests_tax1', 'post');
        register_taxonomy('wptests_tax2', 'post');
        register_taxonomy('wptests_tax3', 'post');

        $t1 = self::factory()->term->create([ 'taxonomy' => 'wptests_tax1' ]);
        $t2 = self::factory()->term->create([ 'taxonomy' => 'wptests_tax2' ]);
        $t3 = self::factory()->term->create([ 'taxonomy' => 'wptests_tax3' ]);

        $object_id = 567;

        wp_set_object_terms($object_id, [ $t1 ], 'wptests_tax1');
        wp_set_object_terms($object_id, [ $t2 ], 'wptests_tax2');
        wp_set_object_terms($object_id, [ $t3 ], 'wptests_tax3');

        // Confirm the setup.
        $terms = wp_get_object_terms($object_id, [ 'wptests_tax1', 'wptests_tax2', 'wptests_tax3' ], [ 'fields' => 'ids' ]);
        $this->assertSameSets([ $t1, $t2, $t3 ], $terms);

        // wp_delete_object_term_relationships() doesn't have a return value.
        wp_delete_object_term_relationships($object_id, [ 'wptests_tax1', 'wptests_tax3' ]);
        $terms = wp_get_object_terms($object_id, [ 'wptests_tax1', 'wptests_tax2', 'wptests_tax3' ], [ 'fields' => 'ids' ]);

        $this->assertSameSets([ $t2 ], $terms);
    }

    /**
     * @ticket 64406
     */
    public function test_delete_when_error()
    {
        $taxonomy_name = 'wptests_tax';
        register_taxonomy($taxonomy_name, 'post');
        $term_id   = self::factory()->term->create([ 'taxonomy' => $taxonomy_name ]);
        $object_id = 567;
        wp_set_object_terms($object_id, [ $term_id ], $taxonomy_name);

        // Confirm the setup.
        $terms = wp_get_object_terms($object_id, [ $taxonomy_name ], [ 'fields' => 'ids' ]);
        $this->assertSame([ $term_id ], $terms, 'Expected same object terms.');

        // Try wp_delete_object_term_relationships() when the taxonomy is invalid (no change expected).
        wp_delete_object_term_relationships($object_id, 'wptests_taxation');
        $terms = wp_get_object_terms($object_id, [ $taxonomy_name ], [ 'fields' => 'ids' ]);
        $this->assertSame([ $term_id ], $terms, 'Expected the object terms to be unchanged.');
    }
}
