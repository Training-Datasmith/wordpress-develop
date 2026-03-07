<?php

declare(strict_types=1);
/**
 * Non-transport-specific WP_Http Tests
 *
 * @group http
 */
class Tests_HTTP_HTTP extends WP_UnitTestCase
{
    public const FULL_TEST_URL = 'http://username:password@host.name:9090/path?arg1=value1&arg2=value2#anchor';

    /**
     * @ticket 20434
     * @ticket 56231
     *
     * @dataProvider data_make_absolute_url
     *
     * @covers WP_Http::make_absolute_url
     */
    public function test_make_absolute_url($relative_url, $absolute_url, $expected)
    {
        $actual = WP_Http::make_absolute_url($relative_url, $absolute_url);
        $this->assertSame($expected, $actual);
    }

    public function data_make_absolute_url()
    {
        // 0: The Location header, 1: The current URL, 3: The expected URL.
        return [
            // Absolute URL provided.
            [ 'http://site.com/', 'http://example.com/', 'http://site.com/' ],
            // No current URL provided.
            [ '/location', '', '/location' ],

            // No location provided.
            [ '', 'http://example.com', 'http://example.com/' ],

            // Location provided relative to site root.
            [ '/root-relative-link.ext', 'http://example.com/', 'http://example.com/root-relative-link.ext' ],
            [ '/root-relative-link.ext?with=query', 'http://example.com/index.ext?query', 'http://example.com/root-relative-link.ext?with=query' ],

            // Location provided relative to current file/directory.
            [ 'relative-file.ext', 'http://example.com/', 'http://example.com/relative-file.ext' ],
            [ 'relative-file.ext', 'http://example.com/filename', 'http://example.com/relative-file.ext' ],
            [ 'relative-file.ext', 'http://example.com/directory/', 'http://example.com/directory/relative-file.ext' ],

            // Location provided relative to current file/directory but in a parent directory.
            [ '../file-in-parent.ext', 'http://example.com', 'http://example.com/file-in-parent.ext' ],
            [ '../file-in-parent.ext', 'http://example.com/filename', 'http://example.com/file-in-parent.ext' ],
            [ '../file-in-parent.ext', 'http://example.com/directory/', 'http://example.com/file-in-parent.ext' ],
            [ '../file-in-parent.ext', 'http://example.com/directory/filename', 'http://example.com/file-in-parent.ext' ],

            // Location provided in multiple levels higher, including impossible to reach (../ below DOCROOT).
            [ '../../file-in-grand-parent.ext', 'http://example.com', 'http://example.com/file-in-grand-parent.ext' ],
            [ '../../file-in-grand-parent.ext', 'http://example.com/filename', 'http://example.com/file-in-grand-parent.ext' ],
            [ '../../file-in-grand-parent.ext', 'http://example.com/directory/', 'http://example.com/file-in-grand-parent.ext' ],
            [ '../../file-in-grand-parent.ext', 'http://example.com/directory/filename/', 'http://example.com/file-in-grand-parent.ext' ],
            [ '../../file-in-grand-parent.ext', 'http://example.com/directory1/directory2/filename', 'http://example.com/file-in-grand-parent.ext' ],

            // Query strings should attach, or replace existing query string.
            [ '?query=string', 'http://example.com', 'http://example.com/?query=string' ],
            [ '?query=string', 'http://example.com/file.ext', 'http://example.com/file.ext?query=string' ],
            [ '?query=string', 'http://example.com/file.ext?existing=query-string', 'http://example.com/file.ext?query=string' ],
            [ 'otherfile.ext?query=string', 'http://example.com/file.ext?existing=query-string', 'http://example.com/otherfile.ext?query=string' ],

            // A file with a leading dot.
            [ '.ext', 'http://example.com/', 'http://example.com/.ext' ],

            // URLs within URLs.
            [ '/expected', 'http://example.com/sub/http://site.com/sub/', 'http://example.com/expected' ],
            [ '/expected/http://site.com/sub/', 'http://example.com/', 'http://example.com/expected/http://site.com/sub/' ],

            // Schemeless URL's (not valid in HTTP Headers, but may be used elsewhere).
            [ '//example.com/sub/', 'https://example.net', 'https://example.com/sub/' ],

            // URLs with fragments.
            [ '/path#frag', 'http://example.org/', 'http://example.org/path#frag' ],
            [ '/path/#frag', 'http://example.org/', 'http://example.org/path/#frag' ],
            [ '/path#frag&ment=1', 'http://example.org/', 'http://example.org/path#frag&ment=1' ],
            [ '/path?query=string#frag', 'http://example.org/', 'http://example.org/path?query=string#frag' ],
            [ '/path?query=string%23frag', 'http://example.org/', 'http://example.org/path?query=string%23frag' ],
        ];
    }

    /**
     * @dataProvider data_wp_parse_url
     *
     * @covers ::wp_parse_url
     */
    public function test_wp_parse_url($url, $expected)
    {
        $actual = wp_parse_url($url);
        $this->assertSame($expected, $actual);
    }

    public function data_wp_parse_url()
    {
        // 0: The URL, 1: The expected resulting structure.
        return [
            [
                self::FULL_TEST_URL,
                [
                    'scheme'   => 'http',
                    'host'     => 'host.name',
                    'port'     => 9090,
                    'user'     => 'username',
                    'pass'     => 'password',
                    'path'     => '/path',
                    'query'    => 'arg1=value1&arg2=value2',
                    'fragment' => 'anchor',
                ],
            ],
            [
                'http://example.com/',
                [
                    'scheme' => 'http',
                    'host'   => 'example.com',
                    'path'   => '/',
                ],
            ],

            // Schemeless URL.
            [
                '//example.com/path/',
                [
                    'host' => 'example.com',
                    'path' => '/path/',
                ],
            ],
            [
                '//example.com/',
                [
                    'host' => 'example.com',
                    'path' => '/',
                ],
            ],
            [
                'http://example.com//path/',
                [
                    'scheme' => 'http',
                    'host'   => 'example.com',
                    'path'   => '//path/',
                ],
            ],

            // Scheme separator in the URL.
            [
                'http://example.com/http://example.net/',
                [
                    'scheme' => 'http',
                    'host'   => 'example.com',
                    'path'   => '/http://example.net/',
                ],
            ],
            [ '/path/http://example.net/', [ 'path' => '/path/http://example.net/' ] ],

            // IPv6 literals in schemeless URLs.
            [
                '//[::FFFF::127.0.0.1]/',
                [
                    'host' => '[::FFFF::127.0.0.1]',
                    'path' => '/',
                ],
            ],

            // PHP's parse_url() calls this an invalid url, we handle it as a path.
            [ '/://example.com/', [ 'path' => '/://example.com/' ] ],

            // Schemeless URL containing colons cause parse errors in PHP 7+.
            [
                '//fonts.googleapis.com/css?family=Open+Sans:400&subset=latin',
                [
                    'host'  => 'fonts.googleapis.com',
                    'path'  => '/css',
                    'query' => 'family=Open+Sans:400&subset=latin',
                ],
            ],
            [
                '//fonts.googleapis.com/css?family=Open+Sans:400',
                [
                    'host'  => 'fonts.googleapis.com',
                    'path'  => '/css',
                    'query' => 'family=Open+Sans:400',
                ],
            ],

            [ 'filenamefound', [ 'path' => 'filenamefound' ] ],

            // Empty string or non-string passed in.
            [ '', [ 'path' => '' ] ],
            [ 123, [ 'path' => '123' ] ],
        ];
        /*
         * Untestable edge cases in various PHP:
         * - ///example.com - Fails in PHP >= 5.4.7, assumed path in <5.4.7
         * - ://example.com - assumed path in PHP >= 5.4.7, fails in <5.4.7
         */
    }

    /**
     * @ticket 36356
     *
     * @covers ::wp_parse_url
     */
    public function test_wp_parse_url_with_default_component()
    {
        $actual = wp_parse_url(self::FULL_TEST_URL, -1);
        $this->assertSame(
            [
                'scheme'   => 'http',
                'host'     => 'host.name',
                'port'     => 9090,
                'user'     => 'username',
                'pass'     => 'password',
                'path'     => '/path',
                'query'    => 'arg1=value1&arg2=value2',
                'fragment' => 'anchor',
            ],
            $actual
        );
    }

    /**
     * @ticket 36356
     *
     * @dataProvider data_wp_parse_url_with_component
     *
     * @covers ::wp_parse_url
     */
    public function test_wp_parse_url_with_component($url, $component, $expected)
    {
        $actual = wp_parse_url($url, $component);
        $this->assertSame($expected, $actual);
    }

    public function data_wp_parse_url_with_component()
    {
        // 0: The URL, 1: The requested component, 2: The expected resulting structure.
        return [
            [ self::FULL_TEST_URL, PHP_URL_SCHEME, 'http' ],
            [ self::FULL_TEST_URL, PHP_URL_USER, 'username' ],
            [ self::FULL_TEST_URL, PHP_URL_PASS, 'password' ],
            [ self::FULL_TEST_URL, PHP_URL_HOST, 'host.name' ],
            [ self::FULL_TEST_URL, PHP_URL_PORT, 9090 ],
            [ self::FULL_TEST_URL, PHP_URL_PATH, '/path' ],
            [ self::FULL_TEST_URL, PHP_URL_QUERY, 'arg1=value1&arg2=value2' ],
            [ self::FULL_TEST_URL, PHP_URL_FRAGMENT, 'anchor' ],

            // Schemeless URL.
            [ '//example.com/path/', PHP_URL_HOST, 'example.com' ],
            [ '//example.com/path/', PHP_URL_PATH, '/path/' ],
            [ '//example.com/', PHP_URL_HOST, 'example.com' ],
            [ '//example.com/', PHP_URL_PATH, '/' ],
            [ 'http://example.com//path/', PHP_URL_HOST, 'example.com' ],
            [ 'http://example.com//path/', PHP_URL_PATH, '//path/' ],

            // Scheme separator in the URL.
            [ 'http://example.com/http://example.net/', PHP_URL_HOST, 'example.com' ],
            [ 'http://example.com/http://example.net/', PHP_URL_PATH, '/http://example.net/' ],
            [ '/path/http://example.net/', PHP_URL_HOST, null ],
            [ '/path/http://example.net/', PHP_URL_PATH, '/path/http://example.net/' ],

            // IPv6 literals in schemeless URLs.
            [ '//[::FFFF::127.0.0.1]/', PHP_URL_HOST, '[::FFFF::127.0.0.1]' ],
            [ '//[::FFFF::127.0.0.1]/', PHP_URL_PATH, '/' ],

            // PHP's parse_url() calls this an invalid URL, we handle it as a path.
            [ '/://example.com/', PHP_URL_PATH, '/://example.com/' ],

            // Schemeless URL containing colons cause parse errors in PHP 7+.
            [ '//fonts.googleapis.com/css?family=Open+Sans:400&subset=latin', PHP_URL_HOST, 'fonts.googleapis.com' ],
            [ '//fonts.googleapis.com/css?family=Open+Sans:400&subset=latin', PHP_URL_PORT, null ],
            [ '//fonts.googleapis.com/css?family=Open+Sans:400&subset=latin', PHP_URL_PATH, '/css' ],
            [ '//fonts.googleapis.com/css?family=Open+Sans:400&subset=latin', PHP_URL_QUERY, 'family=Open+Sans:400&subset=latin' ],
            [ '//fonts.googleapis.com/css?family=Open+Sans:400', PHP_URL_HOST, 'fonts.googleapis.com' ],  // 25
            [ '//fonts.googleapis.com/css?family=Open+Sans:400', PHP_URL_PORT, null ],
            [ '//fonts.googleapis.com/css?family=Open+Sans:400', PHP_URL_PATH, '/css' ],                  // 27
            [ '//fonts.googleapis.com/css?family=Open+Sans:400', PHP_URL_QUERY, 'family=Open+Sans:400' ], // 28

            // Empty string or non-string passed in.
            [ '', PHP_URL_PATH, '' ],
            [ '', PHP_URL_QUERY, null ],
            [ 123, PHP_URL_PORT, null ],
            [ 123, PHP_URL_PATH, '123' ],
        ];
    }

    /**
     * @ticket 35426
     *
     * @covers ::get_status_header_desc
     */
    public function test_http_response_code_constants()
    {
        global $wp_header_to_desc;

        $ref       = new ReflectionClass('WP_Http');
        $constants = $ref->getConstants();

        // This primes the `$wp_header_to_desc` global:
        get_status_header_desc(200);

        $this->assertSame(array_keys($wp_header_to_desc), array_values($constants));
    }

    /**
     * @ticket 37768
     *
     * @covers WP_Http::normalize_cookies
     */
    public function test_normalize_cookies_scalar_values()
    {
        $http = _wp_http_get_object();

        $cookies = [
            'x'   => 'foo',
            'y'   => 2,
            'z'   => 0.45,
            'foo' => [ 'bar' ],
        ];

        $cookie_jar = $http->normalize_cookies(
            [
                'x'   => 'foo',
                'y'   => 2,
                'z'   => 0.45,
                'foo' => [ 'bar' ],
            ]
        );

        $this->assertInstanceOf('WpOrg\Requests\Cookie\Jar', $cookie_jar);

        foreach (array_keys($cookies) as $cookie) {
            if ('foo' === $cookie) {
                $this->assertArrayNotHasKey($cookie, $cookie_jar);
            } else {
                $this->assertInstanceOf('WpOrg\Requests\Cookie', $cookie_jar[ $cookie ]);
            }
        }
    }

    /**
     * @ticket 36356
     *
     * @dataProvider data_get_component_from_parsed_url_array
     *
     * @covers ::wp_parse_url
     * @covers ::_get_component_from_parsed_url_array
     */
    public function test_get_component_from_parsed_url_array($url, $component, $expected)
    {
        $parts  = wp_parse_url($url);
        $actual = _get_component_from_parsed_url_array($parts, $component);
        $this->assertSame($expected, $actual);
    }

    public function data_get_component_from_parsed_url_array()
    {
        // 0: A URL, 1: PHP URL constant, 2: The expected result.
        return [
            [
                'http://example.com/',
                -1,
                [
                    'scheme' => 'http',
                    'host'   => 'example.com',
                    'path'   => '/',
                ],
            ],
            [
                'http://example.com/',
                -1,
                [
                    'scheme' => 'http',
                    'host'   => 'example.com',
                    'path'   => '/',
                ],
            ],
            [ 'http://example.com/', PHP_URL_HOST, 'example.com' ],
            [ 'http://example.com/', PHP_URL_USER, null ],
            [ 'http:///example.com', -1, false ],          // Malformed.
            [ 'http:///example.com', PHP_URL_HOST, null ], // Malformed.
        ];
    }

    /**
     * @ticket 36356
     *
     * @dataProvider data_wp_translate_php_url_constant_to_key
     *
     * @covers ::_wp_translate_php_url_constant_to_key
     */
    public function test_wp_translate_php_url_constant_to_key($input, $expected)
    {
        $actual = _wp_translate_php_url_constant_to_key($input);
        $this->assertSame($expected, $actual);
    }

    public function data_wp_translate_php_url_constant_to_key()
    {
        // 0: PHP URL constant, 1: The expected result.
        return [
            [ PHP_URL_SCHEME, 'scheme' ],
            [ PHP_URL_HOST, 'host' ],
            [ PHP_URL_PORT, 'port' ],
            [ PHP_URL_USER, 'user' ],
            [ PHP_URL_PASS, 'pass' ],
            [ PHP_URL_PATH, 'path' ],
            [ PHP_URL_QUERY, 'query' ],
            [ PHP_URL_FRAGMENT, 'fragment' ],

            // Test with non-PHP_URL_CONSTANT parameter.
            [ 'something', false ],
            [ ABSPATH, false ],
        ];
    }

    /**
     * Test that wp_http_validate_url validates URLs.
     *
     * @ticket 54331
     *
     * @dataProvider data_wp_http_validate_url_should_validate
     *
     * @covers ::wp_http_validate_url
     *
     * @param string       $url            The URL to validate.
     * @param false|string $cb_safe_ports  The name of the callback to http_allowed_safe_ports or false if none.
     *                                     Default false.
     * @param bool         $external_host  Whether or not the host is external.
     *                                     Default false.
     */
    public function test_wp_http_validate_url_should_validate($url, $cb_safe_ports = false, $external_host = false)
    {
        if ($external_host) {
            add_filter('http_request_host_is_external', '__return_true');
        }

        if ($cb_safe_ports) {
            add_filter('http_allowed_safe_ports', [ $this, $cb_safe_ports ]);
        }

        $this->assertSame($url, wp_http_validate_url($url));
    }

    /**
     * Data provider.
     *
     * @return array
     */
    public function data_wp_http_validate_url_should_validate()
    {
        return [
            'no port specified'                 => [
                'url' => 'http://example.com/caniload.php',
            ],
            'an external request when allowed'  => [
                'url'           => 'http://172.20.0.123/caniload.php',
                'cb_safe_ports' => false,
                'external_host' => true,
            ],
            'a port considered safe by default' => [
                'url' => 'https://example.com:8080/caniload.php',
            ],
            'a port considered safe by filter'  => [
                'url'           => 'https://example.com:81/caniload.php',
                'cb_safe_ports' => 'callback_custom_safe_ports',
            ],
        ];
    }

    /**
     * Tests that wp_http_validate_url validates a url that uses an unsafe port
     * but which matches the host and port used by the site's home url.
     *
     * @ticket 54331
     *
     * @covers ::wp_http_validate_url
     */
    public function test_wp_http_validate_url_should_validate_with_an_unsafe_port_when_the_host_and_port_match_the_home_url()
    {
        $original_home    = get_option('home');
        $home_parsed      = parse_url($original_home);
        $home_scheme_host = implode('://', array_slice($home_parsed, 0, 2));
        $home_modified    = $home_scheme_host . ':83';

        update_option('home', $home_modified);

        $url = $home_modified . '/caniload.php';
        $this->assertSame($url, wp_http_validate_url($url));

        update_option('home', $original_home);
    }

    /**
     * Test that wp_http_validate_url does not validate invalid URLs.
     *
     * @ticket 54331
     *
     * @dataProvider data_wp_http_validate_url_should_not_validate
     *
     * @covers ::wp_http_validate_url
     *
     * @param string       $url            The URL to validate.
     * @param false|string $cb_safe_ports  The name of the callback to http_allowed_safe_ports or false if none.
     *                                     Default false.
     * @param bool         $external_host  Whether or not the host is external.
     *                                     Default false.
     */
    public function test_wp_http_validate_url_should_not_validate($url, $cb_safe_ports = false, $external_host = false)
    {
        if ($external_host) {
            add_filter('http_request_host_is_external', '__return_true');
        }

        if ($cb_safe_ports) {
            add_filter('http_allowed_safe_ports', [ $this, $cb_safe_ports ]);
        }

        $this->assertFalse(wp_http_validate_url($url));
    }

    /**
     * Data provider.
     *
     * @return array
     */
    public function data_wp_http_validate_url_should_not_validate()
    {
        return [
            'url as false'                                 => [
                'url' => false,
            ],
            'url as null'                                  => [
                'url' => null,
            ],
            'url as int 0'                                 => [
                'url' => 0,
            ],
            'url as string 0'                              => [
                'url' => '0',
            ],
            'url as int 1'                                 => [
                'url' => 1,
            ],
            'url as string 1'                              => [
                'url' => '1',
            ],
            'url as array()'                               => [
                'url' => [],
            ],
            'an empty url'                                 => [
                'url' => '',
            ],
            'a url with a non-http/https protocol'         => [
                'url' => 'ftp://example.com:81/caniload.php',
            ],
            'a malformed url'                              => [
                'url' => 'http:///example.com:81/caniload.php',
            ],
            'a host that cannot be parsed'                 => [
                'url' => 'http:example.com/caniload.php',
            ],
            'login information'                            => [
                'url' => 'http://user:pass@example.com/caniload.php',
            ],
            'a host with invalid characters'               => [
                'url' => 'http://[exam]ple.com/caniload.php',
            ],
            'a host whose IPv4 address cannot be resolved' => [
                'url' => 'http://example.invalid/caniload.php',
            ],
            'an external request when not allowed'         => [
                'url'           => 'http://192.168.0.1/caniload.php',
                'external_host' => false,
            ],
            'a port not considered safe by default'        => [
                'url' => 'https://example.com:81/caniload.php',
            ],
            'a port not considered safe by filter'         => [
                'url'           => 'https://example.com:82/caniload.php',
                'cb_safe_ports' => 'callback_custom_safe_ports',
            ],
            'all safe ports removed by filter'             => [
                'url'           => 'https://example.com:81/caniload.php',
                'cb_safe_ports' => 'callback_remove_safe_ports',
            ],
        ];
    }

    public function callback_custom_safe_ports($ports)
    {
        return [ 81, 444, 8081 ];
    }

    public function callback_remove_safe_ports($ports)
    {
        return [];
    }

    /**
     * Test HTTP Redirects with multiple Location headers specified.
     *
     * Ensure the WP_Http::handle_redirects() method handles multiple Location headers
     * and the HTTP request it makes uses the last Location header.
     *
     * @ticket 16890
     * @ticket 57306
     *
     * @covers WP_Http::handle_redirects
     */
    public function test_multiple_location_headers()
    {
        $pre_http_request_filter_has_run = false;
        // Filter the response made by WP_Http::handle_redirects().
        add_filter(
            'pre_http_request',
            function ($response, $parsed_args, $url) use (&$pre_http_request_filter_has_run) {
                $pre_http_request_filter_has_run = true;

                // Assert the redirect URL is correct.
                $this->assertSame(
                    $url,
                    'http://example.com/?multiple-location-headers=1&redirected=two'
                );

                if ('http://example.com/?multiple-location-headers=1&redirected=two' === $url) {
                    $body = 'PASS';
                } else {
                    $body = 'FAIL';
                }

                return [
                    'headers'  => [],
                    'body'     => $body,
                    'response' => [
                        'code'    => 200,
                        'message' => 'OK',
                    ],
                    'cookies'  => [],
                    'filename' => null,
                ];
            },
            10,
            3
        );

        $headers = [
            'server'       => 'nginx',
            'date'         => 'Sun, 11 Dec 2022 23:11:22 GMT',
            'content-type' => 'text/html; charset=utf-8',
            'location'     => [
                'http://example.com/?multiple-location-headers=1&redirected=one',
                'http://example.com/?multiple-location-headers=1&redirected=two',
            ],
        ];

        // Test the tests: ensure multiple locations are passed to WP_Http::handle_redirects().
        $this->assertIsArray($headers['location'], 'Location header is expected to be an array.');
        $this->assertCount(2, $headers['location'], 'Location header is expected to contain two values.');

        $args = [
            'timeout'      => 30,
            '_redirection' => 3,
            'redirection'  => 2,
            'method'       => 'GET',
        ];

        $redirect_response = WP_Http::handle_redirects(
            'http://example.com/?multiple-location-headers=1',
            $args,
            [
                'headers'  => $headers,
                'body'     => '',
                'cookies'  => [],
                'filename' => null,
                'response' => [
                    'code'    => 302,
                    'message' => 'Found',
                ],
            ]
        );
        $this->assertSame('PASS', wp_remote_retrieve_body($redirect_response), 'Redirect response body is expected to be PASS.');
        $this->assertTrue($pre_http_request_filter_has_run, 'The pre_http_request filter is expected to run.');
    }

    /**
     * Test that WP_Http::normalize_cookies method correctly casts integer keys to string.
     * @ticket 58566
     *
     * @covers WP_Http::normalize_cookies
     */
    public function test_normalize_cookies_casts_integer_keys_to_string()
    {
        $http = _wp_http_get_object();

        $cookies = [
            '1'   => 'foo',
            2     => 'bar',
            'qux' => 7,
        ];

        $cookie_jar = $http->normalize_cookies($cookies);

        $this->assertInstanceOf('WpOrg\Requests\Cookie\Jar', $cookie_jar);

        foreach (array_keys($cookies) as $cookie) {
            if (is_string($cookie)) {
                $this->assertInstanceOf('WpOrg\Requests\Cookie', $cookie_jar[ $cookie ]);
            } else {
                $this->assertInstanceOf('WpOrg\Requests\Cookie', $cookie_jar[ (string) $cookie ]);
            }
        }
    }

    /**
     * Test that WP_Http::normalize_cookies method correctly casts integer cookie names to strings.
     * @ticket 58566
     *
     * @covers WP_Http::normalize_cookies
     */
    public function test_normalize_cookies_casts_cookie_name_integer_to_string()
    {
        $http = _wp_http_get_object();

        $cookies = [
            'foo' => new WP_Http_Cookie(
                [
                    'name'  => 1,
                    'value' => 'foo',
                ]
            ),
        ];

        $cookie_jar = $http->normalize_cookies($cookies);

        $this->assertInstanceOf('WpOrg\Requests\Cookie\Jar', $cookie_jar);
        $this->assertInstanceOf('WpOrg\Requests\Cookie', $cookie_jar['1']);
    }
}
