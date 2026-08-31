<?php

declare(strict_types=1);

/**
 * @group formatting
 *
 * @covers ::sanitize_locale_name
 */
class Tests_Formatting_SanitizeLocaleName extends WP_UnitTestCase
{
    /**
     * @dataProvider data_sanitize_locale_name_returns_non_empty_string
     */
    public function test_sanitize_locale_name_returns_non_empty_string($expected, $input)
    {
        $this->assertSame($expected, sanitize_locale_name($input));
    }

    public function data_sanitize_locale_name_returns_non_empty_string()
    {
        return [
            // array( expected, input )
            [ 'en_US', 'en_US' ],
            [ 'en', 'en' ],
            [ 'fr_FR', 'fr_FR' ],
            [ 'fr_FR', 'fr_FR' ],
            [ 'fr_FR-e2791ba830489d23043be8650a22a22b', 'fr_FR-e2791ba830489d23043be8650a22a22b' ],
            [ '-fr_FRmo', '-fr_FR.mo' ],
            [ '12324', '$12324' ],
            [ '4124FRRa', '/4124$$$%%FRRa' ],
            [ 'FR', '<FR' ],
            [ 'FR_FR', 'FR_FR' ],
            [ '--__', '--__' ],
        ];
    }

    /**
     * @dataProvider data_sanitize_locale_name_returns_empty_string
     */
    public function test_sanitize_locale_name_returns_empty_string($input)
    {
        $this->assertSame('', sanitize_locale_name($input));
    }

    public function data_sanitize_locale_name_returns_empty_string()
    {
        return [
            // array( input )
            [ '$<>' ],
            [ '/$$$%%\\)' ],
            [ '....' ],
            [ '@///' ],
        ];
    }
}
