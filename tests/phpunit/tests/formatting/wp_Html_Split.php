<?php

declare(strict_types=1);

/**
 * @group formatting
 *
 * @covers ::wp_html_split
 */
class Tests_Formatting_wpHtmlSplit extends WP_UnitTestCase
{
    /**
     * Basic functionality goes here.
     *
     * @dataProvider data_basic_features
     */
    public function test_basic_features($input, $output)
    {
        return $this->assertSame($output, wp_html_split($input));
    }

    public function data_basic_features()
    {
        return [
            [
                'abcd efgh',
                [ 'abcd efgh' ],
            ],
            [
                'abcd <html> efgh',
                [ 'abcd ', '<html>', ' efgh' ],
            ],
            [
                'abcd <!-- <html> --> efgh',
                [ 'abcd ', '<!-- <html> -->', ' efgh' ],
            ],
            [
                'abcd <![CDATA[ <html> ]]> efgh',
                [ 'abcd ', '<![CDATA[ <html> ]]>', ' efgh' ],
            ],
        ];
    }

    /**
     * Automated performance testing of the main regex.
     *
     * @dataProvider data_whole_posts
     *
     * @covers ::get_html_split_regex
     */
    public function test_pcre_performance($input)
    {
        $regex  = get_html_split_regex();
        $result = benchmark_pcre_backtracking($regex, $input, 'split');
        return $this->assertLessThan(200, $result);
    }

    public function data_whole_posts()
    {
        require_once DIR_TESTDATA . '/formatting/whole-posts.php';
        return data_whole_posts();
    }
}
