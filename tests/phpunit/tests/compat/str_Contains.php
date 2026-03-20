<?php

declare(strict_types=1);

/**
 * @group compat
 *
 * @covers ::str_contains
 */
class Tests_Compat_strContains extends WP_UnitTestCase
{
    /**
     * Test that str_contains() is always available (either from PHP or WP).
     *
     * @ticket 49652
     */
    public function test_is_str_contains_availability()
    {
        $this->assertTrue(function_exists('str_contains'));
    }

    /**
     * @dataProvider data_str_contains
     *
     * @ticket 49652
     *
     * @param bool   $expected Whether or not `$haystack` is expected to contain `$needle`.
     * @param string $haystack The string to search in.
     * @param string $needle   The substring to search for in `$haystack`.
     */
    public function test_str_contains($expected, $haystack, $needle)
    {
        $this->assertSame($expected, str_contains($haystack, $needle));
    }

    /**
     * Data provider.
     *
     * @return array
     */
    public function data_str_contains()
    {
        return [
            'empty needle'              => [
                'expected' => true,
                'haystack' => 'This is a Test',
                'needle'   => '',
            ],
            'empty haystack and needle' => [
                'expected' => true,
                'haystack' => '',
                'needle'   => '',
            ],
            'empty haystack'            => [
                'expected' => false,
                'haystack' => '',
                'needle'   => 'test',
            ],
            'start of string'           => [
                'expected' => true,
                'haystack' => 'This is a Test',
                'needle'   => 'This',
            ],
            'middle of string'          => [
                'expected' => true,
                'haystack' => 'The needle in middle of string.',
                'needle'   => 'middle',
            ],
            'end of string'             => [
                'expected' => true,
                'string'   => 'The needle is at end.',
                'needle'   => 'end',
            ],
            'lowercase'                 => [
                'expected' => true,
                'string'   => 'This is a test',
                'needle'   => 'test',
            ],
            'uppercase'                 => [
                'expected' => true,
                'string'   => 'This is a TEST',
                'needle'   => 'TEST',
            ],
            'camelCase'                 => [
                'expected' => true,
                'string'   => 'String contains camelCase.',
                'needle'   => 'camelCase',
            ],
            'with hyphen'               => [
                'expected' => true,
                'string'   => 'String contains foo-bar needle.',
                'needle'   => 'foo-bar',
            ],
            'missing'                   => [
                'expected' => false,
                'haystack' => 'This is a camelcase',
                'needle'   => 'camelCase',
            ],
        ];
    }
}
