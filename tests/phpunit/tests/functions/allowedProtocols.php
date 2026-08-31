<?php

declare(strict_types=1);

/**
 * @group formatting
 * @group functions
 *
 * @covers ::wp_allowed_protocols
 */
class Tests_Functions_AllowedProtocols extends WP_UnitTestCase
{
    /**
     * @ticket 19354
     */
    public function test_data_is_not_an_allowed_protocol()
    {
        $this->assertNotContains('data', wp_allowed_protocols());
    }

    public function test_allowed_protocol_has_an_example()
    {
        $example_protocols = [];
        foreach ($this->data_example_urls() as $example) {
            $example_protocols[] = $example[0];
        }
        $this->assertSameSets($example_protocols, wp_allowed_protocols());
    }

    /**
     * @depends test_allowed_protocol_has_an_example
     * @dataProvider data_example_urls
     *
     * @param string The scheme.
     * @param string Example URL.
     */
    public function test_allowed_protocols($protocol, $url)
    {
        $this->assertSame($url, esc_url($url, $protocol));
        $this->assertSame($url, esc_url($url, wp_allowed_protocols()));
    }

    /**
     * Data provider.
     *
     * @link http://www.iana.org/assignments/uri-schemes/uri-schemes.xhtml
     *
     * @return array[]
     */
    public function data_example_urls()
    {
        return [
            [ 'http', 'http://example.com' ],                                 // RFC7230
            [ 'https', 'https://example.com' ],                               // RFC7230
            [ 'ftp', 'ftp://example.com' ],                                   // RFC1738
            [ 'ftps', 'ftps://example.com' ],
            [ 'mailto', 'mailto://someone@example.com' ],                     // RFC6068
            [ 'news', 'news://news.server.example/example.group.this' ],      // RFC5538
            [ 'irc', 'irc://example.com/wordpress' ],
            [ 'irc6', 'irc6://example.com/wordpress' ],
            [ 'ircs', 'ircs://example.com/wordpress' ],
            [ 'gopher', 'gopher://example.com/7a_gopher_selector%09foobar' ], // RFC4266
            [ 'nntp', 'nntp://news.server.example/example.group.this' ],      // RFC5538
            [ 'feed', 'feed://example.com/rss.xml' ],
            [ 'telnet', 'telnet://user:password@example.com:80/' ],           // RFC4248
            [ 'mms', 'mms://example.com:80/path' ],
            [ 'rtsp', 'rtsp://media.example.com:554/wordpress/audiotrack' ],  // RFC2326
            [ 'svn', 'svn://core.svn.wordpress.org/' ],
            [ 'tel', 'tel:+1-234-567-8910' ],                                 // RFC3966
            [ 'sms', 'sms:+1-234-567-8910' ],                                 // RFC3966
            [ 'fax', 'fax:+123.456.78910' ],                                  // RFC2806/RFC3966
            [ 'xmpp', 'xmpp://guest@example.com' ],                           // RFC5122
            [ 'webcal', 'webcal://example.com/calendar.ics' ],
            [ 'urn', 'urn:uuid:6e8bc430-9c3a-11d9-9669-0800200c9a66' ],       // RFC2141
        ];
    }
}
