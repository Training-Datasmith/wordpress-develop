<?php

declare(strict_types=1);
/**
 * Tests for the WP_URL_Pattern_Prefixer class.
 *
 * @package WordPress
 * @subpackage Speculative Loading
 */

/**
 * @group speculative-loading
 * @coversDefaultClass WP_URL_Pattern_Prefixer
 */
class Tests_Speculative_Loading_wpUrlPatternPrefixer extends WP_UnitTestCase
{
    /**
     * Tests prefixing URL path patterns with a consistent demo context.
     *
     * @ticket 62503
     * @covers ::prefix_path_pattern
     * @dataProvider data_prefix_path_pattern
     */
    public function test_prefix_path_pattern(string $base_path, string $path_pattern, string $expected)
    {
        $p = new WP_URL_Pattern_Prefixer([ 'demo' => $base_path ]);

        $this->assertSame(
            $expected,
            $p->prefix_path_pattern($path_pattern, 'demo')
        );
    }

    public static function data_prefix_path_pattern(): array
    {
        return [
            [ '/', '/my-page/', '/my-page/' ],
            [ '/', 'my-page/', '/my-page/' ],
            [ '/wp/', '/my-page/', '/wp/my-page/' ],
            [ '/wp/', 'my-page/', '/wp/my-page/' ],
            [ '/wp/', '/blog/2023/11/new-post/', '/wp/blog/2023/11/new-post/' ],
            [ '/wp/', 'blog/2023/11/new-post/', '/wp/blog/2023/11/new-post/' ],
            [ '/subdir', '/my-page/', '/subdir/my-page/' ],
            [ '/subdir', 'my-page/', '/subdir/my-page/' ],
            // Missing trailing slash still works, does not consider "cut-off" directory names.
            [ '/subdir', '/subdirectory/my-page/', '/subdir/subdirectory/my-page/' ],
            [ '/subdir', 'subdirectory/my-page/', '/subdir/subdirectory/my-page/' ],
            // A base path containing a : must be enclosed in braces to avoid confusion.
            [ '/scope:0/', '/*/foo', '{/scope\\:0}/*/foo' ],
        ];
    }

    /**
     * Tests the values of the default URL pattern contexts.
     *
     * @ticket 62503
     * @covers ::get_default_contexts
     */
    public function test_get_default_contexts()
    {
        $contexts = WP_URL_Pattern_Prefixer::get_default_contexts();

        $this->assertArrayHasKey('home', $contexts);
        $this->assertArrayHasKey('site', $contexts);
        $this->assertSame('/', $contexts['home']);
        $this->assertSame('/', $contexts['site']);
    }

    /**
     * Tests the values of the default URL pattern contexts when using subdirectories.
     *
     * @ticket 62503
     * @covers ::get_default_contexts
     * @dataProvider data_default_contexts_with_subdirectories
     */
    public function test_get_default_contexts_with_subdirectories(string $context, string $unescaped, string $expected)
    {
        add_filter(
            $context . '_url',
            static function () use ($unescaped) {
                return $unescaped;
            }
        );

        $contexts = WP_URL_Pattern_Prefixer::get_default_contexts();

        $this->assertArrayHasKey($context, $contexts);
        $this->assertSame($expected, $contexts[ $context ]);
    }

    public static function data_default_contexts_with_subdirectories(): array
    {
        return [
            [ 'home', 'https://example.com/subdir/', '/subdir/' ],
            [ 'site', 'https://example.com/subdir/wp/', '/subdir/wp/' ],
            // If the context URL has URL pattern special characters it may need escaping.
            [ 'home', 'https://example.com/scope:0.*/', '/scope\\:0.\\*/' ],
            [ 'site', 'https://example.com/scope:0.*/wp+/', '/scope\\:0.\\*/wp\\+/' ],
        ];
    }
}
