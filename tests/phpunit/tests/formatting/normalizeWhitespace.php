<?php

declare(strict_types=1);
/**
 * @group formatting
 *
 * @covers ::normalize_whitespace
 */
class Tests_Formatting_NormalizeWhitespace extends WP_UnitTestCase
{
    /**
     * Tests the the normalize_whitespace() function.
     *
     * @dataProvider data_normalize_whitespace
     */
    public function test_normalize_whitespace($input, $expected)
    {
        $this->assertSame($expected, normalize_whitespace($input));
    }

    /**
     * Data provider.
     *
     * @return array {
     *     @type array {
     *         @type string $input    Input content.
     *         @type string $expected Expected output.
     *     }
     * }
     */
    public function data_normalize_whitespace()
    {
        return [
            [
                '		',
                '',
            ],
            [
                "\rTEST\r",
                'TEST',
            ],
            [
                "\r\nMY TEST CONTENT\r\n",
                'MY TEST CONTENT',
            ],
            [
                "MY\r\nTEST\r\nCONTENT ",
                "MY\nTEST\nCONTENT",
            ],
            [
                "\tMY\rTEST\rCONTENT ",
                "MY\nTEST\nCONTENT",
            ],
            [
                "\tMY\t\t\tTEST\r\t\t\rCONTENT ",
                "MY TEST\n \nCONTENT",
            ],
            [
                "\tMY TEST \t\t\t CONTENT ",
                'MY TEST CONTENT',
            ],
        ];
    }
}
