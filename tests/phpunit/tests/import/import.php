<?php

declare(strict_types=1);

/**
 * @group import
 */
class Tests_Import_Import extends WP_UnitTestCase
{
    /**
     * @covers ::get_importers
     */
    public function test_ordering_of_importers()
    {
        global $wp_importers;
        $_wp_importers = $wp_importers; // Preserve global state.
        $wp_importers  = [
            'xyz1' => [ 'xyz1' ],
            'XYZ2' => [ 'XYZ2' ],
            'abc2' => [ 'abc2' ],
            'ABC1' => [ 'ABC1' ],
            'def1' => [ 'def1' ],
        ];
        $this->assertSame(
            [
                'ABC1' => [ 'ABC1' ],
                'abc2' => [ 'abc2' ],
                'def1' => [ 'def1' ],
                'xyz1' => [ 'xyz1' ],
                'XYZ2' => [ 'XYZ2' ],
            ],
            get_importers()
        );
        $wp_importers = $_wp_importers; // Restore global state.
    }
}
