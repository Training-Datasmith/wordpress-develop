<?php

declare(strict_types=1);

/**
 * @group canonical
 * @group rewrite
 * @group query
 *
 * @covers ::strip_fragment_from_url
 */
class Tests_Canonical_StripFragmentFromUrl extends WP_UnitTestCase
{
    /**
     * @dataProvider data_strip_fragment_from_url
     * @ticket 55333
     */
    public function test_strip_fragment_from_url($test_url, $expected)
    {
        $this->assertSame($expected, strip_fragment_from_url($test_url));
    }

    /**
     * Data provider for test_strip_fragment_from_url().
     *
     * @return array[] {
     *     Data to test with.
     *
     *     @type string $0 The test URL.
     *     @type string $1 The expected canonical URL.
     * }
     */
    public function data_strip_fragment_from_url()
    {
        return [
            [ '//example.com', '//example.com' ],
            [ 'http://example.com', 'http://example.com' ],
            [ 'https://example.com', 'https://example.com' ],
            [ 'https://example.com/', 'https://example.com/' ],
            [ 'https://example.com/?test', 'https://example.com/?test' ],
            [ 'https://example.com/?#test', 'https://example.com/' ],
            [ 'https://example.com/?#test#', 'https://example.com/' ],
        ];
    }
}
