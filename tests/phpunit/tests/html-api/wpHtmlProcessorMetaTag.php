<?php

declare(strict_types=1);
/**
 * Unit tests covering WP_HTML_Processor META tag handling.
 *
 * @package WordPress
 * @subpackage HTML-API
 *
 * @since 6.9
 *
 * @group html-api
 *
 * @coversDefaultClass WP_HTML_Processor
 */
class Tests_HtmlApi_WpHtmlProcessorMetaTag extends WP_UnitTestCase
{
    /**
     * Data provider.
     */
    public static function data_supported_meta_tags(): array
    {
        return [
            'No attributes'                        => [ '<meta>' ],
            'Unrelated attributes'                 => [ '<meta not-charset="OK">' ],
            'Boolean charset'                      => [ '<meta charset>' ],
            'HTTP Equiv: accept'                   => [ '<meta http-equiv="accept" content="">' ],
            'HTTP Equiv: content-type, no content' => [ '<meta http-equiv="content-type">' ],
            'Boolean HTTP Equiv'                   => [ '<meta http-equiv content="">' ],
        ];
    }

    /**
     * Ensures that META tags correctly handle encoding confidence.
     *
     * @ticket 63738
     *
     * @dataProvider data_supported_meta_tags
     */
    public function test_supported_meta_tag(string $html)
    {
        $html      = '<!DOCTYPE html>' . $html;
        $processor = new class ($html) extends WP_HTML_Processor {
            public function __construct($html)
            {
                parent::__construct($html, parent::CONSTRUCTOR_UNLOCK_CODE);
            }
        };

        $this->assertTrue($processor->next_tag('META'));
    }

    /**
     * Data provider.
     */
    public static function data_unsupported_meta_tags(): array
    {
        return [
            'With charset'                => [ '<meta charset="utf8">', 'Cannot yet process META tags with charset to determine encoding.' ],
            'With CHARSET'                => [ '<meta CHARSET="utf8">', 'Cannot yet process META tags with charset to determine encoding.' ],
            'With http-equiv'             => [ '<meta http-equiv="content-type" content="">', 'Cannot yet process META tags with http-equiv Content-Type to determine encoding.' ],
            'With http-equiv and content' => [ '<meta http-equiv="Content-Type" content="UTF-8">', 'Cannot yet process META tags with http-equiv Content-Type to determine encoding.' ],
        ];
    }

    /**
     * Ensures that unsupported encoding META tags bail.
     *
     * @ticket 63738
     *
     * @dataProvider data_unsupported_meta_tags
     */
    public function test_unsupported_meta_tags(string $html, string $unsupported_message)
    {
        $html      = '<!DOCTYPE html>' . $html;
        $processor = new class ($html) extends WP_HTML_Processor {
            public function __construct($html)
            {
                parent::__construct($html, parent::CONSTRUCTOR_UNLOCK_CODE);
            }
        };

        $this->assertFalse($processor->next_tag('META'));
        $this->assertInstanceOf(WP_HTML_Unsupported_Exception::class, $processor->get_unsupported_exception());
    }
}
