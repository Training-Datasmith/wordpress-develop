<?php

declare(strict_types=1);

/**
 * @group post
 * @group menu
 * @group taxonomy
 * @group walker
 */
class Tests_Walker extends WP_UnitTestCase
{
    /**
     * @var Walker
     */
    private $walker;

    public function set_up()
    {
        parent::set_up();

        $this->walker = new Walker_Test();
    }

    public function test_single_item()
    {

        $items  = [
            (object) [
                'id'     => 1,
                'parent' => 0,
            ],
        ];
        $output = $this->walker->walk($items, 0);

        $this->assertSame(1, $this->walker->get_number_of_root_elements($items));
        $this->assertSame('<li>1</li>', $output);
    }

    public function test_single_item_flat()
    {

        $items  = [
            (object) [
                'id'     => 1,
                'parent' => 0,
            ],
        ];
        $output = $this->walker->walk($items, -1);

        $this->assertSame(1, $this->walker->get_number_of_root_elements($items));
        $this->assertSame('<li>1</li>', $output);
    }

    public function test_single_item_depth_1()
    {

        $items  = [
            (object) [
                'id'     => 1,
                'parent' => 0,
            ],
        ];
        $output = $this->walker->walk($items, 1);

        $this->assertSame(1, $this->walker->get_number_of_root_elements($items));
        $this->assertSame('<li>1</li>', $output);
    }

    public function test_multiple_items_single_level()
    {

        $items = [
            (object) [
                'id'     => 1,
                'parent' => 0,
            ],
            (object) [
                'id'     => 2,
                'parent' => 0,
            ],
        ];

        $output = $this->walker->walk($items, 0);

        $this->assertSame(2, $this->walker->get_number_of_root_elements($items));
        $this->assertSame('<li>1</li><li>2</li>', $output);
    }

    public function test_multiple_items_multiple_levels()
    {

        $items = [
            (object) [
                'id'     => 1,
                'parent' => 0,
            ],
            (object) [
                'id'     => 2,
                'parent' => 1,
            ],
        ];

        $output = $this->walker->walk($items, 0);

        $this->assertSame(1, $this->walker->get_number_of_root_elements($items));
        $this->assertSame('<li>1<ul><li>2</li></ul></li>', $output);
    }

    public function test_multiple_items_multiple_levels_flat()
    {

        $items = [
            (object) [
                'id'     => 1,
                'parent' => 0,
            ],
            (object) [
                'id'     => 2,
                'parent' => 1,
            ],
        ];

        $output = $this->walker->walk($items, -1);

        $this->assertSame(1, $this->walker->get_number_of_root_elements($items));
        $this->assertSame('<li>1</li><li>2</li>', $output);
    }

    public function test_multiple_items_multiple_levels_depth_1()
    {

        $items = [
            (object) [
                'id'     => 1,
                'parent' => 0,
            ],
            (object) [
                'id'     => 2,
                'parent' => 1,
            ],
        ];

        $output = $this->walker->walk($items, 1);

        $this->assertSame(1, $this->walker->get_number_of_root_elements($items));
        $this->assertSame('<li>1</li>', $output);
    }

    public function test_multiple_items_multiple_levels_depth_2()
    {

        $items = [
            (object) [
                'id'     => 1,
                'parent' => 0,
            ],
            (object) [
                'id'     => 2,
                'parent' => 1,
            ],
            (object) [
                'id'     => 3,
                'parent' => 2,
            ],
        ];

        $output = $this->walker->walk($items, 2);

        $this->assertSame(1, $this->walker->get_number_of_root_elements($items));
        $this->assertSame('<li>1<ul><li>2</li></ul></li>', $output);
    }

    public function test_multiple_items_recursive()
    {

        $items = [
            (object) [
                'id'     => 1,
                'parent' => 2,
            ],
            (object) [
                'id'     => 2,
                'parent' => 1,
            ],
        ];

        $output = $this->walker->walk($items, 0);

        $this->assertSame(0, $this->walker->get_number_of_root_elements($items));
        $this->assertSame('<li>1<ul><li>2</li></ul></li>', $output);
    }

    public function test_single_item_child()
    {

        $items = [
            (object) [
                'id'     => 1,
                'parent' => 3,
            ],
        ];

        $output = $this->walker->walk($items, 0);

        $this->assertSame(0, $this->walker->get_number_of_root_elements($items));
        $this->assertSame('<li>1</li>', $output);
    }

    public function test_single_item_missing_parent_depth_1()
    {

        $items = [
            (object) [
                'id'     => 1,
                'parent' => 3,
            ],
        ];

        $output = $this->walker->walk($items, 1);

        $this->assertSame(0, $this->walker->get_number_of_root_elements($items));

        // It's not clear what the output of this "should" be.

        // Currently the item is simply returned.
        $this->assertSame('<li>1</li>', $output);

        // But as we've only asked for the first depth maybe nothing should be returned?
        // $this->assertSame( '', $output );
    }

    public function test_multiple_items_missing_parents()
    {

        $items = [
            (object) [
                'id'     => 4,
                'parent' => 1,
            ],
            (object) [
                'id'     => 5,
                'parent' => 2,
            ],
            (object) [
                'id'     => 6,
                'parent' => 3,
            ],
        ];

        $output = $this->walker->walk($items, 0);

        $this->assertSame(0, $this->walker->get_number_of_root_elements($items));
        $this->assertSame('<li>4</li><li>5</li><li>6</li>', $output);
    }

    public function test_multiple_items_missing_parents_depth_1()
    {

        $items = [
            (object) [
                'id'     => 4,
                'parent' => 1,
            ],
            (object) [
                'id'     => 5,
                'parent' => 2,
            ],
            (object) [
                'id'     => 6,
                'parent' => 3,
            ],
        ];

        $output = $this->walker->walk($items, 1);

        $this->assertSame(0, $this->walker->get_number_of_root_elements($items));

        // It's not clear what the output of this "should" be.

        // Currently the first item is simply returned.
        $this->assertSame('<li>4</li>', $output);

        // But as we've only asked for the first depth maybe nothing should be returned?
        // $this->assertSame( '', $output );

        // Or maybe all items which are missing parents should simply be treat top level?
        // $this->assertSame( '<li>4</li><li>5</li><li>6</li>', $output );
    }

    /**
     * @ticket 53474
     */
    public function test_multiple_items_non_numeric_parent()
    {

        $items  = [
            (object) [
                'id'     => 1,
                'parent' => '',
            ],
            (object) [
                'id'     => 2,
                'parent' => '',
            ],
        ];
        $output = $this->walker->walk($items, 0);

        $this->assertSame(2, $this->walker->get_number_of_root_elements($items));
        $this->assertSame('<li>1</li><li>2</li>', $output);

        $output = $this->walker->paged_walk($items, 0, 1, 1);

        $this->assertSame('<li>1</li>', $output);

        $output = $this->walker->paged_walk($items, 0, 2, 1);

        $this->assertSame('<li>2</li>', $output);
    }
}

class Walker_Test extends Walker
{
    public $tree_type = 'test';
    public $db_fields = [
        'parent' => 'parent',
        'id'     => 'id',
    ];

    public function start_lvl(&$output, $depth = 0, $args = [])
    {
        $output .= '<ul>';
    }

    public function end_lvl(&$output, $depth = 0, $args = [])
    {
        $output .= '</ul>';
    }

    public function start_el(&$output, $item, $depth = 0, $args = [], $current_page = 0)
    {
        $output .= '<li>' . $item->id;
    }

    public function end_el(&$output, $page, $depth = 0, $args = [])
    {
        $output .= '</li>';
    }
}
