<?php

declare(strict_types=1);

/**
 * @group formatting
 *
 * @covers ::wp_rel_nofollow
 */
class Tests_Formatting_wpRelNofollow extends WP_UnitTestCase
{
    /**
     * @ticket 9959
     */
    public function test_add_no_follow()
    {
        $content  = '<p>This is some cool <a href="/">Code</a></p>';
        $expected = '<p>This is some cool <a href="/" rel="nofollow">Code</a></p>';
        $this->assertEqualHTML($expected, stripslashes(wp_rel_nofollow($content)));
    }

    /**
     * @ticket 9959
     */
    public function test_convert_no_follow()
    {
        $content  = '<p>This is some cool <a href="/" rel="weird">Code</a></p>';
        $expected = '<p>This is some cool <a href="/" rel="weird nofollow">Code</a></p>';
        $this->assertEqualHTML($expected, stripslashes(wp_rel_nofollow($content)));
    }

    /**
     * @ticket 11360
     * @dataProvider data_wp_rel_nofollow
     */
    public function test_wp_rel_nofollow($input, $output, $expect_deprecation = false)
    {
        $this->assertEqualHTML($output, stripslashes(wp_rel_nofollow($input)));
    }

    public function data_wp_rel_nofollow()
    {
        $home_url_http  = set_url_scheme(home_url(), 'http');
        $home_url_https = set_url_scheme(home_url(), 'https');

        return [
            [
                '<a href="">Double Quotes</a>',
                '<a href="" rel="nofollow">Double Quotes</a>',
                true,
            ],
            [
                '<a href="https://wordpress.org">Double Quotes</a>',
                '<a href="https://wordpress.org" rel="nofollow">Double Quotes</a>',
            ],
            [
                "<a href='https://wordpress.org'>Single Quotes</a>",
                "<a href='https://wordpress.org' rel=\"nofollow\">Single Quotes</a>",
            ],
            [
                '<a href="https://wordpress.org" title="Title">Multiple attributes</a>',
                '<a href="https://wordpress.org" title="Title" rel="nofollow">Multiple attributes</a>',
            ],
            [
                '<a title="Title" href="https://wordpress.org">Multiple attributes</a>',
                '<a title="Title" href="https://wordpress.org" rel="nofollow">Multiple attributes</a>',
            ],
            [
                '<a data-someflag href="https://wordpress.org">Multiple attributes</a>',
                '<a data-someflag href="https://wordpress.org" rel="nofollow">Multiple attributes</a>',
            ],
            [
                '<a  data-someflag  title="Title"  href="https://wordpress.org" onclick=""  >Everything at once</a>',
                '<a  data-someflag  title="Title"  href="https://wordpress.org" onclick=""   rel="nofollow">Everything at once</a>',
            ],
            [
                '<a href="' . $home_url_http . '/some-url">Home URL (http)</a>',
                '<a href="' . $home_url_http . '/some-url">Home URL (http)</a>',
            ],
            [
                '<a href="' . $home_url_https . '/some-url">Home URL (https)</a>',
                '<a href="' . $home_url_https . '/some-url">Home URL (https)</a>',
            ],
        ];
    }

    public function test_append_no_follow_with_valueless_attribute()
    {
        $content  = '<p>This is some cool <a href="demo.com" download rel="hola">Code</a></p>';
        $expected = '<p>This is some cool <a href="demo.com" download rel="hola nofollow">Code</a></p>';
        $this->assertEqualHTML($expected, stripslashes(wp_rel_nofollow($content)));
    }
}
