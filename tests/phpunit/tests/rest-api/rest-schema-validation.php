<?php

declare(strict_types=1);
/**
 * Unit tests covering schema validation and sanitization functionality.
 *
 * @package    WordPress
 * @subpackage REST API
 *
 * @group restapi
 */
class WP_Test_REST_Schema_Validation extends WP_UnitTestCase
{
    public function test_type_number()
    {
        $schema = [
            'type'    => 'number',
            'minimum' => 1,
            'maximum' => 2,
        ];
        $this->assertTrue(rest_validate_value_from_schema(1, $schema));
        $this->assertTrue(rest_validate_value_from_schema(2, $schema));
        $this->assertWPError(rest_validate_value_from_schema(0.9, $schema));
        $this->assertWPError(rest_validate_value_from_schema(3, $schema));
        $this->assertWPError(rest_validate_value_from_schema(true, $schema));
    }

    public function test_type_integer()
    {
        $schema = [
            'type'    => 'integer',
            'minimum' => 1,
            'maximum' => 2,
        ];
        $this->assertTrue(rest_validate_value_from_schema(1, $schema));
        $this->assertTrue(rest_validate_value_from_schema(2, $schema));
        $this->assertWPError(rest_validate_value_from_schema(0, $schema));
        $this->assertWPError(rest_validate_value_from_schema(3, $schema));
        $this->assertWPError(rest_validate_value_from_schema(1.1, $schema));
    }

    public function test_type_string()
    {
        $schema = [
            'type' => 'string',
        ];
        $this->assertTrue(rest_validate_value_from_schema('Hello :)', $schema));
        $this->assertTrue(rest_validate_value_from_schema('1', $schema));
        $this->assertWPError(rest_validate_value_from_schema(1, $schema));
        $this->assertWPError(rest_validate_value_from_schema([], $schema));
    }

    public function test_type_boolean()
    {
        $schema = [
            'type' => 'boolean',
        ];
        $this->assertTrue(rest_validate_value_from_schema(true, $schema));
        $this->assertTrue(rest_validate_value_from_schema(false, $schema));
        $this->assertTrue(rest_validate_value_from_schema(1, $schema));
        $this->assertTrue(rest_validate_value_from_schema(0, $schema));
        $this->assertTrue(rest_validate_value_from_schema('true', $schema));
        $this->assertTrue(rest_validate_value_from_schema('false', $schema));
        $this->assertWPError(rest_validate_value_from_schema('no', $schema));
        $this->assertWPError(rest_validate_value_from_schema('yes', $schema));
        $this->assertWPError(rest_validate_value_from_schema(1123, $schema));
    }

    public function test_format_email()
    {
        $schema = [
            'type'   => 'string',
            'format' => 'email',
        ];
        $this->assertTrue(rest_validate_value_from_schema('email@example.com', $schema));
        $this->assertTrue(rest_validate_value_from_schema('a@b.co', $schema));
        $this->assertWPError(rest_validate_value_from_schema('email', $schema));
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
        $this->assertTrue(rest_validate_value_from_schema('#000000', $schema));
        $this->assertTrue(rest_validate_value_from_schema('#FFF', $schema));
        $this->assertWPError(rest_validate_value_from_schema('WordPress', $schema));
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
        $this->assertTrue(rest_validate_value_from_schema('123e4567-e89b-12d3-a456-426655440000', $schema));
        $this->assertWPError(rest_validate_value_from_schema('123e4567-e89b-12d3-a456-426655440000X', $schema));
        $this->assertWPError(rest_validate_value_from_schema('123e4567-e89b-?2d3-a456-426655440000', $schema));
    }

    public function test_format_date_time()
    {
        $schema = [
            'type'   => 'string',
            'format' => 'date-time',
        ];
        $this->assertTrue(rest_validate_value_from_schema('2016-06-30T05:43:21', $schema));
        $this->assertTrue(rest_validate_value_from_schema('2016-06-30T05:43:21Z', $schema));
        $this->assertTrue(rest_validate_value_from_schema('2016-06-30T05:43:21+00:00', $schema));
        $this->assertWPError(rest_validate_value_from_schema('20161027T163355Z', $schema));
        $this->assertWPError(rest_validate_value_from_schema('2016', $schema));
        $this->assertWPError(rest_validate_value_from_schema('2016-06-30', $schema));
    }

    public function test_format_ip()
    {
        $schema = [
            'type'   => 'string',
            'format' => 'ip',
        ];

        // IPv4.
        $this->assertTrue(rest_validate_value_from_schema('127.0.0.1', $schema));
        $this->assertWPError(rest_validate_value_from_schema('3333.3333.3333.3333', $schema));
        $this->assertWPError(rest_validate_value_from_schema('1', $schema));

        // IPv6.
        $this->assertTrue(rest_validate_value_from_schema('::1', $schema)); // Loopback, compressed, non-routable.
        $this->assertTrue(rest_validate_value_from_schema('::', $schema)); // Unspecified, compressed, non-routable.
        $this->assertTrue(rest_validate_value_from_schema('0:0:0:0:0:0:0:1', $schema)); // Loopback, full.
        $this->assertTrue(rest_validate_value_from_schema('0:0:0:0:0:0:0:0', $schema)); // Unspecified, full.
        $this->assertTrue(rest_validate_value_from_schema('2001:DB8:0:0:8:800:200C:417A', $schema)); // Unicast, full.
        $this->assertTrue(rest_validate_value_from_schema('FF01:0:0:0:0:0:0:101', $schema)); // Multicast, full.
        $this->assertTrue(rest_validate_value_from_schema('2001:DB8::8:800:200C:417A', $schema)); // Unicast, compressed.
        $this->assertTrue(rest_validate_value_from_schema('FF01::101', $schema)); // Multicast, compressed.
        $this->assertTrue(rest_validate_value_from_schema('fe80::217:f2ff:fe07:ed62', $schema));
        $this->assertWPError(rest_validate_value_from_schema('', $schema)); // Empty string.
        $this->assertWPError(rest_validate_value_from_schema('2001:DB8:0:0:8:800:200C:417A:221', $schema)); // Unicast, full.
        $this->assertWPError(rest_validate_value_from_schema('FF01::101::2', $schema)); // Multicast, compressed.
    }

    /**
     * @ticket 50189
     */
    public function test_format_validation_is_skipped_if_non_string_type()
    {
        $schema = [
            'type'   => 'array',
            'items'  => [
                'type' => 'string',
            ],
            'format' => 'email',
        ];
        $this->assertTrue(rest_validate_value_from_schema('email@example.com', $schema));
        $this->assertTrue(rest_validate_value_from_schema('email', $schema));
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

        $this->setExpectedIncorrectUsage('rest_validate_value_from_schema');

        $schema = [ 'format' => 'email' ];
        $this->assertTrue(rest_validate_value_from_schema('email@example.com', $schema));
        $this->assertWPError(rest_validate_value_from_schema('email', $schema));
    }

    /**
     * @ticket 50189
     */
    public function test_format_validation_is_applied_if_unknown_type()
    {
        $this->setExpectedIncorrectUsage('rest_validate_value_from_schema');

        $schema = [
            'format' => 'email',
            'type'   => 'str',
        ];
        $this->assertTrue(rest_validate_value_from_schema('email@example.com', $schema));
        $this->assertWPError(rest_validate_value_from_schema('email', $schema));
    }

    public function test_type_array()
    {
        $schema = [
            'type'  => 'array',
            'items' => [
                'type' => 'number',
            ],
        ];
        $this->assertTrue(rest_validate_value_from_schema([ 1 ], $schema));
        $this->assertWPError(rest_validate_value_from_schema([ true ], $schema));
        $this->assertWPError(rest_validate_value_from_schema(null, $schema));
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
        $this->assertTrue(rest_validate_value_from_schema([ [ 1 ], [ 2 ] ], $schema));
    }

    public function test_type_array_as_csv()
    {
        $schema = [
            'type'  => 'array',
            'items' => [
                'type' => 'number',
            ],
        ];
        $this->assertTrue(rest_validate_value_from_schema('1', $schema));
        $this->assertTrue(rest_validate_value_from_schema('1,2,3', $schema));
        $this->assertWPError(rest_validate_value_from_schema('lol', $schema));
        $this->assertTrue(rest_validate_value_from_schema('1,,', $schema));
        $this->assertTrue(rest_validate_value_from_schema('', $schema));
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
        $this->assertTrue(rest_validate_value_from_schema([ 'ribs', 'brisket' ], $schema));
        $this->assertWPError(rest_validate_value_from_schema([ 'coleslaw' ], $schema));
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
        $this->assertTrue(rest_validate_value_from_schema('ribs,chicken', $schema));
        $this->assertWPError(rest_validate_value_from_schema('chicken,coleslaw', $schema));
        $this->assertTrue(rest_validate_value_from_schema('ribs,chicken,', $schema));
        $this->assertTrue(rest_validate_value_from_schema('', $schema));
    }

    /**
     * @ticket 51911
     * @ticket 52932
     *
     * @dataProvider data_different_types_of_value_and_enum_elements
     *
     * @param mixed $value
     * @param array $args
     * @param bool  $expected
     */
    public function test_different_types_of_value_and_enum_elements($value, $args, $expected)
    {
        $result = rest_validate_value_from_schema($value, $args);
        if ($expected) {
            $this->assertTrue($result);
        } else {
            $this->assertWPError($result);
        }
    }

    /**
     * @return array
     */
    public function data_different_types_of_value_and_enum_elements()
    {
        return [
            // enum with integers
            [
                0,
                [
                    'type' => 'integer',
                    'enum' => [ 0, 1 ],
                ],
                true,
            ],
            [
                0.0,
                [
                    'type' => 'integer',
                    'enum' => [ 0, 1 ],
                ],
                true,
            ],
            [
                '0',
                [
                    'type' => 'integer',
                    'enum' => [ 0, 1 ],
                ],
                true,
            ],
            [
                1,
                [
                    'type' => 'integer',
                    'enum' => [ 0, 1 ],
                ],
                true,
            ],
            [
                1,
                [
                    'type' => 'integer',
                    'enum' => [ 0.0, 1.0 ],
                ],
                true,
            ],
            [
                1.0,
                [
                    'type' => 'integer',
                    'enum' => [ 0, 1 ],
                ],
                true,
            ],
            [
                '1',
                [
                    'type' => 'integer',
                    'enum' => [ 0, 1 ],
                ],
                true,
            ],
            [
                2,
                [
                    'type' => 'integer',
                    'enum' => [ 0, 1 ],
                ],
                false,
            ],
            [
                2.0,
                [
                    'type' => 'integer',
                    'enum' => [ 0, 1 ],
                ],
                false,
            ],
            [
                '2',
                [
                    'type' => 'integer',
                    'enum' => [ 0, 1 ],
                ],
                false,
            ],

            // enum with floats
            [
                0,
                [
                    'type' => 'number',
                    'enum' => [ 0.0, 1.0 ],
                ],
                true,
            ],
            [
                0.0,
                [
                    'type' => 'number',
                    'enum' => [ 0.0, 1.0 ],
                ],
                true,
            ],
            [
                '0',
                [
                    'type' => 'number',
                    'enum' => [ 0.0, 1.0 ],
                ],
                true,
            ],
            [
                1,
                [
                    'type' => 'number',
                    'enum' => [ 0.0, 1.0 ],
                ],
                true,
            ],
            [
                1,
                [
                    'type' => 'number',
                    'enum' => [ 0, 1 ],
                ],
                true,
            ],
            [
                1.0,
                [
                    'type' => 'number',
                    'enum' => [ 0.0, 1.0 ],
                ],
                true,
            ],
            [
                '1',
                [
                    'type' => 'number',
                    'enum' => [ 0.0, 1.0 ],
                ],
                true,
            ],
            [
                2,
                [
                    'type' => 'number',
                    'enum' => [ 0.0, 1.0 ],
                ],
                false,
            ],
            [
                2.0,
                [
                    'type' => 'number',
                    'enum' => [ 0.0, 1.0 ],
                ],
                false,
            ],
            [
                '2',
                [
                    'type' => 'number',
                    'enum' => [ 0.0, 1.0 ],
                ],
                false,
            ],

            // enum with booleans
            [
                true,
                [
                    'type' => 'boolean',
                    'enum' => [ true ],
                ],
                true,
            ],
            [
                1,
                [
                    'type' => 'boolean',
                    'enum' => [ true ],
                ],
                true,
            ],
            [
                'true',
                [
                    'type' => 'boolean',
                    'enum' => [ true ],
                ],
                true,
            ],
            [
                false,
                [
                    'type' => 'boolean',
                    'enum' => [ true ],
                ],
                false,
            ],
            [
                0,
                [
                    'type' => 'boolean',
                    'enum' => [ true ],
                ],
                false,
            ],
            [
                'false',
                [
                    'type' => 'boolean',
                    'enum' => [ true ],
                ],
                false,
            ],
            [
                false,
                [
                    'type' => 'boolean',
                    'enum' => [ false ],
                ],
                true,
            ],
            [
                0,
                [
                    'type' => 'boolean',
                    'enum' => [ false ],
                ],
                true,
            ],
            [
                'false',
                [
                    'type' => 'boolean',
                    'enum' => [ false ],
                ],
                true,
            ],
            [
                true,
                [
                    'type' => 'boolean',
                    'enum' => [ false ],
                ],
                false,
            ],
            [
                1,
                [
                    'type' => 'boolean',
                    'enum' => [ false ],
                ],
                false,
            ],
            [
                'true',
                [
                    'type' => 'boolean',
                    'enum' => [ false ],
                ],
                false,
            ],

            // enum with arrays
            [
                [ 0, 1 ],
                [
                    'type'  => 'array',
                    'items' => [ 'type' => 'integer' ],
                    'enum'  => [ [ 0, 1 ], [ 1, 2 ] ],
                ],
                true,
            ],
            [
                [ '0', 1 ],
                [
                    'type'  => 'array',
                    'items' => [ 'type' => 'integer' ],
                    'enum'  => [ [ 0, 1 ], [ 1, 2 ] ],
                ],
                true,
            ],
            [
                [ 0, '1' ],
                [
                    'type'  => 'array',
                    'items' => [ 'type' => 'integer' ],
                    'enum'  => [ [ 0, 1 ], [ 1, 2 ] ],
                ],
                true,
            ],
            [
                [ '0', '1' ],
                [
                    'type'  => 'array',
                    'items' => [ 'type' => 'integer' ],
                    'enum'  => [ [ 0, 1 ], [ 1, 2 ] ],
                ],
                true,
            ],
            [
                [ 1, 2 ],
                [
                    'type'  => 'array',
                    'items' => [ 'type' => 'integer' ],
                    'enum'  => [ [ 0, 1 ], [ 1, 2 ] ],
                ],
                true,
            ],
            [
                [ 2, 3 ],
                [
                    'type'  => 'array',
                    'items' => [ 'type' => 'integer' ],
                    'enum'  => [ [ 0, 1 ], [ 1, 2 ] ],
                ],
                false,
            ],
            [
                [ 1, 0 ],
                [
                    'type'  => 'array',
                    'items' => [ 'type' => 'integer' ],
                    'enum'  => [ [ 0, 1 ], [ 1, 2 ] ],
                ],
                false,
            ],

            // enum with objects
            [
                [
                    'a' => 1,
                    'b' => 2,
                ],
                [
                    'type'                 => 'object',
                    'additionalProperties' => [ 'type' => 'integer' ],
                    'enum'                 => [
                        [
                            'a' => 1,
                            'b' => 2,
                        ],
                        [
                            'b' => 2,
                            'c' => 3,
                        ],
                    ],
                ],
                true,
            ],
            [
                [
                    'a' => '1',
                    'b' => 2,
                ],
                [
                    'type'                 => 'object',
                    'additionalProperties' => [ 'type' => 'integer' ],
                    'enum'                 => [
                        [
                            'a' => 1,
                            'b' => 2,
                        ],
                        [
                            'b' => 2,
                            'c' => 3,
                        ],
                    ],
                ],
                true,
            ],
            [
                [
                    'a' => 1,
                    'b' => '2',
                ],
                [
                    'type'                 => 'object',
                    'additionalProperties' => [ 'type' => 'integer' ],
                    'enum'                 => [
                        [
                            'a' => 1,
                            'b' => 2,
                        ],
                        [
                            'b' => 2,
                            'c' => 3,
                        ],
                    ],
                ],
                true,
            ],
            [
                [
                    'a' => '1',
                    'b' => '2',
                ],
                [
                    'type'                 => 'object',
                    'additionalProperties' => [ 'type' => 'integer' ],
                    'enum'                 => [
                        [
                            'a' => 1,
                            'b' => 2,
                        ],
                        [
                            'b' => 2,
                            'c' => 3,
                        ],
                    ],
                ],
                true,
            ],
            [
                [
                    'b' => 2,
                    'a' => 1,
                ],
                [
                    'type'                 => 'object',
                    'additionalProperties' => [ 'type' => 'integer' ],
                    'enum'                 => [
                        [
                            'a' => 1,
                            'b' => 2,
                        ],
                        [
                            'b' => 2,
                            'c' => 3,
                        ],
                    ],
                ],
                true,
            ],
            [
                [
                    'b' => 2,
                    'c' => 3,
                ],
                [
                    'type'                 => 'object',
                    'additionalProperties' => [ 'type' => 'integer' ],
                    'enum'                 => [
                        [
                            'a' => 1,
                            'b' => 2,
                        ],
                        [
                            'b' => 2,
                            'c' => 3,
                        ],
                    ],
                ],
                true,
            ],
            [
                [
                    'a' => 1,
                    'b' => 3,
                ],
                [
                    'type'                 => 'object',
                    'additionalProperties' => [ 'type' => 'integer' ],
                    'enum'                 => [
                        [
                            'a' => 1,
                            'b' => 2,
                        ],
                        [
                            'b' => 2,
                            'c' => 3,
                        ],
                    ],
                ],
                false,
            ],
            [
                [
                    'c' => 3,
                    'd' => 4,
                ],
                [
                    'type'                 => 'object',
                    'additionalProperties' => [ 'type' => 'integer' ],
                    'enum'                 => [
                        [
                            'a' => 1,
                            'b' => 2,
                        ],
                        [
                            'b' => 2,
                            'c' => 3,
                        ],
                    ],
                ],
                false,
            ],
        ];
    }

    public function test_type_array_is_associative()
    {
        $schema = [
            'type'  => 'array',
            'items' => [
                'type' => 'string',
            ],
        ];
        $this->assertWPError(
            rest_validate_value_from_schema(
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
        $this->assertTrue(rest_validate_value_from_schema([ 'a' => 1 ], $schema));
        $this->assertTrue(
            rest_validate_value_from_schema(
                [
                    'a' => 1,
                    'b' => 2,
                ],
                $schema
            )
        );
        $this->assertWPError(rest_validate_value_from_schema([ 'a' => 'invalid' ], $schema));
    }

    /**
     * @ticket 51024
     *
     * @dataProvider data_type_object_pattern_properties
     *
     * @param array $pattern_properties
     * @param array $value
     * @param bool $expected
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

        if ($expected) {
            $this->assertTrue(rest_validate_value_from_schema($value, $schema));
        } else {
            $this->assertWPError(rest_validate_value_from_schema($value, $schema));
        }
    }

    /**
     * @return array
     */
    public function data_type_object_pattern_properties()
    {
        return [
            [ [], [], true ],
            [ [], [ 'propA' => 'a' ], true ],
            [
                [],
                [
                    'propA' => 'a',
                    'propB' => 'b',
                ],
                false,
            ],
            [
                [
                    'propB' => [ 'type' => 'string' ],
                ],
                [ 'propA' => 'a' ],
                true,
            ],
            [
                [
                    'propB' => [ 'type' => 'string' ],
                ],
                [
                    'propA' => 'a',
                    'propB' => 'b',
                ],
                true,
            ],
            [
                [
                    '.*C' => [ 'type' => 'string' ],
                ],
                [
                    'propA' => 'a',
                    'propC' => 'c',
                ],
                true,
            ],
            [
                [
                    '[0-9]' => [ 'type' => 'integer' ],
                ],
                [
                    'propA' => 'a',
                    'prop0' => 0,
                ],
                true,
            ],
            [
                [
                    '[0-9]' => [ 'type' => 'integer' ],
                ],
                [
                    'propA' => 'a',
                    'prop0' => 'notAnInteger',
                ],
                false,
            ],
            [
                [
                    '.+' => [ 'type' => 'string' ],
                ],
                [
                    ''      => '',
                    'propA' => 'a',
                ],
                false,
            ],
        ];
    }

    public function test_type_object_additional_properties_false()
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
        $this->assertTrue(rest_validate_value_from_schema([ 'a' => 1 ], $schema));
        $this->assertWPError(
            rest_validate_value_from_schema(
                [
                    'a' => 1,
                    'b' => 2,
                ],
                $schema
            )
        );
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
        $this->assertTrue(
            rest_validate_value_from_schema(
                [
                    'a' => [
                        'b' => '1',
                        'c' => 3,
                    ],
                ],
                $schema
            )
        );
        $this->assertWPError(
            rest_validate_value_from_schema(
                [
                    'a' => [
                        'b' => 1,
                        'c' => 'invalid',
                    ],
                ],
                $schema
            )
        );
        $this->assertWPError(rest_validate_value_from_schema([ 'a' => 1 ], $schema));
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
        $this->assertTrue(rest_validate_value_from_schema((object) [ 'a' => 1 ], $schema));
    }

    /**
     * @ticket 42961
     */
    public function test_type_object_allows_empty_string()
    {
        $this->assertTrue(rest_validate_value_from_schema('', [ 'type' => 'object' ]));
    }

    public function test_type_unknown()
    {
        $this->setExpectedIncorrectUsage('rest_validate_value_from_schema');

        $schema = [
            'type' => 'lalala',
        ];
        $this->assertTrue(rest_validate_value_from_schema('Best lyrics', $schema));
        $this->assertTrue(rest_validate_value_from_schema(1, $schema));
        $this->assertTrue(rest_validate_value_from_schema([], $schema));
    }

    public function test_type_null()
    {
        $this->assertTrue(rest_validate_value_from_schema(null, [ 'type' => 'null' ]));
        $this->assertWPError(rest_validate_value_from_schema('', [ 'type' => 'null' ]));
        $this->assertWPError(rest_validate_value_from_schema('null', [ 'type' => 'null' ]));
    }

    public function test_nullable_date()
    {
        $schema = [
            'type'   => [ 'string', 'null' ],
            'format' => 'date-time',
        ];

        $this->assertTrue(rest_validate_value_from_schema(null, $schema));
        $this->assertTrue(rest_validate_value_from_schema('2019-09-19T18:00:00', $schema));

        $error = rest_validate_value_from_schema('some random string', $schema);
        $this->assertWPError($error);
        $this->assertSame('Invalid date.', $error->get_error_message());
    }

    /**
     * @ticket 60184
     */
    public function test_epoch()
    {
        $schema = [
            'type'   => 'string',
            'format' => 'date-time',
        ];
        $this->assertTrue(rest_validate_value_from_schema('1970-01-01T00:00:00Z', $schema));
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

        $this->assertTrue(rest_validate_value_from_schema('My Value', $schema));
        $this->assertTrue(rest_validate_value_from_schema([ 'raw' => 'My Value' ], $schema));

        $error = rest_validate_value_from_schema([ 'raw' => [ 'a list' ] ], $schema);
        $this->assertWPError($error);
        $this->assertSame('[raw] is not of type string.', $error->get_error_message());
    }

    /**
     * @ticket 50300
     */
    public function test_null_or_integer()
    {
        $schema = [
            'type'    => [ 'null', 'integer' ],
            'minimum' => 10,
            'maximum' => 20,
        ];

        $this->assertTrue(rest_validate_value_from_schema(null, $schema));
        $this->assertTrue(rest_validate_value_from_schema(15, $schema));
        $this->assertTrue(rest_validate_value_from_schema('15', $schema));

        $error = rest_validate_value_from_schema(30, $schema, 'param');
        $this->assertWPError($error);
        $this->assertSame('param must be between 10 (inclusive) and 20 (inclusive)', $error->get_error_message());
    }

    /**
     * @ticket 51022
     *
     * @dataProvider data_multiply_of
     *
     * @param int|float $value
     * @param int|float $divisor
     * @param bool      $expected
     */
    public function test_numeric_multiple_of($value, $divisor, $expected)
    {
        $schema = [
            'type'       => 'number',
            'multipleOf' => $divisor,
        ];

        $result = rest_validate_value_from_schema($value, $schema);

        if ($expected) {
            $this->assertTrue($result);
        } else {
            $this->assertWPError($result);
        }
    }

    public function data_multiply_of()
    {
        return [
            [ 0, 2, true ],
            [ 4, 2, true ],
            [ 3, 1.5, true ],
            [ 2.4, 1.2, true ],
            [ 1, 2, false ],
            [ 2, 1.5, false ],
            [ 2.1, 1.5, false ],
        ];
    }

    /**
     * @ticket 50300
     */
    public function test_multi_type_with_no_known_types()
    {
        $this->setExpectedIncorrectUsage('rest_handle_multi_type_schema');
        $this->setExpectedIncorrectUsage('rest_validate_value_from_schema');

        $schema = [
            'type' => [ 'invalid', 'type' ],
        ];

        $this->assertTrue(rest_validate_value_from_schema('My Value', $schema));
    }

    /**
     * @ticket 50300
     */
    public function test_multi_type_with_some_unknown_types()
    {
        $this->setExpectedIncorrectUsage('rest_handle_multi_type_schema');
        $this->setExpectedIncorrectUsage('rest_validate_value_from_schema');

        $schema = [
            'type' => [ 'object', 'type' ],
        ];

        $this->assertTrue(rest_validate_value_from_schema('My Value', $schema));
    }

    /**
     * @ticket 48820
     */
    public function test_string_min_length()
    {
        $schema = [
            'type'      => 'string',
            'minLength' => 2,
        ];

        // longer
        $this->assertTrue(rest_validate_value_from_schema('foo', $schema));
        // exact
        $this->assertTrue(rest_validate_value_from_schema('fo', $schema));
        // non-strings does not validate
        $this->assertWPError(rest_validate_value_from_schema(1, $schema));
        // to short
        $this->assertWPError(rest_validate_value_from_schema('f', $schema));
        // one supplementary Unicode code point is not long enough
        $mb_char = mb_convert_encoding('&#x1000;', 'UTF-8', 'HTML-ENTITIES');
        $this->assertWPError(rest_validate_value_from_schema($mb_char, $schema));
        // two supplementary Unicode code point is long enough
        $this->assertTrue(rest_validate_value_from_schema($mb_char . $mb_char, $schema));
    }

    /**
     * @ticket 48820
     */
    public function test_string_max_length()
    {
        $schema = [
            'type'      => 'string',
            'maxLength' => 2,
        ];

        // shorter
        $this->assertTrue(rest_validate_value_from_schema('f', $schema));
        // exact
        $this->assertTrue(rest_validate_value_from_schema('fo', $schema));
        // to long
        $this->assertWPError(rest_validate_value_from_schema('foo', $schema));
        // non string
        $this->assertWPError(rest_validate_value_from_schema(100, $schema));
        // two supplementary Unicode code point is long enough
        $mb_char = mb_convert_encoding('&#x1000;', 'UTF-8', 'HTML-ENTITIES');
        $this->assertTrue(rest_validate_value_from_schema($mb_char, $schema));
        // three supplementary Unicode code point is to long
        $this->assertWPError(rest_validate_value_from_schema($mb_char . $mb_char . $mb_char, $schema));
    }

    /**
     * @ticket 48818
     *
     * @dataProvider data_required_property
     */
    public function test_property_is_required($data, $expected)
    {
        $schema = [
            'type'       => 'object',
            'properties' => [
                'my_prop'          => [
                    'type' => 'string',
                ],
                'my_required_prop' => [
                    'type'     => 'string',
                    'required' => true,
                ],
            ],
        ];

        $valid = rest_validate_value_from_schema($data, $schema);

        if ($expected) {
            $this->assertTrue($valid);
        } else {
            $this->assertWPError($valid);
        }
    }

    /**
     * @ticket 48818
     *
     * @dataProvider data_required_property
     */
    public function test_property_is_required_v4($data, $expected)
    {
        $schema = [
            'type'       => 'object',
            'properties' => [
                'my_prop'          => [
                    'type' => 'string',
                ],
                'my_required_prop' => [
                    'type' => 'string',
                ],
            ],
            'required'   => [ 'my_required_prop' ],
        ];

        $valid = rest_validate_value_from_schema($data, $schema);

        if ($expected) {
            $this->assertTrue($valid);
        } else {
            $this->assertWPError($valid);
        }
    }

    public function data_required_property()
    {
        return [
            [
                [
                    'my_required_prop' => 'test',
                    'my_prop'          => 'test',
                ],
                true,
            ],
            [ [ 'my_prop' => 'test' ], false ],
            [ [], false ],
        ];
    }

    /**
     * @ticket 48818
     *
     * @dataProvider data_required_nested_property
     */
    public function test_nested_property_is_required($data, $expected)
    {
        $schema = [
            'type'       => 'object',
            'properties' => [
                'my_object' => [
                    'type'       => 'object',
                    'properties' => [
                        'my_nested_prop'          => [
                            'type' => 'string',
                        ],
                        'my_required_nested_prop' => [
                            'type'     => 'string',
                            'required' => true,
                        ],
                    ],
                ],
            ],
        ];

        $valid = rest_validate_value_from_schema($data, $schema);

        if ($expected) {
            $this->assertTrue($valid);
        } else {
            $this->assertWPError($valid);
        }
    }

    /**
     * @ticket 48818
     *
     * @dataProvider data_required_nested_property
     */
    public function test_nested_property_is_required_v4($data, $expected)
    {
        $schema = [
            'type'       => 'object',
            'properties' => [
                'my_object' => [
                    'type'       => 'object',
                    'properties' => [
                        'my_nested_prop'          => [
                            'type' => 'string',
                        ],
                        'my_required_nested_prop' => [
                            'type' => 'string',
                        ],
                    ],
                    'required'   => [ 'my_required_nested_prop' ],
                ],
            ],
        ];

        $valid = rest_validate_value_from_schema($data, $schema);

        if ($expected) {
            $this->assertTrue($valid);
        } else {
            $this->assertWPError($valid);
        }
    }

    public function data_required_nested_property()
    {
        return [
            [
                [
                    'my_object' => [
                        'my_required_nested_prop' => 'test',
                        'my_nested_prop'          => 'test',
                    ],
                ],
                true,
            ],
            [
                [
                    'my_object' => [
                        'my_nested_prop' => 'test',
                    ],
                ],
                false,
            ],
            [
                [],
                true,
            ],
        ];
    }

    /**
     * @ticket 48818
     *
     * @dataProvider data_required_deeply_nested_property
     */
    public function test_deeply_nested_v3_required_property($value, $expected)
    {
        $schema = [
            'type'       => 'object',
            'properties' => [
                'propA' => [
                    'type'       => 'object',
                    'required'   => true,
                    'properties' => [
                        'propB' => [
                            'type'       => 'object',
                            'required'   => true,
                            'properties' => [
                                'propC' => [
                                    'type'     => 'string',
                                    'required' => true,
                                ],
                                'propD' => [
                                    'type' => 'string',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $valid = rest_validate_value_from_schema($value, $schema);

        if ($expected) {
            $this->assertTrue($valid);
        } else {
            $this->assertWPError($valid);
        }
    }

    /**
     * @ticket 48818
     *
     * @dataProvider data_required_deeply_nested_property
     */
    public function test_deeply_nested_v4_required_property($value, $expected)
    {
        $schema = [
            'type'       => 'object',
            'required'   => [ 'propA' ],
            'properties' => [
                'propA' => [
                    'type'       => 'object',
                    'required'   => [ 'propB' ],
                    'properties' => [
                        'propB' => [
                            'type'       => 'object',
                            'required'   => [ 'propC' ],
                            'properties' => [
                                'propC' => [
                                    'type' => 'string',
                                ],
                                'propD' => [
                                    'type' => 'string',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $valid = rest_validate_value_from_schema($value, $schema);

        if ($expected) {
            $this->assertTrue($valid);
        } else {
            $this->assertWPError($valid);
        }
    }

    /**
     * @ticket 48818
     *
     * @dataProvider data_required_deeply_nested_property
     */
    public function test_deeply_nested_mixed_version_required_property($value, $expected)
    {
        $schema = [
            'type'       => 'object',
            'required'   => [ 'propA' ],
            'properties' => [
                'propA' => [
                    'type'       => 'object',
                    'required'   => [ 'propB' ],
                    'properties' => [
                        'propB' => [
                            'type'       => 'object',
                            'properties' => [
                                'propC' => [
                                    'type'     => 'string',
                                    'required' => true,
                                ],
                                'propD' => [
                                    'type' => 'string',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $valid = rest_validate_value_from_schema($value, $schema);

        if ($expected) {
            $this->assertTrue($valid);
        } else {
            $this->assertWPError($valid);
        }
    }

    public function data_required_deeply_nested_property()
    {
        return [
            [
                [],
                false,
            ],
            [
                [
                    'propA' => [],
                ],
                false,
            ],
            [
                [
                    'propA' => [
                        'propB' => [],
                    ],
                ],
                false,
            ],
            [
                [
                    'propA' => [
                        'propB' => [
                            'propD' => 'd',
                        ],
                    ],
                ],
                false,
            ],
            [
                [
                    'propA' => [
                        'propB' => [
                            'propC' => 'c',
                        ],
                    ],
                ],
                true,
            ],
        ];
    }

    /**
     * @ticket 51023
     */
    public function test_object_min_properties()
    {
        $schema = [
            'type'          => 'object',
            'minProperties' => 1,
        ];

        $this->assertTrue(
            rest_validate_value_from_schema(
                [
                    'propA' => 'a',
                    'propB' => 'b',
                ],
                $schema
            )
        );
        $this->assertTrue(rest_validate_value_from_schema([ 'propA' => 'a' ], $schema));
        $this->assertWPError(rest_validate_value_from_schema([], $schema));
        $this->assertWPError(rest_validate_value_from_schema('', $schema));
    }

    /**
     * @ticket 51023
     */
    public function test_object_max_properties()
    {
        $schema = [
            'type'          => 'object',
            'maxProperties' => 2,
        ];

        $this->assertTrue(rest_validate_value_from_schema([ 'propA' => 'a' ], $schema));
        $this->assertTrue(
            rest_validate_value_from_schema(
                [
                    'propA' => 'a',
                    'propB' => 'b',
                ],
                $schema
            )
        );
        $this->assertWPError(
            rest_validate_value_from_schema(
                [
                    'propA' => 'a',
                    'propB' => 'b',
                    'propC' => 'c',
                ],
                $schema
            )
        );
        $this->assertWPError(rest_validate_value_from_schema('foobar', $schema));
    }

    /**
     * @ticket 44949
     */
    public function test_string_pattern()
    {
        $schema = [
            'type'    => 'string',
            'pattern' => '^a*$',
        ];

        $this->assertTrue(rest_validate_value_from_schema('a', $schema));
        $this->assertWPError(rest_validate_value_from_schema('b', $schema));
    }

    /**
     * @ticket 44949
     */
    public function test_string_pattern_with_escaped_delimiter()
    {
        $schema = [
            'type'    => 'string',
            'pattern' => '#[0-9]+',
        ];

        $this->assertTrue(rest_validate_value_from_schema('#123', $schema));
        $this->assertWPError(rest_validate_value_from_schema('#abc', $schema));
    }

    /**
     * @ticket 44949
     */
    public function test_string_pattern_with_utf8()
    {
        $schema = [
            'type'    => 'string',
            'pattern' => '^â{1}$',
        ];

        $this->assertTrue(rest_validate_value_from_schema('â', $schema));
        $this->assertWPError(rest_validate_value_from_schema('ââ', $schema));
    }

    /**
     * @ticket 48821
     */
    public function test_array_min_items()
    {
        $schema = [
            'type'     => 'array',
            'minItems' => 1,
            'items'    => [
                'type' => 'number',
            ],
        ];

        $this->assertTrue(rest_validate_value_from_schema([ 1, 2 ], $schema));
        $this->assertTrue(rest_validate_value_from_schema([ 1 ], $schema));
        $this->assertWPError(rest_validate_value_from_schema([], $schema));
        $this->assertWPError(rest_validate_value_from_schema('', $schema));
    }

    /**
     * @ticket 48821
     */
    public function test_array_max_items()
    {
        $schema = [
            'type'     => 'array',
            'maxItems' => 2,
            'items'    => [
                'type' => 'number',
            ],
        ];

        $this->assertTrue(rest_validate_value_from_schema([ 1 ], $schema));
        $this->assertTrue(rest_validate_value_from_schema([ 1, 2 ], $schema));
        $this->assertWPError(rest_validate_value_from_schema([ 1, 2, 3 ], $schema));
        $this->assertWPError(rest_validate_value_from_schema('foobar', $schema));
    }

    /**
     * @ticket 48821
     *
     * @dataProvider data_unique_items
     */
    public function test_unique_items($test, $suite)
    {
        $test_description = $suite['description'] . ': ' . $test['description'];
        $message          = $test_description . ': ' . var_export($test['data'], true);

        $valid = rest_validate_value_from_schema($test['data'], $suite['schema']);

        if ($test['valid']) {
            $this->assertTrue($valid, $message);
        } else {
            $this->assertWPError($valid, $message);
        }
    }

    public function data_unique_items()
    {
        $all_types = [ 'object', 'array', 'null', 'number', 'integer', 'boolean', 'string' ];

        // the following test suites is not supported at the moment
        $skip   = [
            'uniqueItems with an array of items',
            'uniqueItems with an array of items and additionalItems=false',
            'uniqueItems=false with an array of items',
            'uniqueItems=false with an array of items and additionalItems=false',
        ];
        $suites = json_decode(file_get_contents(__DIR__ . '/json_schema_test_suite/uniqueitems.json'), true);

        $tests = [];

        foreach ($suites as $suite) {
            if (in_array($suite['description'], $skip, true)) {
                continue;
            }
            // type is required for our implementation
            if (! isset($suite['schema']['type'])) {
                $suite['schema']['type'] = 'array';
            }
            // items is required for our implementation
            if (! isset($suite['schema']['items'])) {
                $suite['schema']['items'] = [
                    'type'  => $all_types,
                    'items' => [
                        'type' => $all_types,
                    ],
                ];
            }
            foreach ($suite['tests'] as $test) {
                $tests[] = [ $test, $suite ];
            }
        }

        return $tests;
    }

    /**
     * @ticket 48821
     */
    public function test_unique_items_deep_objects()
    {
        $schema = [
            'type'        => 'array',
            'uniqueItems' => true,
            'items'       => [
                'type'       => 'object',
                'properties' => [
                    'release' => [
                        'type'       => 'object',
                        'properties' => [
                            'name'    => [
                                'type' => 'string',
                            ],
                            'version' => [
                                'type' => 'string',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $data = [
            [
                'release' => [
                    'name'    => 'Kirk',
                    'version' => '5.3',
                ],
            ],
            [
                'release' => [
                    'version' => '5.3',
                    'name'    => 'Kirk',
                ],
            ],
        ];

        $this->assertWPError(rest_validate_value_from_schema($data, $schema));

        $data[0]['release']['version'] = '5.3.0';
        $this->assertTrue(rest_validate_value_from_schema($data, $schema));
    }

    /**
     * @ticket 48821
     */
    public function test_unique_items_deep_arrays()
    {
        $schema = [
            'type'        => 'array',
            'uniqueItems' => true,
            'items'       => [
                'type'  => 'array',
                'items' => [
                    'type' => 'string',
                ],
            ],
        ];

        $data = [
            [
                'Kirk',
                'Jaco',
            ],
            [
                'Kirk',
                'Jaco',
            ],
        ];

        $this->assertWPError(rest_validate_value_from_schema($data, $schema));

        $data[1] = array_reverse($data[1]);
        $this->assertTrue(rest_validate_value_from_schema($data, $schema));
    }

    /**
     * @ticket 50300
     */
    public function test_string_or_integer()
    {
        $schema = [
            'type' => [ 'integer', 'string' ],
        ];

        $this->assertTrue(rest_validate_value_from_schema('garbage', $schema));
        $this->assertTrue(rest_validate_value_from_schema(15, $schema));
        $this->assertTrue(rest_validate_value_from_schema('15', $schema));
        $this->assertTrue(rest_validate_value_from_schema('15.5', $schema));
        $this->assertWPError(rest_validate_value_from_schema(15.5, $schema));
    }

    /**
     * @ticket 51025
     *
     * @dataProvider data_any_of
     *
     * @param array $data
     * @param array $schema
     * @param bool $valid
     */
    public function test_any_of($data, $schema, $valid)
    {
        $is_valid = rest_validate_value_from_schema($data, $schema);

        if ($valid) {
            $this->assertTrue($is_valid);
        } else {
            $this->assertWPError($is_valid);
        }
    }

    /**
     * @return array
     */
    public function data_any_of()
    {
        $suites = json_decode(file_get_contents(__DIR__ . '/json_schema_test_suite/anyof.json'), true);
        $skip   = [
            'anyOf with boolean schemas, all true',
            'anyOf with boolean schemas, some true',
            'anyOf with boolean schemas, all false',
            'anyOf with one empty schema',
            'nested anyOf, to check validation semantics',
        ];

        $tests = [];

        foreach ($suites as $suite) {
            if (in_array($suite['description'], $skip, true)) {
                continue;
            }

            foreach ($suite['tests'] as $test) {
                $tests[ $suite['description'] . ': ' . $test['description'] ] = [
                    $test['data'],
                    $suite['schema'],
                    $test['valid'],
                ];
            }
        }

        return $tests;
    }

    /**
     * @ticket 51025
     *
     * @dataProvider data_one_of
     *
     * @param array $data
     * @param array $schema
     * @param bool $valid
     */
    public function test_one_of($data, $schema, $valid)
    {
        $is_valid = rest_validate_value_from_schema($data, $schema);

        if ($valid) {
            $this->assertTrue($is_valid);
        } else {
            $this->assertWPError($is_valid);
        }
    }

    /**
     * @return array
     */
    public function data_one_of()
    {
        $suites = json_decode(file_get_contents(__DIR__ . '/json_schema_test_suite/oneof.json'), true);
        $skip   = [
            'oneOf with boolean schemas, all true',
            'oneOf with boolean schemas, one true',
            'oneOf with boolean schemas, more than one true',
            'oneOf with boolean schemas, all false',
            'oneOf with empty schema',
            'nested oneOf, to check validation semantics',
        ];

        $tests = [];

        foreach ($suites as $suite) {
            if (in_array($suite['description'], $skip, true)) {
                continue;
            }

            foreach ($suite['tests'] as $test) {
                $tests[ $suite['description'] . ': ' . $test['description'] ] = [
                    $test['data'],
                    $suite['schema'],
                    $test['valid'],
                ];
            }
        }

        return $tests;
    }

    /**
     * @ticket 51025
     *
     * @dataProvider data_combining_operation_error_message
     *
     * @param $data
     * @param $schema
     * @param $expected
     */
    public function test_combining_operation_error_message($data, $schema, $expected)
    {
        $is_valid = rest_validate_value_from_schema($data, $schema, 'foo');

        $this->assertWPError($is_valid);
        $this->assertSame($expected, $is_valid->get_error_message());
    }

    /**
     * @return array
     */
    public function data_combining_operation_error_message()
    {
        return [
            [
                10,
                [
                    'anyOf' => [
                        [
                            'title'   => 'circle',
                            'type'    => 'integer',
                            'maximum' => 5,
                        ],
                    ],
                ],
                'foo is not a valid circle. Reason: foo must be less than or equal to 5',
            ],
            [
                10,
                [
                    'anyOf' => [
                        [
                            'type'    => 'integer',
                            'maximum' => 5,
                        ],
                    ],
                ],
                'foo does not match the expected format. Reason: foo must be less than or equal to 5',
            ],
            [
                [ 'a' => 1 ],
                [
                    'anyOf' => [
                        [ 'type' => 'boolean' ],
                        [
                            'title'      => 'circle',
                            'type'       => 'object',
                            'properties' => [
                                'a' => [ 'type' => 'string' ],
                            ],
                        ],
                    ],
                ],
                'foo is not a valid circle. Reason: foo[a] is not of type string.',
            ],
            [
                [ 'a' => 1 ],
                [
                    'anyOf' => [
                        [ 'type' => 'boolean' ],
                        [
                            'type'       => 'object',
                            'properties' => [
                                'a' => [ 'type' => 'string' ],
                            ],
                        ],
                    ],
                ],
                'foo does not match the expected format. Reason: foo[a] is not of type string.',
            ],
            [
                [
                    'a' => 1,
                    'b' => 2,
                    'c' => 3,
                ],
                [
                    'anyOf' => [
                        [ 'type' => 'boolean' ],
                        [
                            'type'       => 'object',
                            'properties' => [
                                'a' => [ 'type' => 'string' ],
                            ],
                        ],
                        [
                            'title'      => 'square',
                            'type'       => 'object',
                            'properties' => [
                                'b' => [ 'type' => 'string' ],
                                'c' => [ 'type' => 'string' ],
                            ],
                        ],
                        [
                            'type'       => 'object',
                            'properties' => [
                                'b' => [ 'type' => 'boolean' ],
                                'x' => [ 'type' => 'boolean' ],
                            ],
                        ],
                    ],
                ],
                'foo is not a valid square. Reason: foo[b] is not of type string.',
            ],
            [
                [
                    'a' => 1,
                    'b' => 2,
                    'c' => 3,
                ],
                [
                    'anyOf' => [
                        [ 'type' => 'boolean' ],
                        [
                            'type'       => 'object',
                            'properties' => [
                                'a' => [ 'type' => 'string' ],
                            ],
                        ],
                        [
                            'type'       => 'object',
                            'properties' => [
                                'b' => [ 'type' => 'string' ],
                                'c' => [ 'type' => 'string' ],
                            ],
                        ],
                        [
                            'type'       => 'object',
                            'properties' => [
                                'b' => [ 'type' => 'boolean' ],
                                'x' => [ 'type' => 'boolean' ],
                            ],
                        ],
                    ],
                ],
                'foo does not match the expected format. Reason: foo[b] is not of type string.',
            ],
            [
                'test',
                [
                    'anyOf' => [
                        [
                            'title' => 'circle',
                            'type'  => 'boolean',
                        ],
                        [
                            'title' => 'square',
                            'type'  => 'integer',
                        ],
                        [
                            'title' => 'triangle',
                            'type'  => 'null',
                        ],
                    ],
                ],
                'foo is not a valid circle, square, and triangle.',
            ],
            [
                'test',
                [
                    'anyOf' => [
                        [ 'type' => 'boolean' ],
                        [ 'type' => 'integer' ],
                        [ 'type' => 'null' ],
                    ],
                ],
                'foo does not match any of the expected formats.',
            ],
            [
                'test',
                [
                    'oneOf' => [
                        [
                            'title' => 'circle',
                            'type'  => 'string',
                        ],
                        [ 'type' => 'integer' ],
                        [
                            'title' => 'triangle',
                            'type'  => 'string',
                        ],
                    ],
                ],
                'foo matches circle and triangle, but should match only one.',
            ],
            [
                'test',
                [
                    'oneOf' => [
                        [ 'type' => 'string' ],
                        [ 'type' => 'integer' ],
                        [ 'type' => 'string' ],
                    ],
                ],
                'foo matches more than one of the expected formats.',
            ],
        ];
    }
}
