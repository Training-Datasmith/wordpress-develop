<?php

declare(strict_types=1);

/**
 * Tests for `is_serialized()`.
 *
 * @ticket 53299
 *
 * @group functions
 *
 * @covers ::is_serialized
 */
class Tests_Functions_IsSerialized extends WP_UnitTestCase
{
    /**
     * @dataProvider data_is_serialized
     * @dataProvider data_is_not_serialized
     *
     * @param mixed $data     Data value to test.
     * @param bool  $expected Expected function result.
     */
    public function test_is_serialized($data, $expected)
    {
        $this->assertSame($expected, is_serialized($data));
    }

    /**
     * Data provider for `test_is_serialized()`.
     *
     * @return array[]
     */
    public function data_is_serialized()
    {
        return [
            'serialized empty array'            => [
                'data'     => serialize([]),
                'expected' => true,
            ],
            'serialized non-empty array'        => [
                'data'     => serialize([ 1, 1, 2, 3, 5, 8, 13 ]),
                'expected' => true,
            ],
            'serialized empty object'           => [
                'data'     => serialize(new stdClass()),
                'expected' => true,
            ],
            'serialized non-empty object'       => [
                'data'     => serialize(
                    (object) [
                        'test' => true,
                        '1',
                        2,
                    ]
                ),
                'expected' => true,
            ],
            'serialized null'                   => [
                'data'     => serialize(null),
                'expected' => true,
            ],
            'serialized boolean true'           => [
                'data'     => serialize(true),
                'expected' => true,
            ],
            'serialized boolean false'          => [
                'data'     => serialize(false),
                'expected' => true,
            ],
            'serialized integer -1'             => [
                'data'     => serialize(-1),
                'expected' => true,
            ],
            'serialized integer 1'              => [
                'data'     => serialize(-1),
                'expected' => true,
            ],
            'serialized float 1.1'              => [
                'data'     => serialize(1.1),
                'expected' => true,
            ],
            'serialized string'                 => [
                'data'     => serialize('this string will be serialized'),
                'expected' => true,
            ],
            'serialized string with line break' => [
                'data'     => serialize("a\nb"),
                'expected' => true,
            ],
            'serialized string with leading and trailing spaces' => [
                'data'     => '   s:25:"this string is serialized";   ',
                'expected' => true,
            ],
            'serialized enum'                   => [
                'data'     => 'E:7:"Foo:bar";',
                'expected' => true,
            ],
        ];
    }

    /**
     * Data provider for `test_is_serialized()`.
     *
     * @return array[]
     */
    public function data_is_not_serialized()
    {
        return [
            'an empty array'                             => [
                'data'     => [],
                'expected' => false,
            ],
            'a non-empty array'                          => [
                'data'     => [ 1, 1, 2, 3, 5, 8, 13 ],
                'expected' => false,
            ],
            'an empty object'                            => [
                'data'     => new stdClass(),
                'expected' => false,
            ],
            'a non-empty object'                         => [
                'data'     => (object) [
                    'test' => true,
                    '1',
                    2,
                ],
                'expected' => false,
            ],
            'null'                                       => [
                'data'     => null,
                'expected' => false,
            ],
            'a boolean true'                             => [
                'data'     => true,
                'expected' => false,
            ],
            'a boolean false'                            => [
                'data'     => false,
                'expected' => false,
            ],
            'an integer -1'                              => [
                'data'     => -1,
                'expected' => false,
            ],
            'an integer 0'                               => [
                'data'     => 0,
                'expected' => false,
            ],
            'an integer 1'                               => [
                'data'     => 1,
                'expected' => false,
            ],
            'a float 0.0'                                => [
                'data'     => 0.0,
                'expected' => false,
            ],
            'a float 1.1'                                => [
                'data'     => 1.1,
                'expected' => false,
            ],
            'a string'                                   => [
                'data'     => 'a string',
                'expected' => false,
            ],
            'a string with line break'                   => [
                'data'     => "a\nb",
                'expected' => false,
            ],
            'a string with leading and trailing garbage' => [
                'data'     => 'garbage:a:0:garbage;',
                'expected' => false,
            ],
            'a string with missing double quotes'        => [
                'data'     => 's:4:test;',
                'expected' => false,
            ],
            'a string that is too short'                 => [
                'data'     => 's:3',
                'expected' => false,
            ],
            'not a colon in second position'             => [
                'data'     => 's!3:"foo";',
                'expected' => false,
            ],
            'no trailing semicolon (strict check)'       => [
                'data'     => 's:3:"foo"',
                'expected' => false,
            ],
        ];
    }

    /**
     * @ticket 46570
     * @dataProvider data_is_serialized_should_return_true_for_large_floats
     */
    public function test_is_serialized_should_return_true_for_large_floats($value)
    {
        $this->assertTrue(is_serialized($value));
    }

    /**
     * Data provider.
     *
     * @return array[]
     */
    public function data_is_serialized_should_return_true_for_large_floats()
    {
        return [
            [ serialize(1.7976931348623157E+308) ],
            [ serialize([ 1.7976931348623157E+308, 1.23e50 ]) ],
        ];
    }

    /**
     * @ticket 17375
     */
    public function test_no_new_serializable_types()
    {
        $this->assertFalse(is_serialized('C:16:"Serialized_Class":6:{a:0:{}}'));
    }
}
