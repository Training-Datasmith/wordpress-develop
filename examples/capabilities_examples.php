<?php

declare(strict_types=1);

/**
 * @file
 * Practical examples of the WordPress Capabilities API.
 *
 * Demonstrates user capability checks, meta capability mapping via
 * map_meta_cap(), and how custom post types declare their own capabilities.
 *
 * These examples run in a WordPress context (admin, REST endpoint,
 * theme template, or plugin file).
 */

// ---------------------------------------------------------------------------
// Example 1: Check if the current user can perform an action
// ---------------------------------------------------------------------------

// Primitive capability — granted directly by role.
if (current_user_can('edit_posts')) {
    echo '<a href="' . esc_url(admin_url('post-new.php')) . '">Write New Post</a>';
}

// Meta capability — object-specific; resolved via map_meta_cap().
$post_id = get_the_ID();
if ($post_id && current_user_can('edit_post', $post_id)) {
    printf(
        '<a href="%s">Edit this post</a>',
        esc_url(get_edit_post_link($post_id))
    );
}

// ---------------------------------------------------------------------------
// Example 2: Capability check in a REST API callback
// ---------------------------------------------------------------------------

/**
 * Permission callback for a custom REST endpoint.
 *
 * @param WP_REST_Request $request The incoming REST request.
 * @return bool|WP_Error TRUE if the current user has permission, WP_Error otherwise.
 */
function my_plugin_rest_permission_callback(WP_REST_Request $request): bool|\WP_Error
{
    $post_id = (int) $request->get_param('id');

    if (!current_user_can('edit_post', $post_id)) {
        return new \WP_Error(
            'rest_forbidden',
            __('You do not have permission to edit this post.', 'my-plugin'),
            ['status' => 403]
        );
    }

    return true;
}

register_rest_route('my-plugin/v1', '/posts/(?P<id>\d+)', [
    'methods'             => 'PUT',
    'callback'            => static fn (WP_REST_Request $r): WP_REST_Response => new WP_REST_Response(['updated' => true]),
    'permission_callback' => 'my_plugin_rest_permission_callback',
    'args'                => [
        'id' => ['type' => 'integer', 'required' => true],
    ],
]);

// ---------------------------------------------------------------------------
// Example 3: Registering a custom post type with capability mapping
// ---------------------------------------------------------------------------

/**
 * Registers a 'product' post type with its own capability set.
 *
 * Using 'capability_type' => 'product' automatically generates capabilities:
 * edit_product, edit_products, edit_others_products, publish_products, etc.
 * Grant these primitives via add_role() or a capabilities plugin.
 */
function my_plugin_register_product_post_type(): void
{
    register_post_type('product', [
        'label'           => __('Products', 'my-plugin'),
        'public'          => true,
        'capability_type' => 'product',
        'map_meta_cap'    => true,   // Let WordPress map edit_product, delete_product, etc.
        'supports'        => ['title', 'editor', 'thumbnail'],
    ]);
}
add_action('init', 'my_plugin_register_product_post_type');

// Grant 'product' capabilities to the editor role on plugin activation.
function my_plugin_add_capabilities(): void
{
    $editor = get_role('editor');
    if ($editor) {
        foreach ([
            'edit_product',
            'edit_products',
            'edit_others_products',
            'publish_products',
            'read_private_products',
            'delete_product',
            'delete_products',
        ] as $cap) {
            $editor->add_cap($cap);
        }
    }
}
register_activation_hook(__FILE__, 'my_plugin_add_capabilities');

// ---------------------------------------------------------------------------
// Example 4: Using map_meta_cap() directly
// ---------------------------------------------------------------------------

// map_meta_cap() translates a meta capability to required primitive capabilities.
// It is called internally by current_user_can() — you rarely need to call it directly.

$user_id = get_current_user_id();
$post_id = 1;

// What primitive capabilities does the user need to edit post 1?
$required = map_meta_cap('edit_post', $user_id, $post_id);
printf("To edit post %d, user %d needs: %s\n", $post_id, $user_id, implode(', ', $required));

// For a post owned by the user, map_meta_cap returns ['edit_posts'].
// For a post owned by someone else, it returns ['edit_others_posts'].
// If the post is published, it may also return ['edit_published_posts'].

// ---------------------------------------------------------------------------
// Example 5: Multisite super admin checks
// ---------------------------------------------------------------------------

if (is_multisite() && is_super_admin()) {
    echo 'This user has super admin access across the network.';
}

// Checking a specific user (not current).
$target_user_id = 5;
if (is_super_admin($target_user_id)) {
    printf("User %d is a network super admin.\n", $target_user_id);
}

// ---------------------------------------------------------------------------
// Example 6: Adding a custom capability check via filter
// ---------------------------------------------------------------------------

/**
 * Grants the 'manage_site_options' capability to users with 'manage_options'.
 *
 * Use this pattern to create virtual capabilities that map to primitives.
 *
 * @param bool[]  $allcaps All capabilities the user currently has.
 * @param string[] $caps   Required capabilities being checked.
 * @param array   $args   [0] => cap, [1] => user_id, [2+] => object IDs.
 * @return bool[] Modified capabilities array.
 */
function my_plugin_grant_custom_caps(array $allcaps, array $caps, array $args): array
{
    if (in_array('manage_site_options', $caps, true) && !empty($allcaps['manage_options'])) {
        $allcaps['manage_site_options'] = true;
    }
    return $allcaps;
}
add_filter('user_has_cap', 'my_plugin_grant_custom_caps', 10, 3);
