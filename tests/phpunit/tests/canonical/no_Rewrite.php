<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/canonical.php';

/**
 * @group canonical
 * @group rewrite
 * @group query
 */
class Tests_Canonical_NoRewrite extends WP_Canonical_UnitTestCase
{
    // These test cases are run against the test handler in WP_Canonical.

    public function set_up()
    {
        global $wp_rewrite;

        parent::set_up();

        $wp_rewrite->init();
        $wp_rewrite->set_permalink_structure('');
        $wp_rewrite->flush_rules();
    }

    /**
     * @dataProvider data
     */
    public function test($test_url, $expected, $ticket = 0, $expected_doing_it_wrong = [])
    {
        $this->assertCanonical($test_url, $expected, $ticket, $expected_doing_it_wrong);
    }

    public function data()
    {
        /*
         * Test URL.
         * [0]: Test URL.
         * [1]: Expected results: Any of the following can be used.
         *      array( 'url': expected redirection location, 'qv': expected query vars to be set via the rewrite AND $_GET );
         *      array( expected query vars to be set, same as 'qv' above );
         *      (string) expected redirect location.
         * [3]: (optional) The ticket the test refers to. Can be skipped if unknown.
         */
        return [
            [ '/?p=123', '/?p=123' ],

            // This post_type arg should be stripped, because p=1 exists, and does not have post_type= in its query string.
            [ '/?post_type=fake-cpt&p=1', '/?p=1' ],

            // Strip an existing but incorrect post_type arg.
            [ '/?post_type=page&page_id=1', '/?p=1' ],

            // Trailing spaces and punctuation in query string args.
            [ // Space.
                '/?p=358 ',
                [
                    'url' => '/?p=358',
                    'qv'  => [ 'p' => '358' ],
                ],
                20383,
            ],
            [ // Encoded space.
                '/?p=358%20',
                [
                    'url' => '/?p=358',
                    'qv'  => [ 'p' => '358' ],
                ],
                20383,
            ],
            [ // Exclamation mark.
                '/?p=358!',
                [
                    'url' => '/?p=358',
                    'qv'  => [ 'p' => '358' ],
                ],
                20383,
            ],
            [ // Encoded exclamation mark.
                '/?p=358%21',
                [
                    'url' => '/?p=358',
                    'qv'  => [ 'p' => '358' ],
                ],
                20383,
            ],
            [ // Double quote.
                '/?p=358"',
                [
                    'url' => '/?p=358',
                    'qv'  => [ 'p' => '358' ],
                ],
                20383,
            ],
            [ // Encoded double quote.
                '/?p=358%22',
                [
                    'url' => '/?p=358',
                    'qv'  => [ 'p' => '358' ],
                ],
                20383,
            ],
            [ // Single quote.
                '/?p=358\'',
                [
                    'url' => '/?p=358',
                    'qv'  => [ 'p' => '358' ],
                ],
                20383,
            ],
            [ // Encoded single quote.
                '/?p=358%27',
                [
                    'url' => '/?p=358',
                    'qv'  => [ 'p' => '358' ],
                ],
                20383,
            ],
            [ // Opening bracket.
                '/?p=358(',
                [
                    'url' => '/?p=358',
                    'qv'  => [ 'p' => '358' ],
                ],
                20383,
            ],
            [ // Encoded opening bracket.
                '/?p=358%28',
                [
                    'url' => '/?p=358',
                    'qv'  => [ 'p' => '358' ],
                ],
                20383,
            ],
            [ // Closing bracket.
                '/?p=358)',
                [
                    'url' => '/?p=358',
                    'qv'  => [ 'p' => '358' ],
                ],
                20383,
            ],
            [ // Encoded closing bracket.
                '/?p=358%29',
                [
                    'url' => '/?p=358',
                    'qv'  => [ 'p' => '358' ],
                ],
                20383,
            ],
            [ // Comma.
                '/?p=358,',
                [
                    'url' => '/?p=358',
                    'qv'  => [ 'p' => '358' ],
                ],
                20383,
            ],
            [ // Encoded comma.
                '/?p=358%2C',
                [
                    'url' => '/?p=358',
                    'qv'  => [ 'p' => '358' ],
                ],
                20383,
            ],
            [ // Period.
                '/?p=358.',
                [
                    'url' => '/?p=358',
                    'qv'  => [ 'p' => '358' ],
                ],
                20383,
            ],
            [ // Encoded period.
                '/?p=358%2E',
                [
                    'url' => '/?p=358',
                    'qv'  => [ 'p' => '358' ],
                ],
                20383,
            ],
            [ // Semicolon.
                '/?p=358;',
                [
                    'url' => '/?p=358',
                    'qv'  => [ 'p' => '358' ],
                ],
                20383,
            ],
            [ // Encoded semicolon.
                '/?p=358%3B',
                [
                    'url' => '/?p=358',
                    'qv'  => [ 'p' => '358' ],
                ],
                20383,
            ],
            [ // Opening curly bracket.
                '/?p=358{',
                [
                    'url' => '/?p=358',
                    'qv'  => [ 'p' => '358' ],
                ],
                20383,
            ],
            [ // Encoded opening curly bracket.
                '/?p=358%7B',
                [
                    'url' => '/?p=358',
                    'qv'  => [ 'p' => '358' ],
                ],
                20383,
            ],
            [ // Closing curly bracket.
                '/?p=358}',
                [
                    'url' => '/?p=358',
                    'qv'  => [ 'p' => '358' ],
                ],
                20383,
            ],
            [ // Encoded closing curly bracket.
                '/?p=358%7D',
                [
                    'url' => '/?p=358',
                    'qv'  => [ 'p' => '358' ],
                ],
                20383,
            ],
            [ // Encoded opening curly quote.
                '/?p=358%E2%80%9C',
                [
                    'url' => '/?p=358',
                    'qv'  => [ 'p' => '358' ],
                ],
                20383,
            ],
            [ // Encoded closing curly quote.
                '/?p=358%E2%80%9D',
                [
                    'url' => '/?p=358',
                    'qv'  => [ 'p' => '358' ],
                ],
                20383,
            ],

            // Trailing spaces and punctuation in permalinks.
            [ '/page/2/ ', '/page/2/', 20383 ],   // Space.
            [ '/page/2/%20', '/page/2/', 20383 ], // Encoded space.
            [ '/page/2/!', '/page/2/', 20383 ],   // Exclamation mark.
            [ '/page/2/%21', '/page/2/', 20383 ], // Encoded exclamation mark.
            [ '/page/2/"', '/page/2/', 20383 ],   // Double quote.
            [ '/page/2/%22', '/page/2/', 20383 ], // Encoded double quote.
            [ '/page/2/\'', '/page/2/', 20383 ],  // Single quote.
            [ '/page/2/%27', '/page/2/', 20383 ], // Encoded single quote.
            [ '/page/2/(', '/page/2/', 20383 ],   // Opening bracket.
            [ '/page/2/%28', '/page/2/', 20383 ], // Encoded opening bracket.
            [ '/page/2/)', '/page/2/', 20383 ],   // Closing bracket.
            [ '/page/2/%29', '/page/2/', 20383 ], // Encoded closing bracket.
            [ '/page/2/,', '/page/2/', 20383 ],   // Comma.
            [ '/page/2/%2C', '/page/2/', 20383 ], // Encoded comma.
            [ '/page/2/.', '/page/2/', 20383 ],   // Period.
            [ '/page/2/%2E', '/page/2/', 20383 ], // Encoded period.
            [ '/page/2/;', '/page/2/', 20383 ],   // Semicolon.
            [ '/page/2/%3B', '/page/2/', 20383 ], // Encoded semicolon.
            [ '/page/2/{', '/page/2/', 20383 ],   // Opening curly bracket.
            [ '/page/2/%7B', '/page/2/', 20383 ], // Encoded opening curly bracket.
            [ '/page/2/}', '/page/2/', 20383 ],   // Closing curly bracket.
            [ '/page/2/%7D', '/page/2/', 20383 ], // Encoded closing curly bracket.
            [ '/page/2/%E2%80%9C', '/page/2/', 20383 ], // Encoded opening curly quote.
            [ '/page/2/%E2%80%9D', '/page/2/', 20383 ], // Encoded closing curly quote.

            [ '/?page_id=1', '/?p=1' ], // Redirect page_id to p (should cover page_id|p|attachment_id to one another).
            [ '/?page_id=1&post_type=revision', '/?p=1' ],

            [ '/?feed=rss2&p=1', '/?feed=rss2&p=1', 21841 ],
            [ '/?feed=rss&p=1', '/?feed=rss2&p=1', 24623 ],

            [ '/?comp=East+(North)', '/?comp=East+(North)', 49347 ],

            [ null, '/', 63316 ], // No Path and Query
        ];
    }

    /**
     * Test the canonical URL when the host header is not set.
     *
     * @ticket 63316
     * @dataProvider data_missing_host_header
     * @param string $wp_home The WP_HOME value to set.
     * @param string $expected_url The expected canonical URL.
     */
    public function test_missing_host_header($wp_home, $expected_url)
    {
        $_SERVER['HTTP_HOST'] = null;
        add_filter(
            'pre_option_home',
            static function () use ($wp_home) {
                return $wp_home;
            }
        );
        $this->assertCanonical('/', $expected_url, 63316);
    }

    /**
     * Data provider for test_missing_host_header().
     *
     * @return array[]
     */
    public function data_missing_host_header()
    {
        return [
            'no port'   => [
                'wp_home'      => 'http://example.com',
                'expected_url' => ':///',
            ],
            'with port' => [
                'wp_home'      => 'http://example.com:8889',
                'expected_url' => '://:8889/',
            ],
        ];
    }
}
