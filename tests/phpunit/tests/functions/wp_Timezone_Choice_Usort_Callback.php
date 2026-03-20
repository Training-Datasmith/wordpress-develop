<?php

declare(strict_types=1);

/**
 * Tests for the _wp_timezone_choice_usort_callback() function.
 *
 * @group functions
 *
 * @covers ::_wp_timezone_choice_usort_callback
 */
class Tests_Functions_WpTimezoneChoiceUsortCallback extends WP_UnitTestCase
{
    /**
     * @ticket 59953
     *
     * @dataProvider data_wp_timezone_choice_usort_callback
     */
    public function test_wp_timezone_choice_usort_callback($unsorted, $sorted)
    {
        usort($unsorted, '_wp_timezone_choice_usort_callback');

        $this->assertSame($sorted, $unsorted);
    }

    public function data_wp_timezone_choice_usort_callback()
    {
        return [
            'just GMT+'                         => [
                'unsorted' => [
                    [
                        'continent'   => 'Etc',
                        'city'        => 'GMT+a',
                        't_continent' => '',
                        't_city'      => '',
                        't_subcity'   => '',
                    ],
                    [
                        'continent'   => 'Etc',
                        'city'        => 'GMT+b',
                        't_continent' => '',
                        't_city'      => '',
                        't_subcity'   => '',
                    ],
                    [
                        'continent'   => 'Etc',
                        'city'        => 'GMT+c',
                        't_continent' => '',
                        't_city'      => '',
                        't_subcity'   => '',
                    ],
                    [
                        'continent'   => 'Etc',
                        'city'        => 'GMT+e',
                        't_continent' => '',
                        't_city'      => '',
                        't_subcity'   => '',
                    ],
                    [
                        'continent'   => 'Etc',
                        'city'        => 'GMT+d',
                        't_continent' => '',
                        't_city'      => '',
                        't_subcity'   => '',
                    ],
                ],
                'sorted'   => [
                    [
                        'continent'   => 'Etc',
                        'city'        => 'GMT+e',
                        't_continent' => '',
                        't_city'      => '',
                        't_subcity'   => '',
                    ],
                    [
                        'continent'   => 'Etc',
                        'city'        => 'GMT+d',
                        't_continent' => '',
                        't_city'      => '',
                        't_subcity'   => '',
                    ],
                    [
                        'continent'   => 'Etc',
                        'city'        => 'GMT+c',
                        't_continent' => '',
                        't_city'      => '',
                        't_subcity'   => '',
                    ],
                    [
                        'continent'   => 'Etc',
                        'city'        => 'GMT+b',
                        't_continent' => '',
                        't_city'      => '',
                        't_subcity'   => '',
                    ],
                    [
                        'continent'   => 'Etc',
                        'city'        => 'GMT+a',
                        't_continent' => '',
                        't_city'      => '',
                        't_subcity'   => '',
                    ],
                ],
            ],

            'mixed UTC and GMT'                 => [
                'unsorted' => [
                    [
                        'continent'   => 'Etc',
                        'city'        => 'GMT+a',
                        't_continent' => '',
                        't_city'      => '',
                        't_subcity'   => '',
                    ],
                    [
                        'continent'   => 'Etc',
                        'city'        => 'UTC',
                        't_continent' => '',
                        't_city'      => '',
                        't_subcity'   => '',
                    ],
                    [
                        'continent'   => 'Etc',
                        'city'        => 'GMT+c',
                        't_continent' => '',
                        't_city'      => '',
                        't_subcity'   => '',
                    ],
                    [
                        'continent'   => 'Etc',
                        'city'        => 'UTC',
                        't_continent' => '',
                        't_city'      => '',
                        't_subcity'   => '',
                    ],
                    [
                        'continent'   => 'Etc',
                        'city'        => 'GMT+d',
                        't_continent' => '',
                        't_city'      => '',
                        't_subcity'   => '',
                    ],
                ],
                'sorted'   => [
                    [
                        'continent'   => 'Etc',
                        'city'        => 'GMT+d',
                        't_continent' => '',
                        't_city'      => '',
                        't_subcity'   => '',
                    ],
                    [
                        'continent'   => 'Etc',
                        'city'        => 'GMT+c',
                        't_continent' => '',
                        't_city'      => '',
                        't_subcity'   => '',
                    ],
                    [
                        'continent'   => 'Etc',
                        'city'        => 'GMT+a',
                        't_continent' => '',
                        't_city'      => '',
                        't_subcity'   => '',
                    ],
                    [
                        'continent'   => 'Etc',
                        'city'        => 'UTC',
                        't_continent' => '',
                        't_city'      => '',
                        't_subcity'   => '',
                    ],
                    [
                        'continent'   => 'Etc',
                        'city'        => 'UTC',
                        't_continent' => '',
                        't_city'      => '',
                        't_subcity'   => '',
                    ],
                ],
            ],

            'just alpha city'                   => [
                'unsorted' => [
                    [
                        'continent'   => 'Etc',
                        'city'        => 'a',
                        't_continent' => '',
                        't_city'      => '',
                        't_subcity'   => '',
                    ],
                    [
                        'continent'   => 'Etc',
                        'city'        => 'e',
                        't_continent' => '',
                        't_city'      => '',
                        't_subcity'   => '',
                    ],
                    [
                        'continent'   => 'Etc',
                        'city'        => 'b',
                        't_continent' => '',
                        't_city'      => '',
                        't_subcity'   => '',
                    ],
                    [
                        'continent'   => 'Etc',
                        'city'        => 'd',
                        't_continent' => '',
                        't_city'      => '',
                        't_subcity'   => '',
                    ],
                    [
                        'continent'   => 'Etc',
                        'city'        => 'c',
                        't_continent' => '',
                        't_city'      => '',
                        't_subcity'   => '',
                    ],
                ],
                'sorted'   => [
                    [
                        'continent'   => 'Etc',
                        'city'        => 'a',
                        't_continent' => '',
                        't_city'      => '',
                        't_subcity'   => '',
                    ],
                    [
                        'continent'   => 'Etc',
                        'city'        => 'b',
                        't_continent' => '',
                        't_city'      => '',
                        't_subcity'   => '',
                    ],
                    [
                        'continent'   => 'Etc',
                        'city'        => 'c',
                        't_continent' => '',
                        't_city'      => '',
                        't_subcity'   => '',
                    ],
                    [
                        'continent'   => 'Etc',
                        'city'        => 'd',
                        't_continent' => '',
                        't_city'      => '',
                        't_subcity'   => '',
                    ],
                    [
                        'continent'   => 'Etc',
                        'city'        => 'e',
                        't_continent' => '',
                        't_city'      => '',
                        't_subcity'   => '',
                    ],
                ],
            ],

            'not Etc continents are not sorted' => [
                'unsorted' => [
                    [
                        'continent'   => 'd',
                        'city'        => '',
                        't_continent' => '',
                        't_city'      => '',
                        't_subcity'   => '',
                    ],
                    [
                        'continent'   => 'c',
                        'city'        => '',
                        't_continent' => '',
                        't_city'      => '',
                        't_subcity'   => '',
                    ],
                    [
                        'continent'   => 'a',
                        'city'        => '',
                        't_continent' => '',
                        't_city'      => '',
                        't_subcity'   => '',
                    ],
                    [
                        'continent'   => 'd',
                        'city'        => '',
                        't_continent' => '',
                        't_city'      => '',
                        't_subcity'   => '',
                    ],
                    [
                        'continent'   => 'e',
                        'city'        => '',
                        't_continent' => '',
                        't_city'      => '',
                        't_subcity'   => '',
                    ],
                ],
                'sorted'   => [
                    [
                        'continent'   => 'd',
                        'city'        => '',
                        't_continent' => '',
                        't_city'      => '',
                        't_subcity'   => '',
                    ],
                    [
                        'continent'   => 'c',
                        'city'        => '',
                        't_continent' => '',
                        't_city'      => '',
                        't_subcity'   => '',
                    ],
                    [
                        'continent'   => 'a',
                        'city'        => '',
                        't_continent' => '',
                        't_city'      => '',
                        't_subcity'   => '',
                    ],
                    [
                        'continent'   => 'd',
                        'city'        => '',
                        't_continent' => '',
                        't_city'      => '',
                        't_subcity'   => '',
                    ],
                    [
                        'continent'   => 'e',
                        'city'        => '',
                        't_continent' => '',
                        't_city'      => '',
                        't_subcity'   => '',
                    ],
                ],
            ],

            'not Etc just t_continent'          => [
                'unsorted' => [
                    [
                        'continent'   => '',
                        'city'        => '',
                        't_continent' => 'a',
                        't_city'      => '',
                        't_subcity'   => '',
                    ],
                    [
                        'continent'   => '',
                        'city'        => '',
                        't_continent' => 'd',
                        't_city'      => '',
                        't_subcity'   => '',
                    ],
                    [
                        'continent'   => '',
                        'city'        => '',
                        't_continent' => 'b',
                        't_city'      => '',
                        't_subcity'   => '',
                    ],
                    [
                        'continent'   => '',
                        'city'        => '',
                        't_continent' => 'e',
                        't_city'      => '',
                        't_subcity'   => '',
                    ],
                    [
                        'continent'   => '',
                        'city'        => '',
                        't_continent' => 'c',
                        't_city'      => '',
                        't_subcity'   => '',
                    ],
                ],
                'sorted'   => [
                    [
                        'continent'   => '',
                        'city'        => '',
                        't_continent' => 'a',
                        't_city'      => '',
                        't_subcity'   => '',
                    ],
                    [
                        'continent'   => '',
                        'city'        => '',
                        't_continent' => 'b',
                        't_city'      => '',
                        't_subcity'   => '',
                    ],
                    [
                        'continent'   => '',
                        'city'        => '',
                        't_continent' => 'c',
                        't_city'      => '',
                        't_subcity'   => '',
                    ],
                    [
                        'continent'   => '',
                        'city'        => '',
                        't_continent' => 'd',
                        't_city'      => '',
                        't_subcity'   => '',
                    ],
                    [
                        'continent'   => '',
                        'city'        => '',
                        't_continent' => 'e',
                        't_city'      => '',
                        't_subcity'   => '',
                    ],
                ],
            ],

            'not Etc just t_city'               => [
                'unsorted' => [
                    [
                        'continent'   => '',
                        'city'        => '',
                        't_continent' => 'a',
                        't_city'      => 'd',
                        't_subcity'   => '',
                    ],
                    [
                        'continent'   => '',
                        'city'        => '',
                        't_continent' => 'a',
                        't_city'      => 'e',
                        't_subcity'   => '',
                    ],
                    [
                        'continent'   => '',
                        'city'        => '',
                        't_continent' => 'a',
                        't_city'      => 'c',
                        't_subcity'   => '',
                    ],
                    [
                        'continent'   => '',
                        'city'        => '',
                        't_continent' => 'a',
                        't_city'      => 'a',
                        't_subcity'   => '',
                    ],
                    [
                        'continent'   => '',
                        'city'        => '',
                        't_continent' => 'a',
                        't_city'      => 'b',
                        't_subcity'   => '',
                    ],
                ],
                'sorted'   => [
                    [
                        'continent'   => '',
                        'city'        => '',
                        't_continent' => 'a',
                        't_city'      => 'a',
                        't_subcity'   => '',
                    ],
                    [
                        'continent'   => '',
                        'city'        => '',
                        't_continent' => 'a',
                        't_city'      => 'b',
                        't_subcity'   => '',
                    ],
                    [
                        'continent'   => '',
                        'city'        => '',
                        't_continent' => 'a',
                        't_city'      => 'c',
                        't_subcity'   => '',
                    ],
                    [
                        'continent'   => '',
                        'city'        => '',
                        't_continent' => 'a',
                        't_city'      => 'd',
                        't_subcity'   => '',
                    ],
                    [
                        'continent'   => '',
                        'city'        => '',
                        't_continent' => 'a',
                        't_city'      => 'e',
                        't_subcity'   => '',
                    ],
                ],
            ],

            'not Etc just t_subcity'            => [
                'unsorted' => [
                    [
                        'continent'   => '',
                        'city'        => '',
                        't_continent' => 'a',
                        't_city'      => 'a',
                        't_subcity'   => 'b',
                    ],
                    [
                        'continent'   => '',
                        'city'        => '',
                        't_continent' => 'a',
                        't_city'      => 'a',
                        't_subcity'   => 'e',
                    ],
                    [
                        'continent'   => '',
                        'city'        => '',
                        't_continent' => 'a',
                        't_city'      => 'a',
                        't_subcity'   => 'a',
                    ],
                    [
                        'continent'   => '',
                        'city'        => '',
                        't_continent' => 'a',
                        't_city'      => 'a',
                        't_subcity'   => 'c',
                    ],
                    [
                        'continent'   => '',
                        'city'        => '',
                        't_continent' => 'a',
                        't_city'      => 'a',
                        't_subcity'   => 'd',
                    ],
                ],
                'sorted'   => [
                    [
                        'continent'   => '',
                        'city'        => '',
                        't_continent' => 'a',
                        't_city'      => 'a',
                        't_subcity'   => 'a',
                    ],
                    [
                        'continent'   => '',
                        'city'        => '',
                        't_continent' => 'a',
                        't_city'      => 'a',
                        't_subcity'   => 'b',
                    ],
                    [
                        'continent'   => '',
                        'city'        => '',
                        't_continent' => 'a',
                        't_city'      => 'a',
                        't_subcity'   => 'c',
                    ],
                    [
                        'continent'   => '',
                        'city'        => '',
                        't_continent' => 'a',
                        't_city'      => 'a',
                        't_subcity'   => 'd',
                    ],
                    [
                        'continent'   => '',
                        'city'        => '',
                        't_continent' => 'a',
                        't_city'      => 'a',
                        't_subcity'   => 'e',
                    ],
                ],
            ],

            'just continent with Etc which pulls 1 to bottom' => [
                'unsorted' => [
                    [
                        'continent'   => '',
                        'city'        => '',
                        't_continent' => 'b',
                        't_city'      => '',
                        't_subcity'   => '',
                    ],
                    [
                        'continent'   => '',
                        'city'        => '',
                        't_continent' => 'c',
                        't_city'      => '',
                        't_subcity'   => '',
                    ],
                    [
                        'continent'   => 'Etc',
                        'city'        => '',
                        't_continent' => '1',
                        't_city'      => '',
                        't_subcity'   => '',
                    ],
                    [
                        'continent'   => '',
                        'city'        => '',
                        't_continent' => 'd',
                        't_city'      => '',
                        't_subcity'   => '',
                    ],
                    [
                        'continent'   => '',
                        'city'        => '',
                        't_continent' => 'a',
                        't_city'      => '',
                        't_subcity'   => '',
                    ],
                ],
                'sorted'   => [
                    [
                        'continent'   => '',
                        'city'        => '',
                        't_continent' => 'a',
                        't_city'      => '',
                        't_subcity'   => '',
                    ],
                    [
                        'continent'   => '',
                        'city'        => '',
                        't_continent' => 'b',
                        't_city'      => '',
                        't_subcity'   => '',
                    ],
                    [
                        'continent'   => '',
                        'city'        => '',
                        't_continent' => 'c',
                        't_city'      => '',
                        't_subcity'   => '',
                    ],
                    [
                        'continent'   => '',
                        'city'        => '',
                        't_continent' => 'd',
                        't_city'      => '',
                        't_subcity'   => '',
                    ],
                    [
                        'continent'   => 'Etc',
                        'city'        => '',
                        't_continent' => '1',
                        't_city'      => '',
                        't_subcity'   => '',
                    ],
                ],
            ],
        ];
    }
}
