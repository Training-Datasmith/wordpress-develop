<?php

declare(strict_types=1);

/**
 * @group formatting
 *
 * @covers ::sanitize_text_field
 * @covers ::sanitize_textarea_field
 */
class Tests_Formatting_SanitizeTextField extends WP_UnitTestCase
{
    /**
     * @ticket 32257
     * @dataProvider data_sanitize_text_field
     */
    public function test_sanitize_text_field($str, $expected)
    {
        if (is_array($expected)) {
            $expected_oneline   = $expected['oneline'];
            $expected_multiline = $expected['multiline'];
        } else {
            $expected_oneline   = $expected;
            $expected_multiline = $expected;
        }
        $this->assertSame($expected_oneline, sanitize_text_field($str));
        $this->assertSameIgnoreEOL($expected_multiline, sanitize_textarea_field($str));
    }

    public function data_sanitize_text_field()
    {
        return [
            [
                'оРангутанг', // Ensure UTF-8 text is safe. The Р is D0 A0 and A0 is the non-breaking space.
                'оРангутанг',
            ],
            [
                'САПР',       // Ensure UTF-8 text is safe. the Р is D0 A0 and A0 is the non-breaking space.
                'САПР',
            ],
            [
                'one is < two',
                'one is &lt; two',
            ],
            [
                "one is <\n two",
                [
                    'oneline'   => 'one is &lt; two',
                    'multiline' => "one is &lt;\n two",
                ],
            ],
            [
                "foo <div\n> bar",
                [
                    'oneline'   => 'foo bar',
                    'multiline' => 'foo  bar',
                ],
            ],
            [
                "foo <\ndiv\n> bar",
                [
                    'oneline'   => 'foo &lt; div > bar',
                    'multiline' => "foo &lt;\ndiv\n> bar",
                ],
            ],
            [
                'tags <span>are</span> <em>not allowed</em> here',
                'tags are not allowed here',
            ],
            [
                ' we should trim leading and trailing whitespace ',
                'we should trim leading and trailing whitespace',
            ],
            [
                'we  trim  extra  internal  whitespace  only  in  single  line  texts',
                [
                    'oneline'   => 'we trim extra internal whitespace only in single line texts',
                    'multiline' => 'we  trim  extra  internal  whitespace  only  in  single  line  texts',
                ],
            ],
            [
                "tabs \tget removed in single line texts",
                [
                    'oneline'   => 'tabs get removed in single line texts',
                    'multiline' => "tabs \tget removed in single line texts",
                ],
            ],
            [
                "newlines are allowed only\n in multiline texts",
                [
                    'oneline'   => 'newlines are allowed only in multiline texts',
                    'multiline' => "newlines are allowed only\n in multiline texts",
                ],
            ],
            [
                'We also %AB remove %ab octets',
                'We also remove octets',
            ],
            [
                'We don\'t need to wory about %A
				B removing %a
				b octets even when %a	B they are obscured by whitespace',
                [
                    'oneline'   => 'We don\'t need to wory about %A B removing %a b octets even when %a B they are obscured by whitespace',
                    'multiline' => "We don't need to wory about %A\n				B removing %a\n				b octets even when %a	B they are obscured by whitespace",
                ],
            ],
            [
                '%AB%BC%DE', // Just octets.
                '',          // Empty as we strip all the octets out.
            ],
            [
                'Invalid octets remain %II',
                'Invalid octets remain %II',
            ],
            [
                'Nested octets %%%ABABAB %A%A%ABBB',
                'Nested octets',
            ],
            [
                [],
                '',
            ],
            [
                [ 1, 2, 'foo' ],
                '',
            ],
            [
                new WP_Query(),
                '',
            ],
            [
                2,
                '2',
            ],
            [
                false,
                '',
            ],
            [
                true,
                '1',
            ],
            [
                10.1,
                '10.1',
            ],
        ];
    }

    /**
     * @ticket 60357
     */
    public function test_sanitize_text_field_filter()
    {
        $filter = new MockAction();
        add_filter('sanitize_text_field', [ $filter, 'filter' ]);

        $this->assertSame('example', sanitize_text_field('example'));
        $this->assertSame(1, $filter->get_call_count(), 'The sanitize_text_field filter was not called.');
    }

    /**
     * @ticket 60357
     */
    public function test_sanitize_textarea_field_filter()
    {
        $filter = new MockAction();
        add_filter('sanitize_textarea_field', [ $filter, 'filter' ]);

        $this->assertSame('example', sanitize_textarea_field('example'));
        $this->assertSame(1, $filter->get_call_count(), 'The sanitize_textarea_field filter was not called.');
    }
}
