<?php

declare(strict_types=1);

/**
 * Test the `_get_cron_array()` function.
 *
 * @group cron
 * @covers ::_get_cron_array
 */
class Tests_Cron_getCronArray extends WP_UnitTestCase
{
    public function set_up()
    {
        parent::set_up();
        // Make sure the schedule is clear.
        _set_cron_array([]);
    }

    public function tear_down()
    {
        // Make sure the schedule is clear.
        _set_cron_array([]);
        parent::tear_down();
    }

    /**
     * Tests the output validation for the `_get_cron_array()` function when the option is unset.
     *
     * @ticket 53940
     */
    public function test_get_cron_array_output_validation_with_no_option()
    {
        delete_option('cron');

        $crons = _get_cron_array();
        $this->assertIsArray($crons, 'Cron jobs is not an array.');
        $this->assertCount(0, $crons, 'Cron job does not contain the expected number of entries.');
    }

    /**
     * Tests the output validation for the `_get_cron_array()` function.
     *
     * @ticket 53940
     *
     * @dataProvider data_get_cron_array_output_validation
     *
     * @param mixed $input    Cron "array".
     * @param int   $expected Expected array entry count of the cron option after update.
     */
    public function test_get_cron_array_output_validation($input, $expected)
    {
        update_option('cron', $input);

        $crons = _get_cron_array();
        $this->assertIsArray($crons, 'Cron jobs is not an array.');
        $this->assertCount($expected, $crons, 'Cron job does not contain the expected number of entries.');
    }

    /**
     * Data provider.
     *
     * @return array
     */
    public function data_get_cron_array_output_validation()
    {
        return [
            'stdClass'    => [
                'input'    => new stdClass(),
                'expected' => 0,
            ],
            'null'        => [
                'input'    => null,
                'expected' => 0,
            ],
            'false'       => [
                'input'    => false,
                'expected' => 0,
            ],
            'true'        => [
                'input'    => true,
                'expected' => 0,
            ],
            'integer'     => [
                'input'    => 53940,
                'expected' => 0,
            ],
            'float'       => [
                'input'    => 539.40,
                'expected' => 0,
            ],
            'string'      => [
                'input'    => 'ticket 53940',
                'expected' => 0,
            ],
            'empty array' => [
                'input'    => [],
                'expected' => 0,
            ],
            'cron array'  => [
                'input'    => [
                    'version' => 2,
                    time()    => [
                        'hookname' => [
                            'event key' => [
                                'schedule' => 'schedule',
                                'args'     => 'args',
                                'interval' => 'interval',
                            ],
                        ],
                    ],
                ],
                'expected' => 1,
            ],
            'cron v1'     => [
                'input'    => [
                    time() => [
                        'hookname' => [
                            'args' => 'args',
                        ],
                    ],
                ],
                'expected' => 1,
            ],
        ];
    }
}
