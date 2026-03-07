<?php

declare(strict_types=1);

/**
 * @group compat
 * @group security-153
 *
 * @covers ::mb_substr
 * @covers ::_mb_substr
 */
class Tests_Compat_mbSubstr extends WP_UnitTestCase
{
    /**
     * Test that mb_substr() is always available (either from PHP or WP).
     */
    public function test_mb_substr_availability()
    {
        $this->assertTrue(
            in_array('mb_substr', get_defined_functions()['internal'], true),
            'Test runner should have `mbstring` extension active but doesn’t.'
        );
    }

    /**
     * @dataProvider data_utf8_substrings
     */
    public function test_mb_substr($input_string, $start, $length)
    {
        $this->assertSame(
            mb_substr($input_string, $start, $length, 'UTF-8'),
            _mb_substr($input_string, $start, $length, 'UTF-8')
        );
    }

    /**
     * @dataProvider data_utf8_substrings
     */
    public function test_8bit_mb_substr($input_string, $start, $length)
    {
        $this->assertSame(
            mb_substr($input_string, $start, $length, '8bit'),
            _mb_substr($input_string, $start, $length, '8bit')
        );
    }

    /**
     * Data provider.
     *
     * @return array[]
     */
    public function data_utf8_substrings()
    {
        return [
            'баба'           => [ 'баба', 0, 3 ],
            'баба'           => [ 'баба', 0, -1 ],
            'баба'           => [ 'баба', 1, null ],
            'баба'           => [ 'баба', -3, null ],
            'баба'           => [ 'баба', -3, 2 ],
            'баба'           => [ 'баба', -2, 1 ],
            'баба'           => [ 'баба', 30, 1 ],
            'баба'           => [ 'баба', 15, -30 ],
            'баба'           => [ 'баба', -5, -5 ],
            'баба'           => [ 'баба', 5, -3 ],
            'баба'           => [ 'баба', -3, 5 ],
            'I am your баба' => [ 'I am your баба', 0, 11 ],
        ];
    }

    /**
     * @link https://github.com/php/php-src/blob/php-5.6.8/ext/mbstring/tests/mb_substr_basic.phpt
     */
    public function test_mb_substr_phpcore_basic()
    {
        $string_ascii = 'ABCDEF';
        $string_mb    = '日本語テキストです。01234５６７８９。';

        $this->assertSame(
            'DEF',
            _mb_substr($string_ascii, 3),
            'Substring does not match expected for offset 3'
        );
        $this->assertSame(
            'DEF',
            _mb_substr($string_ascii, 3, 5, 'ISO-8859-1'),
            'Substring does not match expected for offset 3, length 5, with iso charset'
        );

        // Specific latin-1 as that is the default the core PHP test operates under.
        $this->assertSame(
            "\xA5本語",
            _mb_substr($string_mb, 2, 7, 'latin-1'),
            'Substring does not match expected for offset 2, length 7, with latin-1 charset'
        );
        $this->assertSame(
            '語テキストです',
            _mb_substr($string_mb, 2, 7, 'utf-8'),
            'Substring does not match expected for offset 2, length 7, with utf-8 charset'
        );
    }

    /**
     * @link https://github.com/php/php-src/blob/php-5.6.8/ext/mbstring/tests/mb_substr_variation1.phpt
     *
     * @dataProvider data_mb_substr_phpcore_input_type_handling
     *
     * @param mixed  $input    Input to pass to the function.
     * @param string $expected Expected function output.
     */
    public function test_mb_substr_phpcore_input_type_handling($input, $expected)
    {
        $start  = 0;
        $length = 5;

        $this->assertSame($expected, _mb_substr($input, $start, $length));
    }

    /**
     * Data provider.
     *
     * @return array
     */
    public function data_mb_substr_phpcore_input_type_handling()
    {
        $heredoc = <<<EOT
hello world
EOT;

        return [
            'integer zero'                   => [
                'input'    => 0,
                'expected' => '0',
            ],
            'integer 1'                      => [
                'input'    => 1,
                'expected' => '1',
            ],
            'positive integer'               => [
                'input'    => 12345,
                'expected' => '12345',
            ],
            'negative integer'               => [
                'input'    => -2345,
                'expected' => '-2345',
            ],
            // Float data.
            'positive float with fraction'   => [
                'input'    => 10.5,
                'expected' => '10.5',
            ],
            'negative float with fraction'   => [
                'input'    => -10.5,
                'expected' => '-10.5',
            ],
            'float scientific whole number'  => [
                'input'    => 12.3456789000e10,
                'expected' => '12345',
            ],
            'float scientific with fraction' => [
                'input'    => 12.3456789000E-10,
                'expected' => '1.234',
            ],
            'float, fraction only'           => [
                'input'    => .5,
                'expected' => '0.5',
            ],
            // Null data.
            'null'                           => [
                'input'    => null,
                'expected' => '',
            ],
            // Boolean data.
            'boolean true'                   => [
                'input'    => true,
                'expected' => '1',
            ],
            'boolean false'                  => [
                'input'    => false,
                'expected' => '',
            ],
            // Empty data.
            'empty string'                   => [
                'input'    => '',
                'expected' => '',
            ],
            // String data.
            'double quoted string'           => [
                'input'    => "string'",
                'expected' => 'strin',
            ],
            'single quoted string'           => [
                'input'    => 'string',
                'expected' => 'strin',
            ],
            'heredoc string'                 => [
                'input'    => $heredoc,
                'expected' => 'hello',
            ],
            // Object data.
            'object with __toString method'  => [
                'input'    => new ClassWithToStringForMbSubstr(),
                'expected' => 'Class',
            ],
        ];
    }
}

/* used in data_mb_substr_phpcore_input_type_handling() */
class ClassWithToStringForMbSubstr
{
    public function __toString()
    {
        return 'Class object';
    }
}
