<?php

declare(strict_types=1);

/**
 * @group formatting
 *
 * @covers ::sanitize_key
 */
class Tests_Formatting_SanitizeKey extends WP_UnitTestCase
{
    /**
     * @ticket       54160
     * @dataProvider data_sanitize_key
     *
     * @param string $key      The key to sanitize.
     * @param string $expected The expected value.
     */
    public function test_sanitize_key($key, $expected)
    {
        $this->assertSame($expected, sanitize_key($key));
    }

    /**
     * Data provider.
     *
     * @return array
     */
    public function data_sanitize_key()
    {
        return [
            'an empty string key'            => [
                'key'      => '',
                'expected' => '',
            ],
            'a lowercase key with commas'    => [
                'key'      => 'howdy,admin',
                'expected' => 'howdyadmin',
            ],
            'a lowercase key with commas'    => [
                'key'      => 'HOWDY,ADMIN',
                'expected' => 'howdyadmin',
            ],
            'a mixed case key with commas'   => [
                'key'      => 'HoWdY,aDmIn',
                'expected' => 'howdyadmin',
            ],
            'a key with dashes'              => [
                'key'      => 'howdy-admin',
                'expected' => 'howdy-admin',
            ],
            'a key with spaces'              => [
                'key'      => 'howdy admin',
                'expected' => 'howdyadmin',
            ],
            'a key with a HTML entity'       => [
                'key'      => 'howdy&nbsp;admin',
                'expected' => 'howdynbspadmin',
            ],
            'a key with a unicode character' => [
                'key'      => 'howdy' . chr(140) . 'admin',
                'expected' => 'howdyadmin',
            ],
        ];
    }

    /**
     * @ticket       54160
     * @dataProvider data_sanitize_key_nonstring_scalar
     *
     * @param mixed  $key      The key to sanitize.
     * @param string $expected The expected value.
     */
    public function test_sanitize_key_nonstring_scalar($key, $expected)
    {
        $this->assertSame($expected, sanitize_key($key));
    }

    /**
     * Data provider.
     *
     * @return array
     */
    public function data_sanitize_key_nonstring_scalar()
    {
        return [
            'integer type'  => [
                'key'      => 0,
                'expected' => '0',
            ],
            'boolean true'  => [
                'key'      => true,
                'expected' => '1',
            ],
            'boolean false' => [
                'key'      => false,
                'expected' => '',
            ],
            'float type'    => [
                'key'      => 0.123,
                'expected' => '0123',
            ],
        ];
    }

    /**
     * @ticket       54160
     * @dataProvider data_sanitize_key_with_non_scalars
     *
     * @param mixed $nonscalar_key A non-scalar data type given as a key.
     */
    public function test_sanitize_key_with_non_scalars($nonscalar_key)
    {
        add_filter(
            'sanitize_key',
            function ($sanitized_key, $key) use ($nonscalar_key) {
                $this->assertEmpty($sanitized_key, 'Empty string not passed as first filtered argument');
                $this->assertSame($nonscalar_key, $key, 'Given unsanitized key not passed as second filtered argument');
                return $sanitized_key;
            },
            10,
            2
        );
        $this->assertEmpty(sanitize_key($nonscalar_key), 'Non-scalar key did not return empty string');
    }

    /**
     * Data provider.
     *
     * @return array
     */
    public function data_sanitize_key_with_non_scalars()
    {
        return [
            'array type' => [
                'key'      => [ 'key' ],
                'expected' => '',
            ],
            'null'       => [
                'key'      => null,
                'expected' => '',
            ],
            'object'     => [
                'key'      => new stdClass(),
                'expected' => '',
            ],
        ];
    }
}
