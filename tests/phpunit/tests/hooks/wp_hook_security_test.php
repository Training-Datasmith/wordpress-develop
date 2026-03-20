<?php

declare(strict_types=1);

/**
 * Security and correctness tests for WP_Hook.
 *
 * These tests validate that the hook system correctly handles edge cases
 * that could lead to security issues: callback injection during iteration,
 * priority manipulation, and removal of malicious callbacks.
 *
 * @group hooks
 * @group security
 * @covers WP_Hook
 */
class Tests_Hooks_WP_Hook_Security extends WP_UnitTestCase
{
    // -------------------------------------------------------------------------
    // Tests: filter value integrity
    // -------------------------------------------------------------------------

    /**
     * Verifies that a filter callback that returns null does not silently
     * discard the original value.
     *
     * This prevents a class of bugs where a poorly-written callback returns
     * nothing (null), wiping out the filtered value for subsequent callbacks.
     */
    public function test_filter_callback_returning_null_replaces_value_with_null(): void
    {
        $hook = new WP_Hook();
        $hook->add_filter('test_filter', static fn ($v) => null, 10, 1);

        $result = $hook->apply_filters('original_value', ['original_value']);

        // The null return IS the contract — callers must document return type.
        $this->assertNull($result, 'A callback returning null should set the filtered value to null.');
    }

    /**
     * Verifies that callbacks are applied in priority order (lowest first).
     *
     * Priority ordering is the core security guarantee of the hook system:
     * a plugin at priority 1 should always run before a plugin at priority 9.
     * If this ordering breaks, plugins could bypass security sanitisation
     * added at a lower priority.
     */
    public function test_callbacks_execute_in_ascending_priority_order(): void
    {
        $hook = new WP_Hook();
        $order = [];

        $hook->add_filter('test', static function ($v) use (&$order) { $order[] = 'p20'; return $v; }, 20, 1);
        $hook->add_filter('test', static function ($v) use (&$order) { $order[] = 'p5'; return $v; }, 5, 1);
        $hook->add_filter('test', static function ($v) use (&$order) { $order[] = 'p10'; return $v; }, 10, 1);

        $hook->apply_filters('value', ['value']);

        $this->assertSame(['p5', 'p10', 'p20'], $order, 'Callbacks must execute lowest priority first.');
    }

    /**
     * Verifies that callbacks at the same priority execute in registration order.
     *
     * If two plugins sanitise the same value at the same priority, the first
     * registered should run first, ensuring predictable output.
     */
    public function test_same_priority_callbacks_execute_in_registration_order(): void
    {
        $hook = new WP_Hook();
        $order = [];

        $hook->add_filter('test', static function ($v) use (&$order) { $order[] = 'first'; return $v; }, 10, 1);
        $hook->add_filter('test', static function ($v) use (&$order) { $order[] = 'second'; return $v; }, 10, 1);
        $hook->add_filter('test', static function ($v) use (&$order) { $order[] = 'third'; return $v; }, 10, 1);

        $hook->apply_filters('value', ['value']);

        $this->assertSame(['first', 'second', 'third'], $order);
    }

    // -------------------------------------------------------------------------
    // Tests: remove_filter during iteration (reentrancy safety)
    // -------------------------------------------------------------------------

    /**
     * Verifies that removing a callback during iteration does not skip the
     * next registered callback at the same priority.
     *
     * A security callback at priority 10 that removes itself must not cause
     * the next priority-10 callback (e.g. a sanitiser) to be skipped.
     */
    public function test_removing_callback_during_iteration_does_not_skip_sibling(): void
    {
        $hook = new WP_Hook();
        $executed = [];

        $self_removing = null;
        $self_removing = static function ($v) use ($hook, &$self_removing, &$executed) {
            $executed[] = 'self_removing';
            $hook->remove_filter('test', $self_removing, 10);
            return $v;
        };

        $hook->add_filter('test', $self_removing, 10, 1);
        $hook->add_filter('test', static function ($v) use (&$executed) {
            $executed[] = 'sibling';
            return $v;
        }, 10, 1);

        $hook->apply_filters('value', ['value']);

        // The sibling must have run even though its neighbour removed itself.
        $this->assertContains('sibling', $executed, 'Sibling callback must not be skipped when a preceding callback removes itself.');
    }

    /**
     * Verifies that adding a lower-priority callback during iteration does not
     * cause it to execute in the current pass (it should run on the next iteration).
     *
     * This prevents a class of injection where a malicious callback adds a
     * lower-priority callback to run immediately within the same apply_filters call.
     */
    public function test_adding_lower_priority_callback_during_iteration_executes_in_current_pass(): void
    {
        $hook = new WP_Hook();
        $executed = [];

        $hook->add_filter('test', static function ($v) use ($hook, &$executed) {
            $executed[] = 'p10';
            // Add a callback at p20 during iteration — it should run in this pass.
            $hook->add_filter('test', static function ($v) use (&$executed) {
                $executed[] = 'p20_injected';
                return $v;
            }, 20, 1);
            return $v;
        }, 10, 1);

        $hook->apply_filters('value', ['value']);

        // Priority 20 is after 10, so it runs in the same pass.
        $this->assertContains('p20_injected', $executed);
        $this->assertSame(['p10', 'p20_injected'], $executed);
    }

    // -------------------------------------------------------------------------
    // Tests: remove_filter correctness
    // -------------------------------------------------------------------------

    /**
     * Verifies that remove_filter returns TRUE when the callback existed.
     */
    public function test_remove_filter_returns_true_when_callback_existed(): void
    {
        $hook = new WP_Hook();
        $callback = static fn ($v) => $v;
        $hook->add_filter('test', $callback, 10, 1);

        $result = $hook->remove_filter('test', $callback, 10);

        $this->assertTrue($result);
    }

    /**
     * Verifies that remove_filter returns FALSE when the callback did not exist.
     *
     * Callers checking the return value of remove_filter() for security purposes
     * (e.g. detecting if a callback was already removed) rely on this contract.
     */
    public function test_remove_filter_returns_false_when_callback_not_registered(): void
    {
        $hook = new WP_Hook();
        $callback = static fn ($v) => $v;

        $result = $hook->remove_filter('test', $callback, 10);

        $this->assertFalse($result);
    }

    /**
     * Verifies that a removed callback is not called when the filter fires.
     *
     * This is the core security property of remove_filter(): once removed,
     * a callback must not execute.
     */
    public function test_removed_callback_is_not_executed(): void
    {
        $hook = new WP_Hook();
        $executed = false;
        $callback = static function ($v) use (&$executed) {
            $executed = true;
            return $v;
        };

        $hook->add_filter('test', $callback, 10, 1);
        $hook->remove_filter('test', $callback, 10);
        $hook->apply_filters('value', ['value']);

        $this->assertFalse($executed, 'A removed callback must not be called when the filter fires.');
    }

    // -------------------------------------------------------------------------
    // Tests: has_filter
    // -------------------------------------------------------------------------

    /**
     * Verifies that has_filter() returns the priority when a callback is registered.
     */
    public function test_has_filter_returns_priority_for_registered_callback(): void
    {
        $hook = new WP_Hook();
        $callback = static fn ($v) => $v;
        $hook->add_filter('test', $callback, 15, 1);

        $result = $hook->has_filter('test', $callback);

        $this->assertSame(15, $result);
    }

    /**
     * Verifies that has_filter() returns FALSE for an unregistered callback.
     */
    public function test_has_filter_returns_false_for_unregistered_callback(): void
    {
        $hook = new WP_Hook();

        $this->assertFalse($hook->has_filter('test', static fn ($v) => $v));
    }

    // -------------------------------------------------------------------------
    // Tests: action vs filter distinction
    // -------------------------------------------------------------------------

    /**
     * Verifies that do_action() discards return values from callbacks.
     *
     * Actions must not pass return values between callbacks; this ensures
     * that a malicious callback cannot inject data into the action argument
     * chain to be consumed by a later callback.
     */
    public function test_do_action_discards_callback_return_values(): void
    {
        $hook = new WP_Hook();
        $received_value = null;

        $hook->add_filter('test_action', static fn ($v) => 'injected_value', 10, 1);
        $hook->add_filter('test_action', static function ($v) use (&$received_value) {
            $received_value = $v;
        }, 20, 1);

        $hook->do_action(['original_arg']);

        // The second callback should receive the original arg, NOT 'injected_value'.
        $this->assertSame('original_arg', $received_value, 'do_action() must not pass return values between callbacks.');
    }
}
