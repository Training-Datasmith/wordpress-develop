<?php

declare(strict_types=1);
/**
 * Test class for `_get_non_cached_ids()`.
 *
 * @package WordPress
 *
 * @group functions
 * @group cache
 *
 * @covers ::_get_non_cached_ids
 * @covers ::_validate_cache_id
 */
class Tests_Functions_GetNonCachedIds extends WP_UnitTestCase
{
    /**
     * @ticket 57593
     */
    public function test_uncached_valid_ids_should_be_unique()
    {
        $object_id = 1;

        $this->assertSame(
            [ $object_id ],
            _get_non_cached_ids([ $object_id, $object_id, (string) $object_id ], 'fake-group'),
            'Duplicate object IDs should be removed.'
        );
    }

    /**
     * @ticket 57593
     *
     * @dataProvider data_valid_ids_should_be_returned_as_integers
     *
     * @param mixed $object_id The object ID.
     */
    public function test_valid_ids_should_be_returned_as_integers($object_id)
    {
        $this->assertSame(
            [ (int) $object_id ],
            _get_non_cached_ids([ $object_id ], 'fake-group'),
            'Object IDs should be returned as integers.'
        );
    }

    /**
     * Data provider.
     *
     * @return array[]
     */
    public function data_valid_ids_should_be_returned_as_integers()
    {
        return [
            '(int) 1'    => [ 1 ],
            '(string) 1' => [ '1' ],
        ];
    }

    /**
     * @ticket 57593
     */
    public function test_mix_of_valid_and_invalid_ids_should_return_the_valid_ids_and_throw_a_notice()
    {
        $object_id = 1;

        $this->setExpectedIncorrectUsage('_get_non_cached_ids');
        $this->assertSame(
            [ $object_id ],
            _get_non_cached_ids([ $object_id, null ], 'fake-group'),
            'Valid object IDs should be returned.'
        );
    }

    /**
     * @ticket 57593
     *
     * @dataProvider data_invalid_cache_ids_should_throw_a_notice
     *
     * @param mixed $object_id The object ID.
     */
    public function test_invalid_cache_ids_should_throw_a_notice($object_id)
    {
        $this->setExpectedIncorrectUsage('_get_non_cached_ids');
        $this->assertSame(
            [],
            _get_non_cached_ids([ $object_id ], 'fake-group'),
            'Invalid object IDs should be dropped.'
        );
    }

    /**
     * Data provider.
     *
     * @return array[]
     */
    public function data_invalid_cache_ids_should_throw_a_notice()
    {
        return [
            'null'         => [ null ],
            'false'        => [ false ],
            'true'         => [ true ],
            '(float) 1.0'  => [ 1.0 ],
            '(string) 5.0' => [ '5.0' ],
            'string'       => [ 'johnny cache' ],
            'empty string' => [ '' ],
            'array'        => [ [ 1 ] ],
            'empty array'  => [ [] ],
            'stdClass'     => [ new stdClass() ],
        ];
    }
}
