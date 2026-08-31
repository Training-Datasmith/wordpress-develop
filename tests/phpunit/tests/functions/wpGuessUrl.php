<?php

declare(strict_types=1);

/**
 * Test wp_guess_url().
 *
 * @group functions
 *
 * @covers ::wp_guess_url
 */
class Tests_Functions_wpGuessUrl extends WP_UnitTestCase
{
    /**
     * @ticket 36827
     *
     * @dataProvider data_wp_guess_url_should_return_site_url
     *
     * @param string $url The URL to navigate to, relative to `site_url()`.
     */
    public function test_wp_guess_url_should_return_site_url($url)
    {
        $siteurl = site_url();
        $this->go_to(site_url($url));
        $this->assertSame($siteurl, wp_guess_url());
    }

    /**
     * Data provider.
     *
     * @return array[]
     */
    public function data_wp_guess_url_should_return_site_url()
    {
        return [
            'no trailing slash'                            => [ 'url' => 'wp-admin' ],
            'trailing slash'                               => [ 'url' => 'wp-admin/' ],
            'trailing slash, query var'                    => [ 'url' => 'wp-admin/?foo=bar' ],
            'file extension, no trailing slash'            => [ 'url' => 'wp-login.php' ],
            'file extension, query var, no trailing slash' => [ 'url' => 'wp-login.php?foo=bar' ],
        ];
    }
}
