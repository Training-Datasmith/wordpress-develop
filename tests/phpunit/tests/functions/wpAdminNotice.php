<?php

declare(strict_types=1);

/**
 * Tests for `wp_admin_notice()`.
 *
 * @group functions
 *
 * @covers ::wp_admin_notice
 */
class Tests_Functions_WpAdminNotice extends WP_UnitTestCase
{
    /**
     * Tests that `wp_admin_notice()` outputs the expected admin notice markup.
     *
     * @ticket 57791
     *
     * @dataProvider data_should_output_admin_notice
     *
     * @param string $message  The message to output.
     * @param array  $args     Arguments for the admin notice.
     * @param string $expected The expected admin notice markup.
     */
    public function test_should_output_admin_notice($message, $args, $expected)
    {
        ob_start();
        wp_admin_notice($message, $args);
        $actual = ob_get_clean();

        $this->assertSame($expected, $actual);
    }

    /**
     * Data provider.
     *
     * @return array[]
     */
    public function data_should_output_admin_notice()
    {
        return [
            'defaults'                                  => [
                'message'  => 'A notice with defaults.',
                'args'     => [],
                'expected' => '<div class="notice"><p>A notice with defaults.</p></div>',
            ],
            'an empty message (used for templates)'     => [
                'message'  => '',
                'args'     => [
                    'type'               => 'error',
                    'dismissible'        => true,
                    'id'                 => 'message',
                    'additional_classes' => [ 'inline', 'hidden' ],
                ],
                'expected' => '<div id="message" class="notice notice-error is-dismissible inline hidden"><p></p></div>',
            ],
            'an empty message (used for templates) without paragraph wrapping' => [
                'message'  => '',
                'args'     => [
                    'type'               => 'error',
                    'dismissible'        => true,
                    'id'                 => 'message',
                    'additional_classes' => [ 'inline', 'hidden' ],
                    'paragraph_wrap'     => false,
                ],
                'expected' => '<div id="message" class="notice notice-error is-dismissible inline hidden"></div>',
            ],
            'an "error" notice'                         => [
                'message'  => 'An "error" notice.',
                'args'     => [
                    'type' => 'error',
                ],
                'expected' => '<div class="notice notice-error"><p>An "error" notice.</p></div>',
            ],
            'a "success" notice'                        => [
                'message'  => 'A "success" notice.',
                'args'     => [
                    'type' => 'success',
                ],
                'expected' => '<div class="notice notice-success"><p>A "success" notice.</p></div>',
            ],
            'a "warning" notice'                        => [
                'message'  => 'A "warning" notice.',
                'args'     => [
                    'type' => 'warning',
                ],
                'expected' => '<div class="notice notice-warning"><p>A "warning" notice.</p></div>',
            ],
            'an "info" notice'                          => [
                'message'  => 'An "info" notice.',
                'args'     => [
                    'type' => 'info',
                ],
                'expected' => '<div class="notice notice-info"><p>An "info" notice.</p></div>',
            ],
            'a type that already starts with "notice-"' => [
                'message'  => 'A type that already starts with "notice-".',
                'args'     => [
                    'type' => 'notice-info',
                ],
                'expected' => '<div class="notice notice-notice-info"><p>A type that already starts with "notice-".</p></div>',
            ],
            'a dismissible notice'                      => [
                'message'  => 'A dismissible notice.',
                'args'     => [
                    'dismissible' => true,
                ],
                'expected' => '<div class="notice is-dismissible"><p>A dismissible notice.</p></div>',
            ],
            'no type and an ID'                         => [
                'message'  => 'A notice with an ID.',
                'args'     => [
                    'id' => 'message',
                ],
                'expected' => '<div id="message" class="notice"><p>A notice with an ID.</p></div>',
            ],
            'a type and an ID'                          => [
                'message'  => 'A warning notice with an ID.',
                'args'     => [
                    'type' => 'warning',
                    'id'   => 'message',
                ],
                'expected' => '<div id="message" class="notice notice-warning"><p>A warning notice with an ID.</p></div>',
            ],
            'no type and additional classes'            => [
                'message'  => 'A notice with additional classes.',
                'args'     => [
                    'additional_classes' => [ 'error', 'notice-alt' ],
                ],
                'expected' => '<div class="notice error notice-alt"><p>A notice with additional classes.</p></div>',
            ],
            'a type and additional classes'             => [
                'message'  => 'A warning notice with additional classes.',
                'args'     => [
                    'type'               => 'warning',
                    'additional_classes' => [ 'error', 'notice-alt' ],
                ],
                'expected' => '<div class="notice notice-warning error notice-alt"><p>A warning notice with additional classes.</p></div>',
            ],
            'a dismissible notice with a type and additional classes' => [
                'message'  => 'A dismissible warning notice with a type and additional classes.',
                'args'     => [
                    'type'               => 'warning',
                    'dismissible'        => true,
                    'additional_classes' => [ 'error', 'notice-alt' ],
                ],
                'expected' => '<div class="notice notice-warning is-dismissible error notice-alt"><p>A dismissible warning notice with a type and additional classes.</p></div>',
            ],
            'a notice without paragraph wrapping'       => [
                'message'  => '<span>A notice without paragraph wrapping.</span>',
                'args'     => [
                    'paragraph_wrap' => false,
                ],
                'expected' => '<div class="notice"><span>A notice without paragraph wrapping.</span></div>',
            ],
            'an unsafe type'                            => [
                'message'  => 'A notice with an unsafe type.',
                'args'     => [
                    'type' => '"><script>alert("Howdy,admin!");</script>',
                ],
                'expected' => '<div class="notice notice-">alert("Howdy,admin!");"&gt;<p>A notice with an unsafe type.</p></div>',
            ],
            'an unsafe ID'                              => [
                'message'  => 'A notice with an unsafe ID.',
                'args'     => [
                    'id' => '"><script>alert( "Howdy, admin!" );</script> <div class="notice',
                ],
                'expected' => '<div id="">alert( "Howdy, admin!" ); <div class="notice"><p>A notice with an unsafe ID.</p></div>',
            ],
            'unsafe additional classes'                 => [
                'message'  => 'A notice with unsafe additional classes.',
                'args'     => [
                    'additional_classes' => [ '"><script>alert( "Howdy, admin!" );</script> <div class="notice' ],
                ],
                'expected' => '<div class="notice ">alert( "Howdy, admin!" ); <div class="notice"><p>A notice with unsafe additional classes.</p></div>',
            ],
            'a type that is not a string'               => [
                'message'  => 'A notice with a type that is not a string.',
                'args'     => [
                    'type' => [],
                ],
                'expected' => '<div class="notice"><p>A notice with a type that is not a string.</p></div>',
            ],
            'a type with only empty space'              => [
                'message'  => 'A notice with a type with only empty space.',
                'args'     => [
                    'type' => " \t\r\n",
                ],
                'expected' => '<div class="notice"><p>A notice with a type with only empty space.</p></div>',
            ],
            'an ID that is not a string'                => [
                'message'  => 'A notice with an ID that is not a string.',
                'args'     => [
                    'id' => [ 'message' ],
                ],
                'expected' => '<div class="notice"><p>A notice with an ID that is not a string.</p></div>',
            ],
            'an ID with only empty space'               => [
                'message'  => 'A notice with an ID with only empty space.',
                'args'     => [
                    'id' => " \t\r\n",
                ],
                'expected' => '<div class="notice"><p>A notice with an ID with only empty space.</p></div>',
            ],
            'dismissible as a truthy value rather than (bool) true' => [
                'message'  => 'A notice with dismissible as a truthy value rather than (bool) true.',
                'args'     => [
                    'dismissible' => 1,
                ],
                'expected' => '<div class="notice"><p>A notice with dismissible as a truthy value rather than (bool) true.</p></div>',
            ],
            'additional classes that are not an array'  => [
                'message'  => 'A notice with additional classes that are not an array.',
                'args'     => [
                    'additional_classes' => 'class-1 class-2 class-3',
                ],
                'expected' => '<div class="notice"><p>A notice with additional classes that are not an array.</p></div>',
            ],
            'additional attribute with a value'         => [
                'message'  => 'A notice with an additional attribute with a value.',
                'args'     => [
                    'attributes' => [ 'aria-live' => 'assertive' ],
                ],
                'expected' => '<div class="notice" aria-live="assertive"><p>A notice with an additional attribute with a value.</p></div>',
            ],
            'additional hidden attribute'               => [
                'message'  => 'A notice with the hidden attribute.',
                'args'     => [
                    'attributes' => [ 'hidden' => true ],
                ],
                'expected' => '<div class="notice" hidden><p>A notice with the hidden attribute.</p></div>',
            ],
            'additional attribute no associative keys'  => [
                'message'  => 'A notice with a boolean attribute without an associative key.',
                'args'     => [
                    'attributes' => [ 'hidden' ],
                ],
                'expected' => '<div class="notice" hidden><p>A notice with a boolean attribute without an associative key.</p></div>',
            ],
            'additional attribute with role'            => [
                'message'  => 'A notice with an additional attribute role.',
                'args'     => [
                    'attributes' => [ 'role' => 'alert' ],
                ],
                'expected' => '<div class="notice" role="alert"><p>A notice with an additional attribute role.</p></div>',
            ],
            'multiple additional attributes'            => [
                'message'  => 'A notice with multiple additional attributes.',
                'args'     => [
                    'attributes' => [
                        'role'      => 'alert',
                        'data-test' => -1,
                    ],
                ],
                'expected' => '<div class="notice" role="alert" data-test="-1"><p>A notice with multiple additional attributes.</p></div>',
            ],
            'data attribute with unsafe value'          => [
                'message'  => 'A notice with an additional attribute with an unsafe value.',
                'args'     => [
                    'attributes' => [ 'data-unsafe' => '<script>alert( "Howdy, admin!" );</script>' ],
                ],
                'expected' => '<div class="notice" data-unsafe="&lt;script&gt;alert( &quot;Howdy, admin!&quot; );&lt;/script&gt;"><p>A notice with an additional attribute with an unsafe value.</p></div>',
            ],
            'additional invalid attribute'              => [
                'message'  => 'A notice with an additional attribute that is invalid.',
                'args'     => [
                    'attributes' => [ 'not-valid' => 'not-valid' ],
                ],
                'expected' => '<div class="notice"><p>A notice with an additional attribute that is invalid.</p></div>',
            ],
            'multiple attributes with "role", invalid, data-*, numeric, and boolean' => [
                'message'  => 'A notice with multiple attributes with "role", invalid, "data-*", numeric, and boolean.',
                'args'     => [
                    'attributes' => [
                        'role'      => 'alert',
                        'disabled'  => 'disabled',
                        'data-name' => 'my-name',
                        'data-id'   => 1,
                        'hidden',
                    ],
                ],
                'expected' => '<div class="notice" role="alert" data-name="my-name" data-id="1" hidden><p>A notice with multiple attributes with "role", invalid, "data-*", numeric, and boolean.</p></div>',
            ],
            'paragraph wrapping as a falsy value rather than (bool) false' => [
                'message'  => 'A notice with paragraph wrapping as a falsy value rather than (bool) false.',
                'args'     => [
                    'paragraph_wrap' => 0,
                ],
                'expected' => '<div class="notice"><p>A notice with paragraph wrapping as a falsy value rather than (bool) false.</p></div>',
            ],
        ];
    }

    /**
     * Tests that `_doing_it_wrong()` is thrown when a 'type' containing spaces is passed.
     *
     * @ticket 57791
     *
     * @expectedIncorrectUsage wp_get_admin_notice
     */
    public function test_should_throw_doing_it_wrong_with_a_type_containing_spaces()
    {
        ob_start();
        wp_admin_notice(
            'A type containing spaces.',
            [ 'type' => 'first second third fourth' ]
        );
        $actual = ob_get_clean();

        $this->assertSame(
            '<div class="notice notice-first second third fourth"><p>A type containing spaces.</p></div>',
            $actual
        );
    }

    /**
     * Tests that `wp_admin_notice()` fires the 'wp_admin_notice' action.
     *
     * @ticket 57791
     */
    public function test_should_fire_wp_admin_notice_action()
    {
        $action = new MockAction();
        add_action('wp_admin_notice', [ $action, 'action' ]);

        ob_start();
        wp_admin_notice('A notice.', [ 'type' => 'success' ]);
        ob_end_clean();

        $this->assertSame(1, $action->get_call_count());
    }
}
