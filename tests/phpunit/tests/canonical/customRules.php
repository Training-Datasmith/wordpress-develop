<?php

declare(strict_types=1);

/**
 * @group canonical
 * @group rewrite
 * @group query
 */
class Tests_Canonical_CustomRules extends WP_Canonical_UnitTestCase
{
    public function set_up()
    {
        parent::set_up();
        global $wp_rewrite;
        // Add a custom Rewrite rule to test category redirections.
        $wp_rewrite->add_rule('ccr/(.+?)/sort/(asc|desc)', 'index.php?category_name=$matches[1]&order=$matches[2]', 'top'); // ccr = Custom_Cat_Rule.
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
         * Data format:
         * [0]: Test URL.
         * [1]: Expected results: Any of the following can be used.
         *      array( 'url': expected redirection location, 'qv': expected query vars to be set via the rewrite AND $_GET );
         *      array( expected query vars to be set, same as 'qv' above )
         *      (string) expected redirect location
         * [3]: (optional) The ticket the test refers to, Can be skipped if unknown.
         */
        return [
            // Custom Rewrite rules leading to Categories.
            [
                '/ccr/uncategorized/sort/asc/',
                [
                    'url' => '/ccr/uncategorized/sort/asc/',
                    'qv'  => [
                        'category_name' => 'uncategorized',
                        'order'         => 'asc',
                    ],
                ],
            ],
            [
                '/ccr/uncategorized/sort/desc/',
                [
                    'url' => '/ccr/uncategorized/sort/desc/',
                    'qv'  => [
                        'category_name' => 'uncategorized',
                        'order'         => 'desc',
                    ],
                ],
            ],
        ];
    }
}
