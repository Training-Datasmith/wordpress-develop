<?php

declare(strict_types=1);

/**
 * @group taxonomy
 * @group category
 *
 * @covers ::get_categories
 */
class Tests_Category_GetCategories extends WP_UnitTestCase
{
    /**
     * @ticket 36227
     */
    public function test_wp_error_should_return_an_empty_array()
    {
        $found = get_categories([ 'taxonomy' => 'foo' ]);
        $this->assertSame([], $found);
    }
}
