<?php

declare(strict_types=1);

/**
 * Tests for wp_check_filetype().
 *
 * @group functions
 * @group upload
 *
 * @covers ::wp_check_filetype
 */
class Tests_Functions_WpCheckFiletype extends WP_UnitTestCase
{
    /**
     * Tests that wp_check_filetype() returns the correct extension and MIME type.
     *
     * @ticket 57151
     *
     * @dataProvider data_wp_check_filetype
     *
     * @param string     $filename   The filename to check.
     * @param array|null $mimes      An array of MIME types, or null.
     * @param array      $expected   An array containing the expected extension and MIME type.
     */
    public function test_wp_check_filetype($filename, $mimes, $expected)
    {
        $this->assertSame($expected, wp_check_filetype($filename, $mimes));
    }

    /**
     * Data provider.
     *
     * @return array[]
     */
    public function data_wp_check_filetype()
    {
        return [
            '.jpg filename and default allowed'       => [
                'filename' => 'canola.jpg',
                'mimes'    => null,
                'expected' => [
                    'ext'  => 'jpg',
                    'type' => 'image/jpeg',
                ],
            ],
            '.jpg filename and jpg|jpeg|jpe'          => [
                'filename' => 'canola.jpg',
                'mimes'    => [
                    'jpg|jpeg|jpe' => 'image/jpeg',
                    'gif'          => 'image/gif',
                ],
                'expected' => [
                    'ext'  => 'jpg',
                    'type' => 'image/jpeg',
                ],
            ],
            '.jpeg filename and jpg|jpeg|jpe'         => [
                'filename' => 'canola.jpeg',
                'mimes'    => [
                    'jpg|jpeg|jpe' => 'image/jpeg',
                    'gif'          => 'image/gif',
                ],
                'expected' => [
                    'ext'  => 'jpeg',
                    'type' => 'image/jpeg',
                ],
            ],
            '.jpe filename and jpg|jpeg|jpe'          => [
                'filename' => 'canola.jpe',
                'mimes'    => [
                    'jpg|jpeg|jpe' => 'image/jpeg',
                    'gif'          => 'image/gif',
                ],
                'expected' => [
                    'ext'  => 'jpe',
                    'type' => 'image/jpeg',
                ],
            ],
            'uppercase filename and jpg|jpeg|jpe'     => [
                'filename' => 'canola.JPG',
                'mimes'    => [
                    'jpg|jpeg|jpe' => 'image/jpeg',
                    'gif'          => 'image/gif',
                ],
                'expected' => [
                    'ext'  => 'JPG',
                    'type' => 'image/jpeg',
                ],
            ],
            '.XXX filename and no matching MIME type' => [
                'filename' => 'canola.XXX',
                'mimes'    => [
                    'jpg|jpeg|jpe' => 'image/jpeg',
                    'gif'          => 'image/gif',
                ],
                'expected' => [
                    'ext'  => false,
                    'type' => false,
                ],
            ],
            '.jpg filename but only gif allowed'      => [
                'filename' => 'canola.jpg',
                'mimes'    => [
                    'gif' => 'image/gif',
                ],
                'expected' => [
                    'ext'  => false,
                    'type' => false,
                ],
            ],
        ];
    }
}
