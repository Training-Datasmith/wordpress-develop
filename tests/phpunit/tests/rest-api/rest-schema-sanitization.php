<?php

declare(strict_types=1);
/**
 * Unit tests covering schema validation and sanitization functionality.
 *
 * @package WordPress
 * @subpackage REST API
 *
 * @group restapi
 */
class WP_Test_REST_Schema_Sanitization extends WP_UnitTestCase
{
    public function test_type_number()
    {
        $schema = [
            'type' => 'number',
        ];
        $this->assertSame(1.0, rest_sanitize_value_from_schema(1, $schema));
        $this->assertSame(1.10, rest_sanitize_value_from_schema('1.10', $schema));
        $this->assertSame(1.0, rest_sanitize_value_from_schema('1abc', $schema));
        $this->assertSame(0.0, rest_sanitize_value_from_schema('abc', $schema));
        $this->assertSame(0.0, rest_sanitize_value_from_schema([], $schema));
    }

    public function test_type_integer()
    {
        $schema = [
            'type' => 'integer',
        ];
        $this->assertSame(1, rest_sanitize_value_from_schema(1, $schema));
        $this->assertSame(1, rest_sanitize_value_from_schema('1.10', $schema));
        $this->assertSame(1, rest_sanitize_value_from_schema('1abc', $schema));
        $this->assertSame(0, rest_sanitize_value_from_schema('abc', $schema));
        $this->assertSame(0, rest_sanitize_value_from_schema([], $schema));
    }

    public function test_type_string()
    {
        $schema = [
            'type' => 'string',
        ];
        $this->assertSame('Hello', rest_sanitize_value_from_schema('Hello', $schema));
        $this->assertSame('1.10', rest_sanitize_value_from_schema('1.10', $schema));
        $this->assertSame('1.1', rest_sanitize_value_from_schema(1.1, $schema));
        $this->assertSame('1', rest_sanitize_value_from_schema(1, $schema));
    }

    public function test_type_boolean()
    {
        $schema = [
            'type' => 'boolean',
        ];
        $this->assertTrue(rest_sanitize_value_from_schema('1', $schema));
        $this->assertTrue(rest_sanitize_value_from_schema('true', $schema));
        $this->assertTrue(rest_sanitize_value_from_schema('100', $schema));
        $this->assertTrue(rest_sanitize_value_from_schema(1, $schema));
        $this->assertFalse(rest_sanitize_value_from_schema('0', $schema));
        $this->assertFalse(rest_sanitize_value_from_schema('false', $schema));
        $this->assertFalse(rest_sanitize_value_from_schema(0, $schema));
    }

    public function test_format_email()
    {
        $schema = [
            'type'   => 'string',
            'format' => 'email',
        ];
        $this->assertSame('email@example.com', rest_sanitize_value_from_schema('email@example.com', $schema));
        $this->assertSame('a@b.c', rest_sanitize_value_from_schema('a@b.c', $schema));
        $this->assertSame('invalid', rest_sanitize_value_from_schema('invalid', $schema));
    }

    public function test_format_ip()
    {
        $schema = [
            'type'   => 'string',
            'format' => 'ip',
        ];

        $this->assertSame('127.0.0.1', rest_sanitize_value_from_schema('127.0.0.1', $schema));
        $this->assertSame('hello', rest_sanitize_value_from_schema('hello', $schema));
        $this->assertSame('2001:DB8:0:0:8:800:200C:417A', rest_sanitize_value_from_schema('2001:DB8:0:0:8:800:200C:417A', $schema));
    }

    /**
     * @ticket 49270
     */
    public function test_format_hex_color()
    {
        $schema = [
            'type'   => 'string',
            'format' => 'hex-color',
        ];
        $this->assertSame('#000000', rest_sanitize_value_from_schema('#000000', $schema));
        $this->assertSame('#FFF', rest_sanitize_value_from_schema('#FFF', $schema));
        $this->assertSame('', rest_sanitize_value_from_schema('WordPress', $schema));
    }

    /**
     * @ticket 50053
     */
    public function test_format_uuid()
    {
        $schema = [
            'type'   => 'string',
            'format' => 'uuid',
        ];
        $this->assertSame('44', rest_sanitize_value_from_schema(44, $schema));
        $this->assertSame('hello', rest_sanitize_value_from_schema('hello', $schema));
        $this->assertSame(
            '123e4567-e89b-12d3-a456-426655440000',
            rest_sanitize_value_from_schema('123e4567-e89b-12d3-a456-426655440000', $schema)
        );
    }

    public function test_type_array()
    {
        $schema = [
            'type'  => 'array',
            'items' => [
                'type' => 'number',
            ],
        ];
        $this->assertEquals([ 1 ], rest_sanitize_value_from_schema([ 1 ], $schema));
        $this->assertEquals([ 1 ], rest_sanitize_value_from_schema([ '1' ], $schema));
    }

    public function test_type_array_nested()
    {
        $schema = [
            'type'  => 'array',
            'items' => [
                'type'  => 'array',
                'items' => [
                    'type' => 'number',
                ],
            ],
        ];
        $this->assertEquals([ [ 1 ], [ 2 ] ], rest_sanitize_value_from_schema([ [ 1 ], [ 2 ] ], $schema));
        $this->assertEquals([ [ 1 ], [ 2 ] ], rest_sanitize_value_from_schema([ [ '1' ], [ '2' ] ], $schema));
    }

    public function test_type_array_as_csv()
    {
        $schema = [
            'type'  => 'array',
            'items' => [
                'type' => 'number',
            ],
        ];
        $this->assertEquals([ 1, 2 ], rest_sanitize_value_from_schema('1,2', $schema));
        $this->assertEquals([ 1, 2, 0 ], rest_sanitize_value_from_schema('1,2,a', $schema));
        $this->assertEquals([ 1, 2 ], rest_sanitize_value_from_schema('1,2,', $schema));
    }

    public function test_type_array_with_enum()
    {
        $schema = [
            'type'  => 'array',
            'items' => [
                'enum' => [ 'chicken', 'ribs', 'brisket' ],
                'type' => 'string',
            ],
        ];
        $this->assertSame([ 'ribs', 'brisket' ], rest_sanitize_value_from_schema([ 'ribs', 'brisket' ], $schema));
        $this->assertSame([ 'coleslaw' ], rest_sanitize_value_from_schema([ 'coleslaw' ], $schema));
    }

    public function test_type_array_with_enum_as_csv()
    {
        $schema = [
            'type'  => 'array',
            'items' => [
                'enum' => [ 'chicken', 'ribs', 'brisket' ],
                'type' => 'string',
            ],
        ];
        $this->assertSame([ 'ribs', 'chicken' ], rest_sanitize_value_from_schema('ribs,chicken', $schema));
        $this->assertSame([ 'chicken', 'coleslaw' ], rest_sanitize_value_from_schema('chicken,coleslaw', $schema));
        $this->assertSame([ 'chicken', 'coleslaw' ], rest_sanitize_value_from_schema('chicken,coleslaw,', $schema));
    }

    public function test_type_array_is_associative()
    {
        $schema = [
            'type'  => 'array',
            'items' => [
                'type' => 'string',
            ],
        ];
        $this->assertSame(
            [ '1', '2' ],
            rest_sanitize_value_from_schema(
                [
                    'first'  => '1',
                    'second' => '2',
                ],
                $schema
            )
        );
    }

    public function test_type_object()
    {
        $schema = [
            'type'       => 'object',
            'properties' => [
                'a' => [
                    'type' => 'number',
                ],
            ],
        ];
        $this->assertEquals([ 'a' => 1 ], rest_sanitize_value_from_schema([ 'a' => 1 ], $schema));
        $this->assertEquals([ 'a' => 1 ], rest_sanitize_value_from_schema([ 'a' => '1' ], $schema));
        $this->assertEquals(
            [
                'a' => 1,
                'b' => 1,
            ],
            rest_sanitize_value_from_schema(
                [
                    'a' => '1',
                    'b' => 1,
                ],
                $schema
            )
        );
    }

    public function test_type_object_strips_additional_properties()
    {
        $schema = [
            'type'                 => 'object',
            'properties'           => [
                'a' => [
                    'type' => 'number',
                ],
            ],
            'additionalProperties' => false,
        ];
        $this->assertEquals([ 'a' => 1 ], rest_sanitize_value_from_schema([ 'a' => 1 ], $schema));
        $this->assertEquals([ 'a' => 1 ], rest_sanitize_value_from_schema([ 'a' => '1' ], $schema));
        $this->assertEquals(
            [ 'a' => 1 ],
            rest_sanitize_value_from_schema(
                [
                    'a' => '1',
                    'b' => 1,
                ],
                $schema
            )
        );
    }

    /**
     * @ticket 51024
     *
     * @dataProvider data_type_object_pattern_properties
     *
     * @param array $pattern_properties
     * @param array $value
     * @param array $expected
     */
    public function test_type_object_pattern_properties($pattern_properties, $value, $expected)
    {
        $schema = [
            'type'                 => 'object',
            'properties'           => [
                'propA' => [ 'type' => 'string' ],
            ],
            'patternProperties'    => $pattern_properties,
            'additionalProperties' => false,
        ];

        $this->assertSame($expected, rest_sanitize_value_from_schema($value, $schema));
    }

    /**
     * @return array
     */
    public function data_type_object_pattern_properties()
    {
        return [
            [ [], [], [] ],
            [ [], [ 'propA' => 'a' ], [ 'propA' => 'a' ] ],
            [
                [],
                [
                    'propA' => 'a',
                    'propB' => 'b',
                ],
                [ 'propA' => 'a' ],
            ],
            [
                [
                    'propB' => [ 'type' => 'string' ],
                ],
                [ 'propA' => 'a' ],
                [ 'propA' => 'a' ],
            ],
            [
                [
                    'propB' => [ 'type' => 'string' ],
                ],
                [
                    'propA' => 'a',
                    'propB' => 'b',
                ],
                [
                    'propA' => 'a',
                    'propB' => 'b',
                ],
            ],
            [
                [
                    '.*C' => [ 'type' => 'string' ],
                ],
                [
                    'propA' => 'a',
                    'propC' => 'c',
                ],
                [
                    'propA' => 'a',
                    'propC' => 'c',
                ],
            ],
            [
                [
                    '[0-9]' => [ 'type' => 'integer' ],
                ],
                [
                    'propA' => 'a',
                    'prop0' => '0',
                ],
                [
                    'propA' => 'a',
                    'prop0' => 0,
                ],
            ],
            [
                [
                    '.+' => [ 'type' => 'string' ],
                ],
                [
                    ''      => '',
                    'propA' => 'a',
                ],
                [ 'propA' => 'a' ],
            ],
        ];
    }

    public function test_type_object_nested()
    {
        $schema = [
            'type'       => 'object',
            'properties' => [
                'a' => [
                    'type'       => 'object',
                    'properties' => [
                        'b' => [ 'type' => 'number' ],
                        'c' => [ 'type' => 'number' ],
                    ],
                ],
            ],
        ];

        $this->assertEquals(
            [
                'a' => [
                    'b' => 1,
                    'c' => 3,
                ],
            ],
            rest_sanitize_value_from_schema(
                [
                    'a' => [
                        'b' => '1',
                        'c' => '3',
                    ],
                ],
                $schema
            )
        );
        $this->assertEquals(
            [
                'a' => [
                    'b' => 1,
                    'c' => 3,
                    'd' => '1',
                ],
                'b' => 1,
            ],
            rest_sanitize_value_from_schema(
                [
                    'a' => [
                        'b' => '1',
                        'c' => '3',
                        'd' => '1',
                    ],
                    'b' => 1,
                ],
                $schema
            )
        );
        $this->assertSame([ 'a' => [] ], rest_sanitize_value_from_schema([ 'a' => null ], $schema));
    }

    public function test_type_object_stdclass()
    {
        $schema = [
            'type'       => 'object',
            'properties' => [
                'a' => [
                    'type' => 'number',
                ],
            ],
        ];
        $this->assertEquals([ 'a' => 1 ], rest_sanitize_value_from_schema((object) [ 'a' => '1' ], $schema));
    }

    /**
     * @ticket 42961
     */
    public function test_type_object_accepts_empty_string()
    {
        $this->assertSame([], rest_sanitize_value_from_schema('', [ 'type' => 'object' ]));
    }

    public function test_type_unknown()
    {
        $this->setExpectedIncorrectUsage('rest_sanitize_value_from_schema');

        $schema = [
            'type' => 'lalala',
        ];
        $this->assertSame('Best lyrics', rest_sanitize_value_from_schema('Best lyrics', $schema));
        $this->assertSame(1.10, rest_sanitize_value_from_schema(1.10, $schema));
        $this->assertSame(1, rest_sanitize_value_from_schema(1, $schema));
    }

    public function test_no_type()
    {
        $this->setExpectedIncorrectUsage('rest_sanitize_value_from_schema');

        $schema = [
            'type' => null,
        ];
        $this->assertSame('Nothing', rest_sanitize_value_from_schema('Nothing', $schema));
        $this->assertSame(1.10, rest_sanitize_value_from_schema(1.10, $schema));
        $this->assertSame(1, rest_sanitize_value_from_schema(1, $schema));
    }

    public function test_nullable_date()
    {
        $schema = [
            'type'   => [ 'string', 'null' ],
            'format' => 'date-time',
        ];

        $this->assertNull(rest_sanitize_value_from_schema(null, $schema));
        $this->assertSame('2019-09-19T18:00:00', rest_sanitize_value_from_schema('2019-09-19T18:00:00', $schema));
        $this->assertSame('lalala', rest_sanitize_value_from_schema('lalala', $schema));
    }

    /**
     * @ticket 50189
     */
    public function test_format_validation_is_skipped_if_non_string_type()
    {
        $schema = [
            'type'   => 'array',
            'format' => 'hex-color',
        ];
        $this->assertSame([ '#fff' ], rest_sanitize_value_from_schema('#fff', $schema));
        $this->assertSame([ '#qrst' ], rest_sanitize_value_from_schema('#qrst', $schema));
    }

    /**
     * @ticket 50189
     */
    public function test_format_validation_is_applied_if_missing_type()
    {
        if (PHP_VERSION_ID >= 80000) {
            $this->expectWarning(); // For the undefined index.
        } else {
            $this->expectNotice(); // For the undefined index.
        }

        $this->setExpectedIncorrectUsage('rest_sanitize_value_from_schema');

        $schema = [ 'format' => 'hex-color' ];
        $this->assertSame('#abc', rest_sanitize_value_from_schema('#abc', $schema));
        $this->assertSame('', rest_sanitize_value_from_schema('#jkl', $schema));
    }

    /**
     * @ticket 50189
     */
    public function test_format_validation_is_applied_if_unknown_type()
    {
        $this->setExpectedIncorrectUsage('rest_sanitize_value_from_schema');

        $schema = [
            'format' => 'hex-color',
            'type'   => 'str',
        ];
        $this->assertSame('#abc', rest_sanitize_value_from_schema('#abc', $schema));
        $this->assertSame('', rest_sanitize_value_from_schema('#jkl', $schema));
    }

    public function test_object_or_string()
    {
        $schema = [
            'type'       => [ 'object', 'string' ],
            'properties' => [
                'raw' => [
                    'type' => 'string',
                ],
            ],
        ];

        $this->assertSame('My Value', rest_sanitize_value_from_schema('My Value', $schema));
        $this->assertSame([ 'raw' => 'My Value' ], rest_sanitize_value_from_schema([ 'raw' => 'My Value' ], $schema));
        $this->assertSame([ 'raw' => '1' ], rest_sanitize_value_from_schema([ 'raw' => 1 ], $schema));
    }

    public function test_object_or_bool()
    {
        $schema = [
            'type'       => [ 'object', 'boolean' ],
            'properties' => [
                'raw' => [
                    'type' => 'boolean',
                ],
            ],
        ];

        $this->assertTrue(rest_sanitize_value_from_schema(true, $schema));
        $this->assertTrue(rest_sanitize_value_from_schema('1', $schema));
        $this->assertTrue(rest_sanitize_value_from_schema(1, $schema));

        $this->assertFalse(rest_sanitize_value_from_schema(false, $schema));
        $this->assertFalse(rest_sanitize_value_from_schema('0', $schema));
        $this->assertFalse(rest_sanitize_value_from_schema(0, $schema));

        $this->assertSame([ 'raw' => true ], rest_sanitize_value_from_schema([ 'raw' => true ], $schema));
        $this->assertSame([ 'raw' => true ], rest_sanitize_value_from_schema([ 'raw' => '1' ], $schema));
        $this->assertSame([ 'raw' => true ], rest_sanitize_value_from_schema([ 'raw' => 1 ], $schema));

        $this->assertSame([ 'raw' => false ], rest_sanitize_value_from_schema([ 'raw' => false ], $schema));
        $this->assertSame([ 'raw' => false ], rest_sanitize_value_from_schema([ 'raw' => '0' ], $schema));
        $this->assertSame([ 'raw' => false ], rest_sanitize_value_from_schema([ 'raw' => 0 ], $schema));

        $this->assertSame([ 'raw' => true ], rest_sanitize_value_from_schema([ 'raw' => 'something non boolean' ], $schema));
    }

    /**
     * @ticket 50300
     */
    public function test_multi_type_with_no_known_types()
    {
        $this->setExpectedIncorrectUsage('rest_handle_multi_type_schema');
        $this->setExpectedIncorrectUsage('rest_sanitize_value_from_schema');

        $schema = [
            'type' => [ 'invalid', 'type' ],
        ];

        $this->assertSame('My Value', rest_sanitize_value_from_schema('My Value', $schema));
    }

    /**
     * @ticket 50300
     */
    public function test_multi_type_with_some_unknown_types()
    {
        $this->setExpectedIncorrectUsage('rest_handle_multi_type_schema');
        $this->setExpectedIncorrectUsage('rest_sanitize_value_from_schema');

        $schema = [
            'type' => [ 'object', 'type' ],
        ];

        $this->assertSame('My Value', rest_sanitize_value_from_schema('My Value', $schema));
    }

    /**
     * @ticket 50300
     */
    public function test_multi_type_returns_null_if_no_valid_type()
    {
        $schema = [
            'type' => [ 'number', 'string' ],
        ];

        $this->assertNull(rest_sanitize_value_from_schema([ 'Hello!' ], $schema));
    }

    /**
     * @ticket 48821
     */
    public function test_unique_items_after_sanitization()
    {
        $schema = [
            'type'        => 'array',
            'uniqueItems' => true,
            'items'       => [
                'type'   => 'string',
                'format' => 'uri',
            ],
        ];

        $data = [
            'https://example.org/hello%20world',
            'https://example.org/hello world',
        ];

        $this->assertTrue(rest_validate_value_from_schema($data, $schema));
        $this->assertWPError(rest_sanitize_value_from_schema($data, $schema));
    }

    /**
     * @ticket 51025
     */
    public function test_any_of()
    {
        $schema = [
            'anyOf' => [
                [
                    'type'       => 'integer',
                    'multipleOf' => 2,
                ],
                [
                    'type'      => 'string',
                    'maxLength' => 1,
                ],
            ],
        ];

        $this->assertSame(4, rest_sanitize_value_from_schema('4', $schema));
        $this->assertSame('5', rest_sanitize_value_from_schema('5', $schema));
        $this->assertWPError(rest_sanitize_value_from_schema(true, $schema));
        $this->assertWPError(rest_sanitize_value_from_schema('11', $schema));
    }

    /**
     * @ticket 51025
     */
    public function test_one_of()
    {
        $schema = [
            'oneOf' => [
                [
                    'type'       => 'integer',
                    'multipleOf' => 2,
                ],
                [
                    'type'      => 'string',
                    'maxLength' => 1,
                ],
            ],
        ];

        $this->assertSame(10, rest_sanitize_value_from_schema('10', $schema));
        $this->assertSame('5', rest_sanitize_value_from_schema('5', $schema));
        $this->assertWPError(rest_sanitize_value_from_schema(true, $schema));
        $this->assertWPError(rest_sanitize_value_from_schema('11', $schema));
        $this->assertWPError(rest_sanitize_value_from_schema('4', $schema));
    }
}
