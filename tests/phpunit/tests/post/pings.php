<?php

declare(strict_types=1);

/**
 * @group post
 * @group ping
 */
class Tests_Post_Pings extends WP_UnitTestCase
{
    public function test_returns_to_ping_sites_from_post_id()
    {
        $post_id = self::factory()->post->create(
            [
                'to_ping' => 'http://www.example.com
					http://www.otherexample.com',
            ]
        );
        $this->assertSame([ 'http://www.example.com', 'http://www.otherexample.com' ], get_to_ping($post_id));
    }

    public function test_returns_to_ping_sites_from_post_object()
    {
        $post_id = self::factory()->post->create(
            [
                'to_ping' => 'http://www.example.com
					http://www.otherexample.com',
            ]
        );
        $post    = get_post($post_id);
        $this->assertSame([ 'http://www.example.com', 'http://www.otherexample.com' ], get_to_ping($post));
    }

    public function test_returns_pinged_sites_from_post_id()
    {
        $post_id = self::factory()->post->create([ 'pinged' => 'foo bar baz' ]);
        $this->assertSame([ 'foo', 'bar', 'baz' ], get_pung($post_id));
    }

    public function test_returns_pinged_sites_from_post_object()
    {
        $post_id = self::factory()->post->create([ 'pinged' => 'foo bar baz' ]);
        $post    = get_post($post_id);
        $this->assertSame([ 'foo', 'bar', 'baz' ], get_pung($post));
    }

    public function test_add_ping_with_post_id()
    {
        $post_id = self::factory()->post->create();
        add_ping($post_id, 'foo');
        add_ping($post_id, 'bar');
        add_ping($post_id, 'baz');
        $this->assertSame([ 'foo', 'bar', 'baz' ], get_pung($post_id));
    }

    public function test_add_ping_array_with_post_id()
    {
        $post_id = self::factory()->post->create();
        add_ping($post_id, [ 'foo', 'bar', 'baz' ]);
        $this->assertSame([ 'foo', 'bar', 'baz' ], get_pung($post_id));
    }

    public function test_add_ping_with_post_object()
    {
        $post_id = self::factory()->post->create();
        $post    = get_post($post_id);
        add_ping($post, 'foo');
        $this->assertSame([ 'foo' ], get_pung($post_id));
    }
}
