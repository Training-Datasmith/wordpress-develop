<?php

declare(strict_types=1);

/**
 * @group term
 *
 * @covers ::is_term_publicly_viewable
 */
class Tests_Term_IsTermPubliclyViewable extends WP_UnitTestCase
{
    /**
     * Unit tests for is_term_publicly_viewable().
     *
     * @ticket 56215
     */
    public function test_non_existent_term_is_not_publicly_viewable()
    {
        $this->assertFalse(is_term_publicly_viewable(123));
    }

    /**
     * Unit tests for is_term_publicly_viewable().
     *
     * @dataProvider data_is_term_publicly_viewable
     * @ticket 56215
     *
     * @param string $taxonomy The taxonomy name.
     * @param bool   $expected The expected result of the function call.
     */
    public function test_is_term_publicly_viewable($taxonomy, $expected)
    {
        $term_id = self::factory()->term->create(
            [
                'taxonomy' => $taxonomy,
            ]
        );

        $this->assertSame($expected, is_term_publicly_viewable($term_id));
    }

    /**
     * Data provider for test_is_term_publicly_viewable().
     *
     * return array[] {
     *     @type string $taxonomy The taxonomy.
     *     @type bool   $expected The expected result of the function call.
     * }
     */
    public function data_is_term_publicly_viewable()
    {
        return [
            [ 'category', true ],
            [ 'post_tag', true ],
            [ 'post_format', true ],

            [ 'nav_menu', false ],
            [ 'wp_theme', false ],
            [ 'wp_template_part_area', false ],
        ];
    }
}
