<?php

declare(strict_types=1);

/**
 * Test the IteratorAggregate implementation of WP_Hook
 *
 * @group hooks
 * @covers WP_Hook::build_preinitialized_hooks
 */
class Tests_Hooks_PreinitHooks extends WP_UnitTestCase
{
    public function test_array_to_hooks()
    {
        $hook_name1 = __FUNCTION__ . '_1';
        $priority1  = 1;
        $hook_name2 = __FUNCTION__ . '_2';
        $priority2  = 2;
        $filters    = [
            $hook_name1 => [
                $priority1 => [
                    'test1' => [
                        'function'      => '__return_false',
                        'accepted_args' => 2,
                    ],
                ],
            ],
            $hook_name2 => [
                $priority2 => [
                    'test1' => [
                        'function'      => '__return_null',
                        'accepted_args' => 1,
                    ],
                ],
            ],
        ];

        $hooks = WP_Hook::build_preinitialized_hooks($filters);

        $this->assertSame($priority1, $hooks[ $hook_name1 ]->has_filter($hook_name1, '__return_false'));
        $this->assertSame($priority2, $hooks[ $hook_name2 ]->has_filter($hook_name2, '__return_null'));
    }
}
