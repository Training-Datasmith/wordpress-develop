<?php

declare(strict_types=1);
/**
 * REST API functions.
 *
 * @package WordPress
 * @subpackage REST API
 */

require_once ABSPATH . 'wp-admin/includes/admin.php';
require_once ABSPATH . WPINC . '/rest-api.php';
require_once __DIR__ . '/../includes/class-jsonserializable-object.php';

/**
 * @group restapi
 */
class Tests_REST_API extends WP_UnitTestCase
{
    public function set_up()
    {
        parent::set_up();

        // Override the normal server with our spying server.
        $GLOBALS['wp_rest_server'] = new Spy_REST_Server();
        do_action('rest_api_init', $GLOBALS['wp_rest_server']);
    }

    public function tear_down()
    {
        remove_filter('wp_rest_server_class', [ $this, 'filter_wp_rest_server_class' ]);
        parent::tear_down();
    }

    public function filter_wp_rest_server_class($class_name)
    {
        return 'Spy_REST_Server';
    }

    public function test_rest_get_server_fails_with_undefined_method()
    {
        $this->expectException(Error::class);
        rest_get_server()->does_not_exist();
    }

    /**
     * Checks that the main classes are loaded.
     */
    public function test_rest_api_active()
    {
        $this->assertTrue(class_exists('WP_REST_Server'));
        $this->assertTrue(class_exists('WP_REST_Request'));
        $this->assertTrue(class_exists('WP_REST_Response'));
        $this->assertTrue(class_exists('WP_REST_Posts_Controller'));
    }

    /**
     * The rest_api_init hook should have been registered with init, and should
     * have a default priority of 10.
     */
    public function test_init_action_added()
    {
        $this->assertSame(10, has_action('init', 'rest_api_init'));
    }

    public function test_add_extra_api_taxonomy_arguments()
    {
        $taxonomy = get_taxonomy('category');
        $this->assertTrue($taxonomy->show_in_rest);
        $this->assertSame('categories', $taxonomy->rest_base);
        $this->assertSame('WP_REST_Terms_Controller', $taxonomy->rest_controller_class);

        $taxonomy = get_taxonomy('post_tag');
        $this->assertTrue($taxonomy->show_in_rest);
        $this->assertSame('tags', $taxonomy->rest_base);
        $this->assertSame('WP_REST_Terms_Controller', $taxonomy->rest_controller_class);
    }

    public function test_add_extra_api_post_type_arguments()
    {
        $post_type = get_post_type_object('post');
        $this->assertTrue($post_type->show_in_rest);
        $this->assertSame('posts', $post_type->rest_base);
        $this->assertSame('WP_REST_Posts_Controller', $post_type->rest_controller_class);

        $post_type = get_post_type_object('page');
        $this->assertTrue($post_type->show_in_rest);
        $this->assertSame('pages', $post_type->rest_base);
        $this->assertSame('WP_REST_Posts_Controller', $post_type->rest_controller_class);

        $post_type = get_post_type_object('attachment');
        $this->assertTrue($post_type->show_in_rest);
        $this->assertSame('media', $post_type->rest_base);
        $this->assertSame('WP_REST_Attachments_Controller', $post_type->rest_controller_class);
    }

    /**
     * Check that a single route is canonicalized.
     *
     * Ensures that single and multiple routes are handled correctly.
     */
    public function test_route_canonicalized()
    {
        register_rest_route(
            'test-ns',
            '/test',
            [
                'methods'             => [ 'GET' ],
                'callback'            => '__return_null',
                'permission_callback' => '__return_true',
            ]
        );

        // Check the route was registered correctly.
        $endpoints = $GLOBALS['wp_rest_server']->get_raw_endpoint_data();
        $this->assertArrayHasKey('/test-ns/test', $endpoints);

        // Check the route was wrapped in an array.
        $endpoint = $endpoints['/test-ns/test'];
        $this->assertArrayNotHasKey('callback', $endpoint);
        $this->assertArrayHasKey('namespace', $endpoint);
        $this->assertSame('test-ns', $endpoint['namespace']);

        // Grab the filtered data.
        $filtered_endpoints = $GLOBALS['wp_rest_server']->get_routes();
        $this->assertArrayHasKey('/test-ns/test', $filtered_endpoints);
        $endpoint = $filtered_endpoints['/test-ns/test'];
        $this->assertCount(1, $endpoint);
        $this->assertArrayHasKey('callback', $endpoint[0]);
        $this->assertArrayHasKey('methods', $endpoint[0]);
        $this->assertArrayHasKey('args', $endpoint[0]);
    }

    /**
     * Check that a single route is canonicalized.
     *
     * Ensures that single and multiple routes are handled correctly.
     */
    public function test_route_canonicalized_multiple()
    {
        register_rest_route(
            'test-ns',
            '/test',
            [
                [
                    'methods'             => [ 'GET' ],
                    'callback'            => '__return_null',
                    'permission_callback' => '__return_true',
                ],
                [
                    'methods'             => [ 'POST' ],
                    'callback'            => '__return_null',
                    'permission_callback' => '__return_true',
                ],
            ]
        );

        // Check the route was registered correctly.
        $endpoints = $GLOBALS['wp_rest_server']->get_raw_endpoint_data();
        $this->assertArrayHasKey('/test-ns/test', $endpoints);

        // Check the route was wrapped in an array.
        $endpoint = $endpoints['/test-ns/test'];
        $this->assertArrayNotHasKey('callback', $endpoint);
        $this->assertArrayHasKey('namespace', $endpoint);
        $this->assertSame('test-ns', $endpoint['namespace']);

        $filtered_endpoints = $GLOBALS['wp_rest_server']->get_routes();
        $endpoint           = $filtered_endpoints['/test-ns/test'];
        $this->assertCount(2, $endpoint);

        // Check for both methods.
        foreach ([ 0, 1 ] as $key) {
            $this->assertArrayHasKey('callback', $endpoint[ $key ]);
            $this->assertArrayHasKey('methods', $endpoint[ $key ]);
            $this->assertArrayHasKey('args', $endpoint[ $key ]);
        }
    }

    /**
     * Check that routes are merged by default.
     */
    public function test_route_merge()
    {
        register_rest_route(
            'test-ns',
            '/test',
            [
                'methods'             => [ 'GET' ],
                'callback'            => '__return_null',
                'permission_callback' => '__return_true',
            ]
        );
        register_rest_route(
            'test-ns',
            '/test',
            [
                'methods'             => [ 'POST' ],
                'callback'            => '__return_null',
                'permission_callback' => '__return_true',
            ]
        );

        // Check both routes exist.
        $endpoints = $GLOBALS['wp_rest_server']->get_routes();
        $endpoint  = $endpoints['/test-ns/test'];
        $this->assertCount(2, $endpoint);
    }

    /**
     * Check that we can override routes.
     */
    public function test_route_override()
    {
        register_rest_route(
            'test-ns',
            '/test',
            [
                'methods'             => [ 'GET' ],
                'callback'            => '__return_null',
                'permission_callback' => '__return_true',
                'should_exist'        => false,
            ]
        );
        register_rest_route(
            'test-ns',
            '/test',
            [
                'methods'             => [ 'POST' ],
                'callback'            => '__return_null',
                'permission_callback' => '__return_true',
                'should_exist'        => true,
            ],
            true
        );

        // Check we only have one route.
        $endpoints = $GLOBALS['wp_rest_server']->get_routes();
        $endpoint  = $endpoints['/test-ns/test'];
        $this->assertCount(1, $endpoint);

        // Check it's the right one.
        $this->assertArrayHasKey('should_exist', $endpoint[0]);
        $this->assertTrue($endpoint[0]['should_exist']);
    }

    /**
     * Test that we reject routes without namespaces
     *
     * @expectedIncorrectUsage register_rest_route
     */
    public function test_route_reject_empty_namespace()
    {
        register_rest_route(
            '',
            '/test-empty-namespace',
            [
                'methods'             => [ 'POST' ],
                'callback'            => '__return_null',
                'permission_callback' => '__return_true',
            ],
            true
        );
        $endpoints = $GLOBALS['wp_rest_server']->get_routes();
        $this->assertArrayNotHasKey('/test-empty-namespace', $endpoints);
    }

    /**
     * Test that we reject empty routes
     *
     * @expectedIncorrectUsage register_rest_route
     */
    public function test_route_reject_empty_route()
    {
        register_rest_route(
            '/test-empty-route',
            '',
            [
                'methods'             => [ 'POST' ],
                'callback'            => '__return_null',
                'permission_callback' => '__return_true',
            ],
            true
        );
        $endpoints = $GLOBALS['wp_rest_server']->get_routes();
        $this->assertArrayNotHasKey('/test-empty-route', $endpoints);
    }

    /**
     * The rest_route query variable should be registered.
     */
    public function test_rest_route_query_var()
    {
        rest_api_init();
        $this->assertContains('rest_route', $GLOBALS['wp']->public_query_vars);
    }

    public function test_route_method()
    {
        register_rest_route(
            'test-ns',
            '/test',
            [
                'methods'             => [ 'GET' ],
                'callback'            => '__return_null',
                'permission_callback' => '__return_true',
            ]
        );

        $routes = $GLOBALS['wp_rest_server']->get_routes();

        $this->assertSame($routes['/test-ns/test'][0]['methods'], [ 'GET' => true ]);
    }

    /**
     * The 'methods' arg should accept a single value as well as array.
     */
    public function test_route_method_string()
    {
        register_rest_route(
            'test-ns',
            '/test',
            [
                'methods'             => 'GET',
                'callback'            => '__return_null',
                'permission_callback' => '__return_true',
            ]
        );

        $routes = $GLOBALS['wp_rest_server']->get_routes();

        $this->assertSame($routes['/test-ns/test'][0]['methods'], [ 'GET' => true ]);
    }

    /**
     * The 'methods' arg should accept a single value as well as array.
     */
    public function test_route_method_array()
    {
        register_rest_route(
            'test-ns',
            '/test',
            [
                'methods'             => [ 'GET', 'POST' ],
                'callback'            => '__return_null',
                'permission_callback' => '__return_true',
            ]
        );

        $routes = $GLOBALS['wp_rest_server']->get_routes();

        $this->assertSame(
            $routes['/test-ns/test'][0]['methods'],
            [
                'GET'  => true,
                'POST' => true,
            ]
        );
    }

    /**
     * The 'methods' arg should a comma-separated string.
     */
    public function test_route_method_comma_separated()
    {
        register_rest_route(
            'test-ns',
            '/test',
            [
                'methods'             => 'GET,POST',
                'callback'            => '__return_null',
                'permission_callback' => '__return_true',
            ]
        );

        $routes = $GLOBALS['wp_rest_server']->get_routes();

        $this->assertSame(
            $routes['/test-ns/test'][0]['methods'],
            [
                'GET'  => true,
                'POST' => true,
            ]
        );
    }

    public function test_options_request()
    {
        register_rest_route(
            'test-ns',
            '/test',
            [
                'methods'             => 'GET,POST',
                'callback'            => '__return_null',
                'permission_callback' => '__return_true',
            ]
        );

        $request  = new WP_REST_Request('OPTIONS', '/test-ns/test');
        $response = rest_handle_options_request(null, $GLOBALS['wp_rest_server'], $request);
        $response = rest_send_allow_header($response, $GLOBALS['wp_rest_server'], $request);
        $headers  = $response->get_headers();
        $this->assertArrayHasKey('Allow', $headers);

        $this->assertSame('GET, POST', $headers['Allow']);
    }

    /**
     * Ensure that the OPTIONS handler doesn't kick in for non-OPTIONS requests.
     */
    public function test_options_request_not_options()
    {
        register_rest_route(
            'test-ns',
            '/test',
            [
                'methods'             => 'GET,POST',
                'callback'            => '__return_true',
                'permission_callback' => '__return_true',
            ]
        );

        $request  = new WP_REST_Request('GET', '/test-ns/test');
        $response = rest_handle_options_request(null, $GLOBALS['wp_rest_server'], $request);

        $this->assertNull($response);
    }

    /**
     * Ensure that result fields are not allowed if no request['_fields'] is present.
     */
    public function test_rest_filter_response_fields_no_request_filter()
    {
        $response = new WP_REST_Response();
        $response->set_data([ 'a' => true ]);
        $request = [];

        $response = rest_filter_response_fields($response, null, $request);
        $this->assertSame([ 'a' => true ], $response->get_data());
    }

    /**
     * Ensure that result fields are allowed if request['_fields'] is present.
     */
    public function test_rest_filter_response_fields_single_field_filter()
    {
        $response = new WP_REST_Response();
        $response->set_data(
            [
                'a' => 0,
                'b' => 1,
                'c' => 2,
            ]
        );
        $request = [
            '_fields' => 'b',
        ];

        $response = rest_filter_response_fields($response, null, $request);
        $this->assertSame([ 'b' => 1 ], $response->get_data());
    }

    /**
     * Ensure that multiple comma-separated fields may be allowed with request['_fields'].
     */
    public function test_rest_filter_response_fields_multi_field_filter()
    {
        $response = new WP_REST_Response();
        $response->set_data(
            [
                'a' => 0,
                'b' => 1,
                'c' => 2,
                'd' => 3,
                'e' => 4,
                'f' => 5,
            ]
        );
        $request = [
            '_fields' => 'b,c,e',
        ];

        $response = rest_filter_response_fields($response, null, $request);
        $this->assertSame(
            [
                'b' => 1,
                'c' => 2,
                'e' => 4,
            ],
            $response->get_data()
        );
    }

    /**
     * Ensure that multiple comma-separated fields may be allowed
     * with request['_fields'] using query parameter array syntax.
     */
    public function test_rest_filter_response_fields_multi_field_filter_array()
    {
        $response = new WP_REST_Response();

        $response->set_data(
            [
                'a' => 0,
                'b' => 1,
                'c' => 2,
                'd' => 3,
                'e' => 4,
                'f' => 5,
            ]
        );
        $request = [
            '_fields' => [ 'b', 'c', 'e' ],
        ];

        $response = rest_filter_response_fields($response, null, $request);
        $this->assertSame(
            [
                'b' => 1,
                'c' => 2,
                'e' => 4,
            ],
            $response->get_data()
        );
    }

    /**
     * Ensure that request['_fields'] allowed list apply to items in response collections.
     */
    public function test_rest_filter_response_fields_numeric_array()
    {
        $response = new WP_REST_Response();
        $response->set_data(
            [
                [
                    'a' => 0,
                    'b' => 1,
                    'c' => 2,
                ],
                [
                    'a' => 3,
                    'b' => 4,
                    'c' => 5,
                ],
                [
                    'a' => 6,
                    'b' => 7,
                    'c' => 8,
                ],
            ]
        );
        $request = [
            '_fields' => 'b,c',
        ];

        $response = rest_filter_response_fields($response, null, $request);
        $this->assertSame(
            [
                [
                    'b' => 1,
                    'c' => 2,
                ],
                [
                    'b' => 4,
                    'c' => 5,
                ],
                [
                    'b' => 7,
                    'c' => 8,
                ],
            ],
            $response->get_data()
        );
    }

    /**
     * Ensure that nested fields may be allowed with request['_fields'].
     *
     * @ticket 42094
     */
    public function test_rest_filter_response_fields_nested_field_filter()
    {
        $response = new WP_REST_Response();

        $response->set_data(
            [
                'a' => 0,
                'b' => [
                    '1' => 1,
                    '2' => 2,
                ],
                'c' => 3,
                'd' => [
                    '4' => 4,
                    '5' => 5,
                ],
            ]
        );
        $request = [
            '_fields' => 'b.1,c,d.5',
        ];

        $response = rest_filter_response_fields($response, null, $request);
        $this->assertSame(
            [
                'b' => [
                    '1' => 1,
                ],
                'c' => 3,
                'd' => [
                    '5' => 5,
                ],
            ],
            $response->get_data()
        );
    }

    /**
     * Ensure inclusion of deeply nested fields may be controlled with request['_fields'].
     *
     * @ticket 49648
     */
    public function test_rest_filter_response_fields_deeply_nested_field_filter()
    {
        $response = new WP_REST_Response();

        $response->set_data(
            [
                'field' => [
                    'a' => [
                        'i'  => 'value i',
                        'ii' => 'value ii',
                    ],
                    'b' => [
                        'iii' => 'value iii',
                        'iv'  => 'value iv',
                    ],
                ],
            ]
        );
        $request = [
            '_fields' => 'field.a.i,field.b.iv',
        ];

        $response = rest_filter_response_fields($response, null, $request);
        $this->assertSame(
            [
                'field' => [
                    'a' => [
                        'i' => 'value i',
                    ],
                    'b' => [
                        'iv' => 'value iv',
                    ],
                ],
            ],
            $response->get_data()
        );
    }

    /**
     * Ensure that specifying a single top-level key in _fields includes that field and all children.
     *
     * @ticket 48266
     */
    public function test_rest_filter_response_fields_top_level_key()
    {
        $response = new WP_REST_Response();

        $response->set_data(
            [
                'meta' => [
                    'key1' => 1,
                    'key2' => 2,
                ],
            ]
        );
        $request = [
            '_fields' => 'meta',
        ];

        $response = rest_filter_response_fields($response, null, $request);
        $this->assertSame(
            [
                'meta' => [
                    'key1' => 1,
                    'key2' => 2,
                ],
            ],
            $response->get_data()
        );
    }

    /**
     * Ensure that a top-level key in _fields supersedes any specified children of that field.
     *
     * @ticket 48266
     */
    public function test_rest_filter_response_fields_child_after_parent()
    {
        $response = new WP_REST_Response();

        $response->set_data(
            [
                'meta' => [
                    'key1' => 1,
                    'key2' => 2,
                ],
            ]
        );
        $request = [
            '_fields' => 'meta,meta.key1',
        ];

        $response = rest_filter_response_fields($response, null, $request);
        $this->assertSame(
            [
                'meta' => [
                    'key1' => 1,
                    'key2' => 2,
                ],
            ],
            $response->get_data()
        );
    }

    /**
     * Ensure that specifying two sibling properties in _fields causes both to be included.
     *
     * @ticket 48266
     */
    public function test_rest_filter_response_fields_include_all_specified_siblings()
    {
        $response = new WP_REST_Response();

        $response->set_data(
            [
                'meta' => [
                    'key1' => 1,
                    'key2' => 2,
                ],
            ]
        );
        $request = [
            '_fields' => 'meta.key1,meta.key2',
        ];

        $response = rest_filter_response_fields($response, null, $request);
        $this->assertSame(
            [
                'meta' => [
                    'key1' => 1,
                    'key2' => 2,
                ],
            ],
            $response->get_data()
        );
    }

    /**
     * @ticket 42094
     */
    public function test_rest_is_field_included()
    {
        $fields = [
            'id',
            'title',
            'content.raw',
            'custom.property',
        ];

        $this->assertTrue(rest_is_field_included('id', $fields));
        $this->assertTrue(rest_is_field_included('title', $fields));
        $this->assertTrue(rest_is_field_included('title.raw', $fields));
        $this->assertTrue(rest_is_field_included('title.rendered', $fields));
        $this->assertTrue(rest_is_field_included('content', $fields));
        $this->assertTrue(rest_is_field_included('content.raw', $fields));
        $this->assertTrue(rest_is_field_included('custom.property', $fields));
        $this->assertFalse(rest_is_field_included('content.rendered', $fields));
        $this->assertFalse(rest_is_field_included('type', $fields));
        $this->assertFalse(rest_is_field_included('meta', $fields));
        $this->assertFalse(rest_is_field_included('meta.value', $fields));
    }

    /**
     * The get_rest_url function should return a URL consistently terminated with a "/",
     * whether the blog is configured with pretty permalink support or not.
     */
    public function test_rest_url_generation()
    {
        // In pretty permalinks case, we expect a path of wp-json/ with no query.
        $this->set_permalink_structure('/%year%/%monthnum%/%day%/%postname%/');
        $this->assertSame('http://' . WP_TESTS_DOMAIN . '/wp-json/', get_rest_url());

        // In index permalinks case, we expect a path of index.php/wp-json/ with no query.
        $this->set_permalink_structure('/index.php/%year%/%monthnum%/%day%/%postname%/');
        $this->assertSame('http://' . WP_TESTS_DOMAIN . '/index.php/wp-json/', get_rest_url());

        // In non-pretty case, we get a query string to invoke the rest router.
        $this->set_permalink_structure('');
        $this->assertSame('http://' . WP_TESTS_DOMAIN . '/index.php?rest_route=/', get_rest_url());
    }

    /**
     * @ticket 34299
     */
    public function test_rest_url_scheme()
    {
        $_SERVER['SERVER_NAME'] = parse_url(home_url(), PHP_URL_HOST);
        $_siteurl               = get_option('siteurl');

        set_current_screen('edit.php');
        $this->assertTrue(is_admin());

        // Test an HTTP URL.
        unset($_SERVER['HTTPS']);
        $url = get_rest_url();
        $this->assertSame('http', parse_url($url, PHP_URL_SCHEME));

        // Test an HTTPS URL.
        $_SERVER['HTTPS'] = 'on';
        $url              = get_rest_url();
        $this->assertSame('https', parse_url($url, PHP_URL_SCHEME));

        // Switch to an admin request on a different domain name.
        $_SERVER['SERVER_NAME'] = 'admin.example.org';
        update_option('siteurl', 'http://admin.example.org');
        $this->assertNotEquals($_SERVER['SERVER_NAME'], parse_url(home_url(), PHP_URL_HOST));

        // Test an HTTP URL.
        unset($_SERVER['HTTPS']);
        $url = get_rest_url();
        $this->assertSame('http', parse_url($url, PHP_URL_SCHEME));

        // Test an HTTPS URL.
        $_SERVER['HTTPS'] = 'on';
        $url              = get_rest_url();
        $this->assertSame('https', parse_url($url, PHP_URL_SCHEME));

        // Reset.
        update_option('siteurl', $_siteurl);
    }

    /**
     * @ticket 42452
     */
    public function test_always_prepend_path_with_slash_in_rest_url_filter()
    {
        $filter = new MockAction();
        add_filter('rest_url', [ $filter, 'filter' ], 10, 2);

        // Passing no path should return a slash.
        get_rest_url();
        $args = $filter->get_args();
        $this->assertSame('/', $args[0][1]);
        $filter->reset();

        // Paths without a prepended slash should have one added.
        get_rest_url(null, 'wp/media/');
        $args = $filter->get_args();
        $this->assertSame('/wp/media/', $args[0][1]);
        $filter->reset();

        // Do not modify paths with a prepended slash.
        get_rest_url(null, '/wp/media/');
        $args = $filter->get_args();
        $this->assertSame('/wp/media/', $args[0][1]);

        unset($filter);
    }

    /**
     * @dataProvider data_jsonp_callback_check
     */
    public function test_jsonp_callback_check($callback, $expected)
    {
        $this->assertSame($expected, wp_check_jsonp_callback($callback));
    }

    public function data_jsonp_callback_check()
    {
        return [
            // Standard names.
            [ 'Springfield', true ],
            [ 'shelby.ville', true ],
            [ 'cypress_creek', true ],
            [ 'KampKrusty1', true ],

            // Invalid names.
            [ 'ogden-ville', false ],
            [ 'north haverbrook', false ],
            [ "Terror['Lake']", false ],
            [ 'Cape[Feare]', false ],
            [ '"NewHorrorfield"', false ],
            [ 'Scream\\ville', false ],
        ];
    }

    /**
     * @dataProvider data_rest_parse_date
     */
    public function test_rest_parse_date($date, $expected)
    {
        $this->assertEquals($expected, rest_parse_date($date));
    }

    public function data_rest_parse_date()
    {
        return [
            // Valid dates with timezones.
            [ '2017-01-16T11:30:00-05:00', gmmktime(11, 30, 0, 1, 16, 2017) + 5 * HOUR_IN_SECONDS ],
            [ '2017-01-16T11:30:00-05:30', gmmktime(11, 30, 0, 1, 16, 2017) + 5.5 * HOUR_IN_SECONDS ],
            [ '2017-01-16T11:30:00-05', gmmktime(11, 30, 0, 1, 16, 2017) + 5 * HOUR_IN_SECONDS ],
            [ '2017-01-16T11:30:00+05', gmmktime(11, 30, 0, 1, 16, 2017) - 5 * HOUR_IN_SECONDS ],
            [ '2017-01-16T11:30:00-00', gmmktime(11, 30, 0, 1, 16, 2017) ],
            [ '2017-01-16T11:30:00+00', gmmktime(11, 30, 0, 1, 16, 2017) ],
            [ '2017-01-16T11:30:00Z', gmmktime(11, 30, 0, 1, 16, 2017) ],

            // Valid dates without timezones.
            [ '2017-01-16T11:30:00', gmmktime(11, 30, 0, 1, 16, 2017) ],

            // Invalid dates (TODO: support parsing partial dates as ranges, see #38641).
            [ '2017-01-16T11:30:00-5', false ],
            [ '2017-01-16T11:30', false ],
            [ '2017-01-16T11', false ],
            [ '2017-01-16T', false ],
            [ '2017-01-16', false ],
            [ '2017-01', false ],
            [ '2017', false ],
        ];
    }

    /**
     * @dataProvider data_rest_parse_date_force_utc
     */
    public function test_rest_parse_date_force_utc($date, $expected)
    {
        $this->assertSame($expected, rest_parse_date($date, true));
    }

    public function data_rest_parse_date_force_utc()
    {
        return [
            // Valid dates with timezones.
            [ '2017-01-16T11:30:00-05:00', gmmktime(11, 30, 0, 1, 16, 2017) ],
            [ '2017-01-16T11:30:00-05:30', gmmktime(11, 30, 0, 1, 16, 2017) ],
            [ '2017-01-16T11:30:00-05', gmmktime(11, 30, 0, 1, 16, 2017) ],
            [ '2017-01-16T11:30:00+05', gmmktime(11, 30, 0, 1, 16, 2017) ],
            [ '2017-01-16T11:30:00-00', gmmktime(11, 30, 0, 1, 16, 2017) ],
            [ '2017-01-16T11:30:00+00', gmmktime(11, 30, 0, 1, 16, 2017) ],
            [ '2017-01-16T11:30:00Z', gmmktime(11, 30, 0, 1, 16, 2017) ],

            // Valid dates without timezones.
            [ '2017-01-16T11:30:00', gmmktime(11, 30, 0, 1, 16, 2017) ],

            // Invalid dates (TODO: support parsing partial dates as ranges, see #38641).
            [ '2017-01-16T11:30:00-5', false ],
            [ '2017-01-16T11:30', false ],
            [ '2017-01-16T11', false ],
            [ '2017-01-16T', false ],
            [ '2017-01-16', false ],
            [ '2017-01', false ],
            [ '2017', false ],
        ];
    }

    public function test_register_rest_route_without_server()
    {
        $GLOBALS['wp_rest_server'] = null;
        add_filter('wp_rest_server_class', [ $this, 'filter_wp_rest_server_class' ]);

        register_rest_route(
            'test-ns',
            '/test',
            [
                'methods'             => [ 'GET' ],
                'callback'            => '__return_null',
                'permission_callback' => '__return_true',
            ]
        );

        $routes = $GLOBALS['wp_rest_server']->get_routes();
        $this->assertSame($routes['/test-ns/test'][0]['methods'], [ 'GET' => true ]);
    }

    public function test_rest_preload_api_request_with_method()
    {
        $rest_server               = $GLOBALS['wp_rest_server'];
        $GLOBALS['wp_rest_server'] = null;

        $preload_paths = [
            '/wp/v2/types',
            [ '/wp/v2/media', 'OPTIONS' ],
        ];

        $preload_data = array_reduce(
            $preload_paths,
            'rest_preload_api_request',
            []
        );

        $this->assertSame(array_keys($preload_data), [ '/wp/v2/types', 'OPTIONS' ]);
        $this->assertArrayHasKey('/wp/v2/media', $preload_data['OPTIONS']);

        $GLOBALS['wp_rest_server'] = $rest_server;
    }

    /**
     * @dataProvider data_rest_preload_api_request_removes_trailing_slashes
     *
     * @ticket 51636
     * @ticket 57048
     *
     * @param string       $preload_path          The path to preload.
     * @param array|string $expected_preload_path Expected path after preloading.
     */
    public function test_rest_preload_api_request_removes_trailing_slashes($preload_path, $expected_preload_path)
    {
        $rest_server               = $GLOBALS['wp_rest_server'];
        $GLOBALS['wp_rest_server'] = null;

        $actual_preload_path = rest_preload_api_request([], $preload_path);
        if ('' !== $preload_path) {
            $actual_preload_path = key($actual_preload_path);
        }
        $this->assertSame($expected_preload_path, $actual_preload_path);

        $GLOBALS['wp_rest_server'] = $rest_server;
    }

    /**
     * Data provider.
     *
     * @return array
     */
    public static function data_rest_preload_api_request_removes_trailing_slashes()
    {
        return [
            'no query part'                     => [ '/wp/v2/types//', '/wp/v2/types' ],
            'no query part, more slashes'       => [ '/wp/v2/media///', '/wp/v2/media' ],
            'only slashes'                      => [ '////', '/' ],
            'empty path'                        => [ '', [] ],
            'no query parameters'               => [ '/wp/v2/types//?////', '/wp/v2/types?' ],
            'no query parameters, with slashes' => [ '/wp/v2/types//?fields////', '/wp/v2/types?fields' ],
            'query parameters with no values'   => [ '/wp/v2/types//?fields=////', '/wp/v2/types?fields=' ],
            'single query parameter'            => [ '/wp/v2/types//?_fields=foo,bar////', '/wp/v2/types?_fields=foo,bar' ],
            'multiple query parameters'         => [ '/wp/v2/types////?_fields=foo,bar&limit=1000////', '/wp/v2/types?_fields=foo,bar&limit=1000' ],
        ];
    }

    /**
     * @ticket 40614
     */
    public function test_rest_ensure_request_accepts_path_string()
    {
        $request = rest_ensure_request('/wp/v2/posts');
        $this->assertInstanceOf('WP_REST_Request', $request);
        $this->assertSame('/wp/v2/posts', $request->get_route());
        $this->assertSame('GET', $request->get_method());
    }

    /**
     * @dataProvider data_rest_parse_embed_param
     */
    public function test_rest_parse_embed_param($expected, $embed)
    {
        $this->assertSame($expected, rest_parse_embed_param($embed));
    }

    public function data_rest_parse_embed_param()
    {
        return [
            [ true, '' ],
            [ true, null ],
            [ true, '1' ],
            [ true, 'true' ],
            [ true, [] ],
            [ [ 'author' ], 'author' ],
            [ [ 'author', 'replies' ], 'author,replies' ],
            [ [ 'author', 'replies' ], 'author,replies ' ],
            [ [ 'wp:term' ], 'wp:term' ],
            [ [ 'wp:term', 'wp:attachment' ], 'wp:term,wp:attachment' ],
            [ [ 'author' ], [ 'author' ] ],
            [ [ 'author', 'replies' ], [ 'author', 'replies' ] ],
            [ [ 'https://api.w.org/term' ], 'https://api.w.org/term' ],
            [ [ 'https://api.w.org/term', 'https://api.w.org/attachment' ], 'https://api.w.org/term,https://api.w.org/attachment' ],
            [ [ 'https://api.w.org/term', 'https://api.w.org/attachment' ], [ 'https://api.w.org/term', 'https://api.w.org/attachment' ] ],
        ];
    }

    /**
     * @ticket 48819
     *
     * @dataProvider data_rest_filter_response_by_context
     */
    public function test_rest_filter_response_by_context($schema, $data, $expected)
    {
        $this->assertSame($expected, rest_filter_response_by_context($data, $schema, 'view'));
    }

    /**
     * @ticket 49749
     */
    public function test_register_route_with_invalid_namespace()
    {
        $this->setExpectedIncorrectUsage('register_rest_route');

        register_rest_route(
            '/my-namespace/v1/',
            '/my-route',
            [
                'callback'            => '__return_true',
                'permission_callback' => '__return_true',
            ]
        );

        $routes = rest_get_server()->get_routes('my-namespace/v1');
        $this->assertCount(2, $routes);

        $this->assertTrue(rest_do_request('/my-namespace/v1/my-route')->get_data());
    }

    /**
     * @ticket 50075
     */
    public function test_register_route_with_missing_permission_callback_top_level_route()
    {
        $this->setExpectedIncorrectUsage('register_rest_route');

        $registered = register_rest_route(
            'my-ns/v1',
            '/my-route',
            [
                'callback' => '__return_true',
            ]
        );

        $this->assertTrue($registered);
    }

    /**
     * @ticket 50075
     */
    public function test_register_route_with_missing_permission_callback_single_wrapped_route()
    {
        $this->setExpectedIncorrectUsage('register_rest_route');

        $registered = register_rest_route(
            'my-ns/v1',
            '/my-route',
            [
                [
                    'callback' => '__return_true',
                ],
            ]
        );

        $this->assertTrue($registered);
    }

    /**
     * @ticket 50075
     */
    public function test_register_route_with_missing_permission_callback_multiple_wrapped_route()
    {
        $this->setExpectedIncorrectUsage('register_rest_route');

        $registered = register_rest_route(
            'my-ns/v1',
            '/my-route',
            [
                [
                    'callback' => '__return_true',
                ],
                [
                    'callback'            => '__return_true',
                    'permission_callback' => '__return_true',
                ],
            ]
        );

        $this->assertTrue($registered);
    }

    public function data_rest_filter_response_by_context()
    {
        return [
            'default'                                      => [
                [
                    '$schema'    => 'http://json-schema.org/draft-04/schema#',
                    'type'       => 'object',
                    'properties' => [
                        'first'  => [
                            'type'    => 'string',
                            'context' => [ 'view', 'edit' ],
                        ],
                        'second' => [
                            'type'    => 'string',
                            'context' => [ 'edit' ],
                        ],
                    ],
                ],
                [
                    'first'  => 'a',
                    'second' => 'b',
                ],
                [ 'first' => 'a' ],
            ],
            'keeps missing context'                        => [
                [
                    '$schema'    => 'http://json-schema.org/draft-04/schema#',
                    'type'       => 'object',
                    'properties' => [
                        'first'  => [
                            'type'    => 'string',
                            'context' => [ 'view', 'edit' ],
                        ],
                        'second' => [
                            'type' => 'string',
                        ],
                    ],
                ],
                [
                    'first'  => 'a',
                    'second' => 'b',
                ],
                [
                    'first'  => 'a',
                    'second' => 'b',
                ],
            ],
            'removes empty context'                        => [
                [
                    '$schema'    => 'http://json-schema.org/draft-04/schema#',
                    'type'       => 'object',
                    'properties' => [
                        'first'  => [
                            'type'    => 'string',
                            'context' => [ 'view', 'edit' ],
                        ],
                        'second' => [
                            'type'    => 'string',
                            'context' => [],
                        ],
                    ],
                ],
                [
                    'first'  => 'a',
                    'second' => 'b',
                ],
                [ 'first' => 'a' ],
            ],
            'nested properties'                            => [
                [
                    '$schema'    => 'http://json-schema.org/draft-04/schema#',
                    'type'       => 'object',
                    'properties' => [
                        'parent' => [
                            'type'       => 'object',
                            'context'    => [ 'view', 'edit' ],
                            'properties' => [
                                'child'  => [
                                    'type'    => 'string',
                                    'context' => [ 'view', 'edit' ],
                                ],
                                'hidden' => [
                                    'type'    => 'string',
                                    'context' => [ 'edit' ],
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'parent' => [
                        'child'  => 'hi',
                        'hidden' => 'there',
                    ],
                ],
                [ 'parent' => [ 'child' => 'hi' ] ],
            ],
            'grand child properties'                       => [
                [
                    '$schema'    => 'http://json-schema.org/draft-04/schema#',
                    'type'       => 'object',
                    'properties' => [
                        'parent' => [
                            'type'       => 'object',
                            'context'    => [ 'view', 'edit' ],
                            'properties' => [
                                'child' => [
                                    'type'       => 'object',
                                    'context'    => [ 'view', 'edit' ],
                                    'properties' => [
                                        'grand'  => [
                                            'type'    => 'string',
                                            'context' => [ 'view', 'edit' ],
                                        ],
                                        'hidden' => [
                                            'type'    => 'string',
                                            'context' => [ 'edit' ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'parent' => [
                        'child' => [
                            'grand' => 'hi',
                        ],
                    ],
                ],
                [ 'parent' => [ 'child' => [ 'grand' => 'hi' ] ] ],
            ],
            'array'                                        => [
                [
                    '$schema'    => 'http://json-schema.org/draft-04/schema#',
                    'type'       => 'object',
                    'properties' => [
                        'arr' => [
                            'type'    => 'array',
                            'context' => [ 'view', 'edit' ],
                            'items'   => [
                                'type'       => 'object',
                                'context'    => [ 'view', 'edit' ],
                                'properties' => [
                                    'visible' => [
                                        'type'    => 'string',
                                        'context' => [ 'view', 'edit' ],
                                    ],
                                    'hidden'  => [
                                        'type'    => 'string',
                                        'context' => [ 'edit' ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'arr' => [
                        [
                            'visible' => 'hi',
                            'hidden'  => 'there',
                        ],
                    ],
                ],
                [ 'arr' => [ [ 'visible' => 'hi' ] ] ],
            ],
            'additional properties'                        => [
                [
                    '$schema'    => 'http://json-schema.org/draft-04/schema#',
                    'type'       => 'object',
                    'properties' => [
                        'additional' => [
                            'type'                 => 'object',
                            'context'              => [ 'view', 'edit' ],
                            'properties'           => [
                                'a' => [
                                    'type'    => 'string',
                                    'context' => [ 'view', 'edit' ],
                                ],
                                'b' => [
                                    'type'    => 'string',
                                    'context' => [ 'edit' ],
                                ],
                            ],
                            'additionalProperties' => [
                                'type'    => 'string',
                                'context' => [ 'edit' ],
                            ],
                        ],
                    ],
                ],
                [
                    'additional' => [
                        'a' => '1',
                        'b' => '2',
                        'c' => '3',
                    ],
                ],
                [ 'additional' => [ 'a' => '1' ] ],
            ],
            'pattern properties'                           => [
                [
                    '$schema'              => 'http://json-schema.org/draft-04/schema#',
                    'type'                 => 'object',
                    'properties'           => [
                        'a' => [
                            'type'    => 'string',
                            'context' => [ 'view', 'edit' ],
                        ],
                    ],
                    'patternProperties'    => [
                        '[0-9]' => [
                            'type'    => 'string',
                            'context' => [ 'view', 'edit' ],
                        ],
                        'c.*'   => [
                            'type'    => 'string',
                            'context' => [ 'edit' ],
                        ],
                    ],
                    'additionalProperties' => [
                        'type'    => 'string',
                        'context' => [ 'edit' ],
                    ],
                ],
                [
                    'a'  => '1',
                    'b'  => '2',
                    '0'  => '3',
                    'ca' => '4',
                ],
                [
                    'a' => '1',
                    '0' => '3',
                ],
            ],
            'multiple types object'                        => [
                [
                    '$schema'    => 'http://json-schema.org/draft-04/schema#',
                    'type'       => 'object',
                    'properties' => [
                        'multi' => [
                            'type'       => [ 'object', 'string' ],
                            'context'    => [ 'view', 'edit' ],
                            'properties' => [
                                'a' => [
                                    'type'    => 'string',
                                    'context' => [ 'view', 'edit' ],
                                ],
                                'b' => [
                                    'type'    => 'string',
                                    'context' => [ 'edit' ],
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'multi' => [
                        'a' => '1',
                        'b' => '2',
                    ],
                ],
                [ 'multi' => [ 'a' => '1' ] ],
            ],
            'multiple types array'                         => [
                [
                    '$schema'    => 'http://json-schema.org/draft-04/schema#',
                    'type'       => 'object',
                    'properties' => [
                        'multi' => [
                            'type'    => [ 'array', 'string' ],
                            'context' => [ 'view', 'edit' ],
                            'items'   => [
                                'type'       => 'object',
                                'context'    => [ 'view', 'edit' ],
                                'properties' => [
                                    'visible' => [
                                        'type'    => 'string',
                                        'context' => [ 'view', 'edit' ],
                                    ],
                                    'hidden'  => [
                                        'type'    => 'string',
                                        'context' => [ 'edit' ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'multi' => [
                        [
                            'visible' => '1',
                            'hidden'  => '2',
                        ],
                    ],
                ],
                [ 'multi' => [ [ 'visible' => '1' ] ] ],
            ],
            'does not traverse missing context'            => [
                [
                    '$schema'    => 'http://json-schema.org/draft-04/schema#',
                    'type'       => 'object',
                    'properties' => [
                        'parent' => [
                            'type'       => 'object',
                            'context'    => [ 'view', 'edit' ],
                            'properties' => [
                                'child' => [
                                    'type'       => 'object',
                                    'properties' => [
                                        'grand'  => [
                                            'type'    => 'string',
                                            'context' => [ 'view', 'edit' ],
                                        ],
                                        'hidden' => [
                                            'type'    => 'string',
                                            'context' => [ 'edit' ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'parent' => [
                        'child' => [
                            'grand'  => 'hi',
                            'hidden' => 'there',
                        ],
                    ],
                ],
                [
                    'parent' => [
                        'child' => [
                            'grand'  => 'hi',
                            'hidden' => 'there',
                        ],
                    ],
                ],
            ],
            'object with no matching properties'           => [
                [
                    '$schema'    => 'http://json-schema.org/draft-04/schema#',
                    'type'       => 'object',
                    'properties' => [
                        'a' => [
                            'type'    => 'string',
                            'context' => [ 'edit' ],
                        ],
                        'b' => [
                            'type'    => 'string',
                            'context' => [ 'edit' ],
                        ],
                    ],
                ],
                [
                    'a' => 'hi',
                    'b' => 'hello',
                ],
                [],
            ],
            'array whose type does not match'              => [
                [
                    '$schema'    => 'http://json-schema.org/draft-04/schema#',
                    'type'       => 'object',
                    'properties' => [
                        'arr' => [
                            'type'    => 'array',
                            'context' => [ 'view' ],
                            'items'   => [
                                'type'    => 'string',
                                'context' => [ 'edit' ],
                            ],
                        ],
                    ],
                ],
                [
                    'arr' => [ 'foo', 'bar', 'baz' ],
                ],
                [ 'arr' => [] ],
            ],
            'array and object type passed object'          => [
                [
                    '$schema'    => 'http://json-schema.org/draft-04/schema#',
                    'type'       => [ 'array', 'object' ],
                    'properties' => [
                        'a' => [
                            'type'    => 'string',
                            'context' => [ 'view' ],
                        ],
                        'b' => [
                            'type'    => 'string',
                            'context' => [ 'view' ],
                        ],
                    ],
                    'items'      => [
                        'type'       => 'object',
                        'context'    => [ 'edit' ],
                        'properties' => [
                            'a' => [
                                'type'    => 'string',
                                'context' => [ 'view' ],
                            ],
                            'b' => [
                                'type'    => 'string',
                                'context' => [ 'view' ],
                            ],
                        ],
                    ],
                ],
                [
                    'a' => 'foo',
                    'b' => 'bar',
                ],
                [
                    'a' => 'foo',
                    'b' => 'bar',
                ],
            ],
            'array and object type passed array'           => [
                [
                    '$schema'    => 'http://json-schema.org/draft-04/schema#',
                    'type'       => [ 'array', 'object' ],
                    'properties' => [
                        'a' => [
                            'type'    => 'string',
                            'context' => [ 'view' ],
                        ],
                        'b' => [
                            'type'    => 'string',
                            'context' => [ 'view' ],
                        ],
                    ],
                    'items'      => [
                        'type'       => 'object',
                        'context'    => [ 'edit' ],
                        'properties' => [
                            'a' => [
                                'type'    => 'string',
                                'context' => [ 'view' ],
                            ],
                            'b' => [
                                'type'    => 'string',
                                'context' => [ 'view' ],
                            ],
                        ],
                    ],
                ],
                [
                    [
                        'a' => 'foo',
                        'b' => 'bar',
                    ],
                    [
                        'a' => 'foo',
                        'b' => 'bar',
                    ],
                ],
                [],
            ],
            'anyOf applies the correct schema'             => [
                [
                    '$schema' => 'http://json-schema.org/draft-04/schema#',
                    'type'    => 'object',
                    'anyOf'   => [
                        [
                            'properties' => [
                                'a' => [
                                    'type'    => 'string',
                                    'context' => [ 'view' ],
                                ],
                                'b' => [
                                    'type'    => 'string',
                                    'context' => [ 'edit' ],
                                ],
                            ],
                        ],
                        [
                            'properties' => [
                                'a' => [
                                    'type'    => 'integer',
                                    'context' => [ 'edit' ],
                                ],
                                'b' => [
                                    'type'    => 'integer',
                                    'context' => [ 'view' ],
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'a' => 1,
                    'b' => 2,
                ],
                [
                    'b' => 2,
                ],
            ],
            'anyOf is ignored if no valid schema is found' => [
                [
                    '$schema' => 'http://json-schema.org/draft-04/schema#',
                    'type'    => 'object',
                    'anyOf'   => [
                        [
                            'properties' => [
                                'a' => [
                                    'type'    => 'string',
                                    'context' => [ 'view' ],
                                ],
                                'b' => [
                                    'type'    => 'string',
                                    'context' => [ 'edit' ],
                                ],
                            ],
                        ],
                        [
                            'properties' => [
                                'a' => [
                                    'type'    => 'integer',
                                    'context' => [ 'edit' ],
                                ],
                                'b' => [
                                    'type'    => 'integer',
                                    'context' => [ 'view' ],
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'a' => true,
                    'b' => false,
                ],
                [
                    'a' => true,
                    'b' => false,
                ],
            ],
            'oneOf applies the correct schema'             => [
                [
                    '$schema' => 'http://json-schema.org/draft-04/schema#',
                    'type'    => 'object',
                    'oneOf'   => [
                        [
                            'properties' => [
                                'a' => [
                                    'type'    => 'string',
                                    'context' => [ 'view' ],
                                ],
                                'b' => [
                                    'type'    => 'string',
                                    'context' => [ 'edit' ],
                                ],
                            ],
                        ],
                        [
                            'properties' => [
                                'a' => [
                                    'type'    => 'integer',
                                    'context' => [ 'edit' ],
                                ],
                                'b' => [
                                    'type'    => 'integer',
                                    'context' => [ 'view' ],
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'a' => 1,
                    'b' => 2,
                ],
                [
                    'b' => 2,
                ],
            ],
            'oneOf ignored if no valid schema was found'   => [
                [
                    '$schema' => 'http://json-schema.org/draft-04/schema#',
                    'type'    => 'object',
                    'anyOf'   => [
                        [
                            'properties' => [
                                'a' => [
                                    'type'    => 'string',
                                    'context' => [ 'view' ],
                                ],
                                'b' => [
                                    'type'    => 'string',
                                    'context' => [ 'edit' ],
                                ],
                            ],
                        ],
                        [
                            'properties' => [
                                'a' => [
                                    'type'    => 'integer',
                                    'context' => [ 'edit' ],
                                ],
                                'b' => [
                                    'type'    => 'integer',
                                    'context' => [ 'view' ],
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'a' => true,
                    'b' => false,
                ],
                [
                    'a' => true,
                    'b' => false,
                ],
            ],
            'oneOf combined with base'                     => [
                [
                    '$schema'    => 'http://json-schema.org/draft-04/schema#',
                    'type'       => 'object',
                    'properties' => [
                        'c' => [
                            'type'    => 'integer',
                            'context' => [ 'edit' ],
                        ],
                    ],
                    'oneOf'      => [
                        [
                            'properties' => [
                                'a' => [
                                    'type'    => 'string',
                                    'context' => [ 'view' ],
                                ],
                                'b' => [
                                    'type'    => 'string',
                                    'context' => [ 'edit' ],
                                ],
                            ],
                        ],
                        [
                            'properties' => [
                                'a' => [
                                    'type'    => 'integer',
                                    'context' => [ 'edit' ],
                                ],
                                'b' => [
                                    'type'    => 'integer',
                                    'context' => [ 'view' ],
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'a' => 1,
                    'b' => 2,
                    'c' => 3,
                ],
                [
                    'b' => 2,
                ],
            ],
        ];
    }

    public function test_rest_ensure_response_accepts_wp_error_and_returns_wp_error()
    {
        $response = rest_ensure_response(new WP_Error());
        $this->assertInstanceOf('WP_Error', $response);
    }

    /**
     * @dataProvider data_rest_ensure_response_returns_instance_of_wp_rest_response
     *
     * @param mixed $response      The response passed to rest_ensure_response().
     * @param mixed $expected_data The expected data a response should include.
     */
    public function test_rest_ensure_response_returns_instance_of_wp_rest_response($response, $expected_data)
    {
        $response_object = rest_ensure_response($response);
        $this->assertInstanceOf('WP_REST_Response', $response_object);
        $this->assertSame($expected_data, $response_object->get_data());
    }

    /**
     * Data provider for test_rest_ensure_response_returns_instance_of_wp_rest_response().
     *
     * @return array
     */
    public function data_rest_ensure_response_returns_instance_of_wp_rest_response()
    {
        return [
            [ null, null ],
            [ [ 'chocolate' => 'cookies' ], [ 'chocolate' => 'cookies' ] ],
            [ 123, 123 ],
            [ true, true ],
            [ 'chocolate', 'chocolate' ],
            [ new WP_HTTP_Response('http'), 'http' ],
            [ new WP_REST_Response('rest'), 'rest' ],
        ];
    }

    /**
     * @ticket 49116
     */
    public function test_rest_get_route_for_post_non_post()
    {
        $this->assertSame('', rest_get_route_for_post('garbage'));
    }

    /**
     * @ticket 49116
     */
    public function test_rest_get_route_for_post_invalid_post_type()
    {
        register_post_type('invalid');
        $post = self::factory()->post->create_and_get([ 'post_type' => 'invalid' ]);
        unregister_post_type('invalid');

        $this->assertSame('', rest_get_route_for_post($post));
    }

    /**
     * @ticket 53656
     */
    public function test_rest_get_route_for_post_custom_namespace()
    {
        register_post_type(
            'cpt',
            [
                'show_in_rest'   => true,
                'rest_base'      => 'cpt',
                'rest_namespace' => 'wordpress/v1',
            ]
        );
        $post = self::factory()->post->create_and_get([ 'post_type' => 'cpt' ]);

        $this->assertSame('/wordpress/v1/cpt/' . $post->ID, rest_get_route_for_post($post));
        unregister_post_type('cpt');
    }

    /**
     * @ticket 53656
     */
    public function test_rest_get_route_for_post_type_items()
    {
        $this->assertSame('/wp/v2/posts', rest_get_route_for_post_type_items('post'));
    }

    /**
     * @ticket 53656
     */
    public function test_rest_get_route_for_post_type_items_custom_namespace()
    {
        register_post_type(
            'cpt',
            [
                'show_in_rest'   => true,
                'rest_base'      => 'cpt',
                'rest_namespace' => 'wordpress/v1',
            ]
        );

        $this->assertSame('/wordpress/v1/cpt', rest_get_route_for_post_type_items('cpt'));
        unregister_post_type('cpt');
    }

    /**
     * @ticket 49116
     */
    public function test_rest_get_route_for_post_non_rest()
    {
        $post = self::factory()->post->create_and_get([ 'post_type' => 'custom_css' ]);
        $this->assertSame('', rest_get_route_for_post($post));
    }

    /**
     * @ticket 49116
     * @ticket 53656
     */
    public function test_rest_get_route_for_post_custom_controller()
    {
        $post = self::factory()->post->create_and_get([ 'post_type' => 'wp_block' ]);
        $this->assertSame('/wp/v2/blocks/' . $post->ID, rest_get_route_for_post($post));
    }

    /**
     * @ticket 49116
     */
    public function test_rest_get_route_for_post()
    {
        $post = self::factory()->post->create_and_get();
        $this->assertSame('/wp/v2/posts/' . $post->ID, rest_get_route_for_post($post));
    }

    /**
     * @ticket 49116
     */
    public function test_rest_get_route_for_media()
    {
        $post = self::factory()->attachment->create_and_get();
        $this->assertSame('/wp/v2/media/' . $post->ID, rest_get_route_for_post($post));
    }

    /**
     * @ticket 49116
     */
    public function test_rest_get_route_for_post_id()
    {
        $post = self::factory()->post->create_and_get();
        $this->assertSame('/wp/v2/posts/' . $post->ID, rest_get_route_for_post($post->ID));
    }

    /**
     * @ticket 49116
     */
    public function test_rest_get_route_for_term_non_term()
    {
        $this->assertSame('', rest_get_route_for_term('garbage'));
    }

    /**
     * @ticket 49116
     */
    public function test_rest_get_route_for_term_invalid_term_type()
    {
        register_taxonomy('invalid', 'post');
        $term = self::factory()->term->create_and_get([ 'taxonomy' => 'invalid' ]);
        unregister_taxonomy('invalid');

        $this->assertSame('', rest_get_route_for_term($term));
    }

    /**
     * @ticket 49116
     */
    public function test_rest_get_route_for_term_non_rest()
    {
        $term = self::factory()->term->create_and_get([ 'taxonomy' => 'post_format' ]);
        $this->assertSame('', rest_get_route_for_term($term));
    }

    /**
     * @ticket 49116
     */
    public function test_rest_get_route_for_term()
    {
        $term = self::factory()->term->create_and_get();
        $this->assertSame('/wp/v2/tags/' . $term->term_id, rest_get_route_for_term($term));
    }

    /**
     * @ticket 49116
     */
    public function test_rest_get_route_for_category()
    {
        $term = self::factory()->category->create_and_get();
        $this->assertSame('/wp/v2/categories/' . $term->term_id, rest_get_route_for_term($term));
    }

    /**
     * @ticket 49116
     */
    public function test_rest_get_route_for_term_id()
    {
        $term = self::factory()->term->create_and_get();
        $this->assertSame('/wp/v2/tags/' . $term->term_id, rest_get_route_for_term($term->term_id));
    }

    /**
     * @ticket 54267
     */
    public function test_rest_get_route_for_taxonomy_custom_namespace()
    {
        register_taxonomy(
            'ct',
            'post',
            [
                'show_in_rest'   => true,
                'rest_base'      => 'ct',
                'rest_namespace' => 'wordpress/v1',
            ]
        );
        $term = self::factory()->term->create_and_get([ 'taxonomy' => 'ct' ]);

        $this->assertSame('/wordpress/v1/ct/' . $term->term_id, rest_get_route_for_term($term));
        unregister_taxonomy('ct');
    }

    /**
     * @ticket 54267
     */
    public function test_rest_get_route_for_taxonomy_items()
    {
        $this->assertSame('/wp/v2/categories', rest_get_route_for_taxonomy_items('category'));
    }

    /**
     * @ticket 54267
     */
    public function test_rest_get_route_for_taxonomy_items_custom_namespace()
    {
        register_taxonomy(
            'ct',
            'post',
            [
                'show_in_rest'   => true,
                'rest_base'      => 'ct',
                'rest_namespace' => 'wordpress/v1',
            ]
        );

        $this->assertSame('/wordpress/v1/ct', rest_get_route_for_taxonomy_items('ct'));
        unregister_post_type('ct');
    }

    /**
     * @ticket 50300
     *
     * @dataProvider data_rest_is_object
     *
     * @param bool  $expected Expected result of the check.
     * @param mixed $value    The value to check.
     */
    public function test_rest_is_object($expected, $value)
    {
        $is_object = rest_is_object($value);

        if ($expected) {
            $this->assertTrue($is_object);
        } else {
            $this->assertFalse($is_object);
        }
    }

    public function data_rest_is_object()
    {
        return [
            [
                true,
                '',
            ],
            [
                true,
                new stdClass(),
            ],
            [
                true,
                new JsonSerializable_Object([ 'hi' => 'there' ]),
            ],
            [
                true,
                [ 'hi' => 'there' ],
            ],
            [
                true,
                [],
            ],
            [
                true,
                [ 'a', 'b' ],
            ],
            [
                false,
                new Basic_Object(),
            ],
            [
                false,
                new JsonSerializable_Object('str'),
            ],
            [
                false,
                'str',
            ],
            [
                false,
                5,
            ],
        ];
    }

    /**
     * @ticket 50300
     *
     * @dataProvider data_rest_sanitize_object
     *
     * @param array $expected Expected sanitized version.
     * @param mixed $value    The value to sanitize.
     */
    public function test_rest_sanitize_object($expected, $value)
    {
        $sanitized = rest_sanitize_object($value);
        $this->assertSame($expected, $sanitized);
    }

    public function data_rest_sanitize_object()
    {
        return [
            [
                [],
                '',
            ],
            [
                [ 'a' => '1' ],
                (object) [ 'a' => '1' ],
            ],
            [
                [ 'hi' => 'there' ],
                new JsonSerializable_Object([ 'hi' => 'there' ]),
            ],
            [
                [ 'hi' => 'there' ],
                [ 'hi' => 'there' ],
            ],
            [
                [],
                [],
            ],
            [
                [ 'a', 'b' ],
                [ 'a', 'b' ],
            ],
            [
                [],
                new Basic_Object(),
            ],
            [
                [],
                new JsonSerializable_Object('str'),
            ],
            [
                [],
                'str',
            ],
            [
                [],
                5,
            ],
        ];
    }

    /**
     * @ticket 50300
     *
     * @dataProvider data_rest_is_array
     *
     * @param bool  $expected Expected result of the check.
     * @param mixed $value    The value to check.
     */
    public function test_rest_is_array($expected, $value)
    {
        $is_array = rest_is_array($value);

        if ($expected) {
            $this->assertTrue($is_array);
        } else {
            $this->assertFalse($is_array);
        }
    }

    public function data_rest_is_array()
    {
        return [
            [
                true,
                '',
            ],
            [
                true,
                [ 'a', 'b' ],
            ],
            [
                true,
                [],
            ],
            [
                true,
                'a,b,c',
            ],
            [
                true,
                'a',
            ],
            [
                true,
                5,
            ],
            [
                false,
                new stdClass(),
            ],
            [
                false,
                new JsonSerializable_Object([ 'hi' => 'there' ]),
            ],
            [
                false,
                [ 'hi' => 'there' ],
            ],
            [
                false,
                new Basic_Object(),
            ],
            [
                false,
                new JsonSerializable_Object('str'),
            ],
            [
                false,
                null,
            ],
        ];
    }

    /**
     * @ticket 50300
     *
     * @dataProvider data_rest_sanitize_array
     *
     * @param array $expected Expected sanitized version.
     * @param mixed $value    The value to sanitize.
     */
    public function test_rest_sanitize_array($expected, $value)
    {
        $sanitized = rest_sanitize_array($value);
        $this->assertSame($expected, $sanitized);
    }

    public function data_rest_sanitize_array()
    {
        return [
            [
                [],
                '',
            ],
            [
                [ 'a', 'b' ],
                [ 'a', 'b' ],
            ],
            [
                [],
                [],
            ],
            [
                [ 'a', 'b', 'c' ],
                'a,b,c',
            ],
            [
                [ 'a' ],
                'a',
            ],
            [
                [ 'a', 'b' ],
                'a,b,',
            ],
            [
                [ '5' ],
                5,
            ],
            [
                [],
                new stdClass(),
            ],
            [
                [],
                new JsonSerializable_Object([ 'hi' => 'there' ]),
            ],
            [
                [ 'there' ],
                [ 'hi' => 'there' ],
            ],
            [
                [],
                new Basic_Object(),
            ],
            [
                [],
                new JsonSerializable_Object('str'),
            ],
            [
                [],
                null,
            ],
        ];
    }

    /**
     * @ticket 51146
     *
     * @dataProvider data_rest_is_integer
     *
     * @param bool  $expected Expected result of the check.
     * @param mixed $value    The value to check.
     */
    public function test_rest_is_integer($expected, $value)
    {
        $is_integer = rest_is_integer($value);

        if ($expected) {
            $this->assertTrue($is_integer);
        } else {
            $this->assertFalse($is_integer);
        }
    }

    public function data_rest_is_integer()
    {
        return [
            [
                true,
                1,
            ],
            [
                true,
                '1',
            ],
            [
                true,
                0,
            ],
            [
                true,
                -1,
            ],
            [
                true,
                '05',
            ],
            [
                false,
                'garbage',
            ],
            [
                false,
                5.5,
            ],
            [
                false,
                '5.5',
            ],
            [
                false,
                [],
            ],
            [
                false,
                true,
            ],
        ];
    }

    /**
     * @ticket 50300
     *
     * @dataProvider data_get_best_type_for_value
     *
     * @param string $expected The expected best type.
     * @param mixed  $value    The value to test.
     * @param array  $types    The list of available types.
     */
    public function test_get_best_type_for_value($expected, $value, $types)
    {
        $this->assertSame($expected, rest_get_best_type_for_value($value, $types));
    }

    public function data_get_best_type_for_value()
    {
        return [
            [
                'array',
                [ 'hi' ],
                [ 'array' ],
            ],
            [
                'object',
                [ 'hi' => 'there' ],
                [ 'object' ],
            ],
            [
                'integer',
                5,
                [ 'integer' ],
            ],
            [
                'number',
                4.0,
                [ 'number' ],
            ],
            [
                'boolean',
                true,
                [ 'boolean' ],
            ],
            [
                'string',
                'str',
                [ 'string' ],
            ],
            [
                'null',
                null,
                [ 'null' ],
            ],
            [
                'string',
                '',
                [ 'array', 'string' ],
            ],
            [
                'string',
                '',
                [ 'object', 'string' ],
            ],
            [
                'string',
                'Hello',
                [ 'object', 'string' ],
            ],
            [
                'object',
                [ 'hello' => 'world' ],
                [ 'object', 'string' ],
            ],
            [
                'number',
                '5.0',
                [ 'number', 'string' ],
            ],
            [
                'string',
                '5.0',
                [ 'string', 'number' ],
            ],
            [
                'boolean',
                'false',
                [ 'boolean', 'string' ],
            ],
            [
                'string',
                'false',
                [ 'string', 'boolean' ],
            ],
            [
                'string',
                'a,b',
                [ 'string', 'array' ],
            ],
            [
                'array',
                'a,b',
                [ 'array', 'string' ],
            ],
            [
                'string',
                'hello',
                [ 'integer', 'string' ],
            ],
        ];
    }

    /**
     * @ticket 51722
     * @dataProvider data_rest_preload_api_request_embeds_links
     *
     * @param string   $embed        The embed parameter.
     * @param string[] $expected     The list of link relations that should be embedded.
     * @param string[] $not_expected The list of link relations that should not be embedded.
     */
    public function test_rest_preload_api_request_embeds_links($embed, $expected, $not_expected)
    {
        wp_set_current_user(1);
        $post_id = self::factory()->post->create();
        self::factory()->comment->create_post_comments($post_id);

        $url           = sprintf('/wp/v2/posts/%d?%s', $post_id, $embed);
        $preload_paths = [ $url ];

        $preload_data = array_reduce(
            $preload_paths,
            'rest_preload_api_request',
            []
        );

        $this->assertSame(array_keys($preload_data), $preload_paths);
        $this->assertArrayHasKey('body', $preload_data[ $url ]);
        $this->assertArrayHasKey('_links', $preload_data[ $url ]['body']);

        if ($expected) {
            $this->assertArrayHasKey('_embedded', $preload_data[ $url ]['body']);
        } else {
            $this->assertArrayNotHasKey('_embedded', $preload_data[ $url ]['body']);
        }

        foreach ($expected as $rel) {
            $this->assertArrayHasKey($rel, $preload_data[ $url ]['body']['_embedded']);
        }

        foreach ($not_expected as $rel) {
            $this->assertArrayNotHasKey($rel, $preload_data[ $url ]['body']['_embedded']);
        }
    }

    public function data_rest_preload_api_request_embeds_links()
    {
        return [
            [ '_embed=wp:term,author', [ 'wp:term', 'author' ], [ 'replies' ] ],
            [ '_embed[]=wp:term&_embed[]=author', [ 'wp:term', 'author' ], [ 'replies' ] ],
            [ '_embed', [ 'wp:term', 'author', 'replies' ], [] ],
            [ '_embed=1', [ 'wp:term', 'author', 'replies' ], [] ],
            [ '_embed=true', [ 'wp:term', 'author', 'replies' ], [] ],
            [ '', [], [] ],
        ];
    }

    /**
     * @ticket 55213
     */
    public function test_rest_preload_api_request_fields()
    {
        $preload_paths = [
            '/',
            '/?_fields=description',
        ];

        $preload_data = array_reduce(
            $preload_paths,
            'rest_preload_api_request',
            []
        );

        $this->assertSame(array_keys($preload_data), [ '/', '/?_fields=description' ]);

        // Unfiltered request has all fields
        $this->assertArrayHasKey('description', $preload_data['/']['body']);
        $this->assertArrayHasKey('routes', $preload_data['/']['body']);

        // Filtered request only has the desired fields.
        $this->assertSame(
            array_keys($preload_data['/?_fields=description']['body']),
            [ 'description' ]
        );
    }

    /**
     * @ticket 51986
     */
    public function test_route_args_is_array_of_arrays()
    {
        $this->setExpectedIncorrectUsage('register_rest_route');

        $registered = register_rest_route(
            'my-ns/v1',
            '/my-route',
            [
                'callback'            => '__return_true',
                'permission_callback' => '__return_true',
                'args'                => [ 'pattern' ],
            ]
        );

        $this->assertTrue($registered);
    }

    /**
     * @ticket 62932
     */
    public function test_should_return_error_if_rest_route_not_string()
    {
        global $wp;

        $wp = new stdClass();

        $wp->query_vars = [
            'rest_route' => [ 'invalid' ],
        ];

        $this->expectException(WPDieException::class);

        try {
            rest_api_loaded();
        } catch (WPDieException $e) {
            $this->assertStringContainsString(
                'The REST route parameter must be a string.',
                $e->getMessage()
            );
            throw $e; // Re-throw to satisfy expectException
        }
    }
}
