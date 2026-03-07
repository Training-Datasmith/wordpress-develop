<?php

declare(strict_types=1);
/**
 * Noop functions for load-scripts.php and load-styles.php.
 *
 * @package WordPress
 * @subpackage Administration
 * @since 4.4.0
 */

/**
 * @ignore
 */
function __(): void
{
}

/**
 * @ignore
 */
function _x(): void
{
}

/**
 * @ignore
 */
function add_filter(): void
{
}

/**
 * @ignore
 */
function has_filter(): bool
{
    return false;
}

/**
 * @ignore
 */
function esc_attr(): void
{
}

/**
 * @ignore
 */
function apply_filters(): void
{
}

/**
 * @ignore
 */
function get_option(): void
{
}

/**
 * @ignore
 */
function is_lighttpd_before_150(): void
{
}

/**
 * @ignore
 */
function add_action(): void
{
}

/**
 * @ignore
 */
function did_action(): void
{
}

/**
 * @ignore
 */
function do_action_ref_array(): void
{
}

/**
 * @ignore
 */
function get_bloginfo(): void
{
}

/**
 * @ignore
 */
function is_admin(): bool
{
    return true;
}

/**
 * @ignore
 */
function site_url(): void
{
}

/**
 * @ignore
 */
function admin_url(): void
{
}

/**
 * @ignore
 */
function home_url(): void
{
}

/**
 * @ignore
 */
function includes_url(): void
{
}

/**
 * @ignore
 */
function wp_guess_url(): void
{
}

function get_file($path)
{

    $path = realpath($path);

    if (! $path || ! @is_file($path)) {
        return '';
    }

    return @file_get_contents($path);
}
