<?php

declare(strict_types=1);
/**
 * Unit tests covering WP_HTML_Doctype_Info functionality.
 *
 * @package WordPress
 * @subpackage HTML-API
 */

/**
 * @group html-api
 *
 * @coversDefaultClass WP_HTML_Doctype_Info
 */
class Tests_HtmlApi_WpHtmlDoctypeInfo extends WP_UnitTestCase
{
    /**
     * Test DOCTYPE handling.
     *
     * @ticket 61576
     *
     * @dataProvider data_parseable_raw_doctypes
     */
    public function test_doctype_doc_info(
        string $html,
        string $expected_compat_mode,
        ?string $expected_name = null,
        ?string $expected_public_id = null,
        ?string $expected_system_id = null
    ) {
        $doctype = WP_HTML_Doctype_Info::from_doctype_token($html);
        $this->assertNotNull(
            $doctype,
            "Should have parsed the following doctype declaration: {$html}"
        );

        $this->assertSame(
            $expected_compat_mode,
            $doctype->indicated_compatibility_mode,
            'Failed to infer the expected document compatibility mode.'
        );

        $this->assertSame(
            $expected_name,
            $doctype->name,
            'Failed to parse the expected DOCTYPE name.'
        );

        $this->assertSame(
            $expected_public_id,
            $doctype->public_identifier,
            'Failed to parse the expected DOCTYPE public identifier.'
        );

        $this->assertSame(
            $expected_system_id,
            $doctype->system_identifier,
            'Failed to parse the expected DOCTYPE system identifier.'
        );
    }

    /**
     * Data provider.
     *
     * @return array[]
     */
    public static function data_parseable_raw_doctypes(): array
    {
        return [
            'Missing doctype name'                      => [ '<!DOCTYPE>', 'quirks' ],
            'HTML5 doctype'                             => [ '<!DOCTYPE html>', 'no-quirks', 'html' ],
            'HTML5 doctype no whitespace before name'   => [ '<!DOCTYPEhtml>', 'no-quirks', 'html' ],
            'XHTML doctype'                             => [ '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">', 'no-quirks', 'html', '-//W3C//DTD HTML 4.01//EN', 'http://www.w3.org/TR/html4/strict.dtd' ],
            'SVG doctype'                               => [ '<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">', 'quirks', 'svg', '-//W3C//DTD SVG 1.1//EN', 'http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd' ],
            'MathML doctype'                            => [ '<!DOCTYPE math PUBLIC "-//W3C//DTD MathML 2.0//EN" "http://www.w3.org/Math/DTD/mathml2/mathml2.dtd">', 'quirks', 'math', '-//W3C//DTD MathML 2.0//EN', 'http://www.w3.org/Math/DTD/mathml2/mathml2.dtd' ],
            'Doctype with null byte replacement'        => [ "<!DOCTYPE null-\0 PUBLIC '\0' '\0\0'>", 'quirks', "null-\u{FFFD}", "\u{FFFD}", "\u{FFFD}\u{FFFD}" ],
            'Uppercase doctype'                         => [ '<!DOCTYPE UPPERCASE>', 'quirks', 'uppercase' ],
            'Lowercase doctype'                         => [ '<!doctype lowercase>', 'quirks', 'lowercase' ],
            'Doctype with whitespace'                   => [ "<!DOCTYPE\n\thtml\f\rPUBLIC\r\n''\t''>", 'no-quirks', 'html', '', '' ],
            'Doctype trailing characters'               => [ "<!DOCTYPE html PUBLIC '' '' Anything (except closing angle bracket) is just fine here !!!>", 'no-quirks', 'html', '', '' ],
            'An ugly no-quirks doctype'                 => [ "<!dOcTyPehtml\tPublIC\"pub-id\"'sysid'>", 'no-quirks', 'html', 'pub-id', 'sysid' ],
            'Missing public ID'                         => [ '<!DOCTYPE html PUBLIC>', 'quirks', 'html' ],
            'Missing system ID'                         => [ '<!DOCTYPE html SYSTEM>', 'quirks', 'html' ],
            'Missing close quote public ID'             => [ "<!DOCTYPE html PUBLIC 'xyz>", 'quirks', 'html', 'xyz' ],
            'Missing close quote system ID'             => [ "<!DOCTYPE html SYSTEM 'xyz>", 'quirks', 'html', null, 'xyz' ],
            'Missing close quote system ID with public' => [ "<!DOCTYPE html PUBLIC 'abc' 'xyz>", 'quirks', 'html', 'abc', 'xyz' ],
            'Bogus characters instead of system/public' => [ '<!DOCTYPE html FOOBAR>', 'quirks', 'html' ],
            'Bogus characters instead of PUBLIC quote'  => [ "<!DOCTYPE html PUBLIC x ''''>", 'quirks', 'html' ],
            'Bogus characters instead of SYSTEM quote ' => [ "<!DOCTYPE html SYSTEM x ''>", 'quirks', 'html' ],
            'Emoji'                                     => [ '<!DOCTYPE 🏴󠁧󠁢󠁥󠁮󠁧󠁿 PUBLIC "🔥" "😈">', 'quirks', "\u{1F3F4}\u{E0067}\u{E0062}\u{E0065}\u{E006E}\u{E0067}\u{E007F}", '🔥', '😈' ],
            'Bogus characters instead of SYSTEM quote after public' => [ "<!DOCTYPE html PUBLIC ''x''>", 'quirks', 'html', '' ],
            'Special quirks mode if system unset'       => [ '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Frameset//">', 'quirks', 'html', '-//W3C//DTD HTML 4.01 Frameset//' ],
            'Special limited-quirks mode if system set' => [ '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Frameset//" "">', 'limited-quirks', 'html', '-//W3C//DTD HTML 4.01 Frameset//', '' ],
        ];
    }

    /**
     * @dataProvider invalid_inputs
     *
     * @ticket 61576
     */
    public function test_invalid_inputs_return_null(string $html)
    {
        $this->assertNull(WP_HTML_Doctype_Info::from_doctype_token($html));
    }

    /**
     * Data provider.
     *
     * @return array[]
     */
    public static function invalid_inputs(): array
    {
        return [
            'Empty string'                  => [ '' ],
            'Other HTML'                    => [ '<div>' ],
            'DOCTYPE after HTML'            => [ 'x<!DOCTYPE>' ],
            'DOCTYPE before HTML'           => [ '<!DOCTYPE>x' ],
            'Incomplete DOCTYPE'            => [ '<!DOCTYPE' ],
            'Pseudo DOCTYPE containing ">"' => [ '<!DOCTYPE html PUBLIC ">">' ],
        ];
    }
}
