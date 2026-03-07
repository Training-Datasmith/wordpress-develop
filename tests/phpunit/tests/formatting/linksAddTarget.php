<?php

declare(strict_types=1);
/**
 * Tests for the links_add_target() function.
 *
 * @group formatting
 *
 * @covers ::links_add_target
 */
class Tests_Formatting_LinksAddTarget extends WP_UnitTestCase
{
    /**
     * @ticket 26164
     *
     * @dataProvider data_links_add_target
     */
    public function test_links_add_target($content, $target, $tags, $expected)
    {
        if (is_null($target)) {
            $this->assertSame($expected, links_add_target($content));
        } elseif (is_null($tags)) {
            $this->assertSame($expected, links_add_target($content, $target));
        } else {
            $this->assertSame($expected, links_add_target($content, $target, $tags));
        }
    }

    /**
     * Data provider.
     *
     * @return array {
     *     @type array {
     *         @type string     $content  String to search for links in.
     *         @type string     $target   The target to add to the links.
     *         @type array|null $tags     An array of tags to apply to.
     *         @type string     $expected Expected output.
     *     }
     * }
     */
    public function data_links_add_target()
    {
        return [
            [
                'MY CONTENT <div> SOME ADDITIONAL TEXT <a href="XYZ" src="ABC">LINK</a> HERE </div> END TEXT',
                null,
                null,
                'MY CONTENT <div> SOME ADDITIONAL TEXT <a href="XYZ" src="ABC" target="_blank">LINK</a> HERE </div> END TEXT',
            ],
            [
                'MY CONTENT <div> SOME ADDITIONAL TEXT <A href="XYZ" src="ABC">LINK</A> HERE </div> END TEXT',
                null,
                null,
                'MY CONTENT <div> SOME ADDITIONAL TEXT <A href="XYZ" src="ABC" target="_blank">LINK</A> HERE </div> END TEXT',
            ],
            [
                'MY CONTENT <div> SOME ADDITIONAL TEXT <a href="XYZ" src="ABC">LINK</a> HERE </div> <a href="XYZ"  >LINK</a>END TEXT',
                null,
                null,
                'MY CONTENT <div> SOME ADDITIONAL TEXT <a href="XYZ" src="ABC" target="_blank">LINK</a> HERE </div> <a href="XYZ"   target="_blank">LINK</a>END TEXT',
            ],
            [
                'MY CONTENT <div> SOME ADDITIONAL TEXT <a href="XYZ" src="ABC">LINK</a> HERE </div> <span>END TEXT</span>',
                '_top',
                null,
                'MY CONTENT <div> SOME ADDITIONAL TEXT <a href="XYZ" src="ABC" target="_top">LINK</a> HERE </div> <span>END TEXT</span>',
            ],
            [
                'MY CONTENT <div> SOME ADDITIONAL TEXT <a href="XYZ" src="ABC">LINK</a> HERE </div> <span>END TEXT</span>',
                '_top',
                [ 'span' ],
                'MY CONTENT <div> SOME ADDITIONAL TEXT <a href="XYZ" src="ABC">LINK</a> HERE </div> <span target="_top">END TEXT</span>',
            ],
            [
                'MY CONTENT <div> SOME ADDITIONAL TEXT <a href="XYZ" src="ABC">LINK</a> HERE </div> <span>END TEXT</span>',
                '_top',
                [ 'SPAN' ],
                'MY CONTENT <div> SOME ADDITIONAL TEXT <a href="XYZ" src="ABC">LINK</a> HERE </div> <span target="_top">END TEXT</span>',
            ],
            [
                'MY CONTENT <div> SOME ADDITIONAL TEXT <a href="XYZ" src="ABC">LINK</a> HERE </div> <span target="_top">END TEXT</span>',
                '_top',
                [ 'span', 'div' ],
                'MY CONTENT <div target="_top"> SOME ADDITIONAL TEXT <a href="XYZ" src="ABC">LINK</a> HERE </div> <span target="_top">END TEXT</span>',
            ],
            [
                'MY CONTENT <div target=\'ABC\'> SOME ADDITIONAL TEXT <a href="XYZ" src="ABC">LINK</a> HERE </div> <span target="xyz">END TEXT</span>',
                '_top',
                [ 'span', 'div' ],
                'MY CONTENT <div target="_top"> SOME ADDITIONAL TEXT <a href="XYZ" src="ABC">LINK</a> HERE </div> <span target="_top">END TEXT</span>',
            ],
            [
                'MY CONTENT <div> SOME ADDITIONAL TEXT <a href="XYZ" src="ABC">LINK</a> HERE </div> <span target="xyz" src="ABC">END TEXT</span>',
                '_top',
                [ 'span' ],
                'MY CONTENT <div> SOME ADDITIONAL TEXT <a href="XYZ" src="ABC">LINK</a> HERE </div> <span src="ABC" target="_top">END TEXT</span>',
            ],
            [
                'MY CONTENT <aside> SOME ADDITIONAL TEXT <a href="XYZ" src="ABC">LINK</a> HERE </aside> END TEXT',
                null,
                null,
                'MY CONTENT <aside> SOME ADDITIONAL TEXT <a href="XYZ" src="ABC" target="_blank">LINK</a> HERE </aside> END TEXT',
            ],
            [
                'MY CONTENT <aside class="_blank"> SOME ADDITIONAL TEXT <a href="XYZ" src="ABC">LINK</a> HERE </aside> END TEXT',
                null,
                null,
                'MY CONTENT <aside class="_blank"> SOME ADDITIONAL TEXT <a href="XYZ" src="ABC" target="_blank">LINK</a> HERE </aside> END TEXT',
            ],
            [
                'MY CONTENT <blockquote>SOME</blockquote> ADDITIONAL TEXT <b>LINK</b> HERE END TEXT',
                '_blank',
                [ 'b' ],
                'MY CONTENT <blockquote>SOME</blockquote> ADDITIONAL TEXT <b target="_blank">LINK</b> HERE END TEXT',
            ],
            [
                'MY CONTENT <blockquote target="_self">SOME</blockquote> ADDITIONAL TEXT <b>LINK</b> HERE END TEXT',
                '_blank',
                [ 'b' ],
                'MY CONTENT <blockquote target="_self">SOME</blockquote> ADDITIONAL TEXT <b target="_blank">LINK</b> HERE END TEXT',
            ],
        ];
    }
}
