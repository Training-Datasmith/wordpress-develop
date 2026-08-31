<?php

declare(strict_types=1);

/**
 * @group pluggable
 * @group formatting
 * @group redirect
 */
class Tests_Formatting_Redirect extends WP_UnitTestCase
{
    public function set_up()
    {
        parent::set_up();
        add_filter('home_url', [ $this, 'home_url' ]);
    }

    public function home_url()
    {
        return 'http://example.com/';
    }

    /**
     * @ticket 44317
     *
     * @dataProvider data_wp_redirect_bad_status_code
     *
     * @covers ::wp_redirect
     *
     * @param string $location The path or URL to redirect to.
     * @param int    $status   HTTP response status code to use.
     */
    public function test_wp_redirect_bad_status_code($location, $status)
    {
        $this->expectException('WPDieException');

        wp_redirect($location, $status);
    }

    public function data_wp_redirect_bad_status_code()
    {
        return [
            // Tests for bad arguments.
            [ '/wp-admin', 404 ],
            [ '/wp-admin', 410 ],
            [ '/wp-admin', 500 ],
            // Tests for condition.
            [ '/wp-admin', 299 ],
            [ '/wp-admin', 400 ],
        ];
    }

    /**
     * @covers ::wp_sanitize_redirect
     */
    public function test_wp_sanitize_redirect()
    {
        $this->assertSame('http://example.com/watchthelinefeedgo', wp_sanitize_redirect('http://example.com/watchthelinefeed%0Ago'));
        $this->assertSame('http://example.com/watchthelinefeedgo', wp_sanitize_redirect('http://example.com/watchthelinefeed%0ago'));
        $this->assertSame('http://example.com/watchthecarriagereturngo', wp_sanitize_redirect('http://example.com/watchthecarriagereturn%0Dgo'));
        $this->assertSame('http://example.com/watchthecarriagereturngo', wp_sanitize_redirect('http://example.com/watchthecarriagereturn%0dgo'));
        $this->assertSame('http://example.com/watchtheallowedcharacters-~+_.?#=&;,/:%!*stay', wp_sanitize_redirect('http://example.com/watchtheallowedcharacters-~+_.?#=&;,/:%!*stay'));
        $this->assertSame('http://example.com/watchtheutf8convert%F0%9D%8C%86', wp_sanitize_redirect("http://example.com/watchtheutf8convert\xf0\x9d\x8c\x86"));
        // Nesting checks.
        $this->assertSame('http://example.com/watchthecarriagereturngo', wp_sanitize_redirect('http://example.com/watchthecarriagereturn%0%0ddgo'));
        $this->assertSame('http://example.com/watchthecarriagereturngo', wp_sanitize_redirect('http://example.com/watchthecarriagereturn%0%0DDgo'));
        $this->assertSame('http://example.com/whyisthisintheurl/?param[1]=foo', wp_sanitize_redirect('http://example.com/whyisthisintheurl/?param[1]=foo'));
        $this->assertSame('http://[2606:2800:220:6d:26bf:1447:aa7]/', wp_sanitize_redirect('http://[2606:2800:220:6d:26bf:1447:aa7]/'));
        $this->assertSame('http://example.com/search.php?search=(amistillhere)', wp_sanitize_redirect('http://example.com/search.php?search=(amistillhere)'));
        $this->assertSame('http://example.com/@username', wp_sanitize_redirect('http://example.com/@username'));
    }

    /**
     * @ticket 36998
     *
     * @covers ::wp_sanitize_redirect
     */
    public function test_wp_sanitize_redirect_should_encode_spaces()
    {
        $this->assertSame('http://example.com/test%20spaces', wp_sanitize_redirect('http://example.com/test%20spaces'));
        $this->assertSame('http://example.com/test%20spaces%20in%20url', wp_sanitize_redirect('http://example.com/test spaces in url'));
    }

    /**
     * @dataProvider data_wp_validate_redirect_valid_url
     *
     * @covers ::wp_validate_redirect
     *
     * @param string $url      Redirect requested.
     * @param string $expected Expected destination.
     */
    public function test_wp_validate_redirect_valid_url($url, $expected)
    {
        $this->assertSame($expected, wp_validate_redirect($url));
    }

    public function data_wp_validate_redirect_valid_url()
    {
        return [
            [ 'http://example.com', 'http://example.com' ],
            [ 'http://example.com/', 'http://example.com/' ],
            [ 'https://example.com/', 'https://example.com/' ],
            [ '//example.com', 'http://example.com' ],
            [ '//example.com/', 'http://example.com/' ],
            [ 'http://example.com/?foo=http://example.com/', 'http://example.com/?foo=http://example.com/' ],
            [ 'http://user@example.com/', 'http://user@example.com/' ],
            [ 'http://user:@example.com/', 'http://user:@example.com/' ],
            [ 'http://user:pass@example.com/', 'http://user:pass@example.com/' ],
            [ " \t\n\r\0\x08\x0Bhttp://example.com", 'http://example.com' ],
            [ " \t\n\r\0\x08\x0B//example.com", 'http://example.com' ],
        ];
    }

    /**
     * @dataProvider data_wp_validate_redirect_invalid_url
     *
     * @covers ::wp_validate_redirect
     *
     * @param string       $url      Redirect requested.
     * @param string|false $expected Optional. Expected destination. Default false.
     */
    public function test_wp_validate_redirect_invalid_url($url, $expected = false)
    {
        $this->assertSame($expected, wp_validate_redirect($url, false));
    }

    public function data_wp_validate_redirect_invalid_url()
    {
        return [
            // parse_url() fails.
            [ '', '' ],
            [ 'http://:' ],

            // Non-safelisted domain.
            [ 'http://non-safelisted.example/' ],

            // Non-safelisted domain (leading whitespace).
            [ " \t\n\r\0\x08\x0Bhttp://non-safelisted.example.com" ],
            [ " \t\n\r\0\x08\x0B//non-safelisted.example.com" ],

            // Unsupported schemes.
            [ 'data:text/plain;charset=utf-8,Hello%20World!' ],
            [ 'file:///etc/passwd' ],
            [ 'ftp://example.com/' ],

            // Malformed input.
            [ 'http:example.com' ],
            [ 'http:80' ],
            [ 'http://example.com:1234:5678/' ],
            [ 'http://user:pa:ss@example.com/' ],

            [ 'http://user@@example.com' ],
            [ 'http://user@:example.com' ],
            [ 'http://user?@example.com' ],
            [ 'http://user@?example.com' ],
            [ 'http://user#@example.com' ],
            [ 'http://user@#example.com' ],

            [ 'http://user@@example.com/' ],
            [ 'http://user@:example.com/' ],
            [ 'http://user?@example.com/' ],
            [ 'http://user@?example.com/' ],
            [ 'http://user#@example.com/' ],
            [ 'http://user@#example.com/' ],

            [ 'http://user:pass@@example.com' ],
            [ 'http://user:pass@:example.com' ],
            [ 'http://user:pass?@example.com' ],
            [ 'http://user:pass@?example.com' ],
            [ 'http://user:pass#@example.com' ],
            [ 'http://user:pass@#example.com' ],

            [ 'http://user:pass@@example.com/' ],
            [ 'http://user:pass@:example.com/' ],
            [ 'http://user:pass?@example.com/' ],
            [ 'http://user:pass@?example.com/' ],
            [ 'http://user:pass#@example.com/' ],
            [ 'http://user:pass@#example.com/' ],

            [ 'http://user.pass@@example.com' ],
            [ 'http://user.pass@:example.com' ],
            [ 'http://user.pass?@example.com' ],
            [ 'http://user.pass@?example.com' ],
            [ 'http://user.pass#@example.com' ],
            [ 'http://user.pass@#example.com' ],

            [ 'http://user.pass@@example.com/' ],
            [ 'http://user.pass@:example.com/' ],
            [ 'http://user.pass?@example.com/' ],
            [ 'http://user.pass@?example.com/' ],
            [ 'http://user.pass#@example.com/' ],
            [ 'http://user.pass@#example.com/' ],
        ];
    }

    /**
     * @ticket 47980
     * @dataProvider data_wp_validate_redirect_relative_url
     *
     * @covers ::wp_validate_redirect
     *
     * @param string $current_uri Current URI (i.e. path and query string only).
     * @param string $url         Redirect requested.
     * @param string $expected    Expected destination.
     */
    public function test_wp_validate_redirect_relative_url($current_uri, $url, $expected)
    {
        // Backup the global.
        $unset = false;
        if (! isset($_SERVER['REQUEST_URI'])) {
            $unset = true;
        } else {
            $backup_request_uri = $_SERVER['REQUEST_URI'];
        }

        // Set the global to current URI.
        $_SERVER['REQUEST_URI'] = $current_uri;

        $this->assertSame($expected, wp_validate_redirect($url, false));

        // Delete or reset the global as required.
        if ($unset) {
            unset($_SERVER['REQUEST_URI']);
        } else {
            $_SERVER['REQUEST_URI'] = $backup_request_uri;
        }
    }

    /**
     * Data provider for test_wp_validate_redirect_relative_url().
     *
     * @return array[] {
     *      string Current URI (i.e. path and query string only).
     *      string Redirect requested.
     *      string Expected destination.
     * }
     */
    public function data_wp_validate_redirect_relative_url()
    {
        return [
            [
                '/',
                'wp-login.php?loggedout=true',
                '/wp-login.php?loggedout=true',
            ],
            [
                '/src/',
                'wp-login.php?loggedout=true',
                '/src/wp-login.php?loggedout=true',
            ],
            [
                '/wp-admin/settings.php?page=my-plugin',
                './settings.php?page=my-plugin',
                '/wp-admin/./settings.php?page=my-plugin',
            ],
            [
                '/wp-admin/settings.php?page=my-plugin',
                '/wp-login.php',
                '/wp-login.php',
            ],
            [
                '/wp-admin/settings.php?page=my-plugin',
                '../wp-admin/admin.php?page=my-plugin',
                '/wp-admin/../wp-admin/admin.php?page=my-plugin',
            ],
            [
                '/2019/10/13/my-post',
                '../../',
                '/2019/10/13/../../',
            ],
            [
                '/2019/10/13/my-post',
                '/',
                '/',
            ],
        ];
    }
}
