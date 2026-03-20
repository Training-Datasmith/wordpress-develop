<?php

declare(strict_types=1);

/**
 * Tests for `is_serialized_string()`.
 *
 * @ticket 42870
 *
 * @group functions
 *
 * @covers ::is_serialized_string
 */
class Tests_Functions_IsSerializedString extends WP_UnitTestCase
{
    /**
     * @dataProvider data_is_serialized_string
     *
     * @param array|object|int|string $data     Data value to test.
     * @param bool                    $expected Expected function result.
     */
    public function test_is_serialized_string($data, $expected)
    {
        $this->assertSame($expected, is_serialized_string($data));
    }

    /**
     * Data provider for `test_is_serialized_string()`.
     *
     * @return array[]
     */
    public function data_is_serialized_string()
    {
        return [
            'an array'                                => [
                'data'     => [],
                'expected' => false,
            ],
            'an object'                               => [
                'data'     => new stdClass(),
                'expected' => false,
            ],
            'an integer 0'                            => [
                'data'     => 0,
                'expected' => false,
            ],
            'a string that is too short when trimmed' => [
                'data'     => 's:3       ',
                'expected' => false,
            ],
            'a string that is too short'              => [
                'data'     => 's:3',
                'expected' => false,
            ],
            'not a colon in second position'          => [
                'data'     => 's!3:"foo";',
                'expected' => false,
            ],
            'no trailing semicolon'                   => [
                'data'     => 's:3:"foo"',
                'expected' => false,
            ],
            'wrong type of serialized data'           => [
                'data'     => 'a:3:"foo";',
                'expected' => false,
            ],
            'no closing quote'                        => [
                'data'     => 'a:3:"foo;',
                'expected' => false,
            ],
            'single quotes instead of double'         => [
                'data'     => "s:12:'foo';",
                'expected' => false,
            ],
            'wrong number of characters (should not matter)' => [
                'data'     => 's:12:"foo";',
                'expected' => true,
            ],
            'valid serialized string'                 => [
                'data'     => 's:3:"foo";',
                'expected' => true,
            ],
        ];
    }
}
