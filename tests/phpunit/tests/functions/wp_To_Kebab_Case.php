<?php

declare(strict_types=1);

/**
 * Tests for the _wp_to_kebab_case() function
 *
 * @since 5.8.0
 *
 * @group functions
 *
 * @covers ::_wp_to_kebab_case
 */
class Tests_Functions_wpToKebabCase extends WP_UnitTestCase
{
    /**
     * Tests _wp_to_kebab_case().
     *
     * @dataProvider data_wp_to_kebab_case
     *
     * @ticket 53397
     *
     * @param string $test_value Test value.
     * @param string $expected   Expected return value.
     */
    public function test_wp_to_kebab_case($test_value, $expected)
    {
        $this->assertSame($expected, _wp_to_kebab_case($test_value));
    }

    /**
     * Data provider for test_wp_to_kebab_case().
     *
     * @return array[] Test parameters {
     *     @type string $test_value Test value.
     *     @type string $expected   Expected return value.
     * }
     */
    public function data_wp_to_kebab_case()
    {
        return [
            [ 'white', 'white' ],
            [ 'white+black', 'white-black' ],
            [ 'white:black', 'white-black' ],
            [ 'white*black', 'white-black' ],
            [ 'white.black', 'white-black' ],
            [ 'white black', 'white-black' ],
            [ 'white	black', 'white-black' ],
            [ 'white-to-black', 'white-to-black' ],
            [ 'white2white', 'white-2-white' ],
            [ 'white2nd', 'white-2nd' ],
            [ 'white2ndcolor', 'white-2-ndcolor' ],
            [ 'white2ndColor', 'white-2nd-color' ],
            [ 'white2nd_color', 'white-2nd-color' ],
            [ 'white23color', 'white-23-color' ],
            [ 'white23', 'white-23' ],
            [ '23color', '23-color' ],
            [ 'white4th', 'white-4th' ],
            [ 'font2xl', 'font-2-xl' ],
            [ 'whiteToWhite', 'white-to-white' ],
            [ 'whiteTOwhite', 'white-t-owhite' ],
            [ 'WHITEtoWHITE', 'whit-eto-white' ],
            [ 42, '42' ],
            [ "i've done", 'ive-done' ],
            [ '#ffffff', 'ffffff' ],
            [ '$ffffff', 'ffffff' ],
        ];
    }
}
