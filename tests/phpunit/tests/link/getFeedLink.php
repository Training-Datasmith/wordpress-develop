<?php

declare(strict_types=1);

/**
 * @group link
 * @covers ::get_feed_link
 */
class Tests_Link_GetFeedLink extends WP_UnitTestCase
{
    /**
     * @ticket 51839
     * @dataProvider data_plain_permastruct
     *
     * @param string $expected Expected suffix to home_url().
     * @param string $type     Feed type to request.
     */
    public function tests_plain_permastruct($expected, $type)
    {
        $this->set_permalink_structure('');

        $this->assertSame(home_url($expected), get_feed_link($type));
    }

    public function data_plain_permastruct()
    {
        return [
            [ '?feed=rss2', '' ],
            [ '?feed=atom', 'atom' ],
            [ '?feed=get-feed-link', 'get-feed-link' ],
            [ '?feed=comments-rss2', 'comments_rss2' ],
            [ '?feed=comments-atom', 'comments_atom' ],
        ];
    }

    /**
     * @ticket 51839
     * @dataProvider data_pretty_permastruct
     *
     * @param string $expected Expected suffix to home_url().
     * @param string $type     Feed type to request.
     */
    public function tests_pretty_permastruct($expected, $type)
    {
        $this->set_permalink_structure('/%year%/%monthnum%/%day%/%postname%/');

        $this->assertSame(home_url($expected), get_feed_link($type));
    }

    /**
     * @ticket 51839
     * @dataProvider data_pretty_permastruct
     *
     * @param string $expected Expected suffix to home_url().
     * @param string $type     Feed type to request.
     */
    public function tests_pretty_permastruct_with_prefix($expected, $type)
    {
        $this->set_permalink_structure('/archives/%post_id%/%postname%/');

        $this->assertSame(home_url($expected), get_feed_link($type));
    }

    public function data_pretty_permastruct()
    {
        return [
            [ '/feed/', '' ],
            [ '/feed/atom/', 'atom' ],
            [ '/feed/get-feed-link/', 'get-feed-link' ],
            [ '/comments/feed/', 'comments_rss2' ],
            [ '/comments/feed/atom/', 'comments_atom' ],
        ];
    }
}
