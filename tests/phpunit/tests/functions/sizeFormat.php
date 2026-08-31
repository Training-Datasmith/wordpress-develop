<?php

declare(strict_types=1);

/**
 * Tests for size_format()
 *
 * @ticket 22405
 * @ticket 36635
 * @ticket 40875
 *
 * @group functions
 *
 * @covers ::size_format
 */
class Tests_Functions_SizeFormat extends WP_UnitTestCase
{
    /**
     * Data provider.
     *
     * @return array[]
     */
    public function data_size_format()
    {
        return [
            // Invalid values.
            [ [], 0, false ],
            [ 'baba', 0, false ],
            [ '', 0, false ],
            [ '-1', 0, false ],
            [ -1, 0, false ],
            // Bytes.
            [ 0, 0, '0 B' ],
            [ 1, 0, '1 B' ],
            [ 1023, 0, '1,023 B' ],
            // Kilobytes.
            [ KB_IN_BYTES, 0, '1 KB' ],
            [ KB_IN_BYTES, 2, '1.00 KB' ],
            [ 2.5 * KB_IN_BYTES, 0, '3 KB' ],
            [ 2.5 * KB_IN_BYTES, 2, '2.50 KB' ],
            [ 10 * KB_IN_BYTES, 0, '10 KB' ],
            // Megabytes.
            [ (string) 1024 * KB_IN_BYTES, 2, '1.00 MB' ],
            [ MB_IN_BYTES, 0, '1 MB' ],
            [ 2.5 * MB_IN_BYTES, 0, '3 MB' ],
            [ 2.5 * MB_IN_BYTES, 2, '2.50 MB' ],
            // Gigabytes.
            [ (string) 1024 * MB_IN_BYTES, 2, '1.00 GB' ],
            [ GB_IN_BYTES, 0, '1 GB' ],
            [ 2.5 * GB_IN_BYTES, 0, '3 GB' ],
            [ 2.5 * GB_IN_BYTES, 2, '2.50 GB' ],
            // Terabytes.
            [ (string) 1024 * GB_IN_BYTES, 2, '1.00 TB' ],
            [ TB_IN_BYTES, 0, '1 TB' ],
            [ 2.5 * TB_IN_BYTES, 0, '3 TB' ],
            [ 2.5 * TB_IN_BYTES, 2, '2.50 TB' ],
            // Petabytes.
            [ (string) 1024 * TB_IN_BYTES, 2, '1.00 PB' ],
            [ PB_IN_BYTES, 0, '1 PB' ],
            [ 2.5 * PB_IN_BYTES, 0, '3 PB' ],
            [ 2.5 * PB_IN_BYTES, 2, '2.50 PB' ],
            // Exabytes.
            [ (string) 1024 * PB_IN_BYTES, 2, '1.00 EB' ],
            [ EB_IN_BYTES, 0, '1 EB' ],
            [ 2.5 * EB_IN_BYTES, 0, '3 EB' ],
            [ 2.5 * EB_IN_BYTES, 2, '2.50 EB' ],
            // Zettabytes.
            [ (string) 1024 * EB_IN_BYTES, 2, '1.00 ZB' ],
            [ ZB_IN_BYTES, 0, '1 ZB' ],
            [ 2.5 * ZB_IN_BYTES, 0, '3 ZB' ],
            [ 2.5 * ZB_IN_BYTES, 2, '2.50 ZB' ],
            // Yottabytes.
            [ (string) 1024 * ZB_IN_BYTES, 2, '1.00 YB' ],
            [ YB_IN_BYTES, 0, '1 YB' ],
            [ 2.5 * YB_IN_BYTES, 0, '3 YB' ],
            [ 2.5 * YB_IN_BYTES, 2, '2.50 YB' ],
            // Edge values.
            [ TB_IN_BYTES + (TB_IN_BYTES / 2) + MB_IN_BYTES, 1, '1.5 TB' ],
            [ TB_IN_BYTES - MB_IN_BYTES - KB_IN_BYTES, 3, '1,023.999 GB' ],
        ];
    }

    /**
     * @dataProvider data_size_format
     *
     * @param $bytes
     * @param $decimals
     * @param $expected
     */
    public function test_size_format($bytes, $decimals, $expected)
    {
        $this->assertSame($expected, size_format($bytes, $decimals));
    }
}
