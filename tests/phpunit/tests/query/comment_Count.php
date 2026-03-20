<?php

declare(strict_types=1);
/**
 * @group query
 */
class Tests_Query_CommentCount extends WP_UnitTestCase
{
    public static $post_ids = [];
    public $q;
    public static $post_type = 'page'; // Can be anything.

    public function set_up()
    {
        parent::set_up();
        unset($this->q);
        $this->q = new WP_Query();
    }

    public function tear_down()
    {
        unset($this->q);
        parent::tear_down();
    }

    public static function wpSetUpBeforeClass(WP_UnitTest_Factory $factory)
    {
        $post_id             = $factory->post->create(
            [
                'post_content' => '1 about',
                'post_type'    => self::$post_type,
            ]
        );
        self::$post_ids[1][] = $post_id;
        $factory->comment->create([ 'comment_post_ID' => $post_id ]);

        $post_id             = $factory->post->create(
            [
                'post_content' => '2 about',
                'post_type'    => self::$post_type,
            ]
        );
        self::$post_ids[4][] = $post_id;
        for ($i = 0; $i < 4; $i++) {
            $factory->comment->create([ 'comment_post_ID' => $post_id ]);
        }

        $post_id             = $factory->post->create(
            [
                'post_content' => '3 about',
                'post_type'    => self::$post_type,
            ]
        );
        self::$post_ids[5][] = $post_id;
        for ($i = 0; $i < 5; $i++) {
            $factory->comment->create([ 'comment_post_ID' => $post_id ]);
        }

        $post_id             = $factory->post->create(
            [
                'post_content' => '4 about',
                'post_type'    => self::$post_type,
            ]
        );
        self::$post_ids[5][] = $post_id;
        for ($i = 0; $i < 5; $i++) {
            $factory->comment->create([ 'comment_post_ID' => $post_id ]);
        }
    }

    private function helper_get_found_post_ids()
    {
        return wp_list_pluck($this->q->posts, 'ID');
    }

    public function test_operator_equals()
    {
        $args = [
            'post_type'      => self::$post_type,
            'posts_per_page' => -1,
            'comment_count'  => [
                'value'   => 4,
                'compare' => '=',
            ],
        ];
        $this->q->query($args);

        $found_post_ids = $this->helper_get_found_post_ids();

        $expected = self::$post_ids[4];

        $this->assertSameSets($expected, $found_post_ids);
    }

    public function test_operator_greater_than()
    {
        $args = [
            'post_type'      => self::$post_type,
            'posts_per_page' => -1,
            'comment_count'  => [
                'value'   => 4,
                'compare' => '>',
            ],
        ];
        $this->q->query($args);

        $found_post_ids = $this->helper_get_found_post_ids();

        $expected = self::$post_ids[5];

        $this->assertSameSets($expected, $found_post_ids);
    }

    public function test_operator_greater_than_no_results()
    {
        $args = [
            'post_type'      => self::$post_type,
            'posts_per_page' => -1,
            'comment_count'  => [
                'value'   => 6,
                'compare' => '>',
            ],
        ];
        $this->q->query($args);

        $found_post_ids = $this->helper_get_found_post_ids();

        $expected = [];

        $this->assertSameSets($expected, $found_post_ids);
    }
    public function test_operator_less_than()
    {
        $args = [
            'post_type'      => self::$post_type,
            'posts_per_page' => -1,
            'comment_count'  => [
                'value'   => 6,
                'compare' => '<',
            ],
        ];
        $this->q->query($args);

        $found_post_ids = $this->helper_get_found_post_ids();

        $expected = [];
        foreach (self::$post_ids[1] as $expected_id) {
            $expected[] = $expected_id;
        }
        foreach (self::$post_ids[4] as $expected_id) {
            $expected[] = $expected_id;
        }
        foreach (self::$post_ids[5] as $expected_id) {
            $expected[] = $expected_id;
        }

        $this->assertSameSets($expected, $found_post_ids);
    }

    public function test_operator_less_than_no_results()
    {
        $args = [
            'post_type'      => self::$post_type,
            'posts_per_page' => -1,
            'comment_count'  => [
                'value'   => 1,
                'compare' => '<',
            ],
        ];
        $this->q->query($args);

        $found_post_ids = $this->helper_get_found_post_ids();

        $expected = [];

        $this->assertSameSets($expected, $found_post_ids);
    }

    public function test_operator_not_equal()
    {
        $args = [
            'post_type'      => self::$post_type,
            'posts_per_page' => -1,
            'comment_count'  => [
                'value'   => 15,
                'compare' => '!=',
            ],
        ];
        $this->q->query($args);

        $found_post_ids = $this->helper_get_found_post_ids();

        $expected = [];
        foreach (self::$post_ids[1] as $expected_id) {
            $expected[] = $expected_id;
        }
        foreach (self::$post_ids[4] as $expected_id) {
            $expected[] = $expected_id;
        }
        foreach (self::$post_ids[5] as $expected_id) {
            $expected[] = $expected_id;
        }

        $this->assertSameSets($expected, $found_post_ids);
    }
    public function test_operator_equal_or_greater_than()
    {
        $args = [
            'post_type'      => self::$post_type,
            'posts_per_page' => -1,
            'comment_count'  => [
                'value'   => 4,
                'compare' => '>=',
            ],
        ];
        $this->q->query($args);

        $found_post_ids = $this->helper_get_found_post_ids();

        $expected = [];
        foreach (self::$post_ids[4] as $expected_id) {
            $expected[] = $expected_id;
        }
        foreach (self::$post_ids[5] as $expected_id) {
            $expected[] = $expected_id;
        }

        $this->assertSameSets($expected, $found_post_ids);
    }

    public function test_operator_equal_or_greater_than_no_results()
    {
        $args = [
            'post_type'      => self::$post_type,
            'posts_per_page' => -1,
            'comment_count'  => [
                'value'   => 7,
                'compare' => '>=',
            ],
        ];
        $this->q->query($args);

        $found_post_ids = $this->helper_get_found_post_ids();

        $expected = [];

        $this->assertSameSets($expected, $found_post_ids);
    }

    public function test_operator_equal_or_less_than()
    {
        $args = [
            'post_type'      => self::$post_type,
            'posts_per_page' => -1,
            'comment_count'  => [
                'value'   => 4,
                'compare' => '<=',
            ],
        ];
        $this->q->query($args);

        $found_post_ids = $this->helper_get_found_post_ids();

        $expected = [];
        foreach (self::$post_ids[1] as $expected_id) {
            $expected[] = $expected_id;
        }
        foreach (self::$post_ids[4] as $expected_id) {
            $expected[] = $expected_id;
        }

        $this->assertSameSets($expected, $found_post_ids);
    }

    public function test_operator_equal_or_less_than_no_results()
    {
        $args = [
            'post_type'      => self::$post_type,
            'posts_per_page' => -1,
            'comment_count'  => [
                'value'   => 0,
                'compare' => '<=',
            ],
        ];
        $this->q->query($args);

        $found_post_ids = $this->helper_get_found_post_ids();

        $expected = [];

        $this->assertSameSets($expected, $found_post_ids);
    }

    public function test_invalid_operator_should_fall_back_on_equals()
    {
        $args = [
            'post_type'      => self::$post_type,
            'posts_per_page' => -1,
            'comment_count'  => [
                'value'   => 5,
                'compare' => '@',
            ],
        ];
        $this->q->query($args);

        $found_post_ids = $this->helper_get_found_post_ids();

        $expected = [];
        foreach (self::$post_ids[5] as $expected_id) {
            $expected[] = $expected_id;
        }

        $this->assertSameSets($expected, $found_post_ids);
    }

    public function test_wrong_count_no_results()
    {
        $args = [
            'post_type'      => self::$post_type,
            'posts_per_page' => -1,
            'comment_count'  => [
                'value'   => 'abc',
                'compare' => '=',
            ],
        ];
        $this->q->query($args);

        $found_post_ids = $this->helper_get_found_post_ids();

        $expected = [];

        $this->assertSameSets($expected, $found_post_ids);
    }

    public function test_no_operator_no_results()
    {
        $args = [
            'post_type'      => self::$post_type,
            'posts_per_page' => -1,
            'comment_count'  => [
                'value' => 5,
            ],
        ];
        $this->q->query($args);

        $found_post_ids = $this->helper_get_found_post_ids();

        $expected = self::$post_ids[5];

        $this->assertSameSets($expected, $found_post_ids);
    }

    public function test_empty_non_numeric_string_should_be_ignored()
    {
        $args = [
            'post_type'      => self::$post_type,
            'posts_per_page' => -1,
            'comment_count'  => '',
        ];
        $this->q->query($args);

        $found_post_ids = $this->helper_get_found_post_ids();

        $expected = [];
        foreach (self::$post_ids[1] as $expected_id) {
            $expected[] = $expected_id;
        }
        foreach (self::$post_ids[4] as $expected_id) {
            $expected[] = $expected_id;
        }
        foreach (self::$post_ids[5] as $expected_id) {
            $expected[] = $expected_id;
        }

        $this->assertSameSets($expected, $found_post_ids);
    }

    public function test_simple_count()
    {
        $args = [
            'post_type'      => self::$post_type,
            'posts_per_page' => -1,
            'comment_count'  => 5,
        ];
        $this->q->query($args);

        $found_post_ids = $this->helper_get_found_post_ids();

        $expected = self::$post_ids[5];

        $this->assertSameSets($expected, $found_post_ids);
    }
}
