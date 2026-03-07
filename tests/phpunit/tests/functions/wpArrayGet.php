<?php

declare(strict_types=1);

/**
 * Tests for the _wp_array_get() function
 *
 * @since 5.6.0
 *
 * @group functions
 *
 * @covers ::_wp_array_get
 */
class Tests_Functions_wpArrayGet extends WP_UnitTestCase
{
    /**
     * Tests _wp_array_get() with invalid parameters.
     *
     * @ticket 51720
     */
    public function test_wp_array_get_invalid_parameters()
    {
        $this->assertSame(
            _wp_array_get(
                null,
                [ 'a' ]
            ),
            null
        );

        $this->assertSame(
            _wp_array_get(
                [
                    'key' => 4,
                ],
                null
            ),
            null
        );

        $this->assertSame(
            _wp_array_get(
                [
                    'key' => 4,
                ],
                []
            ),
            null
        );

        $this->assertSame(
            _wp_array_get(
                [
                    'key' => 4,
                ],
                [],
                true
            ),
            true
        );
    }

    /**
     * Tests _wp_array_get() with non-subtree paths.
     *
     * @ticket 51720
     */
    public function test_wp_array_get_simple_non_subtree()
    {
        // Simple non-subtree test.
        $this->assertSame(
            _wp_array_get(
                [
                    'key' => 4,
                ],
                [ 'key' ]
            ),
            4
        );

        // Simple non-subtree not found.
        $this->assertSame(
            _wp_array_get(
                [
                    'key' => 4,
                ],
                [ 'invalid' ]
            ),
            null
        );

        // Simple non-subtree not found with a default.
        $this->assertSame(
            _wp_array_get(
                [
                    'key' => 4,
                ],
                [ 'invalid' ],
                1
            ),
            1
        );

        // Simple non-subtree integer path.
        $this->assertSame(
            _wp_array_get(
                [
                    'a',
                    'b',
                    'c',
                ],
                [ 1 ]
            ),
            'b'
        );
    }

    /**
     * Tests _wp_array_get() with subtrees.
     *
     * @ticket 51720
     */
    public function test_wp_array_get_subtree()
    {
        $this->assertSame(
            _wp_array_get(
                [
                    'a' => [
                        'b' => [
                            'c' => 1,
                        ],
                    ],
                ],
                [ 'a', 'b' ]
            ),
            [ 'c' => 1 ]
        );

        $this->assertSame(
            _wp_array_get(
                [
                    'a' => [
                        'b' => [
                            'c' => 1,
                        ],
                    ],
                ],
                [ 'a', 'b', 'c' ]
            ),
            1
        );

        $this->assertSame(
            _wp_array_get(
                [
                    'a' => [
                        'b' => [
                            'c' => 1,
                        ],
                    ],
                ],
                [ 'a', 'b', 'c', 'd' ]
            ),
            null
        );
    }

    /**
     * Tests _wp_array_get() with zero strings.
     *
     * @ticket 51720
     */
    public function test_wp_array_get_handle_zeros()
    {
        $this->assertSame(
            _wp_array_get(
                [
                    '-0' => 'a',
                    '0'  => 'b',
                ],
                [ 0 ]
            ),
            'b'
        );

        $this->assertSame(
            _wp_array_get(
                [
                    '-0' => 'a',
                    '0'  => 'b',
                ],
                [ -0 ]
            ),
            'b'
        );

        $this->assertSame(
            _wp_array_get(
                [
                    '-0' => 'a',
                    '0'  => 'b',
                ],
                [ '-0' ]
            ),
            'a'
        );

        $this->assertSame(
            _wp_array_get(
                [
                    '-0' => 'a',
                    '0'  => 'b',
                ],
                [ '0' ]
            ),
            'b'
        );
    }

    /**
     * Tests _wp_array_get() with null values.
     *
     * @ticket 51720
     */
    public function test_wp_array_get_null()
    {
        $this->assertSame(
            _wp_array_get(
                [
                    'key' => null,
                ],
                [ 'key' ],
                true
            ),
            null
        );

        $this->assertSame(
            _wp_array_get(
                [
                    'key' => null,
                ],
                [ 'key', 'subkey' ],
                true
            ),
            true
        );
    }

    /**
     * Tests _wp_array_get() with empty paths.
     *
     * @ticket 51720
     */
    public function test_wp_array_get_empty_paths()
    {
        $this->assertSame(
            _wp_array_get(
                [
                    'a' => 4,
                ],
                []
            ),
            null
        );

        $this->assertSame(
            _wp_array_get(
                [
                    'a' => [
                        'b' => [
                            'c' => 1,
                        ],
                    ],
                ],
                [ 'a', 'b', [] ]
            ),
            null
        );
    }
}
