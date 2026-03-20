<?php

declare(strict_types=1);

/**
 * Tests get_status_header_desc function
 *
 * @since 5.3.0
 *
 * @group functions
 *
 * @covers ::get_status_header_desc
 */
class Tests_Functions_GetStatusHeaderDesc extends WP_UnitTestCase
{
    /**
     * @dataProvider data_get_status_header_desc
     *
     * @param int    $code     HTTP status code.
     * @param string $expected Status description.
     */
    public function test_get_status_header_desc($code, $expected)
    {
        $this->assertSame($expected, get_status_header_desc($code));
    }

    /**
     * Data provider for test_get_status_header_desc().
     *
     * @return array[]
     */
    public function data_get_status_header_desc()
    {
        return [
            [ 200, 'OK' ],
            [ 301, 'Moved Permanently' ],
            [ 404, 'Not Found' ],
            [ 500, 'Internal Server Error' ],

            // A string to make sure that the absint() is working.
            [ '200', 'OK' ],

            // Not recognized codes return empty strings.
            [ 9999, '' ],
            [ 'random', '' ],
        ];
    }
}
