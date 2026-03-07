<?php

declare(strict_types=1);

/**
 * @group date
 * @group datetime
 *
 * @covers ::wp_timezone_string
 * @covers ::wp_timezone
 */
class Tests_Date_wpTimezone extends WP_UnitTestCase
{
    /**
     * Cleans up.
     */
    public function tear_down()
    {
        // Reset changed options to their default value.
        update_option('gmt_offset', 0);
        update_option('timezone_string', '');

        parent::tear_down();
    }

    /**
     * @ticket 24730
     *
     * @dataProvider data_should_convert_gmt_offset
     *
     * @param float  $gmt_offset Numeric offset from UTC.
     * @param string $tz_name    Expected timezone name.
     */
    public function test_should_convert_gmt_offset($gmt_offset, $tz_name)
    {
        delete_option('timezone_string');
        update_option('gmt_offset', $gmt_offset);

        $this->assertSame($tz_name, wp_timezone_string());

        $timezone = wp_timezone();

        $this->assertSame($tz_name, $timezone->getName());
    }

    /**
     * Data provider to test numeric offset conversion.
     *
     * @return array
     */
    public function data_should_convert_gmt_offset()
    {
        return [
            [ -12, '-12:00' ],
            [ -11.5, '-11:30' ],
            [ -11, '-11:00' ],
            [ -10.5, '-10:30' ],
            [ -10, '-10:00' ],
            [ -9.5, '-09:30' ],
            [ -9, '-09:00' ],
            [ -8.5, '-08:30' ],
            [ -8, '-08:00' ],
            [ -7.5, '-07:30' ],
            [ -7, '-07:00' ],
            [ -6.5, '-06:30' ],
            [ -6, '-06:00' ],
            [ -5.5, '-05:30' ],
            [ -5, '-05:00' ],
            [ -4.5, '-04:30' ],
            [ -4, '-04:00' ],
            [ -3.5, '-03:30' ],
            [ -3, '-03:00' ],
            [ -2.5, '-02:30' ],
            [ -2, '-02:00' ],
            [ '-1.5', '-01:30' ],
            [ -1.5, '-01:30' ],
            [ -1, '-01:00' ],
            [ -0.5, '-00:30' ],
            [ 0, '+00:00' ],
            [ '0', '+00:00' ],
            [ 0.5, '+00:30' ],
            [ 1, '+01:00' ],
            [ 1.5, '+01:30' ],
            [ '1.5', '+01:30' ],
            [ 2, '+02:00' ],
            [ 2.5, '+02:30' ],
            [ 3, '+03:00' ],
            [ 3.5, '+03:30' ],
            [ 4, '+04:00' ],
            [ 4.5, '+04:30' ],
            [ 5, '+05:00' ],
            [ 5.5, '+05:30' ],
            [ 5.75, '+05:45' ],
            [ 6, '+06:00' ],
            [ 6.5, '+06:30' ],
            [ 7, '+07:00' ],
            [ 7.5, '+07:30' ],
            [ 8, '+08:00' ],
            [ 8.5, '+08:30' ],
            [ 8.75, '+08:45' ],
            [ 9, '+09:00' ],
            [ 9.5, '+09:30' ],
            [ 10, '+10:00' ],
            [ 10.5, '+10:30' ],
            [ 11, '+11:00' ],
            [ 11.5, '+11:30' ],
            [ 12, '+12:00' ],
            [ 12.75, '+12:45' ],
            [ 13, '+13:00' ],
            [ 13.75, '+13:45' ],
            [ 14, '+14:00' ],
        ];
    }

    /**
     * @ticket 24730
     */
    public function test_should_return_timezone_string()
    {
        update_option('timezone_string', 'Europe/Helsinki');

        $this->assertSame('Europe/Helsinki', wp_timezone_string());

        $timezone = wp_timezone();

        $this->assertSame('Europe/Helsinki', $timezone->getName());
    }

    /**
     * Ensures that deprecated timezone strings are handled correctly.
     *
     * @ticket 56468
     */
    public function test_should_return_deprecated_timezone_string()
    {
        $tz_string = 'America/Buenos_Aires'; // This timezone was deprecated pre-PHP 5.6.
        update_option('timezone_string', $tz_string);

        $this->assertSame($tz_string, wp_timezone_string());

        $timezone = wp_timezone();

        $this->assertSame($tz_string, $timezone->getName());
    }
}
