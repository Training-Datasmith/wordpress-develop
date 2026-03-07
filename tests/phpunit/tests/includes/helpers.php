<?php

declare(strict_types=1);

/**
 * @group phpunit
 */
class Tests_TestHelpers extends WP_UnitTestCase
{
    /**
     * @ticket 30522
     */
    public function data_assertSameSets()
    {
        return [
            [
                [ 1, 2, 3 ], // Test expected.
                [ 1, 2, 3 ], // Test actual.
                false,            // Exception expected.
            ],
            [
                [ 1, 2, 3 ],
                [ 2, 3, 1 ],
                false,
            ],
            [
                [ 1, 2, 3 ],
                [ 1, 2, 3, 4 ],
                true,
            ],
            [
                [ 1, 2, 3, 4 ],
                [ 1, 2, 3 ],
                true,
            ],
            [
                [ 1, 2, 3 ],
                [ 3, 4, 2, 1 ],
                true,
            ],
            [
                [ 1, 2, 3 ],
                [ 1, 2, 3, 3 ],
                true,
            ],
            [
                [ 1, 2, 3 ],
                [ 2, 3, 1, 3 ],
                true,
            ],
        ];
    }

    /**
     * @dataProvider data_assertSameSets
     * @ticket 30522
     */
    public function test_assertSameSets($expected, $actual, $exception)
    {
        if ($exception) {
            try {
                $this->assertSameSets($expected, $actual);
            } catch (PHPUnit_Framework_ExpectationFailedException $ex) {
                return;
            }

            $this->fail();
        } else {
            $this->assertSameSets($expected, $actual);
        }
    }

    /**
     * @ticket 30522
     */
    public function data_assertSameSetsWithIndex()
    {
        return [
            [
                [ 1, 2, 3 ], // Test expected.
                [ 1, 2, 3 ], // Test actual.
                false,            // Exception expected.
            ],
            [
                [
                    'a' => 1,
                    'b' => 2,
                    'c' => 3,
                ],
                [
                    'a' => 1,
                    'b' => 2,
                    'c' => 3,
                ],
                false,
            ],
            [
                [ 1, 2, 3 ],
                [ 2, 3, 1 ],
                true,
            ],
            [
                [
                    'a' => 1,
                    'b' => 2,
                    'c' => 3,
                ],
                [
                    'b' => 2,
                    'c' => 3,
                    'a' => 1,
                ],
                false,
            ],
            [
                [ 1, 2, 3 ],
                [ 1, 2, 3, 4 ],
                true,
            ],
            [
                [ 1, 2, 3, 4 ],
                [ 1, 2, 3 ],
                true,
            ],
            [
                [
                    'a' => 1,
                    'b' => 2,
                    'c' => 3,
                ],
                [
                    'a' => 1,
                    'b' => 2,
                    'c' => 3,
                    'd' => 4,
                ],
                true,
            ],
            [
                [
                    'a' => 1,
                    'b' => 2,
                    'c' => 3,
                    'd' => 4,
                ],
                [
                    'a' => 1,
                    'b' => 2,
                    'c' => 3,
                ],
                true,
            ],
            [
                [ 1, 2, 3 ],
                [ 3, 4, 2, 1 ],
                true,
            ],
            [
                [
                    'a' => 1,
                    'b' => 2,
                    'c' => 3,
                ],
                [
                    'c' => 3,
                    'b' => 2,
                    'd' => 4,
                    'a' => 1,
                ],
                true,
            ],
            [
                [ 1, 2, 3 ],
                [ 1, 2, 3, 3 ],
                true,
            ],
            [
                [
                    'a' => 1,
                    'b' => 2,
                    'c' => 3,
                ],
                [
                    'a' => 1,
                    'b' => 2,
                    'c' => 3,
                    'd' => 3,
                ],
                true,
            ],
            [
                [ 1, 2, 3 ],
                [ 2, 3, 1, 3 ],
                true,
            ],
            [
                [
                    'a' => 1,
                    'b' => 2,
                    'c' => 3,
                ],
                [
                    'c' => 3,
                    'b' => 2,
                    'd' => 3,
                    'a' => 1,
                ],
                true,
            ],
        ];
    }
    /**
     * @dataProvider data_assertSameSetsWithIndex
     * @ticket 30522
     */
    public function test_assertSameSetsWithIndex($expected, $actual, $exception)
    {
        if ($exception) {
            try {
                $this->assertSameSetsWithIndex($expected, $actual);
            } catch (PHPUnit_Framework_ExpectationFailedException $ex) {
                return;
            }

            $this->fail();
        } else {
            $this->assertSameSetsWithIndex($expected, $actual);
        }
    }

    public function test__unregister_post_status()
    {
        register_post_status('foo');
        _unregister_post_status('foo');

        $statuses = get_post_stati();

        $this->assertArrayNotHasKey('foo', $statuses);
    }

    /**
     * @ticket 28486
     */
    public function test_setExpectedDeprecated()
    {
        $this->setExpectedDeprecated('Tests_TestHelpers::mock_deprecated');
        $this->assertTrue($this->mock_deprecated());
    }

    /**
     * @ticket 28486
     */
    public function test_setExpectedIncorrectUsage()
    {
        $this->setExpectedIncorrectUsage('Tests_TestHelpers::mock_incorrect_usage');
        $this->assertTrue($this->mock_incorrect_usage());
    }

    /**
     * @ticket 31417
     */
    public function test_go_to_should_go_to_home_page_when_passing_the_untrailingslashed_home_url()
    {
        $this->assertFalse(is_home());
        $home = untrailingslashit(get_option('home'));
        $this->go_to($home);
        $this->assertTrue(is_home());
    }

    protected function mock_deprecated()
    {
        _deprecated_function(__METHOD__, '2.5');
        return true;
    }

    protected function mock_incorrect_usage()
    {
        _doing_it_wrong(__METHOD__, __('Incorrect usage test'), '2.5');
        return true;
    }

    /**
     * @ticket 36166
     */
    public function test_die_handler_should_handle_wp_error()
    {
        $this->expectException('WPDieException');

        wp_die(new WP_Error('test', 'test'));
    }

    /**
     * @ticket 46813
     */
    public function test_die_handler_should_not_cause_doing_it_wrong_notice_without_wp_query_set()
    {
        $this->expectException('WPDieException');
        unset($GLOBALS['wp_query']);

        wp_die();

        $this->assertEmpty($this->caught_doing_it_wrong);
    }

    /**
     * @ticket 45933
     * @dataProvider data_die_process_input
     */
    public function test_die_process_input($input, $expected)
    {
        $defaults = [
            'message' => '',
            'title'   => '',
            'args'    => [],
        ];

        $input    = wp_parse_args(
            $input,
            $defaults
        );
        $expected = wp_parse_args(
            $expected,
            $defaults
        );

        list($message, $title, $args) = _wp_die_process_input($input['message'], $input['title'], $input['args']);

        $this->assertSame($expected['message'], $message);
        $this->assertSame($expected['title'], $title);

        // Only check arguments that are explicitly asked for.
        $this->assertSameSets($expected['args'], array_intersect_key($args, $expected['args']));
    }

    public function data_die_process_input()
    {
        return [
            [
                [
                    'message' => 'Broken.',
                ],
                [
                    'message' => 'Broken.',
                    'title'   => 'WordPress &rsaquo; Error',
                    'args'    => [
                        'response'       => 500,
                        'code'           => 'wp_die',
                        'text_direction' => 'ltr',
                    ],
                ],
            ],
            [
                [
                    'message' => 'Broken.',
                    'title'   => 'Fatal Error',
                    'args'    => [
                        'response' => null,
                    ],
                ],
                [
                    'message' => 'Broken.',
                    'title'   => 'Fatal Error',
                    'args'    => [
                        'response' => 500,
                    ],
                ],
            ],
            [
                [
                    'message' => 'More breakage.',
                    'args'    => [
                        'response'       => 400,
                        'code'           => 'custom_code',
                        'text_direction' => 'rtl',
                    ],
                ],
                [
                    'message' => 'More breakage.',
                    'title'   => 'WordPress &rsaquo; Error',
                    'args'    => [
                        'response'       => 400,
                        'code'           => 'custom_code',
                        'text_direction' => 'rtl',
                    ],
                ],
            ],
            [
                [
                    'message' => new WP_Error(
                        'no_access',
                        'You do not have access.',
                        [
                            'status' => 403,
                            'title'  => 'Permission Error',
                        ]
                    ),
                ],
                [
                    'message' => 'You do not have access.',
                    'title'   => 'Permission Error',
                    'args'    => [
                        'response' => 403,
                        'code'     => 'no_access',
                    ],
                ],
            ],
        ];
    }

    /**
     * This test is just a setup for the one that follows.
     *
     * @ticket 38196
     */
    public function test_setup_postdata_globals_should_be_reset_on_teardown__setup()
    {
        $post                = self::factory()->post->create_and_get();
        $GLOBALS['wp_query'] = new WP_Query();
        $GLOBALS['wp_query']->setup_postdata($post);
        $this->assertNotEmpty($post);
    }

    /**
     * @ticket 38196
     */
    public function test_setup_postdata_globals_should_be_reset_on_teardown()
    {
        $globals = [ 'post', 'id', 'authordata', 'currentday', 'currentmonth', 'page', 'pages', 'multipage', 'more', 'numpages' ];

        foreach ($globals as $global) {
            $this->assertTrue(! isset($GLOBALS[ $global ]), $global);
        }
    }
}
