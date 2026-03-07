<?php

declare(strict_types=1);

/**
 * @group pluggable
 *
 * @coversNothing
 */
class Tests_Pluggable_Signatures extends WP_UnitTestCase
{
    /**
     * Tests that the signatures of all functions in pluggable.php match their expected signature.
     *
     * @ticket 33654
     * @ticket 33867
     *
     * @dataProvider get_defined_pluggable_functions
     */
    public function test_pluggable_function_signatures_match($function_name)
    {

        $signatures = $this->get_pluggable_function_signatures();

        $this->assertTrue(function_exists($function_name));
        $this->assertArrayHasKey($function_name, $signatures);

        $function_ref = new ReflectionFunction($function_name);
        $param_refs   = $function_ref->getParameters();

        $this->assertSame(count($signatures[ $function_name ]), count($param_refs));

        $i = 0;

        foreach ($signatures[ $function_name ] as $name => $value) {

            $param_ref = $param_refs[ $i ];
            $msg       = 'Parameter: ' . $param_ref->getName();

            if (is_numeric($name)) {
                $name = $value;
                $this->assertFalse($param_ref->isOptional(), $msg);
            } else {
                $this->assertTrue($param_ref->isOptional(), $msg);
                $this->assertSame($value, $param_ref->getDefaultValue(), $msg);
            }

            $this->assertSame($name, $param_ref->getName(), $msg);

            ++$i;

        }
    }

    /**
     * Test the tests. Makes sure all the expected pluggable functions exist and that they live in pluggable.php.
     *
     * @ticket 33654
     * @ticket 33867
     */
    public function test_all_pluggable_functions_exist()
    {

        $defined  = wp_list_pluck($this->get_defined_pluggable_functions(), 0);
        $expected = $this->get_pluggable_function_signatures();

        foreach ($expected as $function => $sig) {
            $msg = 'Function: ' . $function . '()';
            $this->assertTrue(function_exists($function), $msg);
            $this->assertContains($function, $defined, $msg);
        }
    }

    /**
     * Data provider for our pluggable function signature tests.
     *
     * @return array Data provider array of pluggable function names.
     */
    public function get_defined_pluggable_functions()
    {

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $test_functions = [
            'install_network',
            'wp_install',
            'wp_install_defaults',
            'wp_new_blog_notification',
            'wp_upgrade',
        ];
        $test_files     = [
            'wp-includes/pluggable.php',
        ];

        // Pluggable function signatures are not tested when an external object cache is in use. See #31491.
        if (! wp_using_ext_object_cache()) {
            $test_files[] = 'wp-includes/cache.php';
        }

        $data = [];

        foreach ($test_functions as $function) {
            $data[] = [
                $function,
            ];
        }

        foreach ($test_files as $file) {
            preg_match_all('#^\t?function (\w+)#m', file_get_contents(ABSPATH . $file), $functions);

            foreach ($functions[1] as $function) {
                $data[] = [
                    $function,
                ];
            }
        }

        return $data;
    }

    /**
     * Expected pluggable function signatures.
     *
     * @return array Array of signatures keyed by their function name.
     */
    public function get_pluggable_function_signatures()
    {

        $signatures = [

            // wp-includes/pluggable.php:
            'wp_set_current_user'             => [
                'id',
                'name' => '',
            ],
            'wp_get_current_user'             => [],
            'get_userdata'                    => [ 'user_id' ],
            'get_user_by'                     => [ 'field', 'value' ],
            'cache_users'                     => [ 'user_ids' ],
            'wp_mail'                         => [
                'to',
                'subject',
                'message',
                'headers'     => '',
                'attachments' => [],
                'embeds'      => [],
            ],
            'wp_authenticate'                 => [ 'username', 'password' ],
            'wp_logout'                       => [],
            'wp_validate_auth_cookie'         => [
                'cookie' => '',
                'scheme' => '',
            ],
            'wp_generate_auth_cookie'         => [
                'user_id',
                'expiration',
                'scheme' => 'auth',
                'token'  => '',
            ],
            'wp_parse_auth_cookie'            => [
                'cookie' => '',
                'scheme' => '',
            ],
            'wp_set_auth_cookie'              => [
                'user_id',
                'remember' => false,
                'secure'   => '',
                'token'    => '',
            ],
            'wp_clear_auth_cookie'            => [],
            'is_user_logged_in'               => [],
            'auth_redirect'                   => [],
            'check_admin_referer'             => [
                'action'    => -1,
                'query_arg' => '_wpnonce',
            ],
            'check_ajax_referer'              => [
                'action'    => -1,
                'query_arg' => false,
                'stop'      => true,
            ],
            'wp_redirect'                     => [
                'location',
                'status'        => 302,
                'x_redirect_by' => 'WordPress',
            ],
            'wp_sanitize_redirect'            => [ 'location' ],
            '_wp_sanitize_utf8_in_redirect'   => [ 'matches' ],
            'wp_safe_redirect'                => [
                'location',
                'status'        => 302,
                'x_redirect_by' => 'WordPress',
            ],
            'wp_validate_redirect'            => [
                'location',
                'fallback_url' => '',
            ],
            'wp_notify_postauthor'            => [
                'comment_id',
                'deprecated' => null,
            ],
            'wp_notify_moderator'             => [ 'comment_id' ],
            'wp_password_change_notification' => [ 'user' ],
            'wp_new_user_notification'        => [
                'user_id',
                'deprecated' => null,
                'notify'     => '',
            ],
            'wp_nonce_tick'                   => [ 'action' => -1 ],
            'wp_verify_nonce'                 => [
                'nonce',
                'action' => -1,
            ],
            'wp_create_nonce'                 => [ 'action' => -1 ],
            'wp_salt'                         => [ 'scheme' => 'auth' ],
            'wp_hash'                         => [
                'data',
                'scheme' => 'auth',
                'algo'   => 'md5',
            ],
            'wp_hash_password'                => [ 'password' ],
            'wp_check_password'               => [
                'password',
                'hash',
                'user_id' => '',
            ],
            'wp_password_needs_rehash'        => [
                'hash',
                'user_id' => '',
            ],
            'wp_generate_password'            => [
                'length'              => 12,
                'special_chars'       => true,
                'extra_special_chars' => false,
            ],
            'wp_rand'                         => [
                'min' => null,
                'max' => null,
            ],
            'wp_set_password'                 => [ 'password', 'user_id' ],
            'get_avatar'                      => [
                'id_or_email',
                'size'          => 96,
                'default_value' => '',
                'alt'           => '',
                'args'          => null,
            ],
            'wp_text_diff'                    => [
                'left_string',
                'right_string',
                'args' => null,
            ],

            // wp-admin/includes/schema.php:
            'install_network'                 => [],

            // wp-admin/includes/upgrade.php:
            'wp_install'                      => [
                'blog_title',
                'user_name',
                'user_email',
                'is_public',
                'deprecated'    => '',
                'user_password' => '',
                'language'      => '',
            ],
            'wp_install_defaults'             => [ 'user_id' ],
            'wp_new_blog_notification'        => [ 'blog_title', 'blog_url', 'user_id', 'password' ],
            'wp_upgrade'                      => [],
        ];

        // Pluggable function signatures are not tested when an external object cache is in use. See #31491.
        if (! wp_using_ext_object_cache()) {
            $signatures = array_merge(
                $signatures,
                [

                    // wp-includes/cache.php:
                    'wp_cache_init'                      => [],
                    'wp_cache_add'                       => [
                        'key',
                        'data',
                        'group'  => '',
                        'expire' => 0,
                    ],
                    'wp_cache_add_multiple'              => [
                        'data',
                        'group'  => '',
                        'expire' => 0,
                    ],
                    'wp_cache_replace'                   => [
                        'key',
                        'data',
                        'group'  => '',
                        'expire' => 0,
                    ],
                    'wp_cache_set'                       => [
                        'key',
                        'data',
                        'group'  => '',
                        'expire' => 0,
                    ],
                    'wp_cache_set_multiple'              => [
                        'data',
                        'group'  => '',
                        'expire' => 0,
                    ],
                    'wp_cache_get'                       => [
                        'key',
                        'group' => '',
                        'force' => false,
                        'found' => null,
                    ],
                    'wp_cache_get_multiple'              => [
                        'keys',
                        'group' => '',
                        'force' => false,
                    ],
                    'wp_cache_delete'                    => [
                        'key',
                        'group' => '',
                    ],
                    'wp_cache_delete_multiple'           => [
                        'keys',
                        'group' => '',
                    ],
                    'wp_cache_incr'                      => [
                        'key',
                        'offset' => 1,
                        'group'  => '',
                    ],
                    'wp_cache_decr'                      => [
                        'key',
                        'offset' => 1,
                        'group'  => '',
                    ],
                    'wp_cache_flush'                     => [],
                    'wp_cache_flush_runtime'             => [],
                    'wp_cache_flush_group'               => [ 'group' ],
                    'wp_cache_supports'                  => [ 'feature' ],
                    'wp_cache_close'                     => [],
                    'wp_cache_add_global_groups'         => [ 'groups' ],
                    'wp_cache_add_non_persistent_groups' => [ 'groups' ],
                    'wp_cache_switch_to_blog'            => [ 'blog_id' ],
                    'wp_cache_reset'                     => [],
                ]
            );
        }

        return $signatures;
    }
}
