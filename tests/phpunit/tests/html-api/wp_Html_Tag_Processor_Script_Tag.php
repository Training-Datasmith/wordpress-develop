<?php

declare(strict_types=1);
/**
 * Unit tests covering WP_HTML_Tag_Processor script tag functionality.
 *
 * @package WordPress
 * @subpackage HTML-API
 *
 * @group html-api
 *
 * @coversDefaultClass WP_HTML_Tag_Processor
 */
class Tests_HtmlApi_WpHtmlTagProcessorScriptTag extends WP_UnitTestCase
{
    /**
     * @ticket 64419
     *
     * @covers ::get_script_content_type()
     *
     * @dataProvider data_get_script_content_type
     *
     * @param string      $html         HTML containing a script tag.
     * @param string|null $content_type Inferred content type of SCRIPT element.
     */
    public function test_get_script_content_type(string $html, ?string $content_type)
    {
        $processor = new WP_HTML_Tag_Processor($html);
        $processor->next_tag();

        $detected = $this->get_script_content_type_with($processor);

        if (isset($content_type, $detected)) {
            $this->assertSame(
                $content_type,
                $detected,
                'Misidentified the type of contents within the SCRIPT element.'
            );
        } elseif (isset($content_type)) {
            $this->assertSame(
                $content_type,
                $detected,
                'Should have identified the type of contents within the SCRIPT element but failed to recognize any type.'
            );
        } else {
            $this->assertNull(
                $detected,
                'Should have failed to identify the type of contents within the SCRIPT element.'
            );
        }
    }

    /**
     * Data provider for test_get_script_content_type.
     *
     * @return array[]
     */
    public static function data_get_script_content_type(): array
    {
        return [
            // Script tags without type or language attributes - should be JavaScript.
            'Script tag without attributes'              => [ '<script></script>', 'javascript' ],
            'Script tag with other attributes'           => [ '<script id="test"></script>', 'javascript' ],

            // Script tags with empty type attribute - should be JavaScript.
            'Script tag with empty type attribute'       => [ '<script type=""></script>', 'javascript' ],
            'Script tag with boolean type attribute'     => [ '<script type></script>', 'javascript' ],

            // Script tags without type but with language attribute - should be JavaScript.
            'Script tag with empty language attribute'   => [ '<script language=""></script>', 'javascript' ],
            'Script tag with boolean language attribute' => [ '<script language></script>', 'javascript' ],

            // Script tags with falsy but non-empty language attribute.
            'Script tag with language="0"'               => [ '<script language="0"></script>', null ],

            // Script tags with JavaScript MIME essence - should be JavaScript.
            'Script tag with application/ecmascript'     => [ '<script type="application/ecmascript"></script>', 'javascript' ],
            'Script tag with application/javascript'     => [ '<script type="application/javascript"></script>', 'javascript' ],
            'Script tag with application/x-ecmascript'   => [ '<script type="application/x-ecmascript"></script>', 'javascript' ],
            'Script tag with application/x-javascript'   => [ '<script type="application/x-javascript"></script>', 'javascript' ],
            'Script tag with text/ecmascript'            => [ '<script type="text/ecmascript"></script>', 'javascript' ],
            'Script tag with text/javascript'            => [ '<script type="text/javascript"></script>', 'javascript' ],
            'Script tag with text/javascript1.0'         => [ '<script type="text/javascript1.0"></script>', 'javascript' ],
            'Script tag with text/javascript1.1'         => [ '<script type="text/javascript1.1"></script>', 'javascript' ],
            'Script tag with text/javascript1.2'         => [ '<script type="text/javascript1.2"></script>', 'javascript' ],
            'Script tag with text/javascript1.3'         => [ '<script type="text/javascript1.3"></script>', 'javascript' ],
            'Script tag with text/javascript1.4'         => [ '<script type="text/javascript1.4"></script>', 'javascript' ],
            'Script tag with text/javascript1.5'         => [ '<script type="text/javascript1.5"></script>', 'javascript' ],
            'Script tag with text/jscript'               => [ '<script type="text/jscript"></script>', 'javascript' ],
            'Script tag with text/livescript'            => [ '<script type="text/livescript"></script>', 'javascript' ],
            'Script tag with text/x-ecmascript'          => [ '<script type="text/x-ecmascript"></script>', 'javascript' ],
            'Script tag with text/x-javascript'          => [ '<script type="text/x-javascript"></script>', 'javascript' ],

            // Case-insensitive matching for JavaScript MIME essence.
            'Script tag with UPPERCASE type'             => [ '<script type="TEXT/JAVASCRIPT"></script>', 'javascript' ],
            'Script tag with MixedCase type'             => [ '<script type="Text/JavaScript"></script>', 'javascript' ],
            'Script tag with APPLICATION/JAVASCRIPT'     => [ '<script type="APPLICATION/JAVASCRIPT"></script>', 'javascript' ],

            // Script tags with module type - should be JavaScript.
            'Script tag with module type'                => [ '<script type="module"></script>', 'javascript' ],
            'Script tag with MODULE type uppercase'      => [ '<script type="MODULE"></script>', 'javascript' ],
            'Script tag with MoDuLe type mixed case'     => [ '<script type="MoDuLe"></script>', 'javascript' ],

            // Script tags with whitespace around type - should strip whitespace.
            'Script tag with leading whitespace'         => [ '<script type=" text/javascript"></script>', 'javascript' ],
            'Script tag with trailing whitespace'        => [ '<script type="text/javascript "></script>', 'javascript' ],
            'Script tag with surrounding whitespace'     => [ '<script type=" text/javascript "></script>', 'javascript' ],
            'Script tag with tab whitespace'             => [ "<script type=\"\ttext/javascript\t\"></script>", 'javascript' ],
            'Script tag with newline whitespace'         => [ "<script type=\"\ntext/javascript\n\"></script>", 'javascript' ],
            'Script tag with mixed whitespace'           => [ "<script type=\" \t\ntext/javascript \t\n\"></script>", 'javascript' ],

            // Script tags with language attribute and non-empty value - should use text/{language}.
            'Script tag with language="javascript"'      => [ '<script language="javascript"></script>', 'javascript' ],
            'Script tag with language="JavaScript"'      => [ '<script language="JavaScript"></script>', 'javascript' ],
            'Script tag with language="ecmascript"'      => [ '<script language="ecmascript"></script>', 'javascript' ],
            'Script tag with language="jscript"'         => [ '<script language="jscript"></script>', 'javascript' ],
            'Script tag with language="livescript"'      => [ '<script language="livescript"></script>', 'javascript' ],

            // Whitespace is not trimmed in the language attribute.
            'Script tag with language=" javascript"'     => [ '<script language=" javascript"></script>', null ],

            // JSON MIME types - should be JSON.
            'Script tag with application/json type'      => [ '<script type="application/json"></script>', 'json' ],
            'Script tag with text/json type'             => [ '<script type="text/json"></script>', 'json' ],

            // importmap and speculationrules - should be JSON.
            'Script tag with importmap type'             => [ '<script type="importmap"></script>', 'json' ],
            'Script tag with speculationrules type'      => [ '<script type="speculationrules"></script>', 'json' ],

            // Case-insensitive matching for JSON types.
            'Script tag with APPLICATION/JSON uppercase' => [ '<script type="APPLICATION/JSON"></script>', 'json' ],
            'Script tag with Text/Json mixed case'       => [ '<script type="Text/Json"></script>', 'json' ],
            'Script tag with IMPORTMAP uppercase'        => [ '<script type="IMPORTMAP"></script>', 'json' ],
            'Script tag with ImportMap mixed case'       => [ '<script type="ImportMap"></script>', 'json' ],
            'Script tag with SPECULATIONRULES uppercase' => [ '<script type="SPECULATIONRULES"></script>', 'json' ],
            'Script tag with SpeculationRules mixed'     => [ '<script type="SpeculationRules"></script>', 'json' ],

            // Script tags with falsy but non-empty type attribute.
            'Script tag with type="0"'                   => [ '<script type="0"></script>', null ],

            // Unknown types should return null.
            'Script tag with unknown MIME type'          => [ '<script type="text/plain"></script>', null ],
            'Script tag with application/xml type'       => [ '<script type="application/xml"></script>', null ],
            'Script tag with random type'                => [ '<script type="random/type"></script>', null ],

            // Non-script tags - unknown content type.
            'DIV tag'                                    => [ '<div></div>', null ],
            'SPAN tag'                                   => [ '<span></span>', null ],
            'P tag'                                      => [ '<p></p>', null ],
        ];
    }

    /**
     * @ticket 64419
     *
     * @covers ::get_script_content_type()
     */
    public function test_get_script_content_type_returns_null_before_finding_tags()
    {
        $processor = new WP_HTML_Tag_Processor('Just some text');
        $processor->next_token();

        $this->assertNull(
            $this->get_script_content_type_with($processor),
            'Should fail to infer a content type when not matched on a SCRIPT element.'
        );
    }

    /**
     * @ticket 64419
     *
     * @covers ::get_script_content_type()
     */
    public function test_get_script_content_type_returns_null_for_non_html_namespace()
    {
        $processor = new WP_HTML_Tag_Processor('<script></script>');
        $processor->change_parsing_namespace('svg');
        $processor->next_tag();

        $this->assertSame(
            'SCRIPT',
            $processor->get_tag(),
            'Expected to find a SCRIPT tag in the SVG namespace: check test setup.'
        );

        $this->assertNull(
            $this->get_script_content_type_with($processor),
            'Should fail to infer content type for SCRIPT elements in non-HTML namespace'
        );
    }

    /**
     * Test helper to call private script content type getter.
     *
     * @since 7.0.0
     *
     * @param WP_HTML_Tag_Processor $processor Call the private method on this instance.
     * @return string|null Script content type if matched and recognized, else `null`.
     */
    private static function get_script_content_type_with(WP_HTML_Tag_Processor $processor)
    {
        $getter = function () {
            return $this->get_script_content_type();
        };

        return $getter->call($processor);
    }
}
