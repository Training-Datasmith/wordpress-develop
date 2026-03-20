# WordPress Architecture

## Purpose

WordPress is an open-source content management system and blogging platform. It is built primarily in procedural PHP with a class-based plugin/hook architecture layered on top. It powers a large portion of the web and is designed to be maximally backward-compatible and extensible by third-party plugins and themes.

## Directory Structure

```
src/
  wp-includes/             # Core library: hooks, queries, templates, classes
    class-wp-query.php     # WP_Query — the central post querying engine
    class-wp-hook.php      # WP_Hook — filter/action hook implementation
    class-wp-user.php      # WP_User — user object with capability checking
    class-wp-post.php      # WP_Post — post object
    class-wp-comment.php   # WP_Comment — comment object
    capabilities.php       # map_meta_cap() and capability mapping logic
    post.php               # Post CRUD functions (get_post, wp_insert_post, etc.)
    query.php              # Global query functions wrapping WP_Query
    functions.php          # Miscellaneous utility functions
    formatting.php         # Text formatting (esc_html, wpautop, etc.)
    pluggable.php          # Overridable core functions (auth, email, etc.)
    plugin.php             # add_filter(), add_action(), apply_filters(), etc.
    taxonomy.php           # Taxonomy and term management
    user.php               # User management functions
    meta.php               # Post/user/term metadata CRUD
    cache.php              # Object cache API (wp_cache_get/set/delete)
  wp-admin/                # Admin UI — dashboard, edit screens, settings
tests/                     # PHPUnit test suite
```

## Key Design Decisions

### Hook System (Filters & Actions)
The hook system is the primary extension mechanism. Every significant operation fires an action (`do_action`) or filter (`apply_filters`) that plugins can subscribe to with `add_action` / `add_filter`. `WP_Hook` manages callback lists per hook name, sorted by priority.

This design allows unlimited extensibility without class inheritance, matching the procedural nature of WordPress code.

### WP_Query — Central Query Engine
All post retrieval flows through `WP_Query`. It:
- Parses query variables from URL parameters or explicit arguments.
- Builds a SQL query against `$wpdb->posts` with joins for taxonomy, meta, and date filtering.
- Populates the Loop (`$posts` array) and conditional flags (`is_single`, `is_archive`, etc.).
- Fires `pre_get_posts` action before SQL execution, enabling plugins to modify any query.

### $wpdb — Database Abstraction
`wpdb` is a thin wrapper around `mysqli` (or PDO in some configurations). It provides `prepare()` for parameterized queries and escaping helpers. Unlike Drupal's query builder, most WordPress queries are hand-written SQL strings passed through `$wpdb->prepare()`.

### Capabilities System
WordPress uses a two-level capability model:
- **Meta capabilities** (e.g., `edit_post`, `delete_user`) are object-specific and resolved to primitive capabilities by `map_meta_cap()`.
- **Primitive capabilities** (e.g., `edit_posts`, `manage_options`) are checked directly against the user's role grants.

Custom post types and taxonomies can declare their own capability mappings.

### Pluggable Functions
Functions in `pluggable.php` (e.g., `wp_mail()`, `wp_authenticate()`) can be entirely replaced by plugins by defining a function of the same name before WordPress loads the file. This is an older extensibility pattern predating hooks.

## Extension Points

- **Filters**: Modify data as it flows through WordPress (`the_content`, `wp_title`, `pre_get_posts`).
- **Actions**: Execute code at lifecycle points (`init`, `save_post`, `wp_head`).
- **Custom Post Types**: Registered via `register_post_type()` with custom capability maps.
- **Custom Taxonomies**: Registered via `register_taxonomy()`.
- **Shortcodes**: `add_shortcode()` maps tags in post content to PHP callbacks.
- **REST API endpoints**: `register_rest_route()` adds custom API routes.
- **Blocks (Block Editor)**: Server-side blocks registered via `register_block_type()`.

## Dependency Flow

```
HTTP Request (index.php)
  └─> wp-blog-header.php
        └─> wp-load.php → wp-settings.php (load all core, plugins, theme)
              └─> WP::main() — parse request, set query vars
                    └─> WP_Query::get_posts() — build and execute SQL
                          └─> Template loader — find and include theme template
                                └─> The Loop: while(have_posts()) the_post()
                                      └─> Template tags (the_title, the_content, etc.)
```

## Coding Standards

- PHP 7.4+ minimum; PHP 8.x supported. `declare(strict_types=1)` used in this fork.
- WordPress Coding Standards (WPCS) enforced via phpcs.
- Procedural style is the norm for functions; classes used for complex subsystems.
- PHPDoc blocks on all functions with `@since`, `@param`, `@return`, `@global`.
