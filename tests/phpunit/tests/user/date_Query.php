<?php

declare(strict_types=1);

/**
 * @group user
 * @group datequery
 */
class Tests_User_DateQuery extends WP_UnitTestCase
{
    /**
     * @ticket 27283
     */
    public function test_user_registered()
    {
        $u1 = self::factory()->user->create(
            [
                'user_registered' => '2012-02-14 05:05:05',
            ]
        );
        $u2 = self::factory()->user->create(
            [
                'user_registered' => '2013-02-14 05:05:05',
            ]
        );

        $uq = new WP_User_Query(
            [
                'date_query' => [
                    [
                        'year' => 2012,
                    ],
                ],
            ]
        );

        $this->assertSameSets([ $u1 ], wp_list_pluck($uq->results, 'ID'));
    }

    /**
     * @ticket 27283
     */
    public function test_user_registered_relation_or()
    {
        $u1 = self::factory()->user->create(
            [
                'user_registered' => '2012-02-14 05:05:05',
            ]
        );
        $u2 = self::factory()->user->create(
            [
                'user_registered' => '2013-02-14 05:05:05',
            ]
        );
        $u3 = self::factory()->user->create(
            [
                'user_registered' => '2014-02-14 05:05:05',
            ]
        );

        $uq = new WP_User_Query(
            [
                'date_query' => [
                    'relation' => 'OR',
                    [
                        'year' => 2013,
                    ],
                    [
                        'before' => '2012-03-01 00:00:00',
                    ],
                ],
            ]
        );

        $this->assertSameSets([ $u1, $u2 ], wp_list_pluck($uq->results, 'ID'));
    }
}
