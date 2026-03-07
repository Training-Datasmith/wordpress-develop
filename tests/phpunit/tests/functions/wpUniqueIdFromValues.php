<?php

declare(strict_types=1);

/**
 * Test cases for the `wp_unique_id_from_values()` function.
 *
 * @package WordPress\UnitTests
 *
 * @since 6.8.0
 *
 * @group functions.php
 * @covers ::wp_unique_id_from_values
 */
class Tests_Functions_WpUniqueIdFromValues extends WP_UnitTestCase
{
    /**
     * Prefix used for testing.
     *
     * @var string
     */
    private $prefix = 'my-prefix-';

    /**
     * Test that the function returns consistent ids for the passed params.
     *
     * @ticket 62985
     *
     * @dataProvider data_wp_unique_id_from_values
     *
     * @since 6.8.0
     */
    public function test_wp_unique_id_from_values($data)
    {
        // Generate IDs.
        $unique_id_original = wp_unique_id_from_values($data);
        $unique_id_prefixed = wp_unique_id_from_values($data, $this->prefix);

        // Ensure that the same input produces the same ID.
        $this->assertSame($unique_id_original, wp_unique_id_from_values($data));
        $this->assertSame($unique_id_prefixed, wp_unique_id_from_values($data, $this->prefix));

        // Ensure that the prefixed ID is the prefix + the original ID.
        $this->assertSame($this->prefix . $unique_id_original, $unique_id_prefixed);
    }

    /**
     * Test that different input data generates distinct IDs.
     *
     * @ticket 62985
     *
     * @dataProvider data_wp_unique_id_from_values
     *
     * @since 6.8.0
     */
    public function test_wp_unique_id_from_values_uniqueness($data)
    {
        // Generate IDs.
        $unique_id_original = wp_unique_id_from_values($data);
        $unique_id_prefixed = wp_unique_id_from_values($data, $this->prefix);

        // Modify the data slightly to generate a different ID.
        $data_modified          = $data;
        $data_modified['value'] = 'modified';

        // Generate new IDs with the modified data.
        $unique_id_modified          = wp_unique_id_from_values($data_modified);
        $unique_id_prefixed_modified = wp_unique_id_from_values($data_modified, $this->prefix);

        // Assert that the IDs for different data are distinct.
        $this->assertNotSame($unique_id_original, $unique_id_modified);
        $this->assertNotSame($unique_id_prefixed, $unique_id_prefixed_modified);
    }

    /**
     * Data provider.
     *
     * @return array[]
     */
    public function data_wp_unique_id_from_values()
    {
        return [
            'string'          => [ [ 'value' => 'text' ] ],
            'integer'         => [ [ 'value' => 123 ] ],
            'float'           => [ [ 'value' => 1.23 ] ],
            'boolean'         => [ [ 'value' => true ] ],
            'object'          => [ [ 'value' => new StdClass() ] ],
            'null'            => [ [ 'value' => null ] ],
            'multiple values' => [
                [
                    'value1' => 'text',
                    'value2' => 123,
                    'value3' => 1.23,
                    'value4' => true,
                    'value5' => new StdClass(),
                    'value6' => null,
                ],
            ],
            'nested arrays'   => [
                [
                    'list1' => [
                        'value1' => 'text',
                        'value2' => 123,
                        'value3' => 1.23,
                    ],
                    'list2' => [
                        'value4' => true,
                        'value5' => new StdClass(),
                        'value6' => null,
                    ],
                ],
            ],
        ];
    }

    /**
     * Test that passing an empty array is not allowed.
     *
     * @ticket 62985
     *
     * @expectedIncorrectUsage wp_unique_id_from_values
     *
     * @since 6.8.0
     */
    public function test_wp_unique_id_from_values_empty_array()
    {
        wp_unique_id_from_values([], $this->prefix);
    }

    /**
     * Test that passing non-array data throws an error.
     *
     * @ticket 62985
     *
     * @dataProvider data_wp_unique_id_from_values_invalid_data
     *
     * @since 6.8.0
     */
    public function test_wp_unique_id_from_values_invalid_data($data)
    {
        $this->expectException(TypeError::class);

        wp_unique_id_from_values($data, $this->prefix);
    }

    /**
     * Data provider for invalid data tests.
     *
     * @return array[]
     */
    public function data_wp_unique_id_from_values_invalid_data()
    {
        return [
            'string'  => [ 'text' ],
            'integer' => [ 123 ],
            'float'   => [ 1.23 ],
            'boolean' => [ true ],
            'object'  => [ new StdClass() ],
            'null'    => [ null ],
        ];
    }
}
