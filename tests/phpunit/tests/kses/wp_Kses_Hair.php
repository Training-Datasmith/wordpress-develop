<?php

declare(strict_types=1);
/**
 * Test wp_kses_hair() function.
 *
 * @group kses
 */
class Tests_Kses_WpKsesHair extends WP_UnitTestCase
{
    /**
     * Standard allowed protocols for testing.
     *
     * @var array
     */
    protected $allowed_protocols;

    /**
     * Set up before each test.
     */
    public function set_up()
    {
        parent::set_up();
        $this->allowed_protocols = wp_allowed_protocols();
    }

    /**
     * Test wp_kses_hair() with various attribute patterns.
     *
     * @ticket 63724
     * @dataProvider data_attribute_parsing
     * @covers wp_kses_hair
     */
    public function test_attribute_parsing(string $input, array $expected)
    {
        $result = wp_kses_hair($input, $this->allowed_protocols);
        $this->assertSame($expected, $result);
    }

    /**
     * Data provider for attribute parsing tests.
     *
     * @return Generator
     */
    public function data_attribute_parsing()
    {
        yield 'empty attributes' => [
            '',
            [],
        ];

        yield 'prematurely-terminated attributes' => [
            '>',
            [],
        ];

        yield 'prematurely-terminated malformed attributes' => [
            'foo>bar="baz"',
            [
                'foo' => [
                    'name'  => 'foo',
                    'value' => '',
                    'whole' => 'foo',
                    'vless' => 'y',
                ],
            ],
        ];

        yield 'single attribute with double quotes' => [
            'class="test-class"',
            [
                'class' => [
                    'name'  => 'class',
                    'value' => 'test-class',
                    'whole' => 'class="test-class"',
                    'vless' => 'n',
                ],
            ],
        ];

        yield 'single attribute with single quotes' => [
            "title='My Title'",
            [
                'title' => [
                    'name'  => 'title',
                    'value' => 'My Title',
                    'whole' => 'title="My Title"',
                    'vless' => 'n',
                ],
            ],
        ];

        yield 'unquoted attribute value' => [
            'id=test123',
            [
                'id' => [
                    'name'  => 'id',
                    'value' => 'test123',
                    'whole' => 'id="test123"',
                    'vless' => 'n',
                ],
            ],
        ];

        yield 'multiple attributes' => [
            'class="btn" id="submit-btn" data-value="123"',
            [
                'class'      => [
                    'name'  => 'class',
                    'value' => 'btn',
                    'whole' => 'class="btn"',
                    'vless' => 'n',
                ],
                'id'         => [
                    'name'  => 'id',
                    'value' => 'submit-btn',
                    'whole' => 'id="submit-btn"',
                    'vless' => 'n',
                ],
                'data-value' => [
                    'name'  => 'data-value',
                    'value' => '123',
                    'whole' => 'data-value="123"',
                    'vless' => 'n',
                ],
            ],
        ];

        yield 'valueless attributes' => [
            'disabled required checked',
            [
                'disabled' => [
                    'name'  => 'disabled',
                    'value' => '',
                    'whole' => 'disabled',
                    'vless' => 'y',
                ],
                'required' => [
                    'name'  => 'required',
                    'value' => '',
                    'whole' => 'required',
                    'vless' => 'y',
                ],
                'checked'  => [
                    'name'  => 'checked',
                    'value' => '',
                    'whole' => 'checked',
                    'vless' => 'y',
                ],
            ],
        ];

        yield 'valueless attribute at end' => [
            'type="checkbox" checked',
            [
                'type'    => [
                    'name'  => 'type',
                    'value' => 'checkbox',
                    'whole' => 'type="checkbox"',
                    'vless' => 'n',
                ],
                'checked' => [
                    'name'  => 'checked',
                    'value' => '',
                    'whole' => 'checked',
                    'vless' => 'y',
                ],
            ],
        ];

        yield 'mixed valued and valueless' => [
            'disabled class="form-control" readonly id=input1',
            [
                'disabled' => [
                    'name'  => 'disabled',
                    'value' => '',
                    'whole' => 'disabled',
                    'vless' => 'y',
                ],
                'class'    => [
                    'name'  => 'class',
                    'value' => 'form-control',
                    'whole' => 'class="form-control"',
                    'vless' => 'n',
                ],
                'readonly' => [
                    'name'  => 'readonly',
                    'value' => '',
                    'whole' => 'readonly',
                    'vless' => 'y',
                ],
                'id'       => [
                    'name'  => 'id',
                    'value' => 'input1',
                    'whole' => 'id="input1"',
                    'vless' => 'n',
                ],
            ],
        ];

        yield 'named character references' => [
            'title="&lt;Hello&gt; &amp; &quot;World&quot;"',
            [
                'title' => [
                    'name'  => 'title',
                    'value' => '&lt;Hello&gt; &amp; &quot;World&quot;',
                    'whole' => 'title="&lt;Hello&gt; &amp; &quot;World&quot;"',
                    'vless' => 'n',
                ],
            ],
        ];

        yield 'numeric decimal character references' => [
            'title="&#60;test&#62;"',
            [
                'title' => [
                    'name'  => 'title',
                    'value' => '&lt;test&gt;',
                    'whole' => 'title="&lt;test&gt;"',
                    'vless' => 'n',
                ],
            ],
        ];

        yield 'numeric hex character references lowercase' => [
            'title="&#x3C;hex&#x3E;"',
            [
                'title' => [
                    'name'  => 'title',
                    'value' => '&lt;hex&gt;',
                    'whole' => 'title="&lt;hex&gt;"',
                    'vless' => 'n',
                ],
            ],
        ];

        yield 'numeric hex character references uppercase' => [
            'title="&#X3C;HEX&#X3E;"',
            [
                'title' => [
                    'name'  => 'title',
                    'value' => '&lt;HEX&gt;',
                    'whole' => 'title="&lt;HEX&gt;"',
                    'vless' => 'n',
                ],
            ],
        ];

        yield 'invalid character references' => [
            'title="&invalid; &#; &#x;"',
            [
                'title' => [
                    'name'  => 'title',
                    'value' => '&amp;invalid; &amp;#; &amp;#x;',
                    'whole' => 'title="&amp;invalid; &amp;#; &amp;#x;"',
                    'vless' => 'n',
                ],
            ],
        ];

        yield 'double quotes' => [
            'data-text="Double quoted value"',
            [
                'data-text' => [
                    'name'  => 'data-text',
                    'value' => 'Double quoted value',
                    'whole' => 'data-text="Double quoted value"',
                    'vless' => 'n',
                ],
            ],
        ];

        yield 'single quotes' => [
            "data-text='Single quoted value'",
            [
                'data-text' => [
                    'name'  => 'data-text',
                    'value' => 'Single quoted value',
                    'whole' => 'data-text="Single quoted value"',
                    'vless' => 'n',
                ],
            ],
        ];

        yield 'mixed quotes' => [
            'title="double" alt=\'single\' id=unquoted',
            [
                'title' => [
                    'name'  => 'title',
                    'value' => 'double',
                    'whole' => 'title="double"',
                    'vless' => 'n',
                ],
                'alt'   => [
                    'name'  => 'alt',
                    'value' => 'single',
                    'whole' => 'alt="single"',
                    'vless' => 'n',
                ],
                'id'    => [
                    'name'  => 'id',
                    'value' => 'unquoted',
                    'whole' => 'id="unquoted"',
                    'vless' => 'n',
                ],
            ],
        ];

        yield 'single quotes in double quoted value' => [
            'title="It\'s working"',
            [
                'title' => [
                    'name'  => 'title',
                    'value' => 'It&apos;s working',
                    'whole' => 'title="It&apos;s working"',
                    'vless' => 'n',
                ],
            ],
        ];

        yield 'double quotes in single quoted value' => [
            'title=\'He said "hello"\'',
            [
                'title' => [
                    'name'  => 'title',
                    'value' => 'He said &quot;hello&quot;',
                    'whole' => 'title="He said &quot;hello&quot;"',
                    'vless' => 'n',
                ],
            ],
        ];

        yield 'unquoted with special chars' => [
            'data-value=test-123_value',
            [
                'data-value' => [
                    'name'  => 'data-value',
                    'value' => 'test-123_value',
                    'whole' => 'data-value="test-123_value"',
                    'vless' => 'n',
                ],
            ],
        ];

        yield 'empty string' => [
            '',
            [],
        ];

        yield 'whitespace only' => [
            '   	  ',
            [],
        ];

        yield 'invalid attribute name starting with number' => [
            '1invalid="value"',
            [
                '1invalid' => [
                    'name'  => '1invalid',
                    'value' => 'value',
                    'whole' => '1invalid="value"',
                    'vless' => 'n',
                ],
            ],
        ];

        yield 'invalid attribute name special chars' => [
            '@invalid="value" $bad="value"',
            [
                '@invalid' => [
                    'name'  => '@invalid',
                    'value' => 'value',
                    'whole' => '@invalid="value"',
                    'vless' => 'n',
                ],
                '$bad'     => [
                    'name'  => '$bad',
                    'value' => 'value',
                    'whole' => '$bad="value"',
                    'vless' => 'n',
                ],
            ],
        ];

        yield 'duplicate attributes first wins' => [
            'id="first" class="test" id="second"',
            [
                'id'    => [
                    'name'  => 'id',
                    'value' => 'first',
                    'whole' => 'id="first"',
                    'vless' => 'n',
                ],
                'class' => [
                    'name'  => 'class',
                    'value' => 'test',
                    'whole' => 'class="test"',
                    'vless' => 'n',
                ],
            ],
        ];

        yield 'malformed unclosed double quote' => [
            'title="unclosed class="test"',
            [
                'title' => [
                    'name'  => 'title',
                    'value' => 'unclosed class=',
                    'whole' => 'title="unclosed class="',
                    'vless' => 'n',
                ],
                'test"' => [
                    'name'  => 'test"',
                    'value' => '',
                    'whole' => 'test"',
                    'vless' => 'y',
                ],
            ],
        ];

        yield 'very long attribute value' => [
            'data-long="' . str_repeat('a', 10000) . '"',
            [
                'data-long' => [
                    'name'  => 'data-long',
                    'value' => str_repeat('a', 10000),
                    'whole' => 'data-long="' . str_repeat('a', 10000) . '"',
                    'vless' => 'n',
                ],
            ],
        ];

        yield 'attribute names with colons and dots' => [
            'xml:lang="en" data.value="test" xlink:href="#anchor"',
            [
                'xml:lang'   => [
                    'name'  => 'xml:lang',
                    'value' => 'en',
                    'whole' => 'xml:lang="en"',
                    'vless' => 'n',
                ],
                'data.value' => [
                    'name'  => 'data.value',
                    'value' => 'test',
                    'whole' => 'data.value="test"',
                    'vless' => 'n',
                ],
                'xlink:href' => [
                    'name'  => 'xlink:href',
                    'value' => '#anchor',
                    'whole' => 'xlink:href="#anchor"',
                    'vless' => 'n',
                ],
            ],
        ];

        yield 'multiple spaces between attributes' => [
            'class="test"    id="value"		title="spaced"',
            [
                'class' => [
                    'name'  => 'class',
                    'value' => 'test',
                    'whole' => 'class="test"',
                    'vless' => 'n',
                ],
                'id'    => [
                    'name'  => 'id',
                    'value' => 'value',
                    'whole' => 'id="value"',
                    'vless' => 'n',
                ],
                'title' => [
                    'name'  => 'title',
                    'value' => 'spaced',
                    'whole' => 'title="spaced"',
                    'vless' => 'n',
                ],
            ],
        ];

        yield 'spaces around equals' => [
            'id = "spaced" class ="left" title= "right"',
            [
                'id'    => [
                    'name'  => 'id',
                    'value' => 'spaced',
                    'whole' => 'id="spaced"',
                    'vless' => 'n',
                ],
                'class' => [
                    'name'  => 'class',
                    'value' => 'left',
                    'whole' => 'class="left"',
                    'vless' => 'n',
                ],
                'title' => [
                    'name'  => 'title',
                    'value' => 'right',
                    'whole' => 'title="right"',
                    'vless' => 'n',
                ],
            ],
        ];

        yield 'common WordPress attributes' => [
            'class="wp-block" id="post-123" style="color: red;"',
            [
                'class' => [
                    'name'  => 'class',
                    'value' => 'wp-block',
                    'whole' => 'class="wp-block"',
                    'vless' => 'n',
                ],
                'id'    => [
                    'name'  => 'id',
                    'value' => 'post-123',
                    'whole' => 'id="post-123"',
                    'vless' => 'n',
                ],
                'style' => [
                    'name'  => 'style',
                    'value' => 'color: red;',
                    'whole' => 'style="color: red;"',
                    'vless' => 'n',
                ],
            ],
        ];

        yield 'data attributes' => [
            'data-post-id="123" data-action="delete" data-confirm="true"',
            [
                'data-post-id' => [
                    'name'  => 'data-post-id',
                    'value' => '123',
                    'whole' => 'data-post-id="123"',
                    'vless' => 'n',
                ],
                'data-action'  => [
                    'name'  => 'data-action',
                    'value' => 'delete',
                    'whole' => 'data-action="delete"',
                    'vless' => 'n',
                ],
                'data-confirm' => [
                    'name'  => 'data-confirm',
                    'value' => 'true',
                    'whole' => 'data-confirm="true"',
                    'vless' => 'n',
                ],
            ],
        ];

        yield 'aria attributes' => [
            'aria-label="Close" aria-hidden="true" aria-describedby="help-text"',
            [
                'aria-label'       => [
                    'name'  => 'aria-label',
                    'value' => 'Close',
                    'whole' => 'aria-label="Close"',
                    'vless' => 'n',
                ],
                'aria-hidden'      => [
                    'name'  => 'aria-hidden',
                    'value' => 'true',
                    'whole' => 'aria-hidden="true"',
                    'vless' => 'n',
                ],
                'aria-describedby' => [
                    'name'  => 'aria-describedby',
                    'value' => 'help-text',
                    'whole' => 'aria-describedby="help-text"',
                    'vless' => 'n',
                ],
            ],
        ];

        yield 'role attribute' => [
            'role="navigation"',
            [
                'role' => [
                    'name'  => 'role',
                    'value' => 'navigation',
                    'whole' => 'role="navigation"',
                    'vless' => 'n',
                ],
            ],
        ];

        yield 'tabindex attribute' => [
            'tabindex="0"',
            [
                'tabindex' => [
                    'name'  => 'tabindex',
                    'value' => '0',
                    'whole' => 'tabindex="0"',
                    'vless' => 'n',
                ],
            ],
        ];

        yield 'complex WordPress attributes' => [
            'class="wp-block-button__link" href="https://wordpress.org" target="_blank" rel="noopener" aria-label="Visit WordPress" data-track="click"',
            [
                'class'      => [
                    'name'  => 'class',
                    'value' => 'wp-block-button__link',
                    'whole' => 'class="wp-block-button__link"',
                    'vless' => 'n',
                ],
                'href'       => [
                    'name'  => 'href',
                    'value' => 'https://wordpress.org',
                    'whole' => 'href="https://wordpress.org"',
                    'vless' => 'n',
                ],
                'target'     => [
                    'name'  => 'target',
                    'value' => '_blank',
                    'whole' => 'target="_blank"',
                    'vless' => 'n',
                ],
                'rel'        => [
                    'name'  => 'rel',
                    'value' => 'noopener',
                    'whole' => 'rel="noopener"',
                    'vless' => 'n',
                ],
                'aria-label' => [
                    'name'  => 'aria-label',
                    'value' => 'Visit WordPress',
                    'whole' => 'aria-label="Visit WordPress"',
                    'vless' => 'n',
                ],
                'data-track' => [
                    'name'  => 'data-track',
                    'value' => 'click',
                    'whole' => 'data-track="click"',
                    'vless' => 'n',
                ],
            ],
        ];

        yield 'underscore in attribute name' => [
            '_custom="value" data_value="test"',
            [
                '_custom'    => [
                    'name'  => '_custom',
                    'value' => 'value',
                    'whole' => '_custom="value"',
                    'vless' => 'n',
                ],
                'data_value' => [
                    'name'  => 'data_value',
                    'value' => 'test',
                    'whole' => 'data_value="test"',
                    'vless' => 'n',
                ],
            ],
        ];

        yield 'empty attribute value' => [
            'title="" alt=\'\' class=""',
            [
                'title' => [
                    'name'  => 'title',
                    'value' => '',
                    'whole' => 'title=""',
                    'vless' => 'n',
                ],
                'alt'   => [
                    'name'  => 'alt',
                    'value' => '',
                    'whole' => 'alt=""',
                    'vless' => 'n',
                ],
                'class' => [
                    'name'  => 'class',
                    'value' => '',
                    'whole' => 'class=""',
                    'vless' => 'n',
                ],
            ],
        ];

        yield 'forward slashes between attributes' => [
            'att / att2=2 /// att3="3"',
            [
                'att'  => [
                    'name'  => 'att',
                    'value' => '',
                    'whole' => 'att',
                    'vless' => 'y',
                ],
                'att2' => [
                    'name'  => 'att2',
                    'value' => '2',
                    'whole' => 'att2="2"',
                    'vless' => 'n',
                ],
                'att3' => [
                    'name'  => 'att3',
                    'value' => '3',
                    'whole' => 'att3="3"',
                    'vless' => 'n',
                ],
            ],
        ];

        yield 'tab whitespace' => [
            "att='val'\tatt2='val2'",
            [
                'att'  => [
                    'name'  => 'att',
                    'value' => 'val',
                    'whole' => 'att="val"',
                    'vless' => 'n',
                ],
                'att2' => [
                    'name'  => 'att2',
                    'value' => 'val2',
                    'whole' => 'att2="val2"',
                    'vless' => 'n',
                ],
            ],
        ];

        yield 'form feed whitespace' => [
            "att='val'\fatt2='val2'",
            [
                'att'  => [
                    'name'  => 'att',
                    'value' => 'val',
                    'whole' => 'att="val"',
                    'vless' => 'n',
                ],
                'att2' => [
                    'name'  => 'att2',
                    'value' => 'val2',
                    'whole' => 'att2="val2"',
                    'vless' => 'n',
                ],
            ],
        ];

        yield 'carriage return whitespace' => [
            "att='val'\ratt2='val2'",
            [
                'att'  => [
                    'name'  => 'att',
                    'value' => 'val',
                    'whole' => 'att="val"',
                    'vless' => 'n',
                ],
                'att2' => [
                    'name'  => 'att2',
                    'value' => 'val2',
                    'whole' => 'att2="val2"',
                    'vless' => 'n',
                ],
            ],
        ];

        yield 'newline whitespace' => [
            "att='val'\ratt2='val2'",
            [
                'att'  => [
                    'name'  => 'att',
                    'value' => 'val',
                    'whole' => 'att="val"',
                    'vless' => 'n',
                ],
                'att2' => [
                    'name'  => 'att2',
                    'value' => 'val2',
                    'whole' => 'att2="val2"',
                    'vless' => 'n',
                ],
            ],
        ];

        yield 'mixed whitespace types' => [
            "att=\"val\"\t\r\n\f att2=\"val2\"",
            [
                'att'  => [
                    'name'  => 'att',
                    'value' => 'val',
                    'whole' => 'att="val"',
                    'vless' => 'n',
                ],
                'att2' => [
                    'name'  => 'att2',
                    'value' => 'val2',
                    'whole' => 'att2="val2"',
                    'vless' => 'n',
                ],
            ],
        ];

        // Malformed Equals Patterns.
        yield 'multiple equals signs' => [
            'att=="val"',
            [
                'att' => [
                    'name'  => 'att',
                    'value' => '=&quot;val&quot;',
                    'whole' => 'att="=&quot;val&quot;"',
                    'vless' => 'n',
                ],
            ],
        ];

        yield 'equals with strange spacing' => [
            'att= ="val"',
            [
                'att' => [
                    'name'  => 'att',
                    'value' => '=&quot;val&quot;',
                    'whole' => 'att="=&quot;val&quot;"',
                    'vless' => 'n',
                ],
            ],
        ];

        yield 'triple equals signs' => [
            'att==="val"',
            [
                'att' => [
                    'name'  => 'att',
                    'value' => '==&quot;val&quot;',
                    'whole' => 'att="==&quot;val&quot;"',
                    'vless' => 'n',
                ],
            ],
        ];

        yield 'equals echo pattern' => [
            "att==echo 'something'",
            [
                'att'         => [
                    'name'  => 'att',
                    'value' => '=echo',
                    'whole' => 'att="=echo"',
                    'vless' => 'n',
                ],
                "'something'" => [
                    'name'  => "'something'",
                    'value' => '',
                    'whole' => "'something'",
                    'vless' => 'y',
                ],
            ],
        ];

        yield 'attribute starting with equals' => [
            '= bool k=v',
            [
                '='    => [
                    'name'  => '=',
                    'value' => '',
                    'whole' => '=',
                    'vless' => 'y',
                ],
                'bool' => [
                    'name'  => 'bool',
                    'value' => '',
                    'whole' => 'bool',
                    'vless' => 'y',
                ],
                'k'    => [
                    'name'  => 'k',
                    'value' => 'v',
                    'whole' => 'k="v"',
                    'vless' => 'n',
                ],
            ],
        ];

        yield 'mixed quotes and equals chaos' => [
            'k=v ="' . "' j=w",
            [
                'k'        => [
                    'name'  => 'k',
                    'value' => 'v',
                    'whole' => 'k="v"',
                    'vless' => 'n',
                ],
                '="' . "'" => [
                    'name'  => '="' . "'",
                    'value' => '',
                    'whole' => '="' . "'",
                    'vless' => 'y',
                ],
                'j'        => [
                    'name'  => 'j',
                    'value' => 'w',
                    'whole' => 'j="w"',
                    'vless' => 'n',
                ],
            ],
        ];

        yield 'triple equals quoted whitespace' => [
            '==="  "',
            [
                '=' => [
                    'name'  => '=',
                    'value' => '=&quot;',
                    'whole' => '=="=&quot;"',
                    'vless' => 'n',
                ],
                '"' => [
                    'name'  => '"',
                    'value' => '',
                    'whole' => '"',
                    'vless' => 'y',
                ],
            ],
        ];

        yield 'boolean with contradictory value' => [
            'disabled=enabled checked',
            [
                'disabled' => [
                    'name'  => 'disabled',
                    'value' => 'enabled',
                    'whole' => 'disabled="enabled"',
                    'vless' => 'n',
                ],
                'checked'  => [
                    'name'  => 'checked',
                    'value' => '',
                    'whole' => 'checked',
                    'vless' => 'y',
                ],
            ],
        ];

        yield 'empty attribute name with value' => [
            '="value" class="test"',
            [
                '="value"' => [
                    'name'  => '="value"',
                    'value' => '',
                    'whole' => '="value"',
                    'vless' => 'y',
                ],
                'class'    => [
                    'name'  => 'class',
                    'value' => 'test',
                    'whole' => 'class="test"',
                    'vless' => 'n',
                ],
            ],
        ];
    }

    /**
     * Test wp_kses_hair() with URL protocol filtering.
     *
     * @ticket 63724
     * @dataProvider data_protocol_filtering
     * @covers wp_kses_hair
     */
    public function test_protocol_filtering(string $input, array $expected)
    {
        $result = wp_kses_hair($input, $this->allowed_protocols);
        $this->assertSame($expected, $result);
    }

    /**
     * Data provider for URL protocol filtering tests.
     *
     * @return Generator
     */
    public function data_protocol_filtering()
    {
        yield 'href allowed protocol http' => [
            'href="http://example.com"',
            [
                'href' => [
                    'name'  => 'href',
                    'value' => 'http://example.com',
                    'whole' => 'href="http://example.com"',
                    'vless' => 'n',
                ],
            ],
        ];

        yield 'href allowed protocol https' => [
            'href="https://secure.example.com"',
            [
                'href' => [
                    'name'  => 'href',
                    'value' => 'https://secure.example.com',
                    'whole' => 'href="https://secure.example.com"',
                    'vless' => 'n',
                ],
            ],
        ];

        yield 'href disallowed protocol javascript' => [
            'href="javascript:alert(1)"',
            [
                'href' => [
                    'name'  => 'href',
                    'value' => 'alert(1)',
                    'whole' => 'href="alert(1)"',
                    'vless' => 'n',
                ],
            ],
        ];

        yield 'href disallowed protocol javascript single quotes' => [
            "href='javascript:alert(1)'",
            [
                'href' => [
                    'name'  => 'href',
                    'value' => 'alert(1)',
                    'whole' => 'href="alert(1)"',
                    'vless' => 'n',
                ],
            ],
        ];

        yield 'href disallowed protocol javascript unquoted' => [
            'href=javascript:alert(1)',
            [
                'href' => [
                    'name'  => 'href',
                    'value' => 'alert(1)',
                    'whole' => 'href="alert(1)"',
                    'vless' => 'n',
                ],
            ],
        ];

        yield 'src allowed protocol' => [
            'src="https://example.com/image.jpg"',
            [
                'src' => [
                    'name'  => 'src',
                    'value' => 'https://example.com/image.jpg',
                    'whole' => 'src="https://example.com/image.jpg"',
                    'vless' => 'n',
                ],
            ],
        ];

        yield 'src data protocol' => [
            'src="data:text/html,<script>alert(1)</script>"',
            [
                'src' => [
                    'name'  => 'src',
                    'value' => 'text/html,&lt;script&gt;alert(1)&lt;/script&gt;',
                    'whole' => 'src="text/html,&lt;script&gt;alert(1)&lt;/script&gt;"',
                    'vless' => 'n',
                ],
            ],
        ];

        yield 'protocol filtering only uri attributes' => [
            'data-url="javascript:alert(1)"',
            [
                'data-url' => [
                    'name'  => 'data-url',
                    'value' => 'javascript:alert(1)',
                    'whole' => 'data-url="javascript:alert(1)"',
                    'vless' => 'n',
                ],
            ],
        ];

        yield 'href relative url' => [
            'href="/path/to/page"',
            [
                'href' => [
                    'name'  => 'href',
                    'value' => '/path/to/page',
                    'whole' => 'href="/path/to/page"',
                    'vless' => 'n',
                ],
            ],
        ];

        yield 'href anchor link' => [
            'href="#section"',
            [
                'href' => [
                    'name'  => 'href',
                    'value' => '#section',
                    'whole' => 'href="#section"',
                    'vless' => 'n',
                ],
            ],
        ];
    }

    /**
     * Test wp_kses_hair() with custom allowed protocols.
     *
     * @ticket 63724
     * @covers wp_kses_hair
     */
    public function test_custom_allowed_protocols()
    {
        $custom_protocols = [ 'gopher' ];
        $attr             = 'href="gopher://gopher.example.org"';
        $result           = wp_kses_hair($attr, $custom_protocols);

        $expected = [
            'href' => [
                'name'  => 'href',
                'value' => 'gopher://gopher.example.org',
                'whole' => 'href="gopher://gopher.example.org"',
                'vless' => 'n',
            ],
        ];

        $this->assertSame($expected, $result);
    }
}
