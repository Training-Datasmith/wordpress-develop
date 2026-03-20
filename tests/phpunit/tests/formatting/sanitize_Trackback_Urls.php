<?php

declare(strict_types=1);

/**
 * @group formatting
 *
 * @covers ::sanitize_trackback_urls
 */
class Tests_Formatting_SanitizeTrackbackUrls extends WP_UnitTestCase
{
    /**
     * @ticket 21624
     * @dataProvider data_sanitize_trackback_urls_with_multiple_urls
     */
    public function test_sanitize_trackback_urls_with_multiple_urls($separator)
    {
        $this->assertSame(
            "http://example.com\nhttp://example.org",
            sanitize_trackback_urls("http://example.com{$separator}http://example.org")
        );
    }

    public function data_sanitize_trackback_urls_with_multiple_urls()
    {
        return [
            [ "\r\n\t " ],
            [ "\r" ],
            [ "\n" ],
            [ "\t" ],
            [ ' ' ],
            [ '  ' ],
            [ "\n  " ],
            [ "\r\n" ],
        ];
    }
}
