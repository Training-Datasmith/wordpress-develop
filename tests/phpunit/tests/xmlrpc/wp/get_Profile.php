<?php

declare(strict_types=1);

/**
 * @group xmlrpc
 * @group user
 */
class Tests_XMLRPC_wp_getProfile extends WP_XMLRPC_UnitTestCase
{
    public function test_invalid_username_password()
    {
        $result = $this->myxmlrpcserver->wp_getProfile([ 1, 'username', 'password' ]);
        $this->assertIXRError($result);
        $this->assertSame(403, $result->code);
    }

    public function test_subscriber()
    {
        $subscriber_id = $this->make_user_by_role('subscriber');

        $result = $this->myxmlrpcserver->wp_getProfile([ 1, 'subscriber', 'subscriber' ]);
        $this->assertNotIXRError($result);
        $this->assertEquals($subscriber_id, $result['user_id']);
        $this->assertContains('subscriber', $result['roles']);
    }

    public function test_administrator()
    {
        $administrator_id = $this->make_user_by_role('administrator');

        $result = $this->myxmlrpcserver->wp_getProfile([ 1, 'administrator', 'administrator' ]);
        $this->assertNotIXRError($result);
        $this->assertEquals($administrator_id, $result['user_id']);
        $this->assertContains('administrator', $result['roles']);
    }

    public function test_arbitrary_fields()
    {
        $editor_id = $this->make_user_by_role('editor');

        $fields = [ 'email', 'bio', 'user_contacts' ];

        $result = $this->myxmlrpcserver->wp_getProfile([ 1, 'editor', 'editor', $fields ]);
        $this->assertNotIXRError($result);
        $this->assertEquals($editor_id, $result['user_id']);

        $expected_fields = [ 'user_id', 'email', 'bio' ];
        $keys            = array_keys($result);
        sort($expected_fields);
        sort($keys);
        $this->assertSameSets($expected_fields, $keys);
    }
}
