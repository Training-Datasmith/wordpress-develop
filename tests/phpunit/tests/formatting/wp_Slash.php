<?php

declare(strict_types=1);

/**
 * @group formatting
 *
 * @covers ::wp_slash
 */
class Tests_Formatting_wpSlash extends WP_UnitTestCase
{
    /**
     * @ticket 42195
     *
     * @dataProvider data_wp_slash
     *
     * @param string $value
     * @param string $expected
     */
    public function test_wp_slash($value, $expected)
    {
        $this->assertSame($expected, wp_slash($value));
    }

    /**
     * Data provider for test_wp_slash().
     *
     * @return array {
     *     @type array {
     *         @type mixed  $value    The value passed to wp_slash().
     *         @type string $expected The expected output of wp_slash().
     *     }
     * }
     */
    public function data_wp_slash()
    {
        return [
            [ 123, 123 ],
            [ 123.4, 123.4 ],
            [ true, true ],
            [ false, false ],
            [
                [
                    'hello',
                    null,
                    '"string"',
                    125.41,
                ],
                [
                    'hello',
                    null,
                    '\"string\"',
                    125.41,
                ],
            ],
            [ "first level 'string'", "first level \'string\'" ],
        ];
    }

    /**
     * @ticket 24106
     */
    public function test_adds_slashes()
    {
        $old = "I can't see, isn't that it?";
        $new = "I can\'t see, isn\'t that it?";
        $this->assertSame($new, wp_slash($old));
        $this->assertSame("I can\\\\\'t see, isn\\\\\'t that it?", wp_slash($new));
        $this->assertSame([ 'a' => $new ], wp_slash([ 'a' => $old ])); // Keyed array.
        $this->assertSame([ $new ], wp_slash([ $old ])); // Non-keyed.
    }

    /**
     * @ticket 24106
     */
    public function test_preserves_original_datatype()
    {

        $this->assertTrue(wp_slash(true));
        $this->assertFalse(wp_slash(false));
        $this->assertSame(4, wp_slash(4));
        $this->assertSame('foo', wp_slash('foo'));
        $arr      = [
            'a' => true,
            'b' => false,
            'c' => 4,
            'd' => 'foo',
        ];
        $arr['e'] = $arr; // Add a sub-array.
        $this->assertSame($arr, wp_slash($arr)); // Keyed array.
        $this->assertSame(array_values($arr), wp_slash(array_values($arr))); // Non-keyed.

        $obj = new stdClass();
        foreach ($arr as $k => $v) {
            $obj->$k = $v;
        }
        $this->assertSame($obj, wp_slash($obj));
    }

    /**
     * @ticket 24106
     */
    public function test_add_even_more_slashes()
    {
        $old = 'single\\slash double\\\\slash triple\\\\\\slash';
        $new = 'single\\\\slash double\\\\\\\\slash triple\\\\\\\\\\\\slash';
        $this->assertSame($new, wp_slash($old));
        $this->assertSame([ 'a' => $new ], wp_slash([ 'a' => $old ])); // Keyed array.
        $this->assertSame([ $new ], wp_slash([ $old ])); // Non-keyed.
    }

    /**
     * Tests that addslashes_gpc() returns the same result as wp_slash() for strings.
     *
     * @ticket 64539
     * @covers ::addslashes_gpc
     * @expectedDeprecated addslashes_gpc
     */
    public function test_addslashes_gpc_matches_wp_slash_for_strings()
    {
        $input = "String with 'quotes' and \"double quotes\"";
        $this->assertSame(wp_slash($input), addslashes_gpc($input));
    }

    /**
     * Tests that addslashes_gpc() returns the same result as wp_slash() for arrays.
     *
     * @ticket 64539
     * @covers ::addslashes_gpc
     * @expectedDeprecated addslashes_gpc
     */
    public function test_addslashes_gpc_matches_wp_slash_for_arrays()
    {
        $input = [
            'field1' => "Value with 'apostrophe'",
            'field2' => 'Value with "quotes"',
            'field3' => 'user@example.com',
            'nested' => [
                'key1' => 'Nested value with \\ backslash',
                'key2' => [ 'deeply', 'nested', 'array' ],
            ],
        ];

        $this->assertSame(wp_slash($input), addslashes_gpc($input));
    }
}
