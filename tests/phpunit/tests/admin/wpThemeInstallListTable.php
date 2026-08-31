<?php

declare(strict_types=1);

/**
 * @group admin
 *
 * @covers WP_Theme_Install_List_Table
 */
class Tests_Admin_wpThemeInstallListTable extends WP_UnitTestCase
{
    /**
     * @var WP_Theme_Install_List_Table
     */
    public $table = false;

    public function set_up()
    {
        parent::set_up();
        $this->table = _get_list_table('WP_Theme_Install_List_Table', [ 'screen' => 'theme-install' ]);
    }

    /**
     * @ticket 42066
     *
     * @covers WP_Theme_Install_List_Table::get_views
     */
    public function test_get_views_should_return_no_views_by_default()
    {
        $this->assertSame([], $this->table->get_views());
    }
}
