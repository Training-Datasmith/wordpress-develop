<?php

declare(strict_types=1);

/**
 * @file
 * Practical WP_Query usage examples.
 *
 * WP_Query is the primary way to fetch posts in WordPress.
 * These examples are standalone-runnable in a WordPress context
 * (e.g. inside a theme template, shortcode callback, or REST endpoint).
 *
 * Always call wp_reset_postdata() after a custom Loop to restore
 * the global $post variable.
 */

// ---------------------------------------------------------------------------
// Example 1: Simple secondary query — latest 5 published articles
// ---------------------------------------------------------------------------

$recent_posts = new WP_Query([
    'post_type'      => 'post',
    'post_status'    => 'publish',
    'posts_per_page' => 5,
    'orderby'        => 'date',
    'order'          => 'DESC',
]);

if ($recent_posts->have_posts()) {
    while ($recent_posts->have_posts()) {
        $recent_posts->the_post();
        printf('<h2><a href="%s">%s</a></h2>', get_permalink(), get_the_title());
    }
    wp_reset_postdata();
}

// ---------------------------------------------------------------------------
// Example 2: Query by taxonomy term
// ---------------------------------------------------------------------------

$tech_articles = new WP_Query([
    'post_type'      => 'post',
    'posts_per_page' => 10,
    'tax_query'      => [
        [
            'taxonomy' => 'category',
            'field'    => 'slug',
            'terms'    => 'technology',
        ],
    ],
]);

if ($tech_articles->have_posts()) {
    echo '<ul>';
    while ($tech_articles->have_posts()) {
        $tech_articles->the_post();
        printf('<li><a href="%s">%s</a></li>', get_permalink(), get_the_title());
    }
    echo '</ul>';
    wp_reset_postdata();
}

// ---------------------------------------------------------------------------
// Example 3: Query by custom field (meta_query)
// ---------------------------------------------------------------------------

$featured = new WP_Query([
    'post_type'      => 'product',
    'posts_per_page' => 6,
    'meta_query'     => [
        [
            'key'     => '_is_featured',
            'value'   => '1',
            'compare' => '=',
        ],
    ],
]);

$featured_ids = [];
if ($featured->have_posts()) {
    while ($featured->have_posts()) {
        $featured->the_post();
        $featured_ids[] = get_the_ID();
    }
    wp_reset_postdata();
}

printf("Featured product IDs: %s\n", implode(', ', $featured_ids));

// ---------------------------------------------------------------------------
// Example 4: Return only post IDs (faster, no object hydration)
// ---------------------------------------------------------------------------

$ids_query = new WP_Query([
    'post_type'      => 'post',
    'post_status'    => 'publish',
    'posts_per_page' => 100,
    'fields'         => 'ids',   // Returns int[], not WP_Post[].
    'no_found_rows'  => true,    // Skip SQL_CALC_FOUND_ROWS for performance.
]);

/** @var int[] $post_ids */
$post_ids = $ids_query->posts;
printf("Found %d post IDs\n", count($post_ids));

// ---------------------------------------------------------------------------
// Example 5: Pagination-aware query
// ---------------------------------------------------------------------------

$paged       = (int) (get_query_var('paged') ?: 1);
$paged_query = new WP_Query([
    'post_type'      => 'post',
    'posts_per_page' => 10,
    'paged'          => $paged,
]);

if ($paged_query->have_posts()) {
    while ($paged_query->have_posts()) {
        $paged_query->the_post();
        the_title('<h2>', '</h2>');
    }
    wp_reset_postdata();

    // Render pagination links.
    $pagination = paginate_links([
        'total'   => $paged_query->max_num_pages,
        'current' => $paged,
    ]);
    echo $pagination;
}

// ---------------------------------------------------------------------------
// Example 6: Modify the main query before execution (pre_get_posts)
// ---------------------------------------------------------------------------

/**
 * Restricts the home page post feed to the 'article' post type.
 *
 * Add this to functions.php — do NOT use new WP_Query() for the main query.
 *
 * @param WP_Query $query The main query passed by reference.
 */
function my_theme_pre_get_posts(WP_Query $query): void
{
    if (!is_admin() && $query->is_main_query() && $query->is_home()) {
        $query->set('post_type', ['post', 'article']);
        $query->set('posts_per_page', 12);
    }
}
add_action('pre_get_posts', 'my_theme_pre_get_posts');

// ---------------------------------------------------------------------------
// Example 7: Query with date range
// ---------------------------------------------------------------------------

$this_year_posts = new WP_Query([
    'post_type'   => 'post',
    'date_query'  => [
        [
            'after'     => ['year' => (int) date('Y'), 'month' => 1, 'day' => 1],
            'inclusive' => true,
        ],
    ],
    'posts_per_page' => -1,
    'fields'         => 'ids',
    'no_found_rows'  => true,
]);

printf("Posts published this year: %d\n", count($this_year_posts->posts));
