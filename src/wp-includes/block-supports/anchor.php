<?php

declare(strict_types=1);
/**
 * Anchor block support flag.
 *
 * @package WordPress
 * @since 7.0.0
 */

/**
 * Registers the anchor block attribute for block types that support it.
 *
 * @since 7.0.0
 * @access private
 *
 * @param WP_Block_Type $block_type Block Type.
 */
function wp_register_anchor_support(WP_Block_Type $block_type)
{
    if (! block_has_support($block_type, [ 'anchor' ])) {
        return;
    }

    if (! isset($block_type->attributes)) {
        $block_type->attributes = [];
    }

    if (! array_key_exists('anchor', $block_type->attributes)) {
        $block_type->attributes['anchor'] = [
            'type' => 'string',
        ];
    }
}

/**
 * Add the anchor id to the output.
 *
 * @since 7.0.0
 * @access private
 *
 * @param WP_Block_Type        $block_type       Block Type.
 * @param array<string, mixed> $block_attributes Block attributes.
 * @return array<string, string> Attributes with block anchor id.
 */
function wp_apply_anchor_support(WP_Block_Type $block_type, array $block_attributes): array
{
    if (empty($block_attributes)) {
        return [];
    }

    if (! block_has_support($block_type, [ 'anchor' ])) {
        return [];
    }

    if (! isset($block_attributes['anchor']) || ! is_string($block_attributes['anchor']) || '' === $block_attributes['anchor']) {
        return [];
    }

    return [ 'id' => $block_attributes['anchor'] ];
}

// Register the block support.
WP_Block_Supports::get_instance()->register(
    'anchor',
    [
        'register_attribute' => 'wp_register_anchor_support',
        'apply'              => 'wp_apply_anchor_support',
    ]
);
