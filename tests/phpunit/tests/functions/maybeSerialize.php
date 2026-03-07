<?php

declare(strict_types=1);

/**
 * Tests for `maybe_serialize()` and `maybe_unserialize()`.
 *
 * @group functions
 *
 * @covers ::maybe_serialize
 * @covers ::maybe_unserialize
 */
class Tests_Functions_MaybeSerialize extends WP_UnitTestCase
{
    /**
     * @dataProvider data_is_not_serialized
     */
    public function test_maybe_serialize($value)
    {
        if (is_array($value) || is_object($value)) {
            $expected = serialize($value);
        } else {
            $expected = $value;
        }

        $this->assertSame($expected, maybe_serialize($value));
    }

    /**
     * @dataProvider data_is_serialized
     */
    public function test_maybe_serialize_with_double_serialization($value)
    {
        $expected = serialize($value);

        $this->assertSame($expected, maybe_serialize($value));
    }

    /**
     * @dataProvider data_is_serialized
     * @dataProvider data_is_not_serialized
     */
    public function test_maybe_unserialize($value, $is_serialized)
    {
        if ($is_serialized) {
            $expected = unserialize(trim($value));
        } else {
            $expected = $value;
        }

        if (is_object($expected)) {
            $this->assertEquals($expected, maybe_unserialize($value));
        } else {
            $this->assertSame($expected, maybe_unserialize($value));
        }
    }

    /**
     * Data provider for `test_maybe_unserialize()`.
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
        ];
    }

    /**
     * Data provider for `test_maybe_serialize()`.
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
     * @dataProvider data_serialize_deserialize_objects
     */
    public function test_deserialize_request_utility_filtered_iterator_objects($value)
    {
        $serialized = maybe_serialize($value);

        if (get_class($value) === 'WpOrg\Requests\Utility\FilteredIterator') {
            $new_value = unserialize($serialized);
            $property  = (new ReflectionClass('WpOrg\Requests\Utility\FilteredIterator'))->getProperty('callback');
            if (PHP_VERSION_ID < 80100) {
                $property->setAccessible(true);
            }
            $callback_value = $property->getValue($new_value);

            $this->assertSame(null, $callback_value);
        } else {
            $this->assertSame($value->count(), unserialize($serialized)->count());
        }
    }

    /**
     * Data provider for test_deserialize_request_utility_filtered_iterator_objects().
     *
     * @return array[]
     */
    public function data_serialize_deserialize_objects()
    {
        return [
            'filtered iterator using md5'  => [
                new WpOrg\Requests\Utility\FilteredIterator([ 1 ], 'md5'),
            ],
            'filtered iterator using sha1' => [
                new WpOrg\Requests\Utility\FilteredIterator([ 1, 2 ], 'sha1'),
            ],
            'array iterator'               => [
                new ArrayIterator([ 1, 2, 3 ]),
            ],
        ];
    }
}
