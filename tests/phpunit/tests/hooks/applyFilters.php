<?php

declare(strict_types=1);

/**
 * Test the apply_filters method of WP_Hook
 *
 * @group hooks
 * @covers WP_Hook::apply_filters
 */
class Tests_Hooks_ApplyFilters extends WP_UnitTestCase
{
    public function test_apply_filters_with_callback()
    {
        $a             = new MockAction();
        $callback      = [ $a, 'filter' ];
        $hook          = new WP_Hook();
        $hook_name     = __FUNCTION__;
        $priority      = 1;
        $accepted_args = 2;
        $arg           = __FUNCTION__ . '_arg';

        $hook->add_filter($hook_name, $callback, $priority, $accepted_args);

        $returned = $hook->apply_filters($arg, [ $arg ]);

        $this->assertSame($returned, $arg);
        $this->assertSame(1, $a->get_call_count());
    }

    public function test_apply_filters_with_multiple_calls()
    {
        $a             = new MockAction();
        $callback      = [ $a, 'filter' ];
        $hook          = new WP_Hook();
        $hook_name     = __FUNCTION__;
        $priority      = 1;
        $accepted_args = 2;
        $arg           = __FUNCTION__ . '_arg';

        $hook->add_filter($hook_name, $callback, $priority, $accepted_args);

        $returned_one = $hook->apply_filters($arg, [ $arg ]);
        $returned_two = $hook->apply_filters($returned_one, [ $returned_one ]);

        $this->assertSame($returned_two, $arg);
        $this->assertSame(2, $a->get_call_count());
    }

    /**
     * @ticket 60193
     *
     * @dataProvider data_priority_callback_order_with_integers
     * @dataProvider data_priority_callback_order_with_unhappy_path_nonintegers
     *
     * @param array $priorities {
     *     Indexed array of the priorities for the MockAction callbacks.
     *
     *     @type mixed $0 Priority for 'action' callback.
     *     @type mixed $1 Priority for 'action2' callback.
     * }
     * @param array  $expected_call_order  An array of callback names in expected call order.
     * @param string $expected_deprecation Optional. Deprecation message. Default ''.
     */
    public function test_priority_callback_order($priorities, $expected_call_order, $expected_deprecation = '')
    {
        $mock      = new MockAction();
        $hook      = new WP_Hook();
        $hook_name = __FUNCTION__;

        if ($expected_deprecation && PHP_VERSION_ID >= 80100) {
            $this->expectDeprecation();
            $this->expectDeprecationMessage($expected_deprecation);
        }

        $hook->add_filter($hook_name, [ $mock, 'filter' ], $priorities[0], 1);
        $hook->add_filter($hook_name, [ $mock, 'filter2' ], $priorities[1], 1);
        $hook->apply_filters(__FUNCTION__ . '_val', [ '' ]);

        $this->assertSame(2, $mock->get_call_count(), 'The number of call counts does not match');

        $actual_call_order = wp_list_pluck($mock->get_events(), 'filter');
        $this->assertSame($expected_call_order, $actual_call_order, 'The filter callback order does not match the expected order');
    }

    /**
     * Happy path data provider.
     *
     * @return array[]
     */
    public function data_priority_callback_order_with_integers()
    {
        return [
            'int DESC' => [
                'priorities'          => [ 10, 9 ],
                'expected_call_order' => [ 'filter2', 'filter' ],
            ],
            'int ASC'  => [
                'priorities'          => [ 9, 10 ],
                'expected_call_order' => [ 'filter', 'filter2' ],
            ],
        ];
    }

    /**
     * Unhappy path data provider.
     *
     * @return array[]
     */
    public function data_priority_callback_order_with_unhappy_path_nonintegers()
    {
        return [
            // Numbers as strings and floats.
            'int as string DESC'               => [
                'priorities'          => [ '10', '9' ],
                'expected_call_order' => [ 'filter2', 'filter' ],
            ],
            'int as string ASC'                => [
                'priorities'          => [ '9', '10' ],
                'expected_call_order' => [ 'filter', 'filter2' ],
            ],
            'float DESC'                       => [
                'priorities'           => [ 10.0, 9.5 ],
                'expected_call_order'  => [ 'filter2', 'filter' ],
                'expected_deprecation' => 'Implicit conversion from float 9.5 to int loses precision',
            ],
            'float ASC'                        => [
                'priorities'           => [ 9.5, 10.0 ],
                'expected_call_order'  => [ 'filter', 'filter2' ],
                'expected_deprecation' => 'Implicit conversion from float 9.5 to int loses precision',
            ],
            'float as string DESC'             => [
                'priorities'          => [ '10.0', '9.5' ],
                'expected_call_order' => [ 'filter2', 'filter' ],
            ],
            'float as string ASC'              => [
                'priorities'          => [ '9.5', '10.0' ],
                'expected_call_order' => [ 'filter', 'filter2' ],
            ],

            // Non-numeric.
            'null'                             => [
                'priorities'          => [ null, null ],
                'expected_call_order' => [ 'filter', 'filter2' ],
            ],
            'bool DESC'                        => [
                'priorities'          => [ true, false ],
                'expected_call_order' => [ 'filter2', 'filter' ],
            ],
            'bool ASC'                         => [
                'priorities'          => [ false, true ],
                'expected_call_order' => [ 'filter', 'filter2' ],
            ],
            'non-numerical string DESC'        => [
                'priorities'          => [ 'test1', 'test2' ],
                'expected_call_order' => [ 'filter', 'filter2' ],
            ],
            'non-numerical string ASC'         => [
                'priorities'          => [ 'test1', 'test2' ],
                'expected_call_order' => [ 'filter', 'filter2' ],
            ],
            'int, non-numerical string DESC'   => [
                'priorities'          => [ 10, 'test' ],
                'expected_call_order' => [ 'filter2', 'filter' ],
            ],
            'int, non-numerical string ASC'    => [
                'priorities'          => [ 'test', 10 ],
                'expected_call_order' => [ 'filter', 'filter2' ],
            ],
            'float, non-numerical string DESC' => [
                'priorities'          => [ 10.0, 'test' ],
                'expected_call_order' => [ 'filter2', 'filter' ],
            ],
            'float, non-numerical string ASC'  => [
                'priorities'          => [ 'test', 10.0 ],
                'expected_call_order' => [ 'filter', 'filter2' ],
            ],
        ];
    }
}
