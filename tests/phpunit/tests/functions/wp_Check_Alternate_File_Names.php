<?php

declare(strict_types=1);

/**
 * @group functions
 *
 * @covers ::_wp_check_alternate_file_names
 */
class Tests_Functions_WpCheckAlternateFileNames extends WP_UnitTestCase
{
    /**
     * @dataProvider data_wp_check_alternate_file_names
     *
     * @ticket 55199
     *
     * @param array  $filenames Array of filenames to check.
     * @param string $dir       The directory to check.
     * @param array  $files     An array of existing files in the directory.
     * @param bool   $expected  Expected result.
     */
    public function test_wp_check_alternate_file_names($filenames, $dir, $files, $expected)
    {
        $this->assertSame($expected, _wp_check_alternate_file_names($filenames, $dir, $files));
    }

    /**
     * Data provider.
     *
     * @return array[]
     */
    public function data_wp_check_alternate_file_names()
    {
        return [
            'an existing file'                         => [
                'filenames' => [ 'canola.jpg' ],
                'dir'       => DIR_TESTDATA . '/images/',
                'files'     => [],
                'expected'  => true,
            ],
            'multiple existing files'                  => [
                'filenames' => [ 'canola.jpg', 'codeispoetry.png' ],
                'dir'       => DIR_TESTDATA . '/images/',
                'files'     => [],
                'expected'  => true,
            ],
            'a non-existent file and an existing file' => [
                'filenames' => [ 'an-image.jpg', 'codeispoetry.png' ],
                'dir'       => DIR_TESTDATA . '/images/',
                'files'     => [],
                'expected'  => true,
            ],
            'a non-existent file and an existing image sub-size file' => [
                'filenames' => [ 'one-blue-pixel.png' ],
                'dir'       => DIR_TESTDATA . '/images/',
                'files'     => [ 'one-blue-pixel-100x100.png' ],
                'expected'  => true,
            ],
            'a non-existent file and no other existing files' => [
                'filenames' => [ 'filename.php' ],
                'dir'       => DIR_TESTDATA . '/images/',
                'files'     => [],
                'expected'  => false,
            ],
            'multiple non-existent files and no existing image sub-size files' => [
                'filenames' => [ 'canola.jpg', 'codeispoetry.png' ],
                'dir'       => DIR_TESTDATA . '/functions/',
                'files'     => [ 'an-image-100x100.jpg', 'another-image-100x100.png' ],
                'expected'  => false,
            ],
        ];
    }
}
