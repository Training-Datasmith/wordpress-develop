<?php

declare(strict_types=1);

/**
 * @group xmlrpc
 */
class Tests_XMLRPC_wp_getTaxonomies extends WP_XMLRPC_UnitTestCase
{
    public function test_invalid_username_password()
    {
        $result = $this->myxmlrpcserver->wp_getTaxonomies([ 1, 'username', 'password' ]);
        $this->assertIXRError($result);
        $this->assertSame(403, $result->code);
    }

    public function test_taxonomy_validated()
    {
        $this->make_user_by_role('editor');

        $result = $this->myxmlrpcserver->wp_getTaxonomies([ 1, 'editor', 'editor' ]);
        $this->assertNotIXRError($result);
    }
}
