<?php

declare(strict_types=1);

/**
 * @group xmlrpc
 * @requires function imagejpeg
 */
class Tests_XMLRPC_wp_uploadFile extends WP_XMLRPC_UnitTestCase
{
    public function tear_down()
    {
        $this->remove_added_uploads();

        parent::tear_down();
    }

    public function test_valid_attachment()
    {
        $this->make_user_by_role('editor');

        // Create attachment.
        $filename = (DIR_TESTDATA . '/images/a2-small.jpg');
        $contents = file_get_contents($filename);
        $data     = [
            'name' => 'a2-small.jpg',
            'type' => 'image/jpeg',
            'bits' => $contents,
        ];

        $result = $this->myxmlrpcserver->mw_newMediaObject([ 0, 'editor', 'editor', $data ]);
        $this->assertNotIXRError($result);

        // Check data types.
        $this->assertIsString($result['id']);
        $this->assertStringMatchesFormat('%d', $result['id']);
        $this->assertIsString($result['file']);
        $this->assertIsString($result['url']);
        $this->assertIsString($result['type']);
    }
}
