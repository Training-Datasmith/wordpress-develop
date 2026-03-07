<?php

declare(strict_types=1);
/**
 * Tests for the insert_hooked_blocks function.
 *
 * @package WordPress
 * @subpackage Blocks
 *
 * @since 6.5.0
 *
 * @group blocks
 * @group block-hooks
 */
class Tests_Blocks_InsertHookedBlocks extends WP_UnitTestCase
{
    public const ANCHOR_BLOCK_TYPE       = 'tests/anchor-block';
    public const HOOKED_BLOCK_TYPE       = 'tests/hooked-block';
    public const OTHER_HOOKED_BLOCK_TYPE = 'tests/other-hooked-block';

    public const HOOKED_BLOCKS = [
        self::ANCHOR_BLOCK_TYPE => [
            'after'  => [ self::HOOKED_BLOCK_TYPE ],
            'before' => [ self::OTHER_HOOKED_BLOCK_TYPE ],
        ],
    ];

    /**
     * @ticket 59572
     * @ticket 60126
     * @ticket 60506
     *
     * @covers ::insert_hooked_blocks
     */
    public function test_insert_hooked_blocks_returns_correct_markup()
    {
        $anchor_block = [
            'blockName' => self::ANCHOR_BLOCK_TYPE,
        ];

        $actual = insert_hooked_blocks($anchor_block, 'after', self::HOOKED_BLOCKS, []);
        $this->assertSame(
            '<!-- wp:' . self::HOOKED_BLOCK_TYPE . ' /-->',
            $actual,
            "Markup for hooked block wasn't generated correctly."
        );
    }

    /**
     * @ticket 59572
     * @ticket 60126
     * @ticket 60506
     *
     * @covers ::insert_hooked_blocks
     */
    public function test_insert_hooked_blocks_if_block_is_ignored()
    {
        $anchor_block = [
            'blockName' => 'tests/anchor-block',
            'attrs'     => [
                'metadata' => [
                    'ignoredHookedBlocks' => [ self::HOOKED_BLOCK_TYPE ],
                ],
            ],
        ];

        $actual = insert_hooked_blocks($anchor_block, 'after', self::HOOKED_BLOCKS, []);
        $this->assertSame(
            '',
            $actual,
            "No markup should've been generated for ignored hooked block."
        );
    }

    /**
     * @ticket 59572
     * @ticket 60126
     * @ticket 60506
     *
     * @covers ::insert_hooked_blocks
     */
    public function test_insert_hooked_blocks_if_other_block_is_ignored()
    {
        $anchor_block = [
            'blockName' => 'tests/anchor-block',
            'attrs'     => [
                'metadata' => [
                    'ignoredHookedBlocks' => [ self::HOOKED_BLOCK_TYPE ],
                ],
            ],
        ];

        $actual = insert_hooked_blocks($anchor_block, 'before', self::HOOKED_BLOCKS, []);
        $this->assertSame(
            '<!-- wp:' . self::OTHER_HOOKED_BLOCK_TYPE . ' /-->',
            $actual,
            "Markup for newly hooked block should've been generated."
        );
    }

    /**
     * @ticket 59572
     * @ticket 60126
     * @ticket 60506
     *
     * @covers ::insert_hooked_blocks
     */
    public function test_insert_hooked_blocks_filter_can_set_attributes()
    {
        $anchor_block = [
            'blockName'    => self::ANCHOR_BLOCK_TYPE,
            'attrs'        => [
                'layout' => [
                    'type' => 'constrained',
                ],
            ],
            'innerContent' => [],
        ];

        $filter = function ($parsed_hooked_block, $hooked_block_type, $relative_position, $parsed_anchor_block) {
            // Is the hooked block adjacent to the anchor block?
            if ('before' !== $relative_position && 'after' !== $relative_position) {
                return $parsed_hooked_block;
            }

            // Does the anchor block have a layout attribute?
            if (isset($parsed_anchor_block['attrs']['layout'])) {
                // Copy the anchor block's layout attribute to the hooked block.
                $parsed_hooked_block['attrs']['layout'] = $parsed_anchor_block['attrs']['layout'];
            }

            return $parsed_hooked_block;
        };
        add_filter('hooked_block_' . self::HOOKED_BLOCK_TYPE, $filter, 10, 4);
        $actual = insert_hooked_blocks($anchor_block, 'after', self::HOOKED_BLOCKS, []);
        remove_filter('hooked_block_' . self::HOOKED_BLOCK_TYPE, $filter);

        $this->assertSame(
            '<!-- wp:' . self::HOOKED_BLOCK_TYPE . ' {"layout":{"type":"constrained"}} /-->',
            $actual,
            "Markup wasn't generated correctly for hooked block with attribute set by filter."
        );
    }

    /**
     * @ticket 59572
     * @ticket 60126
     * @ticket 60506
     *
     * @covers ::insert_hooked_blocks
     */
    public function test_insert_hooked_blocks_filter_can_wrap_block()
    {
        $anchor_block = [
            'blockName'    => self::ANCHOR_BLOCK_TYPE,
            'attrs'        => [
                'layout' => [
                    'type' => 'constrained',
                ],
            ],
            'innerContent' => [],
        ];

        $filter = function ($parsed_hooked_block) {
            if (self::HOOKED_BLOCK_TYPE !== $parsed_hooked_block['blockName']) {
                return $parsed_hooked_block;
            }

            // Wrap the block in a Group block.
            return [
                'blockName'    => 'core/group',
                'attrs'        => [],
                'innerBlocks'  => [ $parsed_hooked_block ],
                'innerContent' => [
                    '<div class="wp-block-group">',
                    null,
                    '</div>',
                ],
            ];
        };
        add_filter('hooked_block_' . self::HOOKED_BLOCK_TYPE, $filter, 10, 3);
        $actual = insert_hooked_blocks($anchor_block, 'after', self::HOOKED_BLOCKS, []);
        remove_filter('hooked_block_' . self::HOOKED_BLOCK_TYPE, $filter);

        $this->assertSame(
            '<!-- wp:group --><div class="wp-block-group"><!-- wp:' . self::HOOKED_BLOCK_TYPE . ' /--></div><!-- /wp:group -->',
            $actual,
            "Markup wasn't generated correctly for hooked block wrapped in Group block by filter."
        );
    }

    /**
     * @ticket 60580
     *
     * @covers ::insert_hooked_blocks
     */
    public function test_insert_hooked_blocks_filter_can_suppress_hooked_block()
    {
        $anchor_block = [
            'blockName'    => self::ANCHOR_BLOCK_TYPE,
            'attrs'        => [
                'layout' => [
                    'type' => 'flex',
                ],
            ],
            'innerContent' => [],
        ];

        $filter = function ($parsed_hooked_block, $hooked_block_type, $relative_position, $parsed_anchor_block) {
            // Is the hooked block adjacent to the anchor block?
            if ('before' !== $relative_position && 'after' !== $relative_position) {
                return $parsed_hooked_block;
            }

            if (
                isset($parsed_anchor_block['attrs']['layout']['type']) &&
                'flex' === $parsed_anchor_block['attrs']['layout']['type']
            ) {
                return null;
            }

            return $parsed_hooked_block;
        };
        add_filter('hooked_block_' . self::HOOKED_BLOCK_TYPE, $filter, 10, 4);
        $actual = insert_hooked_blocks($anchor_block, 'after', self::HOOKED_BLOCKS, []);
        remove_filter('hooked_block_' . self::HOOKED_BLOCK_TYPE, $filter);

        $this->assertSame('', $actual, "No markup should've been generated for hooked block suppressed by filter.");
    }
}
