<?php

declare(strict_types=1);

require_once __DIR__ . '/base.php';

/**
 * @group block-templates
 * @covers ::get_template_hierarchy
 */
class Tests_Block_Templates_GetTemplate_Hierarchy extends WP_Block_Templates_UnitTestCase
{
    public function set_up()
    {
        parent::set_up();
        register_post_type(
            'custom_book',
            [
                'public'       => true,
                'show_in_rest' => true,
            ]
        );
        register_taxonomy('book_type', 'custom_book');
        register_taxonomy('books', 'custom_book');
    }

    public function tear_down()
    {
        unregister_post_type('custom_book');
        unregister_taxonomy('book_type');
        unregister_taxonomy('books');
        parent::tear_down();
    }

    /**
     * @dataProvider data_get_template_hierarchy
     *
     * @ticket 56467
     *
     * @param array $args     Test arguments.
     * @param array $expected Expected results.
     */
    public function test_get_template_hierarchy(array $args, array $expected)
    {
        $this->assertSame($expected, get_template_hierarchy(...$args));
    }

    /**
     * @ticket 60846
     */
    public function test_get_template_hierarchy_with_hooks()
    {
        add_filter(
            'date_template_hierarchy',
            function ($templates) {
                return array_merge([ 'date-custom' ], $templates);
            }
        );
        $expected = [ 'date-custom', 'date', 'archive', 'index' ];
        $this->assertSame($expected, get_template_hierarchy('date'));
    }

    /**
     * Data provider.
     *
     * @return array
     */
    public function data_get_template_hierarchy()
    {
        return [
            'front-page'                               => [
                'args'     => [ 'front-page' ],
                'expected' => [ 'front-page', 'home', 'index' ],
            ],
            'custom template'                          => [
                'args'     => [ 'whatever-slug', true ],
                'expected' => [ 'page', 'singular', 'index' ],
            ],
            'page'                                     => [
                'args'     => [ 'page' ],
                'expected' => [ 'page', 'singular', 'index' ],
            ],
            'tag'                                      => [
                'args'     => [ 'tag' ],
                'expected' => [ 'tag', 'archive', 'index' ],
            ],
            'author'                                   => [
                'args'     => [ 'author' ],
                'expected' => [ 'author', 'archive', 'index' ],
            ],
            'date'                                     => [
                'args'     => [ 'date' ],
                'expected' => [ 'date', 'archive', 'index' ],
            ],
            'taxonomy'                                 => [
                'args'     => [ 'taxonomy' ],
                'expected' => [ 'taxonomy', 'archive', 'index' ],
            ],
            'attachment'                               => [
                'args'     => [ 'attachment' ],
                'expected' => [ 'attachment', 'single', 'singular', 'index' ],
            ],
            'singular'                                 => [
                'args'     => [ 'singular' ],
                'expected' => [ 'singular', 'index' ],
            ],
            'single'                                   => [
                'args'     => [ 'single' ],
                'expected' => [ 'single', 'singular', 'index' ],
            ],
            'archive'                                  => [
                'args'     => [ 'archive' ],
                'expected' => [ 'archive', 'index' ],
            ],
            'index'                                    => [
                'args'     => [ 'index' ],
                'expected' => [ 'index' ],
            ],
            'specific taxonomies'                      => [
                'args'     => [ 'taxonomy-books', false, 'taxonomy-books' ],
                'expected' => [ 'taxonomy-books', 'taxonomy', 'archive', 'index' ],
            ],
            'single word categories'                   => [
                'args'     => [ 'category-fruits', false, 'category' ],
                'expected' => [ 'category-fruits', 'category', 'archive', 'index' ],
            ],
            'single word categories no prefix'         => [
                'args'     => [ 'category-fruits', false ],
                'expected' => [ 'category-fruits', 'category', 'archive', 'index' ],
            ],
            'multi word categories'                    => [
                'args'     => [ 'category-fruits-yellow', false, 'category' ],
                'expected' => [ 'category-fruits-yellow', 'category', 'archive', 'index' ],
            ],
            'multi word categories no prefix'          => [
                'args'     => [ 'category-fruits-yellow', false ],
                'expected' => [ 'category-fruits-yellow', 'category', 'archive', 'index' ],
            ],
            'single word taxonomy and term'            => [
                'args'     => [ 'taxonomy-books-action', false, 'taxonomy-books' ],
                'expected' => [ 'taxonomy-books-action', 'taxonomy-books', 'taxonomy', 'archive', 'index' ],
            ],
            'single word taxonomy and term no prefix'  => [
                'args'     => [ 'taxonomy-books-action', false ],
                'expected' => [ 'taxonomy-books-action', 'taxonomy-books', 'taxonomy', 'archive', 'index' ],
            ],
            'single word taxonomy and multi word term' => [
                'args'     => [ 'taxonomy-books-action-adventure', false, 'taxonomy-books' ],
                'expected' => [ 'taxonomy-books-action-adventure', 'taxonomy-books', 'taxonomy', 'archive', 'index' ],
            ],
            'multi word taxonomy and term'             => [
                'args'     => [ 'taxonomy-greek-books-action-adventure', false, 'taxonomy-greek-books' ],
                'expected' => [ 'taxonomy-greek-books-action-adventure', 'taxonomy-greek-books', 'taxonomy', 'archive', 'index' ],
            ],
            'single word post type'                    => [
                'args'     => [ 'single-book', false, 'single-book' ],
                'expected' => [ 'single-book', 'single', 'singular', 'index' ],
            ],
            'multi word post type'                     => [
                'args'     => [ 'single-art-project', false, 'single-art-project' ],
                'expected' => [ 'single-art-project', 'single', 'singular', 'index' ],
            ],
            'single post with multi word post type'    => [
                'args'     => [ 'single-art-project-imagine', false, 'single-art-project' ],
                'expected' => [ 'single-art-project-imagine', 'single-art-project', 'single', 'singular', 'index' ],
            ],
            'single page'                              => [
                'args'     => [ 'page-hi', false, 'page' ],
                'expected' => [ 'page-hi', 'page', 'singular', 'index' ],
            ],
            'authors'                                  => [
                'args'     => [ 'author-rigas', false, 'author' ],
                'expected' => [ 'author-rigas', 'author', 'archive', 'index' ],
            ],
            'multiple word taxonomy no prefix'         => [
                'args'     => [ 'taxonomy-book_type-adventure', false ],
                'expected' => [ 'taxonomy-book_type-adventure', 'taxonomy-book_type', 'taxonomy', 'archive', 'index' ],
            ],
            'single post type no prefix'               => [
                'args'     => [ 'single-custom_book', false ],
                'expected' => [
                    'single-custom_book',
                    'single',
                    'singular',
                    'index',
                ],
            ],
            'single post and post type no prefix'      => [
                'args'     => [ 'single-custom_book-book-1', false ],
                'expected' => [
                    'single-custom_book-book-1',
                    'single-custom_book',
                    'single',
                    'singular',
                    'index',
                ],
            ],
            'page no prefix'                           => [
                'args'     => [ 'page-hi', false ],
                'expected' => [
                    'page-hi',
                    'page',
                    'singular',
                    'index',
                ],
            ],
            'post type archive no prefix'              => [
                'args'     => [ 'archive-book', false ],
                'expected' => [
                    'archive-book',
                    'archive',
                    'index',
                ],
            ],
        ];
    }
}
