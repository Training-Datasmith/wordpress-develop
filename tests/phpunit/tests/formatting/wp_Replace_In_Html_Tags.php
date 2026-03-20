<?php

declare(strict_types=1);

/**
 * @group formatting
 *
 * @covers ::wp_replace_in_html_tags
 */
class Tests_Formatting_wpReplaceInHtmlTags extends WP_UnitTestCase
{
    /**
     * Check for expected behavior of new function wp_replace_in_html_tags().
     *
     * @dataProvider data_wp_replace_in_html_tags
     */
    public function test_wp_replace_in_html_tags($input, $output)
    {
        return $this->assertSame($output, wp_replace_in_html_tags($input, [ "\n" => ' ' ]));
    }

    public function data_wp_replace_in_html_tags()
    {
        return [
            [
                "Hello \n World",
                "Hello \n World",
            ],
            [
                "<Hello \n World>",
                '<Hello   World>',
            ],
            [
                "<!-- Hello \n World -->",
                '<!-- Hello   World -->',
            ],
            [
                "<!-- Hello <\n> World -->",
                '<!-- Hello < > World -->',
            ],
        ];
    }
}
