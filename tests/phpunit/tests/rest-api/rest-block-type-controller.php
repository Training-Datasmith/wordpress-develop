<?php

declare(strict_types=1);
/**
 * Unit tests covering WP_REST_Block_Types_Controller functionality.
 *
 * @package WordPress
 * @subpackage REST_API
 * @since 5.5.0
 *
 * @covers WP_REST_Block_Types_Controller
 *
 * @group restapi-blocks
 * @group restapi
 */
class REST_Block_Type_Controller_Test extends WP_Test_REST_Controller_Testcase
{
    /**
     * Admin user ID.
     *
     * @since 5.5.0
     *
     * @var int $subscriber_id
     */
    protected static $admin_id;

    /**
     * Subscriber user ID.
     *
     * @since 5.5.0
     *
     * @var int $subscriber_id
     */
    protected static $subscriber_id;

    /**
     * Create fake data before our tests run.
     *
     * @since 5.5.0
     *
     * @param WP_UnitTest_Factory $factory Helper that lets us create fake data.
     */
    public static function wpSetUpBeforeClass(WP_UnitTest_Factory $factory)
    {
        self::$admin_id      = $factory->user->create(
            [
                'role' => 'administrator',
            ]
        );
        self::$subscriber_id = $factory->user->create(
            [
                'role' => 'subscriber',
            ]
        );

        $name     = 'fake/test';
        $settings = [
            'icon' => 'text',
        ];

        register_block_type($name, $settings);
    }

    public static function wpTearDownAfterClass()
    {
        self::delete_user(self::$admin_id);
        self::delete_user(self::$subscriber_id);
        unregister_block_type('fake/test');
        unregister_block_type('fake/invalid');
        unregister_block_type('fake/false');
    }

    /**
     * @ticket 47620
     */
    public function test_register_routes()
    {
        $routes = rest_get_server()->get_routes();
        $this->assertArrayHasKey('/wp/v2/block-types', $routes);
        $this->assertCount(1, $routes['/wp/v2/block-types']);
        $this->assertArrayHasKey('/wp/v2/block-types/(?P<namespace>[a-zA-Z0-9_-]+)', $routes);
        $this->assertCount(1, $routes['/wp/v2/block-types/(?P<namespace>[a-zA-Z0-9_-]+)']);
        $this->assertArrayHasKey('/wp/v2/block-types/(?P<namespace>[a-zA-Z0-9_-]+)/(?P<name>[a-zA-Z0-9_-]+)', $routes);
        $this->assertCount(1, $routes['/wp/v2/block-types/(?P<namespace>[a-zA-Z0-9_-]+)/(?P<name>[a-zA-Z0-9_-]+)']);
    }

    /**
     * @ticket 47620
     */
    public function test_context_param()
    {
        // Collection.
        $request  = new WP_REST_Request('OPTIONS', '/wp/v2/block-types');
        $response = rest_get_server()->dispatch($request);
        $data     = $response->get_data();
        $this->assertSame('view', $data['endpoints'][0]['args']['context']['default']);
        $this->assertSame([ 'view', 'embed', 'edit' ], $data['endpoints'][0]['args']['context']['enum']);
        // Single.
        $request  = new WP_REST_Request('OPTIONS', '/wp/v2/block-types/fake/test');
        $response = rest_get_server()->dispatch($request);
        $data     = $response->get_data();
        $this->assertSame('view', $data['endpoints'][0]['args']['context']['default']);
        $this->assertSame([ 'view', 'embed', 'edit' ], $data['endpoints'][0]['args']['context']['enum']);
    }

    /**
     * @ticket 47620
     */
    public function test_get_items()
    {
        $block_name = 'fake/test';
        wp_set_current_user(self::$admin_id);
        $request  = new WP_REST_Request('GET', '/wp/v2/block-types/fake');
        $response = rest_get_server()->dispatch($request);
        $data     = $response->get_data();
        $this->assertCount(1, $data);
        $block_type = WP_Block_Type_Registry::get_instance()->get_registered($block_name);
        $this->check_block_type_object($block_type, $data[0], $data[0]['_links']);
    }

    /**
     * @ticket 47620
     */
    public function test_get_item()
    {
        $block_name = 'fake/test';
        wp_set_current_user(self::$admin_id);
        $request    = new WP_REST_Request('GET', '/wp/v2/block-types/' . $block_name);
        $response   = rest_get_server()->dispatch($request);
        $block_type = WP_Block_Type_Registry::get_instance()->get_registered($block_name);
        $this->check_block_type_object($block_type, $response->get_data(), $response->get_links());
    }

    /**
     * @ticket 47620
     */
    public function test_get_item_with_styles()
    {
        $block_name   = 'fake/styles';
        $block_styles = [
            'name'         => 'fancy-quote',
            'label'        => 'Fancy Quote',
            'style_handle' => 'myguten-style',
        ];
        register_block_type($block_name);
        register_block_style($block_name, $block_styles);
        wp_set_current_user(self::$admin_id);
        $request  = new WP_REST_Request('GET', '/wp/v2/block-types/' . $block_name);
        $response = rest_get_server()->dispatch($request);
        $data     = $response->get_data();
        $this->assertSameSets([ $block_styles ], $data['styles']);
    }

    /**
     * @ticket 47620
     */
    public function test_get_item_with_styles_merge()
    {
        $block_name   = 'fake/styles2';
        $block_styles = [
            'name'         => 'fancy-quote',
            'label'        => 'Fancy Quote',
            'style_handle' => 'myguten-style',
        ];
        $settings     = [
            'styles' => [
                [
                    'name'         => 'blue-quote',
                    'label'        => 'Blue Quote',
                    'style_handle' => 'myguten-style',
                ],
            ],
        ];
        register_block_type($block_name, $settings);
        register_block_style($block_name, $block_styles);
        wp_set_current_user(self::$admin_id);
        $request  = new WP_REST_Request('GET', '/wp/v2/block-types/' . $block_name);
        $response = rest_get_server()->dispatch($request);
        $data     = $response->get_data();
        $expected = [
            [
                'name'         => 'fancy-quote',
                'label'        => 'Fancy Quote',
                'style_handle' => 'myguten-style',
            ],
            [
                'name'         => 'blue-quote',
                'label'        => 'Blue Quote',
                'style_handle' => 'myguten-style',
            ],
        ];
        $this->assertSameSets($expected, $data['styles']);
    }

    /**
     * @ticket 47620
     */
    public function test_get_block_invalid_name()
    {
        $block_type = 'fake/block';
        wp_set_current_user(self::$admin_id);
        $request  = new WP_REST_Request('GET', '/wp/v2/block-types/' . $block_type);
        $response = rest_get_server()->dispatch($request);

        $this->assertErrorResponse('rest_block_type_invalid', $response, 404);
    }

    /**
     * @ticket 47620
     * @ticket 57585
     * @ticket 59346
     * @ticket 59797
     */
    public function test_get_item_invalid()
    {
        $block_type = 'fake/invalid';
        $settings   = [
            'title'            => true,
            'category'         => true,
            'parent'           => 'invalid_parent',
            'ancestor'         => 'invalid_ancestor',
            'allowed_blocks'   => 'invalid_allowed_blocks',
            'icon'             => true,
            'description'      => true,
            'keywords'         => 'invalid_keywords',
            'textdomain'       => true,
            'attributes'       => 'invalid_attributes',
            'provides_context' => 'invalid_provides_context',
            'uses_context'     => 'invalid_uses_context',
            'selectors'        => 'invalid_selectors',
            'supports'         => 'invalid_supports',
            'styles'           => [],
            'example'          => 'invalid_example',
            'variations'       => 'invalid_variations',
            'block_hooks'      => 'invalid_block_hooks',
            'render_callback'  => 'invalid_callback',
            'editor_script'    => true,
            'script'           => true,
            'view_script'      => true,
            'editor_style'     => true,
            'style'            => true,
        ];
        register_block_type($block_type, $settings);
        wp_set_current_user(self::$admin_id);
        $request  = new WP_REST_Request('GET', '/wp/v2/block-types/' . $block_type);
        $response = rest_get_server()->dispatch($request);
        $data     = $response->get_data();
        $this->assertSame($block_type, $data['name']);
        $this->assertSame('1', $data['title']);
        $this->assertNull($data['category']);
        $this->assertSameSets([ 'invalid_parent' ], $data['parent']);
        $this->assertSameSets([ 'invalid_ancestor' ], $data['ancestor']);
        $this->assertSameSets([ 'invalid_allowed_blocks' ], $data['allowed_blocks']);
        $this->assertNull($data['icon']);
        $this->assertSame('1', $data['description']);
        $this->assertSameSets([ 'invalid_keywords' ], $data['keywords']);
        $this->assertNull($data['textdomain']);
        $this->assertSameSetsWithIndex(
            [
                'lock'     => [ 'type' => 'object' ],
                'metadata' => [ 'type' => 'object' ],
            ],
            $data['attributes']
        );
        $this->assertSameSets([ 'invalid_uses_context' ], $data['uses_context']);
        $this->assertSameSets([], $data['provides_context']);
        $this->assertSameSets([], $data['selectors'], 'invalid selectors defaults to empty array');
        $this->assertSameSets([], $data['supports']);
        $this->assertSameSets([], $data['styles']);
        $this->assertNull($data['example']);
        $this->assertSameSets([ [] ], $data['variations']);
        $this->assertSameSets([], $data['block_hooks'], 'invalid block_hooks defaults to empty array');
        $this->assertSameSets([], $data['editor_script_handles']);
        $this->assertSameSets([], $data['script_handles']);
        $this->assertSameSets([], $data['view_script_handles']);
        $this->assertSameSets([], $data['view_script_module_ids']);
        $this->assertSameSets([], $data['editor_style_handles']);
        $this->assertSameSets([], $data['style_handles']);
        $this->assertFalse($data['is_dynamic']);
        // Deprecated properties.
        $this->assertNull($data['editor_script']);
        $this->assertNull($data['script']);
        $this->assertNull($data['view_script']);
        $this->assertNull($data['editor_style']);
        $this->assertNull($data['style']);
    }

    /**
     * @ticket 47620
     * @ticket 57585
     * @ticket 59346
     * @ticket 59797
     */
    public function test_get_item_defaults()
    {
        $block_type = 'fake/false';
        $settings   = [
            'title'            => false,
            'category'         => false,
            'parent'           => false,
            'ancestor'         => false,
            'allowed_blocks'   => false,
            'icon'             => false,
            'description'      => false,
            'keywords'         => false,
            'textdomain'       => false,
            'attributes'       => false,
            'provides_context' => false,
            'uses_context'     => false,
            'selectors'        => false,
            'supports'         => false,
            'styles'           => false,
            'example'          => false,
            'variations'       => false,
            'block_hooks'      => false,
            'editor_script'    => false,
            'script'           => false,
            'view_script'      => false,
            'editor_style'     => false,
            'style'            => false,
            'render_callback'  => false,
        ];
        register_block_type($block_type, $settings);
        wp_set_current_user(self::$admin_id);
        $request  = new WP_REST_Request('GET', '/wp/v2/block-types/' . $block_type);
        $response = rest_get_server()->dispatch($request);
        $data     = $response->get_data();
        $this->assertSame($block_type, $data['name']);
        $this->assertSame('', $data['title']);
        $this->assertNull($data['category']);
        $this->assertSameSets([], $data['parent']);
        $this->assertSameSets([], $data['ancestor']);
        $this->assertSameSets([], $data['allowed_blocks']);
        $this->assertNull($data['icon']);
        $this->assertSame('', $data['description']);
        $this->assertSameSets([], $data['keywords']);
        $this->assertNull($data['textdomain']);
        $this->assertSameSetsWithIndex(
            [
                'lock'     => [ 'type' => 'object' ],
                'metadata' => [ 'type' => 'object' ],
            ],
            $data['attributes']
        );
        $this->assertSameSets([], $data['provides_context']);
        $this->assertSameSets([], $data['uses_context']);
        $this->assertSameSets([], $data['selectors'], 'selectors defaults to empty array');
        $this->assertSameSets([], $data['supports']);
        $this->assertSameSets([], $data['styles']);
        $this->assertNull($data['example']);
        $this->assertSameSets([], $data['variations']);
        $this->assertSameSets([], $data['block_hooks'], 'block_hooks defaults to empty array');
        $this->assertSameSets([], $data['editor_script_handles']);
        $this->assertSameSets([], $data['script_handles']);
        $this->assertSameSets([], $data['view_script_handles']);
        $this->assertSameSets([], $data['view_script_module_ids']);
        $this->assertSameSets([], $data['editor_style_handles']);
        $this->assertSameSets([], $data['style_handles']);
        $this->assertFalse($data['is_dynamic']);
        // Deprecated properties.
        $this->assertNull($data['editor_script']);
        $this->assertNull($data['script']);
        $this->assertNull($data['view_script']);
        $this->assertNull($data['editor_style']);
        $this->assertNull($data['style']);
    }

    /**
     * @ticket 56733
     */
    public function test_get_item_deprecated()
    {
        $block_type = 'fake/deprecated';
        $settings   = [
            'editor_script' => 'hello_world',
            'script'        => 'gutenberg',
            'view_script'   => 'foo_bar',
            'editor_style'  => 'guten_tag',
            'style'         => 'out_of_style',
        ];
        register_block_type($block_type, $settings);
        wp_set_current_user(self::$admin_id);
        $request  = new WP_REST_Request('GET', '/wp/v2/block-types/' . $block_type);
        $response = rest_get_server()->dispatch($request);
        $data     = $response->get_data();
        $this->assertSameSets(
            [ 'hello_world' ],
            $data['editor_script_handles'],
            "Endpoint doesn't return correct array for editor_script_handles."
        );
        $this->assertSameSets(
            [ 'gutenberg' ],
            $data['script_handles'],
            "Endpoint doesn't return correct array for script_handles."
        );
        $this->assertSameSets(
            [ 'foo_bar' ],
            $data['view_script_handles'],
            "Endpoint doesn't return correct array for view_script_handles."
        );
        $this->assertSameSets(
            [ 'guten_tag' ],
            $data['editor_style_handles'],
            "Endpoint doesn't return correct array for editor_style_handles."
        );
        $this->assertSameSets(
            [ 'out_of_style' ],
            $data['style_handles'],
            "Endpoint doesn't return correct array for style_handles."
        );
        // Deprecated properties.
        $this->assertSame(
            'hello_world',
            $data['editor_script'],
            "Endpoint doesn't return correct string for editor_script."
        );
        $this->assertSame(
            'gutenberg',
            $data['script'],
            "Endpoint doesn't return correct string for script."
        );
        $this->assertSame(
            'foo_bar',
            $data['view_script'],
            "Endpoint doesn't return correct string for view_script."
        );
        $this->assertSame(
            'guten_tag',
            $data['editor_style'],
            "Endpoint doesn't return correct string for editor_style."
        );
        $this->assertSame(
            'out_of_style',
            $data['style'],
            "Endpoint doesn't return correct string for style."
        );
    }

    /**
     * @ticket 56733
     */
    public function test_get_item_deprecated_with_arrays()
    {
        $block_type = 'fake/deprecated-with-arrays';
        $settings   = [
            'editor_script' => [ 'hello', 'world' ],
            'script'        => [ 'gutenberg' ],
            'view_script'   => [ 'foo', 'bar' ],
            'editor_style'  => [ 'guten', 'tag' ],
            'style'         => [ 'out', 'of', 'style' ],
        ];
        register_block_type($block_type, $settings);
        wp_set_current_user(self::$admin_id);
        $request  = new WP_REST_Request('GET', '/wp/v2/block-types/' . $block_type);
        $response = rest_get_server()->dispatch($request);
        $data     = $response->get_data();
        $this->assertSameSets(
            $settings['editor_script'],
            $data['editor_script_handles'],
            "Endpoint doesn't return correct array for editor_script_handles."
        );
        $this->assertSameSets(
            $settings['script'],
            $data['script_handles'],
            "Endpoint doesn't return correct array for script_handles."
        );
        $this->assertSameSets(
            $settings['view_script'],
            $data['view_script_handles'],
            "Endpoint doesn't return correct array for view_script_handles."
        );
        $this->assertSameSets(
            $settings['editor_style'],
            $data['editor_style_handles'],
            "Endpoint doesn't return correct array for editor_style_handles."
        );
        $this->assertSameSets(
            $settings['style'],
            $data['style_handles'],
            "Endpoint doesn't return correct array for style_handles."
        );
        // Deprecated properties.
        // Since the schema only allows strings or null (but no arrays), we return the first array item.
        // Deprecated properties.
        $this->assertSame(
            'hello',
            $data['editor_script'],
            "Endpoint doesn't return first array element for editor_script."
        );
        $this->assertSame(
            'gutenberg',
            $data['script'],
            "Endpoint doesn't return first array element for script."
        );
        $this->assertSame(
            'foo',
            $data['view_script'],
            "Endpoint doesn't return first array element for view_script."
        );
        $this->assertSame(
            'guten',
            $data['editor_style'],
            "Endpoint doesn't return first array element for editor_style."
        );
        $this->assertSame(
            'out',
            $data['style'],
            "Endpoint doesn't return first array element for style."
        );
    }

    public function test_get_variation()
    {
        $block_type = 'fake/variations';
        $settings   = [
            'title'       => 'variations block test',
            'description' => 'a variations block test',
            'attributes'  => [ 'kind' => [ 'type' => 'string' ] ],
            'variations'  => [
                [
                    'name'        => 'variation',
                    'title'       => 'variation title',
                    'description' => 'variation description',
                    'category'    => 'media',
                    'icon'        => 'checkmark',
                    'attributes'  => [ 'kind' => 'foo' ],
                    'isDefault'   => true,
                    'example'     => [ 'attributes' => [ 'kind' => 'example' ] ],
                    'scope'       => [ 'inserter', 'block' ],
                    'keywords'    => [ 'dogs', 'cats', 'mice' ],
                    'innerBlocks' => [
                        [
                            'name'       => 'fake/bar',
                            'attributes' => [ 'label' => 'hi' ],
                        ],
                    ],
                ],
            ],
        ];
        register_block_type($block_type, $settings);
        wp_set_current_user(self::$admin_id);
        $request  = new WP_REST_Request('GET', '/wp/v2/block-types/' . $block_type);
        $response = rest_get_server()->dispatch($request);
        $data     = $response->get_data();
        $this->assertSame($block_type, $data['name']);
        $this->assertArrayHasKey('variations', $data);
        $this->assertCount(1, $data['variations']);
        $variation = $data['variations'][0];
        $this->assertSame('variation title', $variation['title']);
        $this->assertSame('variation description', $variation['description']);
        $this->assertSame('media', $variation['category']);
        $this->assertSame('checkmark', $variation['icon']);
        $this->assertSameSets([ 'inserter', 'block' ], $variation['scope']);
        $this->assertSameSets([ 'dogs', 'cats', 'mice' ], $variation['keywords']);
        $this->assertSameSets([ 'attributes' => [ 'kind' => 'example' ] ], $variation['example']);
        $this->assertSameSets(
            [
                [
                    'name'       => 'fake/bar',
                    'attributes' => [ 'label' => 'hi' ],
                ],
            ],
            $variation['innerBlocks']
        );
        $this->assertSameSets(
            [ 'kind' => 'foo' ],
            $variation['attributes']
        );
    }

    /**
     * @ticket 47620
     * @ticket 57585
     * @ticket 59346
     * @ticket 60403
     */
    public function test_get_item_schema()
    {
        wp_set_current_user(self::$admin_id);
        $request    = new WP_REST_Request('OPTIONS', '/wp/v2/block-types');
        $response   = rest_get_server()->dispatch($request);
        $data       = $response->get_data();
        $properties = $data['schema']['properties'];
        $this->assertCount(33, $properties);
        $this->assertArrayHasKey('api_version', $properties);
        $this->assertArrayHasKey('name', $properties);
        $this->assertArrayHasKey('title', $properties);
        $this->assertArrayHasKey('category', $properties);
        $this->assertArrayHasKey('parent', $properties);
        $this->assertArrayHasKey('ancestor', $properties);
        $this->assertArrayHasKey('allowed_blocks', $properties);
        $this->assertArrayHasKey('icon', $properties);
        $this->assertArrayHasKey('description', $properties);
        $this->assertArrayHasKey('keywords', $properties);
        $this->assertArrayHasKey('textdomain', $properties);
        $this->assertArrayHasKey('attributes', $properties);
        $this->assertArrayHasKey('provides_context', $properties);
        $this->assertArrayHasKey('uses_context', $properties);
        $this->assertArrayHasKey('selectors', $properties, 'schema must contain selectors');
        $this->assertArrayHasKey('supports', $properties);
        $this->assertArrayHasKey('styles', $properties);
        $this->assertArrayHasKey('example', $properties);
        $this->assertArrayHasKey('variations', $properties);
        $this->assertArrayHasKey('block_hooks', $properties);
        $this->assertArrayHasKey('editor_script_handles', $properties);
        $this->assertArrayHasKey('script_handles', $properties);
        $this->assertArrayHasKey('view_script_handles', $properties);
        $this->assertArrayHasKey('view_script_module_ids', $properties);
        $this->assertArrayHasKey('editor_style_handles', $properties);
        $this->assertArrayHasKey('style_handles', $properties);
        $this->assertArrayHasKey('view_style_handles', $properties, 'schema must contain view_style_handles');
        $this->assertArrayHasKey('is_dynamic', $properties);
        // Deprecated properties.
        $this->assertArrayHasKey('editor_script', $properties);
        $this->assertArrayHasKey('script', $properties);
        $this->assertArrayHasKey('view_script', $properties);
        $this->assertArrayHasKey('editor_style', $properties);
        $this->assertArrayHasKey('style', $properties);
    }

    /**
     * @dataProvider data_readable_http_methods
     * @ticket 56481
     *
     * @param string $method The HTTP method to use.
     */
    public function test_get_item_should_allow_adding_headers_via_filter($method)
    {
        $block_name = 'fake/test';
        wp_set_current_user(self::$admin_id);

        $hook_name = 'rest_prepare_block_type';
        $filter    = new MockAction();
        $callback  = [ $filter, 'filter' ];
        add_filter($hook_name, $callback);
        $header_filter = new class () {
            public static function add_custom_header($response)
            {
                $response->header('X-Test-Header', 'Test');

                return $response;
            }
        };
        add_filter($hook_name, [ $header_filter, 'add_custom_header' ]);
        $request  = new WP_REST_Request($method, '/wp/v2/block-types/' . $block_name);
        $response = rest_get_server()->dispatch($request);
        remove_filter($hook_name, $callback);
        remove_filter($hook_name, [ $header_filter, 'add_custom_header' ]);

        $this->assertSame(200, $response->get_status(), 'The response status should be 200.');
        $this->assertSame(1, $filter->get_call_count(), 'The "' . $hook_name . '" filter was called when it should not be for HEAD requests.');
        $headers = $response->get_headers();
        $this->assertArrayHasKey('X-Test-Header', $headers, 'The "X-Test-Header" header should be present in the response.');
        $this->assertSame('Test', $headers['X-Test-Header'], 'The "X-Test-Header" header value should be equal to "Test".');
        if ('HEAD' !== $method) {
            return null;
        }
        $this->assertSame([], $response->get_data(), 'The server should not generate a body in response to a HEAD request.');
    }

    /**
     * Data provider intended to provide HTTP method names for testing GET and HEAD requests.
     *
     * @return array
     */
    public static function data_readable_http_methods()
    {
        return [
            'GET request'  => [ 'GET' ],
            'HEAD request' => [ 'HEAD' ],
        ];
    }

    /**
     * @ticket 56481
     */
    public function test_get_items_with_head_request_should_not_prepare_block_type_data()
    {
        wp_set_current_user(self::$admin_id);
        $request  = new WP_REST_Request('HEAD', '/wp/v2/block-types');
        $response = rest_get_server()->dispatch($request);
        $this->assertSame(200, $response->get_status(), 'The response status should be 200.');
        $this->assertSame([], $response->get_data(), 'The server should not generate a body in response to a HEAD request.');
    }

    /**
     * @dataProvider data_head_request_with_specified_fields_returns_success_response
     * @ticket 56481
     *
     * @param string $path The path to test.
     */
    public function test_head_request_with_specified_fields_returns_success_response($path)
    {
        wp_set_current_user(self::$admin_id);
        $request = new WP_REST_Request('HEAD', $path);
        $request->set_param('_fields', 'title');
        $server   = rest_get_server();
        $response = $server->dispatch($request);
        add_filter('rest_post_dispatch', 'rest_filter_response_fields', 10, 3);
        $response = apply_filters('rest_post_dispatch', $response, $server, $request);
        remove_filter('rest_post_dispatch', 'rest_filter_response_fields', 10);

        $this->assertSame(200, $response->get_status(), 'The response status should be 200.');
    }

    /**
     * Data provider intended to provide paths for testing HEAD requests.
     *
     * @return array
     */
    public static function data_head_request_with_specified_fields_returns_success_response()
    {
        return [
            'get_item request'  => [ '/wp/v2/block-types/fake/test' ],
            'get_items request' => [ '/wp/v2/block-types' ],
        ];
    }

    /**
     * @dataProvider data_readable_http_methods
     * @ticket 47620
     * @ticket 56481
     *
     * @param string $method HTTP method to use.
     */
    public function test_get_items_wrong_permission($method)
    {
        wp_set_current_user(self::$subscriber_id);
        $request  = new WP_REST_Request($method, '/wp/v2/block-types');
        $response = rest_get_server()->dispatch($request);
        $this->assertErrorResponse('rest_block_type_cannot_view', $response, 403);
    }

    /**
     * @dataProvider data_readable_http_methods
     * @ticket 47620
     * @ticket 56481
     *
     * @param string $method HTTP method to use.
     */
    public function test_get_item_wrong_permission($method)
    {
        wp_set_current_user(self::$subscriber_id);
        $request  = new WP_REST_Request($method, '/wp/v2/block-types/fake/test');
        $response = rest_get_server()->dispatch($request);
        $this->assertErrorResponse('rest_block_type_cannot_view', $response, 403);
    }

    /**
     * @dataProvider data_readable_http_methods
     * @ticket 47620
     * @ticket 56481
     *
     * @param string $method HTTP method to use.
     */
    public function test_get_items_no_permission($method)
    {
        wp_set_current_user(0);
        $request  = new WP_REST_Request($method, '/wp/v2/block-types');
        $response = rest_get_server()->dispatch($request);
        $this->assertErrorResponse('rest_block_type_cannot_view', $response, 401);
    }

    /**
     * @dataProvider data_readable_http_methods
     * @ticket 47620
     * @ticket 56481
     *
     * @param string $method HTTP method to use.
     */
    public function test_get_item_no_permission($method)
    {
        wp_set_current_user(0);
        $request  = new WP_REST_Request($method, '/wp/v2/block-types/fake/test');
        $response = rest_get_server()->dispatch($request);
        $this->assertErrorResponse('rest_block_type_cannot_view', $response, 401);
    }

    /**
     * @dataProvider data_readable_http_methods
     * @ticket 47620
     * @ticket 56481
     *
     * @param string $method HTTP method to use.
     */
    public function test_prepare_item()
    {
        $registry = new WP_Block_Type_Registry();
        $settings = [
            'icon'            => 'text',
            'render_callback' => '__return_null',
        ];
        $registry->register('fake/line', $settings);
        $block_type = $registry->get_registered('fake/line');
        $endpoint   = new WP_REST_Block_Types_Controller();
        $request    = new WP_REST_Request();
        $request->set_param('context', 'edit');
        $response = $endpoint->prepare_item_for_response($block_type, $request);
        $this->check_block_type_object($block_type, $response->get_data(), $response->get_links());
    }

    /**
     * @ticket 47620
     */
    public function test_prepare_item_limit_fields()
    {
        $registry = new WP_Block_Type_Registry();
        $settings = [
            'icon'            => 'text',
            'render_callback' => '__return_null',
        ];
        $registry->register('fake/line', $settings);
        $block_type = $registry->get_registered('fake/line');
        $request    = new WP_REST_Request();
        $endpoint   = new WP_REST_Block_Types_Controller();
        $request->set_param('context', 'edit');
        $request->set_param('_fields', 'name');
        $response = $endpoint->prepare_item_for_response($block_type, $request);
        $this->assertSame(
            [
                'name',
            ],
            array_keys($response->get_data())
        );
    }

    /**
     * Util check block type object against.
     *
     * @since 5.5.0
     * @since 6.4.0 Added the `block_hooks` extra field.
     *
     * @param WP_Block_Type $block_type Sample block type.
     * @param array         $data Data to compare against.
     * @param array         $links Links to compare again.
     */
    protected function check_block_type_object($block_type, $data, $links)
    {
        // Test data.
        $this->assertSame($data['attributes'], $block_type->get_attributes());
        $this->assertSame($data['is_dynamic'], $block_type->is_dynamic());

        $extra_fields = [
            'api_version',
            'name',
            'title',
            'category',
            'parent',
            'ancestor',
            'allowedBlocks',
            'icon',
            'description',
            'keywords',
            'textdomain',
            'provides_context',
            'uses_context',
            'selectors',
            'supports',
            'styles',
            'example',
            'variations',
            'block_hooks',
            'editor_script_handles',
            'script_handles',
            'view_script_handles',
            'view_script_module_ids',
            'editor_style_handles',
            'style_handles',
            // Deprecated fields.
            'editor_script',
            'script',
            'view_script',
            'editor_style',
            'style',
        ];

        foreach ($extra_fields as $extra_field) {
            if (isset($block_type->$extra_field)) {
                $this->assertSame($data[ $extra_field ], $block_type->$extra_field);
            }
        }

        // Test links.
        $this->assertSame(rest_url('wp/v2/block-types'), $links['collection'][0]['href']);
        $this->assertSame(rest_url('wp/v2/block-types/' . $block_type->name), $links['self'][0]['href']);
        if ($block_type->is_dynamic()) {
            $this->assertArrayHasKey('https://api.w.org/render-block', $links);
        }
    }

    /**
     * @ticket 59969
     */
    public function test_variation_callback()
    {
        $block_type = 'test/block';
        $settings   = [
            'title'              => true,
            'variation_callback' => [ $this, 'mock_variation_callback' ],
        ];
        register_block_type($block_type, $settings);
        wp_set_current_user(self::$admin_id);
        $request  = new WP_REST_Request('GET', '/wp/v2/block-types/' . $block_type);
        $response = rest_get_server()->dispatch($request);
        $data     = $response->get_data();
        $this->assertSameSets($this->mock_variation_callback(), $data['variations']);
    }

    /**
     * Mock variation callback.
     *
     * @return array
     */
    public function mock_variation_callback()
    {
        return [
            [ 'name' => 'var1' ],
            [ 'name' => 'var2' ],
        ];
    }

    /**
     * The create_item() method does not exist for block types.
     *
     * @doesNotPerformAssertions
     */
    public function test_create_item()
    {
        // Controller does not implement create_item().
    }

    /**
     * The update_item() method does not exist for block types.
     *
     * @doesNotPerformAssertions
     */
    public function test_update_item()
    {
        // Controller does not implement create_item().
    }

    /**
     * The delete_item() method does not exist for block types.
     *
     * @doesNotPerformAssertions
     */
    public function test_delete_item()
    {
        // Controller does not implement delete_item().
    }
}
