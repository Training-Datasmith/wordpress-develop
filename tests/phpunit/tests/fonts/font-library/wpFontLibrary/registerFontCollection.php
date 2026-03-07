<?php

declare(strict_types=1);
/**
 * Test WP_Font_Library::register_font_collection().
 *
 * @package WordPress
 * @subpackage Font Library
 *
 * @group fonts
 * @group font-library
 *
 * @covers WP_Font_Library::register_font_collection
 */
class Tests_Fonts_WpFontLibrary_RegisterFontCollection extends WP_Font_Library_UnitTestCase
{
    public function test_should_register_font_collection()
    {
        $config = [
            'name'          => 'My Collection',
            'font_families' => [ 'mock' ],
        ];

        $collection = WP_Font_Library::get_instance()->register_font_collection('my-collection', $config);
        $this->assertInstanceOf('WP_Font_Collection', $collection);
    }

    public function test_should_return_error_if_slug_is_repeated()
    {
        $mock_collection_data = [
            'name'          => 'Test Collection',
            'font_families' => [ 'mock' ],
        ];

        // Register first collection.
        $collection1 = WP_Font_Library::get_instance()->register_font_collection('my-collection-1', $mock_collection_data);
        $this->assertInstanceOf('WP_Font_Collection', $collection1, 'A collection should be registered.');

        // Expects a _doing_it_wrong notice.
        $this->setExpectedIncorrectUsage('WP_Font_Library::register_font_collection');

        // Try to register a second collection with same slug.
        WP_Font_Library::get_instance()->register_font_collection('my-collection-1', $mock_collection_data);
    }
}
