<?php

declare(strict_types=1);

/**
 * @group oembed
 */
class Tests_Filter_oEmbed_Iframe_Title_Attribute extends WP_UnitTestCase
{
    public function data_filter_oembed_iframe_title_attribute()
    {
        return [
            [
                '<p>Foo</p><iframe src=""></iframe><b>Bar</b>',
                [
                    'type' => 'rich',
                ],
                'https://www.youtube.com/watch?v=72xdCU__XCk',
                '<p>Foo</p><iframe src=""></iframe><b>Bar</b>',
            ],
            [
                '<p>Foo</p><iframe src="" title="Hello World"></iframe><b>Bar</b>',
                [
                    'type' => 'rich',
                ],
                'https://www.youtube.com/watch?v=72xdCU__XCk',
                '<p>Foo</p><iframe title="Hello World" src=""></iframe><b>Bar</b>',
            ],
            [
                '<p>Foo</p>',
                [
                    'type'  => 'rich',
                    'title' => 'Hello World',
                ],
                'https://www.youtube.com/watch?v=72xdCU__XCk',
                '<p>Foo</p>',
            ],
            [
                '<p title="Foo">Bar</p>',
                [
                    'type'  => 'rich',
                    'title' => 'Hello World',
                ],
                'https://www.youtube.com/watch?v=72xdCU__XCk',
                '<p title="Foo">Bar</p>',
            ],
            [
                '<p>Foo</p><iframe src=""></iframe><b>Bar</b>',
                [
                    'type'  => 'rich',
                    'title' => 'Hello World',
                ],
                'https://www.youtube.com/watch?v=72xdCU__XCk',
                '<p>Foo</p><iframe title="Hello World" src=""></iframe><b>Bar</b>',
            ],
            [
                '<iframe src="" title="Foo"></iframe>',
                [
                    'type'  => 'rich',
                    'title' => 'Bar',
                ],
                'https://www.youtube.com/watch?v=72xdCU__XCk',
                '<iframe title="Foo" src=""></iframe>',
            ],
        ];
    }

    /**
     * @dataProvider data_filter_oembed_iframe_title_attribute
     */
    public function test_oembed_iframe_title_attribute($html, $oembed_data, $url, $expected)
    {
        $actual = wp_filter_oembed_iframe_title_attribute($html, (object) $oembed_data, $url);

        $this->assertEqualHTML($expected, $actual);
    }

    public function test_filter_oembed_iframe_title_attribute()
    {
        add_filter('oembed_iframe_title_attribute', [ $this, '_filter_oembed_iframe_title_attribute' ]);

        $actual = wp_filter_oembed_iframe_title_attribute(
            '<iframe title="Foo" src=""></iframe>',
            (object) [
                'type'  => 'rich',
                'title' => 'Bar',
            ],
            'https://www.youtube.com/watch?v=72xdCU__XCk'
        );

        remove_filter('oembed_iframe_title_attribute', [ $this, '_filter_oembed_iframe_title_attribute' ]);

        $this->assertEqualHTML('<iframe title="Baz" src=""></iframe>', $actual);
    }

    public function test_filter_oembed_iframe_title_attribute_does_not_modify_other_tags()
    {
        add_filter('oembed_iframe_title_attribute', [ $this, '_filter_oembed_iframe_title_attribute' ]);

        $actual = wp_filter_oembed_iframe_title_attribute(
            '<p title="Bar">Baz</p><iframe title="Foo" src=""></iframe>',
            (object) [
                'type'  => 'rich',
                'title' => 'Bar',
            ],
            'https://www.youtube.com/watch?v=72xdCU__XCk'
        );

        remove_filter('oembed_iframe_title_attribute', [ $this, '_filter_oembed_iframe_title_attribute' ]);

        $this->assertEqualHTML('<p title="Bar">Baz</p><iframe title="Baz" src=""></iframe>', $actual);
    }

    public function _filter_oembed_iframe_title_attribute()
    {
        return 'Baz';
    }
}
