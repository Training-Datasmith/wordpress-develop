<?php

declare(strict_types=1);

/**
 * @group admin
 *
 * @covers WP_Plugin_Install_List_Table
 */
class Tests_Admin_wpPluginInstallListTable extends WP_UnitTestCase
{
    /**
     * @var WP_Plugin_Install_List_Table
     */
    public $table = false;

    public function set_up()
    {
        parent::set_up();
        $this->table = _get_list_table('WP_Plugin_Install_List_Table', [ 'screen' => 'plugin-install' ]);
    }

    /**
     * @ticket 42066
     *
     * @covers WP_Plugin_Install_List_Table::get_views
     */
    public function test_get_views_should_return_no_views_by_default()
    {
        $this->assertSame([], $this->table->get_views());
    }
}
