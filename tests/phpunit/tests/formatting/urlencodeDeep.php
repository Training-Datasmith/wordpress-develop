<?php

declare(strict_types=1);

/**
 * @group formatting
 * @ticket 22300
 *
 * @covers ::urlencode_deep
 */
class Tests_Formatting_UrlencodeDeep extends WP_UnitTestCase
{
    /**
     * Tests the urlencode_deep() function pair by pair.
     *
     * @dataProvider data_urlencode_deep
     *
     * @param string $input
     * @param string $expected
     */
    public function test_urlencode_deep_should_encode_individual_value($input, $expected)
    {
        $this->assertSame($expected, urlencode_deep($input));
    }

    /**
     * Data provider.
     */
    public function data_urlencode_deep()
    {
        return [
            [ 'qwerty123456', 'qwerty123456' ],
            [ '|!"£$%&/()=?', '%7C%21%22%C2%A3%24%25%26%2F%28%29%3D%3F' ],
            [ '^é*ç°§;:_-.,', '%5E%C3%A9%2A%C3%A7%C2%B0%C2%A7%3B%3A_-.%2C' ],
            [ 'abc123 @#[]€', 'abc123+%40%23%5B%5D%E2%82%AC' ],
            [ 'abc123 @#[]€', urlencode('abc123 @#[]€') ],
        ];
    }

    /**
     * Tests the whole array as input.
     */
    public function test_urlencode_deep_should_encode_all_values_in_array()
    {
        $data = $this->data_urlencode_deep();

        $actual   = wp_list_pluck($data, 0);
        $expected = wp_list_pluck($data, 1);

        $this->assertSame($expected, urlencode_deep($actual));
    }
}
