<?php

declare(strict_types=1);

/**
 * @file
 * Practical examples of the WordPress Hook (Filter & Action) API.
 *
 * These examples demonstrate how to use add_filter(), add_action(),
 * remove_filter(), and do_action() / apply_filters() properly.
 *
 * Add these functions to a plugin file or a theme's functions.php.
 * They are not standalone-runnable outside of a WordPress installation.
 */

// ---------------------------------------------------------------------------
// Example 1: Filter the post content (add a disclaimer after every post)
// ---------------------------------------------------------------------------

/**
 * Appends a disclaimer paragraph to all singular post content.
 *
 * @param string $content The post content HTML.
 * @return string Modified content with disclaimer appended.
 */
function my_plugin_append_disclaimer(string $content): string
{
    if (!is_singular('post') || !in_the_loop() || !is_main_query()) {
        return $content;
    }

    return $content . '<p class="disclaimer"><em>The views expressed here are those of the author.</em></p>';
}
add_filter('the_content', 'my_plugin_append_disclaimer');

// ---------------------------------------------------------------------------
// Example 2: Action — fire custom code after a post is saved
// ---------------------------------------------------------------------------

/**
 * Clears a custom transient cache whenever a post is saved.
 *
 * @param int     $post_id The post ID being saved.
 * @param WP_Post $post    The post object.
 * @param bool    $update  Whether this is an update to an existing post.
 */
function my_plugin_clear_post_cache(int $post_id, WP_Post $post, bool $update): void
{
    // Skip auto-saves and revisions.
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (wp_is_post_revision($post_id)) {
        return;
    }

    delete_transient('my_plugin_featured_posts');
    delete_transient("my_plugin_post_{$post_id}");
}
add_action('save_post', 'my_plugin_clear_post_cache', 10, 3);

// ---------------------------------------------------------------------------
// Example 3: Filter with priority — chain multiple modifications
// ---------------------------------------------------------------------------

// First transformation at priority 10 (default).
add_filter('the_title', static function (string $title): string {
    return trim($title);
}, 10, 1);

// Second transformation at priority 20 (runs after the first).
add_filter('the_title', static function (string $title): string {
    return esc_html($title);
}, 20, 1);

// ---------------------------------------------------------------------------
// Example 4: Remove a filter added by a third-party plugin
// ---------------------------------------------------------------------------

/**
 * Removes the default WordPress texturize filter for a specific context.
 * Must be called on the 'init' action to ensure the function is registered.
 */
function my_plugin_disable_texturize(): void
{
    remove_filter('the_content', 'wptexturize');
}
add_action('init', 'my_plugin_disable_texturize');

// ---------------------------------------------------------------------------
// Example 5: Custom filter hook — allow other plugins to modify your data
// ---------------------------------------------------------------------------

/**
 * Retrieves a list of featured post IDs, extensible via filter.
 *
 * @return int[] Array of featured post IDs.
 */
function my_plugin_get_featured_post_ids(): array
{
    $ids = get_transient('my_plugin_featured_posts');

    if ($ids === false) {
        $query = new WP_Query([
            'post_type'      => 'post',
            'meta_key'       => '_featured',
            'meta_value'     => '1',
            'posts_per_page' => 5,
            'fields'         => 'ids',
            'no_found_rows'  => true,
        ]);
        $ids = $query->posts;
        set_transient('my_plugin_featured_posts', $ids, HOUR_IN_SECONDS);
    }

    /**
     * Filters the list of featured post IDs returned by my_plugin.
     *
     * @since 1.0.0
     *
     * @param int[] $ids Array of featured post IDs.
     */
    return (array) apply_filters('my_plugin_featured_post_ids', $ids);
}

// Another plugin can now override the list:
add_filter('my_plugin_featured_post_ids', static function (array $ids): array {
    // Prepend post ID 42 to always appear first.
    array_unshift($ids, 42);
    return array_unique($ids);
});

// ---------------------------------------------------------------------------
// Example 6: Custom action hook — allow extensibility in your plugin output
// ---------------------------------------------------------------------------

/**
 * Renders a featured posts widget, with before/after action hooks.
 */
function my_plugin_render_featured_widget(): void
{
    /**
     * Fires before the featured posts widget is rendered.
     *
     * @since 1.0.0
     */
    do_action('my_plugin_before_featured_widget');

    $ids = my_plugin_get_featured_post_ids();

    echo '<ul class="featured-posts">';
    foreach ($ids as $id) {
        printf(
            '<li><a href="%s">%s</a></li>',
            esc_url(get_permalink($id)),
            esc_html(get_the_title($id))
        );
    }
    echo '</ul>';

    /**
     * Fires after the featured posts widget is rendered.
     *
     * @since 1.0.0
     */
    do_action('my_plugin_after_featured_widget');
}

// ---------------------------------------------------------------------------
// Example 7: Object method as a hook callback
// ---------------------------------------------------------------------------

class My_Plugin_Email_Handler
{
    public function __construct()
    {
        add_action('user_register', [$this, 'send_welcome_email'], 10, 2);
    }

    /**
     * Sends a welcome email to a newly registered user.
     *
     * @param int   $user_id  The newly created user ID.
     * @param array $userdata The raw $_POST data from the registration form.
     */
    public function send_welcome_email(int $user_id, array $userdata): void
    {
        $user = get_userdata($user_id);
        if (!$user) {
            return;
        }

        wp_mail(
            $user->user_email,
            __('Welcome!', 'my-plugin'),
            sprintf(__('Hi %s, welcome to our site!', 'my-plugin'), $user->display_name)
        );
    }
}

// Instantiate during plugin init; hooks are registered in the constructor.
add_action('plugins_loaded', static function (): void {
    new My_Plugin_Email_Handler();
});
