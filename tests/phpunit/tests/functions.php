<?php

declare(strict_types=1);

/**
 * @group functions
 */
class Tests_Functions extends WP_UnitTestCase
{
    public function test_wp_parse_args_object()
    {
        $x        = new MockClass();
        $x->_baba = 5;
        $x->yZ    = 'baba'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
        $x->a     = [ 5, 111, 'x' ];
        $this->assertSame(
            [
                '_baba' => 5,
                'yZ'    => 'baba',
                'a'     => [ 5, 111, 'x' ],
            ],
            wp_parse_args($x)
        );
        $y = new MockClass();
        $this->assertSame([], wp_parse_args($y));
    }

    public function test_wp_parse_args_array()
    {
        // Arrays.
        $a = [];
        $this->assertSame([], wp_parse_args($a));
        $b = [
            '_baba' => 5,
            'yZ'    => 'baba',
            'a'     => [ 5, 111, 'x' ],
        ];
        $this->assertSame(
            [
                '_baba' => 5,
                'yZ'    => 'baba',
                'a'     => [ 5, 111, 'x' ],
            ],
            wp_parse_args($b)
        );
    }

    public function test_wp_parse_args_defaults()
    {
        $x        = new MockClass();
        $x->_baba = 5;
        $x->yZ    = 'baba'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
        $x->a     = [ 5, 111, 'x' ];
        $d        = [ 'pu' => 'bu' ];
        $this->assertSame(
            [
                'pu'    => 'bu',
                '_baba' => 5,
                'yZ'    => 'baba',
                'a'     => [ 5, 111, 'x' ],
            ],
            wp_parse_args($x, $d)
        );
        $e = [ '_baba' => 6 ];
        $this->assertSame(
            [
                '_baba' => 5,
                'yZ'    => 'baba',
                'a'     => [ 5, 111, 'x' ],
            ],
            wp_parse_args($x, $e)
        );
    }

    public function test_wp_parse_args_other()
    {
        $b = true;
        wp_parse_str($b, $s);
        $this->assertSame($s, wp_parse_args($b));
        $q = 'x=5&_baba=dudu&';
        wp_parse_str($q, $ss);
        $this->assertSame($ss, wp_parse_args($q));
    }

    /**
     * @ticket 30753
     */
    public function test_wp_parse_args_boolean_strings()
    {
        $args = wp_parse_args('foo=false&bar=true');
        $this->assertIsString($args['foo']);
        $this->assertIsString($args['bar']);
    }

    /**
     * @ticket 35972
     */
    public function test_bool_from_yn()
    {
        $this->assertTrue(bool_from_yn('Y'));
        $this->assertTrue(bool_from_yn('y'));
        $this->assertFalse(bool_from_yn('n'));
    }

    public function test_path_is_absolute()
    {
        $absolute_paths = [
            '/',
            '/foo/',
            '/foo',
            '/FOO/bar',
            '/foo/bar/',
            '/foo/../bar/',
            '\\WINDOWS',
            'C:\\',
            'C:\\WINDOWS',
            '\\\\sambashare\\foo',
        ];
        foreach ($absolute_paths as $path) {
            $this->assertTrue(path_is_absolute($path), "path_is_absolute('$path') should return true");
        }
    }

    public function test_path_is_not_absolute()
    {
        $relative_paths = [
            '',
            '.',
            '..',
            '../foo',
            '../',
            '../foo.bar',
            'foo/bar',
            'foo',
            'FOO',
            '..\\WINDOWS',
        ];
        foreach ($relative_paths as $path) {
            $this->assertFalse(path_is_absolute($path), "path_is_absolute('$path') should return false");
        }
    }

    /**
     * Tests path_join().
     *
     * @ticket 55897
     * @dataProvider data_path_join
     */
    public function test_path_join($base, $path, $expected)
    {
        $this->assertSame($expected, path_join($base, $path));
    }

    /**
     * Data provider for test_path_join().
     *
     * @return string[][]
     */
    public function data_path_join()
    {
        return [
            // Absolute paths.
            'absolute path should return path' => [
                'base'     => 'base',
                'path'     => '/path',
                'expected' => '/path',
            ],
            'windows path with slashes'        => [
                'base'     => 'base',
                'path'     => '//path',
                'expected' => '//path',
            ],
            'windows path with backslashes'    => [
                'base'     => 'base',
                'path'     => '\\\\path',
                'expected' => '\\\\path',
            ],
            // Non-absolute paths.
            'join base and path'               => [
                'base'     => 'base',
                'path'     => 'path',
                'expected' => 'base/path',
            ],
            'strip trailing slashes in base'   => [
                'base'     => 'base///',
                'path'     => 'path',
                'expected' => 'base/path',
            ],
            'empty path'                       => [
                'base'     => 'base',
                'path'     => '',
                'expected' => 'base/',
            ],
            'empty base'                       => [
                'base'     => '',
                'path'     => 'path',
                'expected' => '/path',
            ],
            'empty path and base'              => [
                'base'     => '',
                'path'     => '',
                'expected' => '/',
            ],
        ];
    }

    /**
     * @ticket 33265
     * @ticket 35996
     *
     * @dataProvider data_wp_normalize_path
     */
    public function test_wp_normalize_path($path, $expected)
    {
        $this->assertSame($expected, wp_normalize_path($path));
    }

    public function data_wp_normalize_path()
    {
        return [
            // Windows paths.
            [ 'C:\\www\\path\\', 'C:/www/path/' ],
            [ 'C:\\www\\\\path\\', 'C:/www/path/' ],
            [ 'c:/www/path', 'C:/www/path' ],
            [ 'c:\\www\\path\\', 'C:/www/path/' ], // Uppercase drive letter.
            [ 'c:\\\\www\\path\\', 'C:/www/path/' ],
            [ '\\\\Domain\\DFSRoots\\share\\path\\', '//Domain/DFSRoots/share/path/' ],
            [ '\\\\Server\\share\\path', '//Server/share/path' ],
            [ '\\\\Server\\share', '//Server/share' ],

            // Linux paths.
            [ '/www/path/', '/www/path/' ],
            [ '/www/path/////', '/www/path/' ],
            [ '/www/path', '/www/path' ],

            // PHP stream wrappers.
            [ 'php://input', 'php://input' ],
            [ 'http://example.com//path.ext', 'http://example.com/path.ext' ],
            [ 'file://c:\\www\\path\\', 'file://C:/www/path/' ],
        ];
    }

    public function test_wp_unique_filename()
    {

        $testdir = DIR_TESTDATA . '/images/';

        // Confidence check.
        $this->assertSame('abcdefg.png', wp_unique_filename($testdir, 'abcdefg.png'), 'Test non-existing file, file name should be unchanged.');

        // Ensure correct images exist.
        $this->assertFileExists($testdir . 'test-image.png', 'Test image does not exist');
        $this->assertFileDoesNotExist($testdir . 'test-image-1.png');

        // Check number is appended if file already exists.
        $this->assertSame('test-image-1.png', wp_unique_filename($testdir, 'test-image.png'), 'File name not unique, number not appended.');

        // Check file with uppercase extension.
        $this->assertSame('test-image-1.png', wp_unique_filename($testdir, 'test-image.PNG'), 'File name with uppercase extension not unique, number not appended.');

        // Check file name with already added number.
        $this->assertSame('test-image-2-1.gif', wp_unique_filename($testdir, 'test-image-2.gif'), 'File name not unique, number not appended correctly.');

        // Check special chars.
        $this->assertSame('testtest-image.png', wp_unique_filename($testdir, 'testtést-imagé.png'), 'Filename with special chars failed');

        // Check special chars with potential conflicting name.
        $this->assertSame('test-image-1.png', wp_unique_filename($testdir, 'tést-imagé.png'), 'Filename with special chars failed');

        // Check with single quotes in name (somehow).
        $this->assertSame('abcdefgh.png', wp_unique_filename($testdir, "abcdefg'h.png"), 'File with quote failed');

        // Check with double quotes in name (somehow).
        $this->assertSame('abcdefgh.png', wp_unique_filename($testdir, 'abcdefg"h.png'), 'File with quote failed');

        // Test crazy name (useful for regression tests).
        $this->assertSame('12af34567890@.^_qwerty-fghjkl-zx.png', wp_unique_filename($testdir, '12%af34567890#~!@#$..%^&*()|_+qwerty  fgh`jkl zx<>?:"{}[]="\'/?.png'), 'Failed crazy file name');

        // Test slashes in names.
        $this->assertSame('abcdefg.png', wp_unique_filename($testdir, 'abcde\fg.png'), 'Slash not removed');
        $this->assertSame('abcdefg.png', wp_unique_filename($testdir, 'abcde\\fg.png'), 'Double slashed not removed');
        $this->assertSame('abcdefg.png', wp_unique_filename($testdir, 'abcde\\\fg.png'), 'Triple slashed not removed');
    }

    /**
     * @ticket 42437
     */
    public function test_unique_filename_with_dimension_like_filename()
    {
        $testdir = DIR_TESTDATA . '/images/';

        add_filter('upload_dir', [ $this, 'upload_dir_patch_basedir' ]);

        // Test collision with "dimension-like" original filename.
        $this->assertSame('one-blue-pixel-100x100-1.png', wp_unique_filename($testdir, 'one-blue-pixel-100x100.png'));
        // Test collision with existing sub-size filename.
        // Existing files: one-blue-pixel-100x100.png, one-blue-pixel-1-100x100.png.
        $this->assertSame('one-blue-pixel-2.png', wp_unique_filename($testdir, 'one-blue-pixel.png'));
        // Same as above with upper case extension.
        $this->assertSame('one-blue-pixel-2.png', wp_unique_filename($testdir, 'one-blue-pixel.PNG'));

        remove_filter('upload_dir', [ $this, 'upload_dir_patch_basedir' ]);
    }

    // Callback to patch "basedir" when used in `wp_unique_filename()`.
    public function upload_dir_patch_basedir($upload_dir)
    {
        $upload_dir['basedir'] = DIR_TESTDATA . '/images/';
        return $upload_dir;
    }

    /**
     * @ticket 53668
     */
    public function test_wp_unique_filename_with_additional_image_extension()
    {
        $testdir = DIR_TESTDATA . '/images/';

        add_filter('upload_dir', [ $this, 'upload_dir_patch_basedir' ]);

        // Set conversions for uploaded images.
        add_filter('image_editor_output_format', [ $this, 'image_editor_output_format_handler' ]);

        // Ensure the test images exist.
        $this->assertFileExists($testdir . 'test-image-1-100x100.jpg', 'test-image-1-100x100.jpg does not exist');
        $this->assertFileExists($testdir . 'test-image-2.gif', 'test-image-2.gif does not exist');
        $this->assertFileExists($testdir . 'test-image-3.jpg', 'test-image-3.jpg does not exist');
        $this->assertFileExists($testdir . 'test-image-4.png', 'test-image-4.png does not exist');

        // Standard test: file does not exist and there are no possible intersections with other files.
        $this->assertSame(
            'abcdef.png',
            wp_unique_filename($testdir, 'abcdef.png'),
            'The abcdef.png, abcdef.gif, and abcdef.jpg images do not exist. The file name should not be changed.'
        );

        // Actual clash recognized.
        $this->assertSame(
            'canola-1.jpg',
            wp_unique_filename($testdir, 'canola.jpg'),
            'The canola.jpg image exists. The file name should be unique.'
        );

        // Same name with different extension and the image will be converted.
        $this->assertSame(
            'canola-1.png',
            wp_unique_filename($testdir, 'canola.png'),
            'The canola.jpg image exists. Uploading canola.png that will be converted to canola.jpg should produce unique file name.'
        );

        // Same name with different uppercase extension and the image will be converted.
        $this->assertSame(
            'canola-1.png',
            wp_unique_filename($testdir, 'canola.PNG'),
            'The canola.jpg image exists. Uploading canola.PNG that will be converted to canola.jpg should produce unique file name.'
        );

        // Actual clash with several images with different extensions.
        $this->assertSame(
            'test-image-5.png',
            wp_unique_filename($testdir, 'test-image.png'),
            'The test-image.png, test-image-1-100x100.jpg, test-image-2.gif, test-image-3.jpg, and test-image-4.png images exist.' .
            'All of them may clash when creating sub-sizes or regenerating thumbnails in the future. The filename should be unique.'
        );

        // Possible clash with regenerated thumbnails in the future.
        $this->assertSame(
            'codeispoetry-1.jpg',
            wp_unique_filename($testdir, 'codeispoetry.jpg'),
            'The codeispoetry.png image exists. When regenerating thumbnails for it they will be converted to JPG.' .
            'The name of the newly uploaded codeispoetry.jpg should be made unique.'
        );

        remove_filter('image_editor_output_format', [ $this, 'image_editor_output_format_handler' ]);
        remove_filter('upload_dir', [ $this, 'upload_dir_patch_basedir' ]);
    }

    /**
     * Changes the output format when editing images. When uploading a PNG file
     * it will be converted to JPEG, GIF to JPEG, and PICT to BMP
     * (if the image editor in PHP supports it).
     *
     * @param array $formats
     *
     * @return array
     */
    public function image_editor_output_format_handler($formats)
    {
        $formats['image/png'] = 'image/jpeg';
        $formats['image/gif'] = 'image/jpeg';
        $formats['image/pct'] = 'image/bmp';

        return $formats;
    }

    /**
     * @group add_query_arg
     */
    public function test_add_query_arg()
    {
        $old_req_uri = $_SERVER['REQUEST_URI'];

        $urls = [
            '/',
            '/2012/07/30/',
            'edit.php',
            admin_url('edit.php'),
            admin_url('edit.php', 'https'),
        ];

        $frag_urls = [
            '/#frag',
            '/2012/07/30/#frag',
            'edit.php#frag',
            admin_url('edit.php#frag'),
            admin_url('edit.php#frag', 'https'),
        ];

        foreach ($urls as $url) {
            $_SERVER['REQUEST_URI'] = 'nothing';

            $this->assertSame("$url?foo=1", add_query_arg('foo', '1', $url));
            $this->assertSame("$url?foo=1", add_query_arg([ 'foo' => '1' ], $url));
            $this->assertSame(
                "$url?foo=2",
                add_query_arg(
                    [
                        'foo' => '1',
                        'foo' => '2',
                    ],
                    $url
                )
            );
            $this->assertSame(
                "$url?foo=1&bar=2",
                add_query_arg(
                    [
                        'foo' => '1',
                        'bar' => '2',
                    ],
                    $url
                )
            );

            $_SERVER['REQUEST_URI'] = $url;

            $this->assertSame("$url?foo=1", add_query_arg('foo', '1'));
            $this->assertSame("$url?foo=1", add_query_arg([ 'foo' => '1' ]));
            $this->assertSame(
                "$url?foo=2",
                add_query_arg(
                    [
                        'foo' => '1',
                        'foo' => '2',
                    ]
                )
            );
            $this->assertSame(
                "$url?foo=1&bar=2",
                add_query_arg(
                    [
                        'foo' => '1',
                        'bar' => '2',
                    ]
                )
            );
        }

        foreach ($frag_urls as $frag_url) {
            $_SERVER['REQUEST_URI'] = 'nothing';
            $url                    = str_replace('#frag', '', $frag_url);

            $this->assertSame("$url?foo=1#frag", add_query_arg('foo', '1', $frag_url));
            $this->assertSame("$url?foo=1#frag", add_query_arg([ 'foo' => '1' ], $frag_url));
            $this->assertSame(
                "$url?foo=2#frag",
                add_query_arg(
                    [
                        'foo' => '1',
                        'foo' => '2',
                    ],
                    $frag_url
                )
            );
            $this->assertSame(
                "$url?foo=1&bar=2#frag",
                add_query_arg(
                    [
                        'foo' => '1',
                        'bar' => '2',
                    ],
                    $frag_url
                )
            );

            $_SERVER['REQUEST_URI'] = $frag_url;

            $this->assertSame("$url?foo=1#frag", add_query_arg('foo', '1'));
            $this->assertSame("$url?foo=1#frag", add_query_arg([ 'foo' => '1' ]));
            $this->assertSame(
                "$url?foo=2#frag",
                add_query_arg(
                    [
                        'foo' => '1',
                        'foo' => '2',
                    ]
                )
            );
            $this->assertSame(
                "$url?foo=1&bar=2#frag",
                add_query_arg(
                    [
                        'foo' => '1',
                        'bar' => '2',
                    ]
                )
            );
        }

        $qs_urls = [
            'baz=1', // #WP4903
            '/?baz',
            '/2012/07/30/?baz',
            'edit.php?baz',
            admin_url('edit.php?baz'),
            admin_url('edit.php?baz', 'https'),
            admin_url('edit.php?baz&za=1'),
            admin_url('edit.php?baz=1&za=1'),
            admin_url('edit.php?baz=0&za=0'),
        ];

        foreach ($qs_urls as $url) {
            $_SERVER['REQUEST_URI'] = 'nothing';

            $this->assertSame("$url&foo=1", add_query_arg('foo', '1', $url));
            $this->assertSame("$url&foo=1", add_query_arg([ 'foo' => '1' ], $url));
            $this->assertSame(
                "$url&foo=2",
                add_query_arg(
                    [
                        'foo' => '1',
                        'foo' => '2',
                    ],
                    $url
                )
            );
            $this->assertSame(
                "$url&foo=1&bar=2",
                add_query_arg(
                    [
                        'foo' => '1',
                        'bar' => '2',
                    ],
                    $url
                )
            );

            $_SERVER['REQUEST_URI'] = $url;

            $this->assertSame("$url&foo=1", add_query_arg('foo', '1'));
            $this->assertSame("$url&foo=1", add_query_arg([ 'foo' => '1' ]));
            $this->assertSame(
                "$url&foo=2",
                add_query_arg(
                    [
                        'foo' => '1',
                        'foo' => '2',
                    ]
                )
            );
            $this->assertSame(
                "$url&foo=1&bar=2",
                add_query_arg(
                    [
                        'foo' => '1',
                        'bar' => '2',
                    ]
                )
            );
        }

        $_SERVER['REQUEST_URI'] = $old_req_uri;
    }

    /**
     * @ticket 31306
     */
    public function test_add_query_arg_numeric_keys()
    {
        $url = add_query_arg([ 'foo' => 'bar' ], '1=1');
        $this->assertSame('1=1&foo=bar', $url);

        $url = add_query_arg(
            [
                'foo' => 'bar',
                '1'   => '2',
            ],
            '1=1'
        );
        $this->assertSame('1=2&foo=bar', $url);

        $url = add_query_arg([ '1' => '2' ], 'foo=bar');
        $this->assertSame('foo=bar&1=2', $url);
    }

    /**
     * Tests that add_query_arg removes the question mark when
     * a parameter is set to false.
     *
     * @dataProvider data_add_query_arg_removes_question_mark
     *
     * @ticket 44499
     * @group  add_query_arg
     *
     * @covers ::add_query_arg
     *
     * @param string $url      Url to test.
     * @param string $expected Expected URL.
     */
    public function test_add_query_arg_removes_question_mark($url, $expected, $key = 'param', $value = false)
    {
        $this->assertSame($expected, add_query_arg($key, $value, $url));
    }

    /**
     * Data provider.
     *
     * @return array
     */
    public function data_add_query_arg_removes_question_mark()
    {
        return [
            'anchor'                                     => [
                'url'      => 'http://example.org?#anchor',
                'expected' => 'http://example.org#anchor',
            ],
            '/ then anchor'                              => [
                'url'      => 'http://example.org/?#anchor',
                'expected' => 'http://example.org/#anchor',
            ],
            'invalid query param and anchor'             => [
                'url'      => 'http://example.org?param=value#anchor',
                'expected' => 'http://example.org#anchor',
            ],
            '/ then invalid query param and anchor'      => [
                'url'      => 'http://example.org/?param=value#anchor',
                'expected' => 'http://example.org/#anchor',
            ],
            '?#anchor when adding valid key/value args'  => [
                'url'      => 'http://example.org?#anchor',
                'expected' => 'http://example.org?foo=bar#anchor',
                'key'      => 'foo',
                'value'    => 'bar',
            ],
            '/?#anchor when adding valid key/value args' => [
                'url'      => 'http://example.org/?#anchor',
                'expected' => 'http://example.org/?foo=bar#anchor',
                'key'      => 'foo',
                'value'    => 'bar',
            ],
        ];
    }

    /**
     * @ticket 21594
     */
    public function test_get_allowed_mime_types()
    {
        $mimes = get_allowed_mime_types();

        $this->assertIsArray($mimes);
        $this->assertNotEmpty($mimes);

        add_filter('upload_mimes', '__return_empty_array');
        $mimes = get_allowed_mime_types();
        $this->assertIsArray($mimes);
        $this->assertEmpty($mimes);

        remove_filter('upload_mimes', '__return_empty_array');
        $mimes = get_allowed_mime_types();
        $this->assertIsArray($mimes);
        $this->assertNotEmpty($mimes);
    }

    /**
     * @ticket 21594
     */
    public function test_wp_get_mime_types()
    {
        $mimes = wp_get_mime_types();

        $this->assertIsArray($mimes);
        $this->assertNotEmpty($mimes);

        add_filter('mime_types', '__return_empty_array');
        $mimes = wp_get_mime_types();
        $this->assertIsArray($mimes);
        $this->assertEmpty($mimes);

        remove_filter('mime_types', '__return_empty_array');
        $mimes = wp_get_mime_types();
        $this->assertIsArray($mimes);
        $this->assertNotEmpty($mimes);

        // 'upload_mimes' should not affect wp_get_mime_types().
        add_filter('upload_mimes', '__return_empty_array');
        $mimes = wp_get_mime_types();
        $this->assertIsArray($mimes);
        $this->assertNotEmpty($mimes);

        remove_filter('upload_mimes', '__return_empty_array');
        $mimes2 = wp_get_mime_types();
        $this->assertIsArray($mimes2);
        $this->assertNotEmpty($mimes2);
        $this->assertSame($mimes2, $mimes);
    }

    /**
     * @ticket 23688
     */
    public function test_canonical_charset()
    {
        $orig_blog_charset = get_option('blog_charset');

        update_option('blog_charset', 'utf8');
        $this->assertSame('UTF-8', get_option('blog_charset'));

        update_option('blog_charset', 'utf-8');
        $this->assertSame('UTF-8', get_option('blog_charset'));

        update_option('blog_charset', 'UTF8');
        $this->assertSame('UTF-8', get_option('blog_charset'));

        update_option('blog_charset', 'UTF-8');
        $this->assertSame('UTF-8', get_option('blog_charset'));

        update_option('blog_charset', 'ISO-8859-1');
        $this->assertSame('ISO-8859-1', get_option('blog_charset'));

        update_option('blog_charset', 'ISO8859-1');
        $this->assertSame('ISO-8859-1', get_option('blog_charset'));

        update_option('blog_charset', 'iso8859-1');
        $this->assertSame('ISO-8859-1', get_option('blog_charset'));

        update_option('blog_charset', 'iso-8859-1');
        $this->assertSame('ISO-8859-1', get_option('blog_charset'));

        // Arbitrary strings are passed through.
        update_option('blog_charset', 'foobarbaz');
        $this->assertSame('foobarbaz', get_option('blog_charset'));

        update_option('blog_charset', $orig_blog_charset);
    }

    /**
     * @dataProvider data_device_can_upload
     */
    public function test_device_can_upload($user_agent, $expected)
    {
        $_SERVER['HTTP_USER_AGENT'] = $user_agent;
        $actual                     = _device_can_upload();
        unset($_SERVER['HTTP_USER_AGENT']);
        $this->assertSame($expected, $actual);
    }

    public function data_device_can_upload()
    {
        return [
            // iPhone iOS 5.0.1, Safari 5.1.
            [
                'Mozilla/5.0 (iPhone; CPU iPhone OS 5_0_1 like Mac OS X) AppleWebKit/534.46 (KHTML, like Gecko) Mobile/9A406)',
                false,
            ],
            // iPad iOS 3.2, Safari 4.0.4.
            [
                'Mozilla/5.0 (iPad; U; CPU OS 3_2 like Mac OS X; en-us) AppleWebKit/531.21.10 (KHTML, like Gecko) Version/4.0.4 Mobile/7B334b Safari/531.21.10',
                false,
            ],
            // iPod iOS 4.3.3, Safari 5.0.2.
            [
                'Mozilla/5.0 (iPod; U; CPU iPhone OS 4_3_3 like Mac OS X; ja-jp) AppleWebKit/533.17.9 (KHTML, like Gecko) Version/5.0.2 Mobile/8J2 Safari/6533.18.5',
                false,
            ],
            // iPhone iOS 6.0.0, Safari 6.0.
            [
                'Mozilla/5.0 (iPhone; CPU iPhone OS 6_0 like Mac OS X) AppleWebKit/536.26 (KHTML, like Gecko) Version/6.0 Mobile/10A5376e Safari/8536.25',
                true,
            ],
            // iPad iOS 6.0.0, Safari 6.0.
            [
                'Mozilla/5.0 (iPad; CPU OS 6_0 like Mac OS X) AppleWebKit/536.26 (KHTML, like Gecko) Version/6.0 Mobile/10A5376e Safari/8536.25',
                true,
            ],
            // Android 2.2, Android Webkit Browser.
            [
                'Mozilla/5.0 (Android 2.2; Windows; U; Windows NT 6.1; en-US) AppleWebKit/533.19.4 (KHTML, like Gecko) Version/5.0.3 Safari/533.19.4',
                true,
            ],
            // BlackBerry 9900, BlackBerry browser.
            [
                'Mozilla/5.0 (BlackBerry; U; BlackBerry 9900; en) AppleWebKit/534.11+ (KHTML, like Gecko) Version/7.1.0.346 Mobile Safari/534.11+',
                true,
            ],
            // Windows Phone 8.0, Internet Explorer 10.0.
            [
                'Mozilla/5.0 (compatible; MSIE 10.0; Windows Phone 8.0; Trident/6.0; IEMobile/10.0; ARM; Touch; NOKIA; Lumia 920)',
                true,
            ],
            // Ubuntu desktop, Firefox 41.0.
            [
                'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:41.0) Gecko/20100101 Firefox/41.0',
                true,
            ],
        ];
    }

    /**
     * @ticket 9064
     */
    public function test_wp_extract_urls()
    {
        $original_urls = [
            'http://woo.com/1,2,3,4,5,6/-1-2-3-4-/woo.html',
            'http://this.com',
            'http://127.0.0.1',
            'http://www111.urwyeoweytwutreyytqytwetowteuiiu.com/?346236346326&2134362574863.437',
            'http://wordpress-core/1,2,3,4,5,6/-1-2-3-4-/woo.html',
            'http://wordpress-core.com:8080/',
            'http://www.website.com:5000',
            'http://wordpress-core/?346236346326&2134362574863.437',
            'http://افغانستا.icom.museum',
            'http://الجزائر.icom.museum',
            'http://österreich.icom.museum',
            'http://বাংলাদেশ.icom.museum',
            'http://беларусь.icom.museum',
            'http://belgië.icom.museum',
            'http://българия.icom.museum',
            'http://تشادر.icom.museum',
            'http://中国.icom.museum',
            // 'http://القمر.icom.museum',         // Comoros http://القمر.icom.museum
            // 'http://κυπρος.icom.museum',        // Cyprus  http://κυπρος.icom.museum
            'http://českárepublika.icom.museum',
            // 'http://مصر.icom.museum',           // Egypt   http://مصر.icom.museum
            'http://ελλάδα.icom.museum',
            'http://magyarország.icom.museum',
            'http://ísland.icom.museum',
            'http://भारत.icom.museum',
            'http://ايران.icom.museum',
            'http://éire.icom.museum',
            'http://איקו״ם.ישראל.museum',
            'http://日本.icom.museum',
            'http://الأردن.icom.museum',
            'http://қазақстан.icom.museum',
            'http://한국.icom.museum',
            'http://кыргызстан.icom.museum',
            'http://ລາວ.icom.museum',
            'http://لبنان.icom.museum',
            'http://македонија.icom.museum',
            // 'http://موريتانيا.icom.museum',     // Mauritania http://موريتانيا.icom.museum
            'http://méxico.icom.museum',
            'http://монголулс.icom.museum',
            // 'http://المغرب.icom.museum',        // Morocco    http://المغرب.icom.museum
            'http://नेपाल.icom.museum',
            // 'http://عمان.icom.museum',          // Oman       http://عمان.icom.museum
            'http://قطر.icom.museum',
            'http://românia.icom.museum',
            'http://россия.иком.museum',
            'http://србијаицрнагора.иком.museum',
            'http://இலங்கை.icom.museum',
            'http://españa.icom.museum',
            'http://ไทย.icom.museum',
            'http://تونس.icom.museum',
            'http://türkiye.icom.museum',
            'http://украина.icom.museum',
            'http://việtnam.icom.museum',
            'ftp://127.0.0.1/',
            'http://www.woo.com/video?v=exvUH2qKLTU',
            'http://taco.com?burrito=enchilada#guac',
            'http://example.org/?post_type=post&p=4',
            'http://example.org/?post_type=post&p=5',
            'http://example.org/?post_type=post&p=6',
            'http://typo-in-query.org/?foo=bar&ampbaz=missing_semicolon',
        ];

        $blob = '
			http://woo.com/1,2,3,4,5,6/-1-2-3-4-/woo.html

			http://this.com

			http://127.0.0.1

			http://www111.urwyeoweytwutreyytqytwetowteuiiu.com/?346236346326&amp;2134362574863.437

			http://wordpress-core/1,2,3,4,5,6/-1-2-3-4-/woo.html

			http://wordpress-core.com:8080/

			http://www.website.com:5000

			http://wordpress-core/?346236346326&amp;2134362574863.437

			http://افغانستا.icom.museum
			http://الجزائر.icom.museum
			http://österreich.icom.museum
			http://বাংলাদেশ.icom.museum
			http://беларусь.icom.museum
			http://belgië.icom.museum
			http://българия.icom.museum
			http://تشادر.icom.museum
			http://中国.icom.museum
			http://českárepublika.icom.museum
			http://ελλάδα.icom.museum
			http://magyarország.icom.museum
			http://ísland.icom.museum
			http://भारत.icom.museum
			http://ايران.icom.museum
			http://éire.icom.museum
			http://איקו״ם.ישראל.museum
			http://日本.icom.museum
			http://الأردن.icom.museum
			http://қазақстан.icom.museum
			http://한국.icom.museum
			http://кыргызстан.icom.museum
			http://ລາວ.icom.museum
			http://لبنان.icom.museum
			http://македонија.icom.museum
			http://méxico.icom.museum
			http://монголулс.icom.museum
			http://नेपाल.icom.museum
			http://قطر.icom.museum
			http://românia.icom.museum
			http://россия.иком.museum
			http://србијаицрнагора.иком.museum
			http://இலங்கை.icom.museum
			http://españa.icom.museum
			http://ไทย.icom.museum
			http://تونس.icom.museum
			http://türkiye.icom.museum
			http://украина.icom.museum
			http://việtnam.icom.museum
			ftp://127.0.0.1/
			http://www.woo.com/video?v=exvUH2qKLTU

			http://taco.com?burrito=enchilada#guac

			http://example.org/?post_type=post&amp;p=4
			http://example.org/?post_type=post&#038;p=5
			http://example.org/?post_type=post&p=6

			http://typo-in-query.org/?foo=bar&ampbaz=missing_semicolon
		';

        $urls = wp_extract_urls($blob);
        $this->assertNotEmpty($urls);
        $this->assertIsArray($urls);
        $this->assertCount(count($original_urls), $urls);
        $this->assertSame($original_urls, $urls);

        $exploded = array_values(array_filter(array_map('trim', explode("\n", $blob))));
        // wp_extract_urls() calls html_entity_decode().
        $decoded = array_map('html_entity_decode', $exploded);

        $this->assertSame($decoded, $urls);
        $this->assertSame($original_urls, $decoded);

        $blob = 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor
			incididunt ut labore http://woo.com/1,2,3,4,5,6/-1-2-3-4-/woo.html et dolore magna aliqua.
			Ut http://this.com enim ad minim veniam, quis nostrud exercitation 16.06. to 18.06.2014 ullamco http://127.0.0.1
			laboris nisi ut aliquip ex http://www111.urwyeoweytwutreyytqytwetowteuiiu.com/?346236346326&amp;2134362574863.437 ea
			commodo consequat. http://wordpress-core/1,2,3,4,5,6/-1-2-3-4-/woo.html Duis aute irure dolor in reprehenderit in voluptate
			velit esse http://wordpress-core.com:8080/ cillum dolore eu fugiat nulla <A href="http://www.website.com:5000">http://www.website.com:5000</B> pariatur. Excepteur sint occaecat cupidatat non proident,
			sunt in culpa qui officia deserunt mollit http://wordpress-core/?346236346326&amp;2134362574863.437 anim id est laborum.';

        $urls = wp_extract_urls($blob);
        $this->assertNotEmpty($urls);
        $this->assertIsArray($urls);
        $this->assertCount(8, $urls);
        $this->assertSame(array_slice($original_urls, 0, 8), $urls);

        $blob = 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor
			incididunt ut labore <a href="http://woo.com/1,2,3,4,5,6/-1-2-3-4-/woo.html">343462^</a> et dolore magna aliqua.
			Ut <a href="http://this.com">&amp;3640i6p1yi499</a> enim ad minim veniam, quis nostrud exercitation 16.06. to 18.06.2014 ullamco <a href="http://127.0.0.1">localhost</a>
			laboris nisi ut aliquip ex <a href="http://www111.urwyeoweytwutreyytqytwetowteuiiu.com/?346236346326&amp;2134362574863.437">343462^</a> ea
			commodo consequat. <a href="http://wordpress-core/1,2,3,4,5,6/-1-2-3-4-/woo.html">343462^</a> Duis aute irure dolor in reprehenderit in voluptate
			velit esse <a href="http://wordpress-core.com:8080/">-3-4--321-64-4@#!$^$!@^@^</a> cillum dolore eu <A href="http://www.website.com:5000">http://www.website.com:5000</B> fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident,
			sunt in culpa qui officia deserunt mollit <a href="http://wordpress-core/?346236346326&amp;2134362574863.437">)(*&^%$</a> anim id est laborum.';

        $urls = wp_extract_urls($blob);
        $this->assertNotEmpty($urls);
        $this->assertIsArray($urls);
        $this->assertCount(8, $urls);
        $this->assertSame(array_slice($original_urls, 0, 8), $urls);
    }

    /**
     * Tests for backward compatibility of `wp_extract_urls` to remove unused semicolons.
     *
     * @ticket 30580
     *
     * @covers ::wp_extract_urls
     */
    public function test_wp_extract_urls_remove_semicolon()
    {
        $expected = [
            'http://typo.com',
            'http://example.org/?post_type=post&p=8',
        ];
        $actual   = wp_extract_urls(
            '
				http://typo.com;
				http://example.org/?post_type=;p;o;s;t;&amp;p=8;
			'
        );

        $this->assertSame($expected, $actual);
    }

    /**
     * @ticket 28786
     */
    public function test_wp_json_encode()
    {
        $this->assertSame(wp_json_encode('a'), '"a"');
    }

    /**
     * @ticket 28786
     */
    public function test_wp_json_encode_utf8()
    {
        $this->assertSame(wp_json_encode('这'), '"\u8fd9"');
    }

    /**
     * @ticket 28786
     * @requires function mb_detect_order
     */
    public function test_wp_json_encode_non_utf8()
    {
        $charsets     = mb_detect_order();
        $old_charsets = $charsets;
        if (! in_array('EUC-JP', $charsets, true)) {
            $charsets[] = 'EUC-JP';
            mb_detect_order($charsets);
        }

        $eucjp = mb_convert_encoding('aあb', 'EUC-JP', 'UTF-8');
        $utf8  = mb_convert_encoding($eucjp, 'UTF-8', 'EUC-JP');

        $this->assertSame('aあb', $utf8);

        $this->assertSame('"a\u3042b"', wp_json_encode($eucjp));

        mb_detect_order($old_charsets);
    }

    /**
     * @ticket 28786
     * @requires function mb_detect_order
     */
    public function test_wp_json_encode_non_utf8_in_array()
    {
        $charsets     = mb_detect_order();
        $old_charsets = $charsets;
        if (! in_array('EUC-JP', $charsets, true)) {
            $charsets[] = 'EUC-JP';
            mb_detect_order($charsets);
        }

        $eucjp = mb_convert_encoding('aあb', 'EUC-JP', 'UTF-8');
        $utf8  = mb_convert_encoding($eucjp, 'UTF-8', 'EUC-JP');

        $this->assertSame('aあb', $utf8);

        $this->assertSame('["c","a\u3042b"]', wp_json_encode([ 'c', $eucjp ]));

        mb_detect_order($old_charsets);
    }

    /**
     * @ticket 28786
     */
    public function test_wp_json_encode_array()
    {
        $this->assertSame(wp_json_encode([ 'a' ]), '["a"]');
    }

    /**
     * @ticket 28786
     */
    public function test_wp_json_encode_object()
    {
        $object    = new stdClass();
        $object->a = 'b';
        $this->assertSame(wp_json_encode($object), '{"a":"b"}');
    }

    /**
     * @ticket 28786
     */
    public function test_wp_json_encode_depth()
    {
        $data = [ [ [ 1, 2, 3 ] ] ];
        $json = wp_json_encode($data, 0, 1);
        $this->assertFalse($json);

        $data = [ 'あ', [ [ 1, 2, 3 ] ] ];
        $json = wp_json_encode($data, 0, 1);
        $this->assertFalse($json);
    }

    /**
     * @ticket 53238
     */
    public function test_wp_json_file_decode()
    {
        $result = wp_json_file_decode(
            DIR_TESTDATA . '/blocks/notice/block.json'
        );

        $this->assertIsObject($result);
        $this->assertSame('tests/notice', $result->name);
    }

    /**
     * @ticket 53238
     */
    public function test_wp_json_file_decode_associative_array()
    {
        $result = wp_json_file_decode(
            DIR_TESTDATA . '/blocks/notice/block.json',
            [ 'associative' => true ]
        );

        $this->assertIsArray($result);
        $this->assertSame('tests/notice', $result['name']);
    }

    /**
     * @ticket 36054
     * @dataProvider data_mysql_to_rfc3339
     */
    public function test_mysql_to_rfc3339($expected, $actual)
    {
        $date_return = mysql_to_rfc3339($actual);

        $this->assertIsString($date_return, 'The date return must be a string');
        $this->assertNotEmpty($date_return, 'The date return could not be an empty string');
        $this->assertSame($expected, $date_return, 'The date does not match');
        $this->assertEquals(new DateTime($expected), new DateTime($date_return), 'The date is not the same after the call method');
    }

    public function data_mysql_to_rfc3339()
    {
        return [
            [ '2016-03-15T18:54:46', '15-03-2016 18:54:46' ],
            [ '2016-03-02T19:13:25', '2016-03-02 19:13:25' ],
            [ '2016-03-02T19:13:00', '2016-03-02 19:13' ],
            [ '2016-03-02T19:13:00', '16-03-02 19:13' ],
            [ '2016-03-02T19:13:00', '16-03-02 19:13' ],
        ];
    }

    /**
     * @ticket 35987
     */
    public function test_wp_get_ext_types()
    {
        $extensions = wp_get_ext_types();

        $this->assertIsArray($extensions);
        $this->assertNotEmpty($extensions);

        add_filter('ext2type', '__return_empty_array');
        $extensions = wp_get_ext_types();
        $this->assertSame([], $extensions);

        remove_filter('ext2type', '__return_empty_array');
        $extensions = wp_get_ext_types();
        $this->assertIsArray($extensions);
        $this->assertNotEmpty($extensions);
    }

    /**
     * @ticket 35987
     */
    public function test_wp_ext2type()
    {
        $extensions = wp_get_ext_types();

        $this->assertNotEmpty($extensions);

        foreach ($extensions as $type => $extension_list) {
            foreach ($extension_list as $extension) {
                $this->assertSame($type, wp_ext2type($extension));
                $this->assertSame($type, wp_ext2type(strtoupper($extension)));
            }
        }

        $this->assertNull(wp_ext2type('unknown_format'));
    }

    /**
     * Tests raising the memory limit.
     *
     * Unfortunately as the default for 'WP_MAX_MEMORY_LIMIT' in the
     * test suite is -1, we can not test the memory limit negotiations.
     *
     * @ticket 32075
     */
    public function test_wp_raise_memory_limit()
    {
        if (-1 !== WP_MAX_MEMORY_LIMIT) {
            $this->markTestSkipped('WP_MAX_MEMORY_LIMIT should be set to -1.');
        }

        $ini_limit_before = ini_get('memory_limit');
        $raised_limit     = wp_raise_memory_limit();
        $ini_limit_after  = ini_get('memory_limit');

        $this->assertSame($ini_limit_before, $ini_limit_after);
        $this->assertFalse($raised_limit);
        $this->assertEquals(WP_MAX_MEMORY_LIMIT, $ini_limit_after);
    }

    /**
     * Tests wp_generate_uuid4().
     *
     * @covers ::wp_generate_uuid4
     * @ticket 38164
     */
    public function test_wp_generate_uuid4()
    {
        $uuids = [];
        for ($i = 0; $i < 20; $i += 1) {
            $uuid = wp_generate_uuid4();
            $this->assertTrue(wp_is_uuid($uuid, 4));
            $uuids[] = $uuid;
        }

        $unique_uuids = array_unique($uuids);
        $this->assertSame($uuids, $unique_uuids);
    }

    /**
     * Tests wp_is_uuid().
     *
     * @covers ::wp_is_uuid
     * @ticket 39778
     */
    public function test_wp_is_valid_uuid()
    {
        $uuids_v4 = [
            '27fe2421-780c-44c5-b39b-fff753092b55',
            'b7c7713a-4ee9-45a1-87ed-944a90390fc7',
            'fbedbe35-7bf5-49cc-a5ac-0343bd94360a',
            '4c58e67e-123b-4290-a41c-5eeb6970fa3e',
            'f54f5b78-e414-4637-84a9-a6cdc94a1beb',
            'd1c533ac-abcf-44b6-9b0e-6477d2c91b09',
            '7fcd683f-e5fd-454a-a8b9-ed15068830da',
            '7962c750-e58c-470a-af0d-ec1eae453ff2',
            'a59878ce-9a67-4493-8ca0-a756b52804b3',
            '6faa519d-1e13-4415-bd6f-905ae3689d1d',
        ];

        foreach ($uuids_v4 as $uuid) {
            $this->assertTrue(wp_is_uuid($uuid, 4));
        }

        $uuids = [
            '00000000-0000-0000-0000-000000000000', // Nil.
            '9e3a0460-d72d-11e4-a631-c8e0eb141dab', // Version 1.
            '2c1d43b8-e6d7-376e-af7f-d4bde997cc3f', // Version 3.
            '39888f87-fb62-5988-a425-b2ea63f5b81e', // Version 5.
        ];

        foreach ($uuids as $uuid) {
            $this->assertTrue(wp_is_uuid($uuid));
            $this->assertFalse(wp_is_uuid($uuid, 4));
        }

        $invalid_uuids = [
            'a19d5192-ea41-41e6-b006',
            'this-is-not-valid',
            1234,
            true,
            [],
        ];

        foreach ($invalid_uuids as $invalid_uuid) {
            $this->assertFalse(wp_is_uuid($invalid_uuid, 4));
            $this->assertFalse(wp_is_uuid($invalid_uuid));
        }
    }

    /**
     * Tests wp_unique_id().
     *
     * @covers ::wp_unique_id
     * @ticket 44883
     */
    public function test_wp_unique_id()
    {

        // Test without prefix.
        $ids = [];
        for ($i = 0; $i < 20; $i += 1) {
            $id = wp_unique_id();
            $this->assertIsString($id);
            $this->assertIsNumeric($id);
            $ids[] = $id;
        }
        $this->assertSame($ids, array_unique($ids));

        // Test with prefix.
        $ids = [];
        for ($i = 0; $i < 20; $i += 1) {
            $id = wp_unique_id('foo-');
            $this->assertMatchesRegularExpression('/^foo-\d+$/', $id);
            $ids[] = $id;
        }
        $this->assertSame($ids, array_unique($ids));
    }

    /**
     * @ticket 40017
     * @dataProvider data_wp_get_image_mime
     */
    public function test_wp_get_image_mime($file, $expected)
    {
        if (! is_callable('exif_imagetype') && ! function_exists('getimagesize')) {
            $this->markTestSkipped('The exif PHP extension is not loaded.');
        }

        if (is_array($expected)) {
            $this->assertContains(wp_get_image_mime($file), $expected);
        } else {
            $this->assertSame($expected, wp_get_image_mime($file));
        }
    }

    /**
     * Data provider for test_wp_get_image_mime().
     */
    public function data_wp_get_image_mime()
    {
        $data = [
            // Standard JPEG.
            [
                DIR_TESTDATA . '/images/test-image.jpg',
                'image/jpeg',
            ],
            // Standard GIF.
            [
                DIR_TESTDATA . '/images/test-image.gif',
                'image/gif',
            ],
            // Standard PNG.
            [
                DIR_TESTDATA . '/images/test-image.png',
                'image/png',
            ],
            // Image with wrong extension.
            [
                DIR_TESTDATA . '/images/test-image-mime-jpg.png',
                'image/jpeg',
            ],
            // Animated WebP.
            [
                DIR_TESTDATA . '/images/webp-animated.webp',
                'image/webp',
            ],
            // Lossless WebP.
            [
                DIR_TESTDATA . '/images/webp-lossless.webp',
                'image/webp',
            ],
            // Lossy WebP.
            [
                DIR_TESTDATA . '/images/webp-lossy.webp',
                'image/webp',
            ],
            // Transparent WebP.
            [
                DIR_TESTDATA . '/images/webp-transparent.webp',
                'image/webp',
            ],
            // Not an image.
            [
                DIR_TESTDATA . '/uploads/dashicons.woff',
                false,
            ],
            // Animated AVIF.
            [
                DIR_TESTDATA . '/images/avif-animated.avif',
                'image/avif',
            ],
            // Lossless AVIF.
            [
                DIR_TESTDATA . '/images/avif-lossless.avif',
                'image/avif',
            ],
            // Lossy AVIF.
            [
                DIR_TESTDATA . '/images/avif-lossy.avif',
                'image/avif',
            ],
            // Transparent AVIF.
            [
                DIR_TESTDATA . '/images/avif-transparent.avif',
                'image/avif',
            ],
            // HEIC.
            [
                DIR_TESTDATA . '/images/test-image.heic',
                // In PHP 8.5, it returns 'image/heif'. Before that, it returns 'image/heic'.
                [ 'image/heic', 'image/heif' ],
            ],
        ];

        return $data;
    }

    /**
     * @ticket 35725
     * @dataProvider data_wp_getimagesize
     */
    public function test_wp_getimagesize($file, $expected)
    {
        if (! is_callable('exif_imagetype') && ! function_exists('getimagesize')) {
            $this->markTestSkipped('The exif PHP extension is not loaded.');
        }

        $result = wp_getimagesize($file);

        // The getimagesize() function varies in its response, so
        // let's restrict comparison to expected keys only.
        if (is_array($expected)) {
            foreach ($expected as $k => $v) {
                $this->assertArrayHasKey($k, $result);
                $this->assertSame($expected[ $k ], $result[ $k ]);
            }
        } else {
            $this->assertSame($expected, $result);
        }
    }

    /**
     * Data provider for test_wp_getimagesize().
     */
    public function data_wp_getimagesize()
    {
        $data = [
            // Standard JPEG.
            [
                DIR_TESTDATA . '/images/test-image.jpg',
                [
                    50,
                    50,
                    IMAGETYPE_JPEG,
                    'width="50" height="50"',
                    'mime' => 'image/jpeg',
                ],
            ],
            // Standard GIF.
            [
                DIR_TESTDATA . '/images/test-image.gif',
                [
                    50,
                    50,
                    IMAGETYPE_GIF,
                    'width="50" height="50"',
                    'mime' => 'image/gif',
                ],
            ],
            // Standard PNG.
            [
                DIR_TESTDATA . '/images/test-image.png',
                [
                    50,
                    50,
                    IMAGETYPE_PNG,
                    'width="50" height="50"',
                    'mime' => 'image/png',
                ],
            ],
            // Image with wrong extension.
            [
                DIR_TESTDATA . '/images/test-image-mime-jpg.png',
                [
                    50,
                    50,
                    IMAGETYPE_JPEG,
                    'width="50" height="50"',
                    'mime' => 'image/jpeg',
                ],
            ],
            // Animated WebP.
            [
                DIR_TESTDATA . '/images/webp-animated.webp',
                [
                    100,
                    100,
                    IMAGETYPE_WEBP,
                    'width="100" height="100"',
                    'mime' => 'image/webp',
                ],
            ],
            // Lossless WebP.
            [
                DIR_TESTDATA . '/images/webp-lossless.webp',
                [
                    1200,
                    675,
                    IMAGETYPE_WEBP,
                    'width="1200" height="675"',
                    'mime' => 'image/webp',
                ],
            ],
            // Lossy WebP.
            [
                DIR_TESTDATA . '/images/webp-lossy.webp',
                [
                    1200,
                    675,
                    IMAGETYPE_WEBP,
                    'width="1200" height="675"',
                    'mime' => 'image/webp',
                ],
            ],
            // Transparent WebP.
            [
                DIR_TESTDATA . '/images/webp-transparent.webp',
                [
                    1200,
                    675,
                    IMAGETYPE_WEBP,
                    'width="1200" height="675"',
                    'mime' => 'image/webp',
                ],
            ],
            // Not an image.
            [
                DIR_TESTDATA . '/uploads/dashicons.woff',
                false,
            ],
            // Animated AVIF.
            [
                DIR_TESTDATA . '/images/avif-animated.avif',
                [
                    150,
                    150,
                    IMAGETYPE_AVIF,
                    'width="150" height="150"',
                    'mime' => 'image/avif',
                ],
            ],
            // Lossless AVIF.
            [
                DIR_TESTDATA . '/images/avif-lossless.avif',
                [
                    400,
                    400,
                    IMAGETYPE_AVIF,
                    'width="400" height="400"',
                    'mime' => 'image/avif',
                ],
            ],
            // Lossy AVIF.
            [
                DIR_TESTDATA . '/images/avif-lossy.avif',
                [
                    400,
                    400,
                    IMAGETYPE_AVIF,
                    'width="400" height="400"',
                    'mime' => 'image/avif',
                ],
            ],
            // Transparent AVIF.
            [
                DIR_TESTDATA . '/images/avif-transparent.avif',
                [
                    128,
                    128,
                    IMAGETYPE_AVIF,
                    'width="128" height="128"',
                    'mime' => 'image/avif',
                ],
            ],
            // Grid AVIF.
            [
                DIR_TESTDATA . '/images/avif-alpha-grid2x1.avif',
                [
                    199,
                    200,
                    IMAGETYPE_AVIF,
                    'width="199" height="200"',
                    'mime' => 'image/avif',
                ],
            ],
        ];

        return $data;
    }

    /**
     * Tests that wp_getimagesize() correctly handles HEIC image files.
     *
     * @ticket 53645
     */
    public function test_wp_getimagesize_heic()
    {
        if (! is_callable('exif_imagetype') && ! function_exists('getimagesize')) {
            $this->markTestSkipped('The exif PHP extension is not loaded.');
        }

        $file = DIR_TESTDATA . '/images/test-image.heic';

        $editor = wp_get_image_editor($file);
        if (is_wp_error($editor) || ! $editor->supports_mime_type('image/heic')) {
            $this->markTestSkipped('No HEIC support in the editor engine on this system.');
        }

        $expected = [
            1180,
            1180,
            IMAGETYPE_HEIF,
            'width="1180" height="1180"',
        ];

        // As of PHP 8.5.0, getimagesize() supports HEIF/HEIC files.
        if (PHP_VERSION_ID >= 80500) {
            $expected = array_merge(
                $expected,
                [
                    'bits'        => 8,
                    'channels'    => 3,
                    'mime'        => 'image/heif',
                    'width_unit'  => 'px',
                    'height_unit' => 'px',
                ]
            );
        } else {
            $expected['mime'] = 'image/heic';
        }

        $result = wp_getimagesize($file);
        $this->assertSame($expected, $result);
    }

    /**
     * @ticket 39550
     * @dataProvider data_wp_check_filetype_and_ext
     * @requires extension fileinfo
     */
    public function test_wp_check_filetype_and_ext($file, $filename, $expected)
    {
        $this->assertSame($expected, wp_check_filetype_and_ext($file, $filename));
    }

    public function data_wp_check_filetype_and_ext()
    {
        $data = [
            // Standard image.
            [
                DIR_TESTDATA . '/images/canola.jpg',
                'canola.jpg',
                [
                    'ext'             => 'jpg',
                    'type'            => 'image/jpeg',
                    'proper_filename' => false,
                ],
            ],
            // Image with wrong extension.
            [
                DIR_TESTDATA . '/images/test-image-mime-jpg.png',
                'test-image-mime-jpg.png',
                [
                    'ext'             => 'jpg',
                    'type'            => 'image/jpeg',
                    'proper_filename' => 'test-image-mime-jpg.jpg',
                ],
            ],
            // Image without extension.
            [
                DIR_TESTDATA . '/images/test-image-no-extension',
                'test-image-no-extension',
                [
                    'ext'             => false,
                    'type'            => false,
                    'proper_filename' => false,
                ],
            ],
            // Valid non-image file with an image extension.
            [
                DIR_TESTDATA . '/formatting/big5.txt',
                'big5.jpg',
                [
                    'ext'             => false,
                    'type'            => false,
                    'proper_filename' => false,
                ],
            ],
            // Non-image file not allowed.
            [
                DIR_TESTDATA . '/export/crazy-cdata.xml',
                'crazy-cdata.xml',
                [
                    'ext'             => false,
                    'type'            => false,
                    'proper_filename' => false,
                ],
            ],
            // Non-image file not allowed even if it's named like one.
            [
                DIR_TESTDATA . '/export/crazy-cdata.xml',
                'crazy-cdata.jpg',
                [
                    'ext'             => false,
                    'type'            => false,
                    'proper_filename' => false,
                ],
            ],
            // Non-image file not allowed if it's named like something else.
            [
                DIR_TESTDATA . '/export/crazy-cdata.xml',
                'crazy-cdata.doc',
                [
                    'ext'             => false,
                    'type'            => false,
                    'proper_filename' => false,
                ],
            ],
            // Non-image file not allowed even if it's named like one.
            [
                DIR_TESTDATA . '/export/crazy-cdata.xml',
                'crazy-cdata.jpg',
                [
                    'ext'             => false,
                    'type'            => false,
                    'proper_filename' => false,
                ],
            ],
            // Non-image file not allowed if it's named like something else.
            [
                DIR_TESTDATA . '/export/crazy-cdata.xml',
                'crazy-cdata.doc',
                [
                    'ext'             => false,
                    'type'            => false,
                    'proper_filename' => false,
                ],
            ],
        ];

        // Test a few additional file types on single sites.
        if (! is_multisite()) {
            $data = array_merge(
                $data,
                [
                    // Standard non-image file.
                    [
                        DIR_TESTDATA . '/formatting/big5.txt',
                        'big5.txt',
                        [
                            'ext'             => 'txt',
                            'type'            => 'text/plain',
                            'proper_filename' => false,
                        ],
                    ],
                    // Google Docs file for which finfo_file() returns a duplicate mime type.
                    [
                        DIR_TESTDATA . '/uploads/double-mime-type.docx',
                        'double-mime-type.docx',
                        [
                            'ext'             => 'docx',
                            'type'            => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                            'proper_filename' => false,
                        ],
                    ],
                    // Non-image file with wrong sub-type.
                    [
                        DIR_TESTDATA . '/uploads/pages-to-word.docx',
                        'pages-to-word.docx',
                        [
                            'ext'             => 'docx',
                            'type'            => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                            'proper_filename' => false,
                        ],
                    ],
                    // FLAC file.
                    [
                        DIR_TESTDATA . '/uploads/small-audio.flac',
                        'small-audio.flac',
                        [
                            'ext'             => 'flac',
                            'type'            => 'audio/flac',
                            'proper_filename' => false,
                        ],
                    ],
                    // Assorted text/* sample files
                    [
                        DIR_TESTDATA . '/uploads/test.vtt',
                        'test.vtt',
                        [
                            'ext'             => 'vtt',
                            'type'            => 'text/vtt',
                            'proper_filename' => false,
                        ],
                    ],
                    [
                        DIR_TESTDATA . '/uploads/test.csv',
                        'test.csv',
                        [
                            'ext'             => 'csv',
                            'type'            => 'text/csv',
                            'proper_filename' => false,
                        ],
                    ],
                    // RTF files.
                    [
                        DIR_TESTDATA . '/uploads/test.rtf',
                        'test.rtf',
                        [
                            'ext'             => 'rtf',
                            'type'            => 'application/rtf',
                            'proper_filename' => false,
                        ],
                    ],
                ]
            );
        }

        return $data;
    }

    /**
     * @ticket 39550
     * @group ms-excluded
     * @requires extension fileinfo
     */
    public function test_wp_check_filetype_and_ext_with_filtered_svg()
    {
        $file     = DIR_TESTDATA . '/uploads/video-play.svg';
        $filename = 'video-play.svg';

        $expected = [
            'ext'             => 'svg',
            'type'            => 'image/svg+xml',
            'proper_filename' => false,
        ];

        add_filter(
            'upload_mimes',
            static function ($mimes) {
                $mimes['svg'] = 'image/svg+xml';
                return $mimes;
            }
        );

        $this->assertSame($expected, wp_check_filetype_and_ext($file, $filename));
    }

    /**
     * @ticket 39550
     * @group ms-excluded
     * @requires extension fileinfo
     */
    public function test_wp_check_filetype_and_ext_with_filtered_woff()
    {
        $file     = DIR_TESTDATA . '/uploads/dashicons.woff';
        $filename = 'dashicons.woff';

        $woff_mime_type = 'application/font-woff';

        /*
         * As of PHP 8.1.12, which includes libmagic/file update to version 5.42,
         * the expected mime type for WOFF files is 'font/woff'.
         *
         * See https://github.com/php/php-src/issues/8805.
         */
        if (PHP_VERSION_ID >= 80112) {
            $woff_mime_type = 'font/woff';
        }

        $expected = [
            'ext'             => 'woff',
            'type'            => $woff_mime_type,
            'proper_filename' => false,
        ];

        add_filter(
            'upload_mimes',
            static function ($mimes) use ($woff_mime_type) {
                $mimes['woff'] = $woff_mime_type;
                return $mimes;
            }
        );

        $this->assertSame($expected, wp_check_filetype_and_ext($file, $filename));
    }

    /**
     * Test file path validation
     *
     * @ticket 42016
     * @ticket 61488
     * @dataProvider data_validate_file
     *
     * @param string $file          File path.
     * @param array  $allowed_files List of allowed files.
     * @param int    $expected      Expected result.
     */
    public function test_validate_file($file, $allowed_files, $expected)
    {
        $this->assertSame($expected, validate_file($file, $allowed_files));
    }

    /**
     * Data provider for file validation.
     *
     * @return array {
     *     @type array ...$0 {
     *         @type string $0 File path.
     *         @type array  $1 List of allowed files.
     *         @type int    $2 Expected result.
     *     }
     * }
     */
    public function data_validate_file()
    {
        return [

            // Allowed files:
            [
                null,
                [],
                0,
            ],
            [
                '',
                [],
                0,
            ],
            [
                ' ',
                [],
                0,
            ],
            [
                '.',
                [],
                0,
            ],
            [
                '..',
                [],
                0,
            ],
            [
                './',
                [],
                0,
            ],
            [
                'foo.ext',
                [ 'foo.ext' ],
                0,
            ],
            [
                'dir/foo.ext',
                [],
                0,
            ],
            [
                'foo..ext',
                [],
                0,
            ],
            [
                'dir/dir/../',
                [],
                0,
            ],

            // Directory traversal:
            [
                '../',
                [],
                1,
            ],
            [
                '../../',
                [],
                1,
            ],
            [
                '../file.ext',
                [],
                1,
            ],
            [
                '../dir/../',
                [],
                1,
            ],
            [
                '/dir/dir/../../',
                [],
                1,
            ],
            [
                '/dir/dir/../../',
                [ '/dir/dir/../../' ],
                1,
            ],

            // Windows drives:
            [
                'c:',
                [],
                2,
            ],
            [
                'C:/WINDOWS/system32',
                [ 'C:/WINDOWS/system32' ],
                2,
            ],

            // Windows Path with allowed file
            [
                'Apache24\htdocs\wordpress/wp-content/themes/twentyten/style.css',
                [ 'Apache24\htdocs\wordpress/wp-content/themes/twentyten/style.css' ],
                0,
            ],

            // Disallowed files:
            [
                'foo.ext',
                [ 'bar.ext' ],
                3,
            ],
            [
                'foo.ext',
                [ '.ext' ],
                3,
            ],
            [
                'path/foo.ext',
                [ 'foo.ext' ],
                3,
            ],

        ];
    }

    /**
     * Test stream URL validation.
     *
     * @dataProvider data_wp_is_stream
     *
     * @param string $path     The resource path or URL.
     * @param bool   $expected Expected result.
     */
    public function test_wp_is_stream($path, $expected)
    {
        if (! extension_loaded('openssl') && false !== strpos($path, 'https://')) {
            $this->markTestSkipped('The openssl PHP extension is not loaded.');
        }

        $this->assertSame($expected, wp_is_stream($path));
    }

    /**
     * Data provider for stream URL validation.
     *
     * @return array {
     *     @type array ...$0 {
     *         @type string $0 The resource path or URL.
     *         @type bool   $1 Expected result.
     *     }
     * }
     */
    public function data_wp_is_stream()
    {
        return [
            // Legitimate stream examples.
            [ 'http://example.com', true ],
            [ 'https://example.com', true ],
            [ 'ftp://example.com', true ],
            [ 'file:///path/to/some/file', true ],
            [ 'php://some/php/file.php', true ],

            // Non-stream examples.
            [ 'fakestream://foo/bar/baz', false ],
            [ '../../some/relative/path', false ],
            [ 'some/other/relative/path', false ],
            [ '/leading/relative/path', false ],
        ];
    }

    /**
     * Test human_readable_duration().
     *
     * @ticket 39667
     * @dataProvider data_human_readable_duration
     *
     * @param string $input    Duration.
     * @param string $expected Expected human readable duration.
     */
    public function test_human_readable_duration($input, $expected)
    {
        $this->assertSame($expected, human_readable_duration($input));
    }

    /**
     * Data provider for test_duration_format().
     *
     * @return array {
     *     @type array {
     *         @type string $input  Duration.
     *         @type string $expect Expected human readable duration.
     *     }
     * }
     */
    public function data_human_readable_duration()
    {
        return [
            // Valid ii:ss cases.
            [ '0:0', '0 minutes, 0 seconds' ],
            [ '00:00', '0 minutes, 0 seconds' ],
            [ '0:5', '0 minutes, 5 seconds' ],
            [ '0:05', '0 minutes, 5 seconds' ],
            [ '01:01', '1 minute, 1 second' ],
            [ '30:00', '30 minutes, 0 seconds' ],
            [ ' 30:00 ', '30 minutes, 0 seconds' ],
            // Valid HH:ii:ss cases.
            [ '0:0:0', '0 hours, 0 minutes, 0 seconds' ],
            [ '00:00:00', '0 hours, 0 minutes, 0 seconds' ],
            [ '00:30:34', '0 hours, 30 minutes, 34 seconds' ],
            [ '01:01:01', '1 hour, 1 minute, 1 second' ],
            [ '1:02:00', '1 hour, 2 minutes, 0 seconds' ],
            [ '10:30:34', '10 hours, 30 minutes, 34 seconds' ],
            [ '1234567890:59:59', '1234567890 hours, 59 minutes, 59 seconds' ],
            // Valid ii:ss cases with negative sign.
            [ '-00:00', '0 minutes, 0 seconds' ],
            [ '-3:00', '3 minutes, 0 seconds' ],
            [ '-03:00', '3 minutes, 0 seconds' ],
            [ '-30:00', '30 minutes, 0 seconds' ],
            // Valid HH:ii:ss cases with negative sign.
            [ '-00:00:00', '0 hours, 0 minutes, 0 seconds' ],
            [ '-1:02:00', '1 hour, 2 minutes, 0 seconds' ],
            // Invalid cases.
            [ null, false ],
            [ '', false ],
            [ ':', false ],
            [ '::', false ],
            [ [], false ],
            [ 'Batman Begins !', false ],
            [ '', false ],
            [ '-1', false ],
            [ -1, false ],
            [ 0, false ],
            [ 1, false ],
            [ '00', false ],
            [ '30:-10', false ],
            [ ':30:00', false ],   // Missing HH.
            [ 'MM:30:00', false ], // Invalid HH.
            [ '30:MM:00', false ], // Invalid ii.
            [ '30:30:MM', false ], // Invalid ss.
            [ '30:MM', false ],    // Invalid ss.
            [ 'MM:00', false ],    // Invalid ii.
            [ 'MM:MM', false ],    // Invalid ii and ss.
            [ '10 :30', false ],   // Containing a space.
            [ '59:61', false ],    // Out of bound.
            [ '61:59', false ],    // Out of bound.
            [ '3:59:61', false ],  // Out of bound.
            [ '03:61:59', false ], // Out of bound.
        ];
    }

    /**
     * @ticket 49404
     * @dataProvider data_wp_is_json_media_type
     */
    public function test_wp_is_json_media_type($input, $expected)
    {
        $this->assertSame($expected, wp_is_json_media_type($input));
    }

    public function data_wp_is_json_media_type()
    {
        return [
            [ 'application/ld+json', true ],
            [ 'application/ld+json; profile="https://www.w3.org/ns/activitystreams"', true ],
            [ 'application/activity+json', true ],
            [ 'application/json+oembed', true ],
            [ 'application/json', true ],
            [ 'application/nojson', false ],
            [ 'application/no.json', false ],
            [ 'text/html, application/xhtml+xml, application/xml;q=0.9, image/webp, */*;q=0.8', false ],
            [ 'application/activity+json, application/nojson', true ],
        ];
    }

    /**
     * @ticket 53668
     */
    public function test_wp_get_default_extension_for_mime_type()
    {
        $this->assertSame('jpg', wp_get_default_extension_for_mime_type('image/jpeg'), 'jpg not returned as default extension for "image/jpeg"');
        $this->assertNotEquals('jpeg', wp_get_default_extension_for_mime_type('image/jpeg'), 'jpeg should not be returned as default extension for "image/jpeg"');
        $this->assertSame('png', wp_get_default_extension_for_mime_type('image/png'), 'png not returned as default extension for "image/png"');
        $this->assertFalse(wp_get_default_extension_for_mime_type('wibble/wobble'), 'false not returned for unrecognized mime type');
        $this->assertFalse(wp_get_default_extension_for_mime_type(''), 'false not returned when empty string as mime type supplied');
        $this->assertFalse(wp_get_default_extension_for_mime_type('   '), 'false not returned when empty string as mime type supplied');
        $this->assertFalse(wp_get_default_extension_for_mime_type(123), 'false not returned when int as mime type supplied');
        $this->assertFalse(wp_get_default_extension_for_mime_type(null), 'false not returned when null as mime type supplied');
    }

    /**
     * @ticket 55505
     * @covers ::wp_recursive_ksort
     */
    public function test_wp_recursive_ksort()
    {
        // Create an array to test.
        $theme_json = [
            'version'  => 1,
            'settings' => [
                'typography' => [
                    'fontFamilies' => [
                        'fontFamily' => 'DM Sans, sans-serif',
                        'slug'       => 'dm-sans',
                        'name'       => 'DM Sans',
                    ],
                ],
                'color'      => [
                    'palette' => [
                        [
                            'slug'  => 'foreground',
                            'color' => '#242321',
                            'name'  => 'Foreground',
                        ],
                        [
                            'slug'  => 'background',
                            'color' => '#FCFBF8',
                            'name'  => 'Background',
                        ],
                        [
                            'slug'  => 'primary',
                            'color' => '#71706E',
                            'name'  => 'Primary',
                        ],
                        [
                            'slug'  => 'tertiary',
                            'color' => '#CFCFCF',
                            'name'  => 'Tertiary',
                        ],
                    ],
                ],
            ],
        ];

        // Sort the array.
        wp_recursive_ksort($theme_json);

        // Expected result.
        $expected_theme_json = [
            'settings' => [
                'color'      => [
                    'palette' => [
                        [
                            'color' => '#242321',
                            'name'  => 'Foreground',
                            'slug'  => 'foreground',
                        ],
                        [
                            'color' => '#FCFBF8',
                            'name'  => 'Background',
                            'slug'  => 'background',
                        ],
                        [
                            'color' => '#71706E',
                            'name'  => 'Primary',
                            'slug'  => 'primary',
                        ],
                        [
                            'color' => '#CFCFCF',
                            'name'  => 'Tertiary',
                            'slug'  => 'tertiary',
                        ],
                    ],
                ],
                'typography' => [
                    'fontFamilies' => [
                        'fontFamily' => 'DM Sans, sans-serif',
                        'name'       => 'DM Sans',
                        'slug'       => 'dm-sans',
                    ],
                ],
            ],
            'version'  => 1,
        ];
        $this->assertSameSetsWithIndex($theme_json, $expected_theme_json);
    }
}
