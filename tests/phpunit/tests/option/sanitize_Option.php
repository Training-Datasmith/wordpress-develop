<?php

declare(strict_types=1);

/**
 * @group option
 */
class Tests_Option_SanitizeOption extends WP_UnitTestCase
{
    /**
     * @dataProvider data_sanitize_option
     *
     * @covers ::sanitize_option
     */
    public function test_sanitize_option($option_name, $sanitized, $original)
    {
        $this->assertSame($sanitized, sanitize_option($option_name, $original));
    }
    /**
     * Data provider to test all of the sanitize_option() case
     *
     * Inner array params: $option_name, $sanitized, $original
     *
     * @return array
     */
    public function data_sanitize_option()
    {
        return [
            [ 'admin_email', 'mail@example.com', 'mail@example.com' ],
            [ 'admin_email', get_option('admin_email'), 'invalid' ],
            [ 'page_on_front', 0, 0 ],
            [ 'page_on_front', 10, '-10' ],
            [ 'posts_per_page', 10, 10 ],
            [ 'posts_per_page', -1, -1 ],
            [ 'posts_per_page', 2, -2 ],
            [ 'posts_per_page', 1, 'ten' ],
            [ 'default_ping_status', 'open', 'open' ],
            [ 'default_ping_status', 'closed', '' ],
            [ 'blogname', 'My Site', 'My Site' ],
            [ 'blogname', '&lt;i&gt;My Site&lt;/i&gt;', '<i>My Site</i>' ],
            [ 'blog_charset', 'UTF-8', 'UTF-8' ],
            [ 'blog_charset', 'charset', '">charset<"' ],
            [ 'blog_charset', '', null ],
            [ 'blog_public', 1, null ],
            [ 'blog_public', 1, '1' ],
            [ 'blog_public', -2, '-2' ],
            [ 'date_format', 'F j, Y', 'F j, Y' ],
            [ 'date_format', 'F j, Y', 'F j, <strong>Y</strong>' ],
            [ 'ping_sites', 'http://rpc.pingomatic.com/', 'http://rpc.pingomatic.com/' ],
            [ 'ping_sites', 'https://rpc.pingomatic.com/', 'https://rpc.pingomatic.com/' ],
            [ 'ping_sites', "http://www.example.com\nhttp://example.org", "www.example.com \n\texample.org\n\n" ],
            [ 'gmt_offset', '0', 0 ],
            [ 'gmt_offset', '1.5', '1.5' ],
            [ 'gmt_offset', '', null ],
            [ 'siteurl', 'http://example.org', 'http://example.org' ],
            [ 'siteurl', 'http://example.org/subdir', 'http://example.org/subdir' ],
            [ 'siteurl', get_option('siteurl'), '' ],
            [ 'home', 'http://example.org', 'http://example.org' ],
            [ 'home', 'https://example.org', 'https://example.org' ],
            [ 'home', 'http://localhost:8000', 'http://localhost:8000' ],
            [ 'home', get_option('home'), '' ],
            [ 'WPLANG', 0, 0 ],
            [ 'WPLANG', '', '' ],
            [
                'illegal_names',
                [ 'www', 'web', 'root', 'admin', 'main', 'invite', 'administrator', 'files' ],
                [ 'www', 'web', 'root', 'admin', 'main', 'invite', 'administrator', 'files' ],
            ],
            [
                'illegal_names',
                [ 'www', 'web', 'root', 'admin', 'main', 'invite', 'administrator', 'files' ],
                'www     web root admin main invite administrator files',
            ],
            [
                'banned_email_domains',
                [ 'mail.com', 'gmail.com' ],
                [ 'mail.com', 'gmail.com' ],
            ],
            [
                'banned_email_domains',
                [ 'mail.com' ],
                "mail.com\ngmail,com",
            ],
            [ 'timezone_string', 0, 0 ],
            [ 'timezone_string', 'Europe/London', 'Europe/London' ],
            [ 'timezone_string', get_option('timezone_string'), 'invalid' ],
            // @ticket 56468
            'deprecated timezone string is accepted as valid' => [
                'option_name' => 'timezone_string',
                'sanitized'   => 'America/Buenos_Aires',
                'original'    => 'America/Buenos_Aires',
            ],
            [ 'permalink_structure', '', '' ],
            [ 'permalink_structure', '/%year%/%20%postname%', '/%year%/ %postname%' ],
            [ 'default_role', 'subscriber', 'subscriber' ],
            [ 'default_role', 'subscriber', 'invalid' ],
            [ 'default_role', 'editor', 'editor' ],
            [ 'moderation_keys', 'string of words', 'string of words' ],
            [ 'moderation_keys', "one\ntwo three", "one\none\ntwo three" ],
        ];
    }

    /**
     * @dataProvider data_sanitize_option_upload_path
     *
     * @covers ::sanitize_option
     */
    public function test_sanitize_option_upload_path($provided, $expected)
    {
        $this->assertSame($expected, sanitize_option('upload_path', $provided));
    }

    public function data_sanitize_option_upload_path()
    {
        return [
            [ '<a href="http://www.example.com">Link</a>', 'Link' ],
            [ '<scr' . 'ipt>url</scr' . 'ipt>', 'url' ],
            [ '/path/to/things', '/path/to/things' ],
            [ '\path\to\things', '\path\to\things' ],
        ];
    }

    /**
     * @ticket 36122
     *
     * @covers ::sanitize_option
     */
    public function test_emoji_in_blogname_and_description()
    {
        global $wpdb;

        $value = "whee\xf0\x9f\x98\x88";

        if ('utf8mb4' === $wpdb->get_col_charset($wpdb->options, 'option_value')) {
            $expected = $value;
        } else {
            $expected = 'whee&#x1f608;';
        }

        $this->assertSame($expected, sanitize_option('blogname', $value));
        $this->assertSame($expected, sanitize_option('blogdescription', $value));
    }

    /**
     * @dataProvider data_sanitize_option_permalink_structure
     *
     * @covers ::sanitize_option
     * @covers ::get_settings_errors
     */
    public function test_sanitize_option_permalink_structure($provided, $expected, $valid)
    {
        global $wp_settings_errors;

        $old_wp_settings_errors = (array) $wp_settings_errors;

        $actual = sanitize_option('permalink_structure', $provided);
        $errors = get_settings_errors('permalink_structure');

        // Clear errors.
        $wp_settings_errors = $old_wp_settings_errors;

        if ($valid) {
            $this->assertEmpty($errors);
        } else {
            $this->assertNotEmpty($errors);
            $this->assertSame('invalid_permalink_structure', $errors[0]['code']);
        }

        $this->assertEquals($expected, $actual);
    }

    public function data_sanitize_option_permalink_structure()
    {
        return [
            [ '', '', true ],
            [ '%postname', false, false ],
            [ '%/%', false, false ],
            [ '%%%', false, false ],
            [ '%a%', '%a%', true ],
            [ '%postname%', '%postname%', true ],
            [ '/%postname%/', '/%postname%/', true ],
            [ '/%year%/%monthnum%/%day%/%postname%/', '/%year%/%monthnum%/%day%/%postname%/', true ],
            [ '/%year/%postname%/', '/%year/%postname%/', true ],
            [ new WP_Error('wpdb_get_table_charset_failure'), false, false ], // @ticket 53986
        ];
    }
}
