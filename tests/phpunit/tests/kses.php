<?php

declare(strict_types=1);
/**
 * Some simple test cases for KSES post content filtering
 *
 * @group formatting
 * @group kses
 */
class Tests_Kses extends WP_UnitTestCase
{
    /**
     * @dataProvider data_wp_filter_post_kses_address
     * @ticket 20210
     *
     * @param string $content  Test string for kses.
     * @param string $expected Expected result after passing through kses.
     */
    public function test_wp_filter_post_kses_address($content, $expected)
    {
        global $allowedposttags;

        $this->assertEqualHTML($expected, wp_kses($content, $allowedposttags));
    }

    /**
     * Data provider for test_wp_filter_post_kses_address.
     *
     * @return array[] Arguments {
     *     @type string $content  Test string for kses.
     *     @type string $expected Expected result after passing through kses.
     * }
     */
    public function data_wp_filter_post_kses_address()
    {
        $attributes = [
            'class' => 'classname',
            'id'    => 'id',
            'style' => [
                'color: red;',
                'color: red',
                'color: red; text-align:center',
                'color: red; text-align:center;',
            ],
            'title' => 'title',
        ];

        $data = [];

        foreach ($attributes as $name => $values) {
            foreach ((array) $values as $value) {
                $content  = "<address $name='$value'>1 WordPress Avenue, The Internet.</address>";
                $expected = "<address $name='" . str_replace('; ', ';', trim($value, ';')) . "'>1 WordPress Avenue, The Internet.</address>";

                $data[] = [ $content, $expected ];
            }
        }

        return $data;
    }

    /**
     * @dataProvider data_wp_filter_post_kses_a
     * @ticket 20210
     *
     * @param string $content  Test string for kses.
     * @param string $expected Expected result after passing through kses.
     */
    public function test_wp_filter_post_kses_a($content, $expected)
    {
        global $allowedposttags;

        $this->assertEqualHTML($expected, wp_kses($content, $allowedposttags));
    }

    /**
     * Data provider for test_wp_filter_post_kses_a.
     *
     * @return array[] Arguments {
     *     @type string $content  Test string for kses.
     *     @type string $expected Expected result after passing through kses.
     * }
     */
    public function data_wp_filter_post_kses_a()
    {
        $attributes = [
            'class'    => 'classname',
            'id'       => 'id',
            'style'    => 'color: red;',
            'title'    => 'title',
            'href'     => 'http://example.com',
            'rel'      => 'related',
            'rev'      => 'revision',
            'name'     => 'name',
            'target'   => '_blank',
            'download' => '',
        ];

        $data = [];

        foreach ($attributes as $name => $value) {
            if ($value) {
                $attr          = "$name='$value'";
                $expected_attr = "$name='" . trim($value, ';') . "'";
            } else {
                $attr          = $name;
                $expected_attr = $name;
            }
            $content  = "<a $attr>I link this</a>";
            $expected = "<a $expected_attr>I link this</a>";
            $data[]   = [ $content, $expected ];
        }

        return $data;
    }

    /**
     * Test video tag.
     *
     * @ticket 50167
     * @ticket 29826
     * @dataProvider data_wp_kses_video
     *
     * @param string $source   Source HTML.
     * @param string $context  Context to use for parsing source.
     * @param string $expected Expected output following KSES parsing.
     */
    public function test_wp_kses_video($source, $context, $expected)
    {
        $this->assertEqualHTML($expected, wp_kses($source, $context));
    }

    /**
     * Data provider for test_wp_kses_video
     *
     * @return array[] Array containing test data {
     *     @type string $source   Source HTML.
     *     @type string $context  Context to use for parsing source.
     *     @type string $expected Expected output following KSES parsing.
     * }
     */
    public function data_wp_kses_video()
    {
        return [
            // Set 0: Valid post object params in post context.
            [
                '<video src="movie.mov" autoplay controls height=9 loop muted poster="still.gif" playsinline preload width=16 />',
                'post',
                '<video src="movie.mov" autoplay controls height="9" loop muted poster="still.gif" playsinline preload width="16" />',
            ],
            // Set 1: Valid post object params in data context.
            [
                '<video src="movie.mov" autoplay controls height=9 loop muted poster="still.gif" playsinline preload width=16 />',
                'data',
                '',
            ],
            // Set 2: Disallowed urls in post context.
            [
                '<video src="bad://w.org/movie.mov" poster="bad://w.org/movie.jpg" />',
                'post',
                '<video src="//w.org/movie.mov" poster="//w.org/movie.jpg" />',
            ],
            // Set 3: Disallowed attributes in post context.
            [
                '<video onload="alert(1);" src="https://videos.files.wordpress.com/DZEMDKxc/video-0f9c363010.mp4" />',
                'post',
                '<video src="https://videos.files.wordpress.com/DZEMDKxc/video-0f9c363010.mp4" />',
            ],
        ];
    }

    /**
     * @dataProvider data_wp_filter_post_kses_abbr
     * @ticket 20210
     *
     * @param string $content  Test string for kses.
     * @param string $expected Expected result after passing through kses.
     */
    public function test_wp_filter_post_kses_abbr($content, $expected)
    {
        global $allowedposttags;

        $this->assertEqualHTML($expected, wp_kses($content, $allowedposttags));
    }

    /**
     * Data provider for data_wp_filter_post_kses_abbr.
     *
     * @return array[] Arguments {
     *     @type string $content  Test string for kses.
     *     @type string $expected Expected result after passing through kses.
     * }
     */
    public function data_wp_filter_post_kses_abbr()
    {
        $attributes = [
            'class' => 'classname',
            'id'    => 'id',
            'style' => 'color: red;',
            'title' => 'title',
        ];

        $data = [];

        foreach ($attributes as $name => $value) {
            $content  = "<abbr $name='$value'>WP</abbr>";
            $expected = "<abbr $name='" . trim($value, ';') . "'>WP</abbr>";
            $data[]   = [ $content, $expected ];
        }

        return $data;
    }

    public function test_feed_links()
    {
        global $allowedposttags;

        $content = <<<EOF
<a href="feed:javascript:alert(1)">CLICK ME</a>
<a href="feed:javascript:feed:alert(1)">CLICK ME</a>
<a href="feed:feed:javascript:alert(1)">CLICK ME</a>
<a href="javascript:feed:alert(1)">CLICK ME</a>
<a href="javascript:feed:javascript:alert(1)">CLICK ME</a>
<a href="feed:feed:feed:javascript:alert(1)">CLICK ME</a>
<a href="feed:feed:feed:feed:javascript:alert(1)">CLICK ME</a>
<a href="feed:feed:feed:feed:feed:javascript:alert(1)">CLICK ME</a>
<a href="feed:javascript:feed:javascript:feed:javascript:alert(1)">CLICK ME</a>
<a href="feed:javascript:feed:javascript:feed:javascript:feed:javascript:feed:javascript:alert(1)">CLICK ME</a>
<a href="feed:feed:feed:http:alert(1)">CLICK ME</a>
EOF;

        $expected = <<<EOF
<a href="feed:alert(1)">CLICK ME</a>
<a href="feed:feed:alert(1)">CLICK ME</a>
<a href="feed:feed:alert(1)">CLICK ME</a>
<a href="feed:alert(1)">CLICK ME</a>
<a href="feed:alert(1)">CLICK ME</a>
<a href="">CLICK ME</a>
<a href="">CLICK ME</a>
<a href="">CLICK ME</a>
<a href="">CLICK ME</a>
<a href="">CLICK ME</a>
<a href="">CLICK ME</a>
EOF;

        $this->assertEqualHTML($expected, wp_kses($content, $allowedposttags));
    }

    public function test_wp_kses_bad_protocol()
    {
        $bad = [
            'dummy:alert(1)',
            'javascript:alert(1)',
            'JaVaScRiPt:alert(1)',
            'javascript:alert(1);',
            'javascript&#58;alert(1);',
            'javascript&#0058;alert(1);',
            'javascript&#0000058alert(1);',
            'javascript&#x3A;alert(1);',
            'javascript&#X3A;alert(1);',
            'javascript&#X3a;alert(1);',
            'javascript&#x3a;alert(1);',
            'javascript&#x003a;alert(1);',
            '&#x6A&#x61&#x76&#x61&#x73&#x63&#x72&#x69&#x70&#x74&#x3A&#x61&#x6C&#x65&#x72&#x74&#x28&#x27&#x58&#x53&#x53&#x27&#x29',
            'jav	ascript:alert(1);',
            'jav&#x09;ascript:alert(1);',
            'jav&#x0A;ascript:alert(1);',
            'jav&#x0D;ascript:alert(1);',
            ' &#14;  javascript:alert(1);',
            'javascript:javascript:alert(1);',
            'javascript&#58;javascript:alert(1);',
            'javascript&#0000058javascript:alert(1);',
            'javascript:javascript&#58;alert(1);',
            'javascript:javascript&#0000058alert(1);',
            'javascript&#0000058alert(1)//?:',
            'feed:javascript:alert(1)',
            'feed:javascript:feed:javascript:feed:javascript:alert(1)',
            'javascript&#58alert(1)',
            'javascript&#x3ax=1;alert(1)',
        ];
        foreach ($bad as $k => $x) {
            $result = wp_kses_bad_protocol(wp_kses_normalize_entities($x), wp_allowed_protocols());
            if (! empty($result) && 'alert(1);' !== $result && 'alert(1)' !== $result) {
                switch ($k) {
                    case 6:
                        $this->assertSame('javascript&amp;#0000058alert(1);', $result);
                        break;
                    case 12:
                        $this->assertSame(str_replace('&', '&amp;', $x), $result);
                        break;
                    case 22:
                        $this->assertSame('javascript&amp;#0000058alert(1);', $result);
                        break;
                    case 23:
                        $this->assertSame('javascript&amp;#0000058alert(1)//?:', $result);
                        break;
                    case 24:
                        $this->assertSame('feed:alert(1)', $result);
                        break;
                    case 26:
                        $this->assertSame('javascript&amp;#58alert(1)', $result);
                        break;
                    case 27:
                        $this->assertSame('javascript&amp;#x3ax=1;alert(1)', $result);
                        break;
                    default:
                        $this->fail("wp_kses_bad_protocol failed on $k, $x. Result: $result");
                }
            }
        }

        $bad_not_normalized = [
            'dummy&colon;alert(1)',
            'javascript&colon;alert(1)',
            'javascript&CoLon;alert(1)',
            'javascript&COLON;alert(1);',
            'javascript&#58;alert(1);',
            'javascript&#0058;alert(1);',
            'javascript&#0000058alert(1);',
            'jav	ascript&COLON;alert(1);',
            'javascript&#58;javascript&colon;alert(1);',
            'javascript&#58;javascript&colon;alert(1);',
            'javascript&#0000058javascript&colon;alert(1);',
            'javascript&#58;javascript&#0000058alert(1);',
            'javascript&#58alert(1)',
        ];
        foreach ($bad_not_normalized as $k => $x) {
            $result = wp_kses_bad_protocol($x, wp_allowed_protocols());
            if (! empty($result) && 'alert(1);' !== $result && 'alert(1)' !== $result) {
                $this->fail("wp_kses_bad_protocol failed on $k, $x. Result: $result");
            }
        }

        $safe = [
            'dummy:alert(1)',
            'HTTP://example.org/',
            'http://example.org/',
            'http&#58;//example.org/',
            'http&#x3A;//example.org/',
            'https://example.org',
            'http://example.org/wp-admin/post.php?post=2&amp;action=edit',
            'http://example.org/index.php?test=&#039;blah&#039;',
        ];
        foreach ($safe as $x) {
            $result = wp_kses_bad_protocol(wp_kses_normalize_entities($x), [ 'http', 'https', 'dummy' ]);
            if ($result !== $x && 'http://example.org/' !== $result) {
                $this->fail("wp_kses_bad_protocol incorrectly blocked $x");
            }
        }
    }

    public function test_hackers_attacks()
    {
        $xss = simplexml_load_file(DIR_TESTDATA . '/formatting/xssAttacks.xml');
        foreach ($xss->attack as $attack) {
            if (in_array((string) $attack->name, [ 'IMG Embedded commands 2', 'US-ASCII encoding', 'OBJECT w/Flash 2', 'Character Encoding Example' ], true)) {
                continue;
            }

            $code = (string) $attack->code;

            if ('See Below' === $code) {
                continue;
            }

            if (substr($code, 0, 4) === 'perl') {
                $pos  = strpos($code, '"') + 1;
                $code = substr($code, $pos, strrpos($code, '"') - $pos);
                $code = str_replace('\0', "\0", $code);
            }

            $result = trim(wp_kses_data($code));

            if (in_array($result, [ '', 'XSS', 'alert("XSS");', "alert('XSS');" ], true)) {
                continue;
            }

            switch ($attack->name) {
                case 'XSS Locator':
                    $this->assertSame('\';alert(String.fromCharCode(88,83,83))//\\\';alert(String.fromCharCode(88,83,83))//";alert(String.fromCharCode(88,83,83))//\\";alert(String.fromCharCode(88,83,83))//--&gt;"&gt;\'&gt;alert(String.fromCharCode(88,83,83))=&amp;{}', $result);
                    break;
                case 'XSS Quick Test':
                    $this->assertSame('\'\';!--"=&amp;{()}', $result);
                    break;
                case 'SCRIPT w/Alert()':
                    $this->assertSame("alert('XSS')", $result);
                    break;
                case 'SCRIPT w/Char Code':
                    $this->assertSame('alert(String.fromCharCode(88,83,83))', $result);
                    break;
                case 'IMG STYLE w/expression':
                    $this->assertSame('exp/*', $result);
                    break;
                case 'List-style-image':
                    $this->assertSame('li {list-style-image: url("javascript:alert(\'XSS\')");}XSS', $result);
                    break;
                case 'STYLE':
                    $this->assertSame("alert('XSS');", $result);
                    break;
                case 'STYLE w/background-image':
                    $this->assertSame('.XSS{background-image:url("javascript:alert(\'XSS\')");}<A></A>', $result);
                    break;
                case 'STYLE w/background':
                    $this->assertSame('BODY{background:url("javascript:alert(\'XSS\')")}', $result);
                    break;
                case 'Remote Stylesheet 2':
                    $this->assertSame("@import'http://ha.ckers.org/xss.css';", $result);
                    break;
                case 'Remote Stylesheet 3':
                    $this->assertSame('&lt;META HTTP-EQUIV=&quot;Link&quot; Content=&quot;; REL=stylesheet"&gt;', $result);
                    break;
                case 'Remote Stylesheet 4':
                    $this->assertSame('BODY{-moz-binding:url("http://ha.ckers.org/xssmoz.xml#xss")}', $result);
                    break;
                case 'XML data island w/CDATA':
                    $this->assertSame('&lt;![CDATA[]]&gt;', $result);
                    break;
                case 'XML data island w/comment':
                    $this->assertSame("<I><B>&lt;IMG SRC=&quot;javas<!-- -->cript:alert('XSS')\"&gt;</B></I>", $result);
                    break;
                case 'XML HTML+TIME':
                    $this->assertSame('&lt;t:set attributeName=&quot;innerHTML&quot; to=&quot;XSSalert(\'XSS\')"&gt;', $result);
                    break;
                case 'Commented-out Block':
                    $this->assertSame("<!--[if gte IE 4]&gt;-->\nalert('XSS');", $result);
                    break;
                case 'Cookie Manipulation':
                    $this->assertSame('&lt;META HTTP-EQUIV=&quot;Set-Cookie&quot; Content=&quot;USERID=alert(\'XSS\')"&gt;', $result);
                    break;
                case 'SSI':
                    $this->assertSame('&lt;!--#exec cmd=&quot;/bin/echo &#039;<!--#exec cmd="/bin/echo \'=http://ha.ckers.org/xss.js&gt;\'"-->', $result);
                    break;
                case 'PHP':
                    $this->assertSame('&lt;? echo(&#039;alert("XSS")\'); ?&gt;', $result);
                    break;
                case 'UTF-7 Encoding':
                    $this->assertSame('+ADw-SCRIPT+AD4-alert(\'XSS\');+ADw-/SCRIPT+AD4-', $result);
                    break;
                case 'Escaping JavaScript escapes':
                    $this->assertSame('\";alert(\'XSS\');//', $result);
                    break;
                case 'STYLE w/broken up JavaScript':
                    $this->assertSame('@im\port\'\ja\vasc\ript:alert("XSS")\';', $result);
                    break;
                case 'Null Chars 2':
                    $this->assertSame('&amp;alert("XSS")', $result);
                    break;
                case 'No Closing Script Tag':
                    $this->assertSame('&lt;SCRIPT SRC=http://ha.ckers.org/xss.js', $result);
                    break;
                case 'Half-Open HTML/JavaScript':
                    $this->assertSame('&lt;IMG SRC=&quot;javascript:alert(&#039;XSS&#039;)&quot;', $result);
                    break;
                case 'Double open angle brackets':
                    $this->assertSame('&lt;IFRAME SRC=http://ha.ckers.org/scriptlet.html &lt;', $result);
                    break;
                case 'Extraneous Open Brackets':
                    $this->assertSame('&lt;alert("XSS");//&lt;', $result);
                    break;
                case 'Malformed IMG Tags':
                    $this->assertSame('alert("XSS")"&gt;', $result);
                    break;
                case 'No Quotes/Semicolons':
                    $this->assertSame("a=/XSS/\nalert(a.source)", $result);
                    break;
                case 'Evade Regex Filter 1':
                    $this->assertSame('" SRC="http://ha.ckers.org/xss.js"&gt;', $result);
                    break;
                case 'Evade Regex Filter 4':
                    $this->assertSame('\'" SRC="http://ha.ckers.org/xss.js"&gt;', $result);
                    break;
                case 'Evade Regex Filter 5':
                    $this->assertSame('` SRC="http://ha.ckers.org/xss.js"&gt;', $result);
                    break;
                case 'Filter Evasion 1':
                    $this->assertSame('document.write("&lt;SCRI&quot;);PT SRC="http://ha.ckers.org/xss.js"&gt;', $result);
                    break;
                case 'Filter Evasion 2':
                    $this->assertSame('\'&gt;" SRC="http://ha.ckers.org/xss.js"&gt;', $result);
                    break;
                default:
                    $this->fail('KSES failed on ' . $attack->name . ': ' . $result);
            }
        }
    }

    public function wp_kses_allowed_html_filter($html, $context)
    {
        if ('post' === $context) {
            return [ 'a' => [ 'href' => true ] ];
        } else {
            return [ 'a' => [ 'href' => false ] ];
        }
    }

    /**
     * @ticket 20210
     */
    public function test_wp_kses_allowed_html()
    {
        global $allowedposttags, $allowedtags, $allowedentitynames;

        $this->assertSame($allowedposttags, wp_kses_allowed_html('post'));

        $tags = wp_kses_allowed_html('post');

        $this->assertNotEmpty($tags);

        foreach ($tags as $tag) {
            $this->assertTrue($tag['class']);
            $this->assertTrue($tag['dir']);
            $this->assertTrue($tag['id']);
            $this->assertTrue($tag['lang']);
            $this->assertTrue($tag['style']);
            $this->assertTrue($tag['title']);
            $this->assertTrue($tag['xml:lang']);
        }

        $this->assertSame($allowedtags, wp_kses_allowed_html('data'));
        $this->assertSame($allowedtags, wp_kses_allowed_html(''));
        $this->assertSame($allowedtags, wp_kses_allowed_html());

        $tags = wp_kses_allowed_html('user_description');
        $this->assertTrue($tags['a']['rel']);

        $tags = wp_kses_allowed_html();
        $this->assertArrayNotHasKey('rel', $tags['a']);

        $this->assertSame([], wp_kses_allowed_html('strip'));

        $custom_tags = [
            'a' => [
                'href'   => true,
                'rel'    => true,
                'rev'    => true,
                'name'   => true,
                'target' => true,
            ],
        ];

        $this->assertSame($custom_tags, wp_kses_allowed_html($custom_tags));

        add_filter('wp_kses_allowed_html', [ $this, 'wp_kses_allowed_html_filter' ], 10, 2);

        $this->assertSame([ 'a' => [ 'href' => true ] ], wp_kses_allowed_html('post'));
        $this->assertSame([ 'a' => [ 'href' => false ] ], wp_kses_allowed_html('data'));

        remove_filter('wp_kses_allowed_html', [ $this, 'wp_kses_allowed_html_filter' ]);
        $this->assertSame($allowedposttags, wp_kses_allowed_html('post'));
        $this->assertSame($allowedtags, wp_kses_allowed_html('data'));
    }

    public function test_hyphenated_tag()
    {
        $content     = '<hyphenated-tag attribute="value" otherattribute="value2">Alot of hyphens.</hyphenated-tag>';
        $custom_tags = [
            'hyphenated-tag' => [
                'attribute' => true,
            ],
        ];

        $expect_stripped_content = 'Alot of hyphens.';
        $expect_valid_content    = '<hyphenated-tag attribute="value">Alot of hyphens.</hyphenated-tag>';

        $this->assertEqualHTML($expect_stripped_content, wp_kses_post($content));
        $this->assertEqualHTML($expect_valid_content, wp_kses($content, $custom_tags));
    }

    /**
     * Data provider.
     */
    public static function data_normalize_entities(): array
    {
        return [
            /**
             * These examples are from the wp_kses_normalize_entities function description.
             */
            'AT&T'                               => [ 'AT&T', 'AT&amp;T' ],
            '&#00058;'                           => [ '&#00058;', '&#058;' ],
            '&#XYZZY;'                           => [ '&#XYZZY;', '&amp;#XYZZY;' ],

            'Named ref &amp;'                    => [ '&spades;', '&spades;' ],
            'Named ref &AMP;'                    => [ '&spades;', '&spades;' ],
            'Named ref &spades;'                 => [ '&spades;', '&spades;' ],
            'Named ref &sup1;'                   => [ '&sup1;', '&sup1;' ],
            'Named ref &sup2;'                   => [ '&sup2;', '&sup2;' ],
            'Named ref &sup3;'                   => [ '&sup3;', '&sup3;' ],
            'Named ref &frac14;'                 => [ '&frac14;', '&frac14;' ],
            'Named ref &frac12;'                 => [ '&frac12;', '&frac12;' ],
            'Named ref &frac34;'                 => [ '&frac34;', '&frac34;' ],
            'Named ref &there4;'                 => [ '&there4;', '&there4;' ],

            'Decimal ref &#9; ( )'               => [ '&#9;', '&#009;' ],
            'Decimal ref &#34; (")'              => [ '&#34;', '&#034;' ],
            'Decimal ref &#0034; (")'            => [ '&#0034;', '&#034;' ],
            'Decimal ref &#38; (&)'              => [ '&#38;', '&#038;' ],
            "Decimal ref &#39; (')"              => [ '&#39;', '&#039;' ],
            'Decimal ref &#128525; (😍)'          => [ '&#128525;', '&#128525;' ],
            'Decimal ref &#00128525; (😍)'        => [ '&#00128525;', '&#128525;' ],

            'Hex ref &#x9; ( )'                  => [ '&#x9;', '&#x9;' ],
            'Hex ref &#x22; (")'                 => [ '&#x22;', '&#x22;' ],
            'Hex ref &#x0022; (")'               => [ '&#x0022;', '&#x22;' ],
            'Hex ref &#x26; (&)'                 => [ '&#x26;', '&#x26;' ],
            "Hex ref &#x27; (')"                 => [ '&#x27;', '&#x27;' ],
            'Hex ref &#x1f60d; (😍)'              => [ '&#x1f60d;', '&#x1f60d;' ],
            'Hex ref &#x001f60d; (😍)'            => [ '&#x001f60d;', '&#x1f60d;' ],

            'HEX REF &#X22; (")'                 => [ '&#X22;', '&#x22;' ],
            'HEX REF &#X26; (&)'                 => [ '&#X26;', '&#x26;' ],
            "HEX REF &#X27; (')"                 => [ '&#X27;', '&#x27;' ],
            'HEX REF &#X1F60D; (😍)'              => [ '&#X1F60D;', '&#x1F60D;' ],

            'Encoded named ref &amp;amp;'        => [ '&amp;amp;', '&amp;amp;' ],
            'Encoded named ref &#38;amp;'        => [ '&#38;amp;', '&#038;amp;' ],
            'Encoded named ref &#x26;amp;'       => [ '&#x26;amp;', '&#x26;amp;' ],
            'Encoded numeric ref &amp;#39;'      => [ '&amp;#39;', '&amp;#39;' ],
            'Encoded numeric ref &#38;#39;'      => [ '&#38;#39;', '&#038;#39;' ],
            'Encoded numeric ref &#x26;#39;'     => [ '&#x26;#39;', '&#x26;#39;' ],
            'Encoded hex ref &amp;#x27;'         => [ '&amp;#x27;', '&amp;#x27;' ],
            'Encoded hex ref &#38;#x27;'         => [ '&#38;#x27;', '&#038;#x27;' ],
            'Encoded hex ref &#x26;#x27;'        => [ '&#x26;#x27;', '&#x26;#x27;' ],

            /*
             * The codepoint value here is outside of the valid unicode range whose
             * maximum is 0x10FFFF or 1114111.
             */
            'Invalid decimal unicode &#1114112;' => [ '&#1114112;', '&amp;#1114112;' ],
            'Invalid hex unicode &#x110000;'     => [ '&#x110000;', '&amp;#x110000;' ],
        ];
    }

    /**
     * @ticket 26290
     * @ticket 63630
     *
     * @dataProvider data_normalize_entities
     */
    public function test_wp_kses_normalize_entities(string $input, string $expected)
    {
        $this->assertEqualHTML($expected, wp_kses_normalize_entities($input));
    }

    /**
     * Test removal of invalid binary data for HTML.
     *
     * @ticket 28506
     * @dataProvider data_ctrl_removal
     */
    public function test_ctrl_removal($content, $expected)
    {
        global $allowedposttags;

        return $this->assertEqualHTML($expected, wp_kses($content, $allowedposttags));
    }

    public function data_ctrl_removal()
    {
        return [
            [
                "\x00\x01\x02\x03\x04\x05\x06\x07\x08\x0B\x0C\x0E\x0F\x10\x11\x12\x13\x14\x15\x16\x17\x18\x19\x1A\x1B\X1C\x1D\x1E\x1F",
                '',
            ],
            [
                "\x00h\x01e\x02l\x03l\x04o\x05 \x06w\x07o\x08r\x0Bl\x0Cd\x0E.\x0F \x10W\x11O\x12R\x13D\x14P\x15R\x16E\x17S\x18S\x19 \x1AK\x1BS\X1CE\x1DS\x1E.\x1F/",
                'hello world. WORDPRESS KSES./',
            ],
            [
                "\x1F\x1E\x1D\x1C\x1B\x1A\x19\x18\x17\x16\x15\x14\x13\x12\x11\x10\x0F\x0E\x0C\x0B\x08\x07\x06\x05\x04\X03\x02\x01\x00",
                '',
            ],
            [
                "\x1Fh\x1Ee\x1Dl\x1Cl\x1Bo\x1A \x19w\x18o\x17r\x16l\x15d\x14.\x13 \x12W\x11O\x10R\x0FD\x0EP\x0CR\x0BE\x08S\x07S\x06 \x05K\x04S\X03E\x02S\x01.\x00/",
                'hello world. WORDPRESS KSES./',
            ],
            [
                "\t\r\n word \n\r\t",
                "\t\r\n word \n\r\t",
            ],
        ];
    }

    /**
     * Test removal of '\0' strings.
     *
     * @ticket 28699
     * @dataProvider data_slash_zero_removal
     */
    public function test_slash_zero_removal($content, $expected)
    {
        global $allowedposttags;

        return $this->assertEqualHTML($expected, wp_kses($content, $allowedposttags));
    }

    public function data_slash_zero_removal()
    {
        return [
            [
                'This \\0 should be no big deal.',
                'This \\0 should be no big deal.',
            ],
            [
                '<div>This \\0 should be no big deal.</div>',
                '<div>This \\0 should be no big deal.</div>',
            ],
            [
                '<div align="\\0left">This should be no big deal.</div>',
                '<div align="\\0left">This should be no big deal.</div>',
            ],
            [
                'This <div style="float:\\0left"> is more of a concern.',
                'This <div style="float:left"> is more of a concern.',
            ],
            [
                'This <div style="float:\\0\\0left"> is more of a concern.',
                'This <div style="float:left"> is more of a concern.',
            ],
            [
                'This <div style="float:\\\\00left"> is more of a concern.',
                'This <div style="float:left"> is more of a concern.',
            ],
            [
                'This <div style="float:\\\\\\\\0000left"> is more of a concern.',
                'This <div style="float:left"> is more of a concern.',
            ],
            [
                'This <div style="float:\\0000left"> is more of a concern.',
                'This <div style="float:left"> is more of a concern.',
            ],
            [
                '<style type="text/css">div {background-image:\\0}</style>',
                'div {background-image:\\0}',
            ],
        ];
    }

    /**
     * Test new function wp_kses_hair_parse().
     *
     * @dataProvider data_hair_parse
     */
    public function test_hair_parse($input, $output)
    {
        return $this->assertSame($output, wp_kses_hair_parse($input));
    }

    public function data_hair_parse()
    {
        return [
            [
                'title="hello" href="#" id="my_id" ',
                [ 'title="hello" ', 'href="#" ', 'id="my_id" ' ],
            ],
            [
                '[shortcode attr="value"] href="http://www.google.com/"title="moo"disabled',
                [ '[shortcode attr="value"] ', 'href="http://www.google.com/"', 'title="moo"', 'disabled' ],
            ],
            [
                '',
                [],
            ],
            [
                'a',
                [ 'a' ],
            ],
            [
                'title="hello"disabled href=# id=\'my_id\'',
                [ 'title="hello"', 'disabled ', 'href=# ', "id='my_id'" ],
            ],
            [
                '     ', // Calling function is expected to strip leading whitespace.
                false,
            ],
            [
                'abcd=abcd"abcd"',
                false,
            ],
            [
                "array[1]='z'z'z'z",
                false,
            ],
            // Using a digit in attribute name should work.
            [
                'href="https://example.com/[shortcode attr=\'value\']" data-op3-timer-seconds="0"',
                [ 'href="https://example.com/[shortcode attr=\'value\']" ', 'data-op3-timer-seconds="0"' ],
            ],
            // Using an underscore in attribute name should work.
            [
                'href="https://example.com/[shortcode attr=\'value\']" data-op_timer-seconds="0"',
                [ 'href="https://example.com/[shortcode attr=\'value\']" ', 'data-op_timer-seconds="0"' ],
            ],
            // Using a period in attribute name should work.
            [
                'href="https://example.com/[shortcode attr=\'value\']" data-op.timer-seconds="0"',
                [ 'href="https://example.com/[shortcode attr=\'value\']" ', 'data-op.timer-seconds="0"' ],
            ],
            // Using a digit at the beginning of attribute name should return false.
            [
                'href="https://example.com/[shortcode attr=\'value\']" 3data-op-timer-seconds="0"',
                false,
            ],
        ];
    }

    /**
     * Test new function wp_kses_attr_parse().
     *
     * @dataProvider data_attr_parse
     */
    public function test_attr_parse($input, $output)
    {
        return $this->assertSame($output, wp_kses_attr_parse($input));
    }

    public function data_attr_parse()
    {
        return [
            [
                '<a title="hello" href="#" id="my_id" >',
                [ '<a ', 'title="hello" ', 'href="#" ', 'id="my_id" ', '>' ],
            ],
            [
                '<a [shortcode attr="value"] href="http://www.google.com/"title="moo"disabled>',
                [ '<a ', '[shortcode attr="value"] ', 'href="http://www.google.com/"', 'title="moo"', 'disabled', '>' ],
            ],
            [
                '',
                false,
            ],
            [
                'a',
                false,
            ],
            [
                '<a>',
                [ '<a', '>' ],
            ],
            [
                '<a%%&&**>',
                false,
            ],
            [
                '<a title="hello"disabled href=# id=\'my_id\'>',
                [ '<a ', 'title="hello"', 'disabled ', 'href=# ', "id='my_id'", '>' ],
            ],
            [
                '<a     >',
                [ '<a     ', '>' ],
            ],
            [
                '<a abcd=abcd"abcd">',
                false,
            ],
            [
                "<a array[1]='z'z'z'z>",
                false,
            ],
            [
                '<img title="hello" src="#" id="my_id" />',
                [ '<img ', 'title="hello" ', 'src="#" ', 'id="my_id"', ' />' ],
            ],
        ];
    }

    /**
     * Test new function wp_kses_one_attr().
     *
     * @dataProvider data_one_attr
     */
    public function test_one_attr($element, $input, $output)
    {
        return $this->assertSame($output, wp_kses_one_attr($input, $element));
    }

    public function data_one_attr()
    {
        return [
            [
                'a',
                ' title="hello" ',
                ' title="hello" ',
            ],
            [
                'a',
                'title  =  "hello"',
                'title="hello"',
            ],
            [
                'a',
                "title='hello'",
                "title='hello'",
            ],
            [
                'a',
                'title=hello',
                'title="hello"',
            ],
            [
                'a',
                'href="javascript:alert(1)"',
                'href="alert(1)"',
            ],
            [
                'a',
                'style ="style "',
                'style="style"',
            ],
            [
                'a',
                'style="style "',
                'style="style"',
            ],
            [
                'a',
                'style ="style ="',
                '',
            ],
            [
                'img',
                'src="mypic.jpg"',
                'src="mypic.jpg"',
            ],
            [
                'img',
                'loading="lazy"',
                'loading="lazy"',
            ],
            [
                'img',
                'onerror=alert(1)',
                '',
            ],
            [
                'img',
                'title=>',
                'title="&gt;"',
            ],
            [
                'img',
                'title="&garbage";"',
                'title="&amp;garbage&quot;;"',
            ],
        ];
    }

    /**
     * @ticket 34063
     */
    public function test_bdo_tag_allowed()
    {
        global $allowedposttags;

        $content = '<p>This is <bdo dir="rtl">a BDO tag</bdo>. Weird, <bdo dir="ltr">right?</bdo></p>';

        $this->assertEqualHTML($content, wp_kses($content, $allowedposttags));
    }

    /**
     * @ticket 54698
     */
    public function test_ruby_tag_allowed()
    {
        global $allowedposttags;

        $content = '<ruby>✶<rp>: </rp><rt>Star</rt><rp>, </rp><rt lang="fr">Étoile</rt><rp>.</rp></ruby>';

        $this->assertEqualHTML($content, wp_kses($content, $allowedposttags));
    }

    /**
     * @ticket 35079
     */
    public function test_ol_reversed_attribute_allowed()
    {
        global $allowedposttags;

        $content = '<ol reversed="reversed"><li>Item 1</li><li>Item 2</li><li>Item 3</li></ol>';

        $this->assertEqualHTML($content, wp_kses($content, $allowedposttags));
    }

    /**
     * @ticket 40680
     */
    public function test_wp_kses_attr_no_attributes_allowed_with_empty_array()
    {
        $element   = 'foo';
        $attribute = 'title="foo" class="bar"';

        $this->assertEqualHTML("<{$element}>", wp_kses_attr($element, $attribute, [ 'foo' => [] ], []));
    }

    /**
     * @ticket 40680
     */
    public function test_wp_kses_attr_no_attributes_allowed_with_true()
    {
        $element   = 'foo';
        $attribute = 'title="foo" class="bar"';

        $this->assertEqualHTML("<{$element}>", wp_kses_attr($element, $attribute, [ 'foo' => true ], []));
    }

    /**
     * @ticket 40680
     */
    public function test_wp_kses_attr_single_attribute_is_allowed()
    {
        $element   = 'foo';
        $attribute = 'title="foo" class="bar"';

        $this->assertEqualHTML("<{$element} title=\"foo\">", wp_kses_attr($element, $attribute, [ 'foo' => [ 'title' => true ] ], []));
    }

    /**
     * @ticket 43312
     */
    public function test_wp_kses_attr_no_attributes_allowed_with_false()
    {
        $element   = 'foo';
        $attribute = 'title="foo" class="bar"';

        $this->assertEqualHTML("<{$element}>", wp_kses_attr($element, $attribute, [ 'foo' => false ], []));
    }

    /**
     * Testing the safecss_filter_attr() function.
     *
     * @ticket 37248
     * @ticket 42729
     * @ticket 48376
     * @ticket 55966
     * @ticket 56122
     * @ticket 58551
     * @ticket 60132
     * @ticket 64414
     *
     * @dataProvider data_safecss_filter_attr
     *
     * @param string $css      A string of CSS rules.
     * @param string $expected Expected string of CSS rules.
     */
    public function test_safecss_filter_attr($css, $expected)
    {
        $this->assertSame($expected, safecss_filter_attr($css));
    }

    /**
     * Data provider for test_safecss_filter_attr().
     *
     * @return array {
     *     @type array {
     *         @type string $css      A string of CSS rules.
     *         @type string $expected Expected string of CSS rules.
     *     }
     * }
     */
    public function data_safecss_filter_attr()
    {
        return [
            // Empty input, empty output.
            [
                'css'      => '',
                'expected' => '',
            ],
            // An arbitrary attribute name isn't allowed.
            [
                'css'      => 'foo:bar',
                'expected' => '',
            ],
            // A single attribute name, with a single value.
            [
                'css'      => 'margin-top: 2px',
                'expected' => 'margin-top: 2px',
            ],
            // Backslash \ isn't supported.
            [
                'css'      => 'margin-top: \2px',
                'expected' => '',
            ],
            // Curly bracket } isn't supported.
            [
                'css'      => 'margin-bottom: 2px}',
                'expected' => '',
            ],
            // A single attribute name, with a single text value.
            [
                'css'      => 'text-transform: uppercase',
                'expected' => 'text-transform: uppercase',
            ],
            // Only lowercase attribute names are supported.
            [
                'css'      => 'Text-transform: capitalize',
                'expected' => '',
            ],
            // Uppercase attribute values goes through.
            [
                'css'      => 'text-transform: None',
                'expected' => 'text-transform: None',
            ],
            // A single attribute, with multiple values.
            [
                'css'      => 'font: bold 15px arial, sans-serif',
                'expected' => 'font: bold 15px arial, sans-serif',
            ],
            // Multiple attributes, with single values.
            [
                'css'      => 'font-weight: bold;font-size: 15px',
                'expected' => 'font-weight: bold;font-size: 15px',
            ],
            // Multiple attributes, separated by a space.
            [
                'css'      => 'font-weight: bold; font-size: 15px',
                'expected' => 'font-weight: bold;font-size: 15px',
            ],
            // Multiple attributes, with multiple values.
            [
                'css'      => 'margin: 10px 20px;padding: 5px 10px',
                'expected' => 'margin: 10px 20px;padding: 5px 10px',
            ],
            // Parenthesis ( is supported for some attributes.
            [
                'css'      => 'background: green url("foo.jpg") no-repeat fixed center',
                'expected' => 'background: green url("foo.jpg") no-repeat fixed center',
            ],
            // Additional background attributes introduced in 5.3.
            [
                'css'      => 'background-size: cover;background-size: 200px 100px;background-attachment: local, scroll;background-blend-mode: hard-light',
                'expected' => 'background-size: cover;background-size: 200px 100px;background-attachment: local, scroll;background-blend-mode: hard-light',
            ],
            // `border-radius` attribute introduced in 5.3.
            [
                'css'      => 'border-radius: 10% 30% 50% 70%;border-radius: 30px',
                'expected' => 'border-radius: 10% 30% 50% 70%;border-radius: 30px',
            ],
            // `flex` and related attributes introduced in 5.3.
            [
                'css'      => 'flex: 0 1 auto;flex-basis: 75%;flex-direction: row-reverse;flex-flow: row-reverse nowrap;flex-grow: 2;flex-shrink: 1;flex-wrap: nowrap',
                'expected' => 'flex: 0 1 auto;flex-basis: 75%;flex-direction: row-reverse;flex-flow: row-reverse nowrap;flex-grow: 2;flex-shrink: 1;flex-wrap: nowrap',
            ],
            // `grid` and related attributes introduced in 5.3.
            [
                'css'      => 'grid-template-columns: 1fr 60px;grid-auto-columns: min-content;grid-column-start: span 2;grid-column-end: -1;grid-column-gap: 10%;grid-gap: 10px 20px',
                'expected' => 'grid-template-columns: 1fr 60px;grid-auto-columns: min-content;grid-column-start: span 2;grid-column-end: -1;grid-column-gap: 10%;grid-gap: 10px 20px',
            ],
            [
                'css'      => 'grid-template-rows: 40px 4em 40px;grid-auto-rows: min-content;grid-row-start: -1;grid-row-end: 3;grid-row-gap: 1em',
                'expected' => 'grid-template-rows: 40px 4em 40px;grid-auto-rows: min-content;grid-row-start: -1;grid-row-end: 3;grid-row-gap: 1em',
            ],
            // `grid` does not yet support `\`.
            [
                'css'      => 'grid-template: 1em / 20% 20px 1fr',
                'expected' => '',
            ],
            // `flex` and `grid` alignments introduced in 5.3.
            [
                'css'      => 'align-content: space-between;align-items: start;align-self: center;justify-items: center;justify-content: space-between;justify-self: end',
                'expected' => 'align-content: space-between;align-items: start;align-self: center;justify-items: center;justify-content: space-between;justify-self: end',
            ],
            // `columns` and related attributes introduced in 5.3.
            [
                'css'      => 'columns: 6rem auto;column-count: 4;column-fill: balance;column-gap: 9px;column-rule: thick inset blue;column-span: none;column-width: 120px',
                'expected' => 'columns: 6rem auto;column-count: 4;column-fill: balance;column-gap: 9px;column-rule: thick inset blue;column-span: none;column-width: 120px',
            ],
            // Gradients introduced in 5.3.
            [
                'css'      => 'background: linear-gradient(135deg,rgba(6,147,227,1) 0%,rgb(155,81,224) 100%)',
                'expected' => 'background: linear-gradient(135deg,rgba(6,147,227,1) 0%,rgb(155,81,224) 100%)',
            ],
            [
                'css'      => 'background: linear-gradient(135deg,rgba(6,147,227,1) ) (0%,rgb(155,81,224) 100%)',
                'expected' => '',
            ],
            [
                'css'      => 'background-image: linear-gradient(red,yellow);',
                'expected' => 'background-image: linear-gradient(red,yellow)',
            ],
            [
                'css'      => 'color: linear-gradient(red,yellow);',
                'expected' => '',
            ],
            [
                'css'      => 'background-image: linear-gradient(red,yellow); background: prop( red,yellow); width: 100px;',
                'expected' => 'background-image: linear-gradient(red,yellow);width: 100px',
            ],
            [
                'css'      => 'background: unknown-gradient(135deg,rgba(6,147,227,1) 0%,rgb(155,81,224) 100%)',
                'expected' => '',
            ],
            [
                'css'      => 'background: repeating-linear-gradient(135deg,rgba(6,147,227,1) 0%,rgb(155,81,224) 100%)',
                'expected' => 'background: repeating-linear-gradient(135deg,rgba(6,147,227,1) 0%,rgb(155,81,224) 100%)',
            ],
            [
                'css'      => 'width: 100px; height: 100px; background: linear-gradient(135deg,rgba(0,208,132,1) 0%,rgba(6,147,227,1) 100%);',
                'expected' => 'width: 100px;height: 100px;background: linear-gradient(135deg,rgba(0,208,132,1) 0%,rgba(6,147,227,1) 100%)',
            ],
            [
                'css'      => 'background: radial-gradient(#ff0, red, yellow, green, rgba(6,147,227,1), rgb(155,81,224) 90%);',
                'expected' => 'background: radial-gradient(#ff0, red, yellow, green, rgba(6,147,227,1), rgb(155,81,224) 90%)',
            ],
            [
                'css'      => 'background: radial-gradient(#ff0, red, yellow, green, rgba(6,147,227,1), rgb(155,81,224) 90%);',
                'expected' => 'background: radial-gradient(#ff0, red, yellow, green, rgba(6,147,227,1), rgb(155,81,224) 90%)',
            ],
            [
                'css'      => 'background: conic-gradient(at 0% 30%, red 10%, yellow 30%, #1e90ff 50%)',
                'expected' => 'background: conic-gradient(at 0% 30%, red 10%, yellow 30%, #1e90ff 50%)',
            ],
            // `object-position` introduced in 5.7.1.
            [
                'css'      => 'object-position: right top',
                'expected' => 'object-position: right top',
            ],
            // `object-fit` introduced in 6.1.
            [
                'css'      => 'object-fit: cover',
                'expected' => 'object-fit: cover',
            ],
            // `white-space` introduced in 6.9.0.
            [
                'css'      => 'white-space: nowrap',
                'expected' => 'white-space: nowrap',
            ],
            [
                'css'      => 'white-space: pre',
                'expected' => 'white-space: pre',
            ],
            [
                'css'      => 'white-space: pre-wrap',
                'expected' => 'white-space: pre-wrap',
            ],
            [
                'css'      => 'white-space: pre-line',
                'expected' => 'white-space: pre-line',
            ],
            // Expressions are not allowed.
            [
                'css'      => 'height: expression( body.scrollTop + 50 + "px" )',
                'expected' => '',
            ],
            // RGB color values are not allowed.
            [
                'css'      => 'color: rgb( 100, 100, 100 )',
                'expected' => '',
            ],
            // RGBA color values are not allowed.
            [
                'css'      => 'color: rgb( 100, 100, 100, .4 )',
                'expected' => '',
            ],
            // Allow min().
            [
                'css'      => 'width: min(50%, 400px)',
                'expected' => 'width: min(50%, 400px)',
            ],
            // Allow max().
            [
                'css'      => 'width: max(50%, 40rem)',
                'expected' => 'width: max(50%, 40rem)',
            ],
            // Allow minmax().
            [
                'css'      => 'width: minmax(100px, 50%)',
                'expected' => 'width: minmax(100px, 50%)',
            ],
            // Allow clamp().
            [
                'css'      => 'width: clamp(100px, 50%, 100vw)',
                'expected' => 'width: clamp(100px, 50%, 100vw)',
            ],
            // Allow two functions in the same CSS.
            [
                'css'      => 'width: clamp(min(100px, 350px), 50%, 500px), 600px)',
                'expected' => 'width: clamp(min(100px, 350px), 50%, 500px), 600px)',
            ],
            // Allow gradient() function.
            [
                'css'      => 'background: linear-gradient(90deg, rgba(2,0,36,1) 0%, rgba(9,9,121,1) 35%, rgba(0,212,255,1) 100%)',
                'expected' => 'background: linear-gradient(90deg, rgba(2,0,36,1) 0%, rgba(9,9,121,1) 35%, rgba(0,212,255,1) 100%)',
            ],
            // Combined CSS function names.
            [
                'css'      => 'width: calcmax(100px + 50%)',
                'expected' => '',
            ],
            // Allow calc().
            [
                'css'      => 'width: calc(2em + 3px)',
                'expected' => 'width: calc(2em + 3px)',
            ],
            // Allow calc() with nested brackets.
            [
                'css'      => 'width: calc(3em + (10px * 2))',
                'expected' => 'width: calc(3em + (10px * 2))',
            ],
            // Allow var().
            [
                'css'      => 'padding: var(--wp-var1) var(--wp-var2)',
                'expected' => 'padding: var(--wp-var1) var(--wp-var2)',
            ],
            // Allow var() with fallback (commas).
            [
                'css'      => 'padding: var(--wp-var1, 10px)',
                'expected' => 'padding: var(--wp-var1, 10px)',
            ],
            // Allow var() with fallback (percentage).
            [
                'css'      => 'padding: var(--wp-var1, 50%)',
                'expected' => 'padding: var(--wp-var1, 50%)',
            ],
            // Allow var() with fallback var().
            [
                'css'      => 'background-color: var(--wp-var, var(--wp-var-fallback, pink))',
                'expected' => 'background-color: var(--wp-var, var(--wp-var-fallback, pink))',
            ],
            // Allow var() with square brackets.
            [
                'css'      => 'background-color: var(--wp-var, [pink])',
                'expected' => 'background-color: var(--wp-var, [pink])',
            ],
            // Allow calc() with var().
            [
                'css'      => 'margin-top: calc(var(--wp-var1) * 3 + 2em)',
                'expected' => 'margin-top: calc(var(--wp-var1) * 3 + 2em)',
            ],
            // Malformed min, no closing `)`.
            [
                'css'      => 'width: min(3em + 10px',
                'expected' => '',
            ],
            // Malformed max, no closing `)`.
            [
                'css'      => 'width: max(3em + 10px',
                'expected' => '',
            ],
            // Malformed minmax, no closing `)`.
            [
                'css'      => 'width: minmax(3em + 10px',
                'expected' => '',
            ],
            // Malformed calc, no closing `)`.
            [
                'css'      => 'width: calc(3em + 10px',
                'expected' => '',
            ],
            // Malformed var, no closing `)`.
            [
                'css'      => 'width: var(--wp-var1',
                'expected' => '',
            ],
            // Malformed calc, mismatching brackets.
            [
                'css'      => 'width: calc(3em + (10px * 2)',
                'expected' => '',
            ],
            // Malformed var, mismatching brackets.
            [
                'css'      => 'background-color: var(--wp-var, var(--wp-var-fallback, pink)',
                'expected' => '',
            ],
            // Don't allow expressions outside of a calc().
            [
                'css'      => 'width: (3em + (10px * 2))',
                'expected' => '',
            ],
            // Gap introduced in 6.1.
            [
                'css'      => 'gap: 10px;column-gap: 5px;row-gap: 20px',
                'expected' => 'gap: 10px;column-gap: 5px;row-gap: 20px',
            ],
            // Margin and padding logical properties introduced in 6.1.
            [
                'css'      => 'margin-block-start: 1px;margin-block-end: 2px;margin-inline-start: 3px;margin-inline-end: 4px;',
                'expected' => 'margin-block-start: 1px;margin-block-end: 2px;margin-inline-start: 3px;margin-inline-end: 4px',
            ],
            [
                'css'      => 'padding-block-start: 1px;padding-block-end: 2px;padding-inline-start: 3px;padding-inline-end: 4px;',
                'expected' => 'padding-block-start: 1px;padding-block-end: 2px;padding-inline-start: 3px;padding-inline-end: 4px',
            ],
            // Assigning values to CSS variables introduced in 6.1.
            [
                'css'      => '--wp--medium-width: 100px; --var_with_underscores: #cccccc;',
                'expected' => '--wp--medium-width: 100px;--var_with_underscores: #cccccc',
            ],
            [
                'css'      => '--miXeD-CAse: red; --with-numbers-3_56: red; --with-url-value: url("foo.jpg");',
                'expected' => '--miXeD-CAse: red;--with-numbers-3_56: red;--with-url-value: url("foo.jpg")',
            ],
            [
                'css'      => '--with-gradient: repeating-linear-gradient(135deg,rgba(6,147,227,1) 0%,rgb(155,81,224) 100%);',
                'expected' => '--with-gradient: repeating-linear-gradient(135deg,rgba(6,147,227,1) 0%,rgb(155,81,224) 100%)',
            ],
            [
                'css'      => '--?><.%-not-allowed: red;',
                'expected' => '',
            ],
            // Position properties introduced in 6.2.
            [
                'css'      => 'position: sticky;top: 0;left: 0;right: 0;bottom: 0;z-index: 10;',
                'expected' => 'position: sticky;top: 0;left: 0;right: 0;bottom: 0;z-index: 10',
            ],
            // `aspect-ratio` introduced in 6.2.
            [
                'css'      => 'aspect-ratio: auto;',
                'expected' => 'aspect-ratio: auto',
            ],
            [
                'css'      => 'aspect-ratio: 0.5;',
                'expected' => 'aspect-ratio: 0.5',
            ],
            [
                'css'      => 'aspect-ratio: 1;',
                'expected' => 'aspect-ratio: 1',
            ],
            [
                'css'      => 'aspect-ratio: 16 / 9;',
                'expected' => 'aspect-ratio: 16 / 9',
            ],
            [
                'css'      => 'aspect-ratio: expression( 16 / 9 );',
                'expected' => '',
            ],
            [
                'css'      => 'aspect-ratio: calc( 16 / 9;',
                'expected' => '',
            ],
            [
                'css'      => 'aspect-ratio: calc( 16 / 9 );',
                'expected' => 'aspect-ratio: calc( 16 / 9 )',
            ],
            [
                'css'      => 'aspect-ratio: url( https://wordpress.org/wp-content/uploads/aspect-ratio.jpg );',
                'expected' => '',
            ],
            // URL support for `filter` introduced in 6.3.
            [
                'css'      => 'filter: url( my-file.svg#svg-blur );',
                'expected' => 'filter: url( my-file.svg#svg-blur )',
            ],
            // Support for `repeat` function.
            [
                'css'      => 'grid-template-columns: repeat(4, minmax(0, 1fr))',
                'expected' => 'grid-template-columns: repeat(4, minmax(0, 1fr))',
            ],
            [
                'css'      => 'grid-template-columns: repeat(auto-fill, minmax(min(12rem, 100%), 1fr))',
                'expected' => 'grid-template-columns: repeat(auto-fill, minmax(min(12rem, 100%), 1fr))',
            ],
            // Malformed repeat, no closing `)`.
            [
                'css'      => 'grid-template-columns: repeat(4, minmax(0, 1fr)',
                'expected' => '',
            ],
            // Malformed repeat, contains unsupported function.
            [
                'css'      => 'grid-template-columns: repeat(4, unsupported(0, 1fr)',
                'expected' => '',
            ],
            // `writing-mode` introduced in 6.4.
            [
                'css'      => 'writing-mode: vertical-rl',
                'expected' => 'writing-mode: vertical-rl',
            ],
            // `background-repeat` introduced in 6.5.
            [
                'css'      => 'background-repeat: no-repeat',
                'expected' => 'background-repeat: no-repeat',
            ],
            // `opacity` introduced in 6.7.
            [
                'css'      => 'opacity: 10',
                'expected' => 'opacity: 10',
            ],
            // `display` introduced in 7.0.0.
            [
                'css'      => 'display: none',
                'expected' => 'display: none',
            ],
            [
                'css'      => 'display: block',
                'expected' => 'display: block',
            ],
            [
                'css'      => 'display: inline',
                'expected' => 'display: inline',
            ],
            [
                'css'      => 'display: inline-block',
                'expected' => 'display: inline-block',
            ],
            [
                'css'      => 'display: inline-flex',
                'expected' => 'display: inline-flex',
            ],
            [
                'css'      => 'display: inline-grid',
                'expected' => 'display: inline-grid',
            ],
            [
                'css'      => 'display: table',
                'expected' => 'display: table',
            ],
            [
                'css'      => 'display: flex',
                'expected' => 'display: flex',
            ],
            [
                'css'      => 'display: grid',
                'expected' => 'display: grid',
            ],
        ];
    }

    /**
     * Data attributes are globally accepted.
     *
     * @ticket 33121
     */
    public function test_wp_kses_attr_data_attribute_is_allowed()
    {
        $test     = '<div data-foo="foo" data-bar="bar" datainvalid="gone" data-two-hyphens="remains">Pens and pencils</div>';
        $expected = '<div data-foo="foo" data-bar="bar" data-two-hyphens="remains">Pens and pencils</div>';

        $this->assertEqualHTML($expected, wp_kses_post($test));
    }

    /**
     * Data attributes with leading, trailing, and double "-" are globally accepted.
     *
     * @ticket 61052
     */
    public function test_wp_kses_attr_data_attribute_hypens_allowed()
    {
        $test     = '<div data--leading="remains" data-trailing-="remains" data-middle--double="remains">Pens and pencils</div>';
        $expected = '<div data--leading="remains" data-trailing-="remains" data-middle--double="remains">Pens and pencils</div>';

        $this->assertEqualHTML($expected, wp_kses_post($test));
    }

    /**
     * Ensure wildcard attributes block unprefixed wildcard uses.
     *
     * @ticket 33121
     */
    public function test_wildcard_requires_hyphen_after_prefix()
    {
        $allowed_html = [
            'div' => [
                'data-*' => true,
                'on-*'   => true,
            ],
        ];

        $content  = '<div datamelformed-prefix="gone" data="gone" data-="gone" onclick="alert(1)">Malformed attributes</div>';
        $expected = '<div>Malformed attributes</div>';

        $actual = wp_kses($content, $allowed_html);

        $this->assertEqualHTML($expected, $actual);
    }

    /**
     * Ensure wildcard allows two hyphen.
     *
     * @ticket 33121
     */
    public function test_wildcard_allows_two_hyphens()
    {
        $allowed_html = [
            'div' => [
                'data-*' => true,
            ],
        ];

        $content  = '<div data-wp-id="pens-and-pencils">Well formed attribute</div>';
        $expected = '<div data-wp-id="pens-and-pencils">Well formed attribute</div>';

        $actual = wp_kses($content, $allowed_html);

        $this->assertEqualHTML($expected, $actual);
    }

    /**
     * Ensure wildcard attributes only support valid prefixes.
     *
     * @dataProvider data_wildcard_attribute_prefixes
     *
     * @ticket 33121
     */
    public function test_wildcard_attribute_prefixes($wildcard_attribute, $expected)
    {
        $allowed_html = [
            'div' => [
                $wildcard_attribute => true,
            ],
        ];

        $name  = str_replace('*', strtolower(__FUNCTION__), $wildcard_attribute);
        $value = __FUNCTION__;
        $whole = "{$name}=\"{$value}\"";

        $actual = wp_kses_attr_check($name, $value, $whole, 'n', 'div', $allowed_html);

        $this->assertSame($expected, $actual);
    }

    /**
     * @return array Array of arguments for wildcard testing
     *               [0] The prefix being tested.
     *               [1] The outcome of `wp_kses_attr_check` for the prefix.
     */
    public function data_wildcard_attribute_prefixes()
    {
        return [
            // Ends correctly.
            [ 'data-*', true ],

            // Does not end with trialing `-`.
            [ 'data*', false ],

            // Multiple wildcards.
            [ 'd*ta-*', false ],
            [ 'data**', false ],
        ];
    }

    /**
     * Test URL sanitization in the style tag.
     *
     * @dataProvider data_kses_style_attr_with_url
     *
     * @ticket 45067
     * @ticket 46197
     * @ticket 46498
     *
     * @param $input string The style attribute saved in the editor.
     * @param $expected string The sanitized style attribute.
     */
    public function test_kses_style_attr_with_url($input, $expected)
    {
        $actual = safecss_filter_attr($input);

        $this->assertSame($expected, $actual);
    }

    /**
     * Data provider testing style attribute sanitization.
     *
     * @return array Nested array of input, expected pairs.
     */
    public function data_kses_style_attr_with_url()
    {
        return [
            /*
             * Valid use cases.
             */

            // Double quotes.
            [
                'background-image: url( "http://example.com/valid.gif" );',
                'background-image: url( "http://example.com/valid.gif" )',
            ],

            // Single quotes.
            [
                "background-image: url( 'http://example.com/valid.gif' );",
                "background-image: url( 'http://example.com/valid.gif' )",
            ],

            // No quotes.
            [
                'background-image: url( http://example.com/valid.gif );',
                'background-image: url( http://example.com/valid.gif )',
            ],

            // Single quotes, extra spaces.
            [
                "background-image: url( '  http://example.com/valid.gif ' );",
                "background-image: url( '  http://example.com/valid.gif ' )",
            ],

            // Line breaks, single quotes.
            [
                "background-image: url(\n'http://example.com/valid.gif' );",
                "background-image: url('http://example.com/valid.gif' )",
            ],

            // Tabs not spaces, single quotes.
            [
                "background-image: url(\t'http://example.com/valid.gif'\t\t);",
                "background-image: url('http://example.com/valid.gif')",
            ],

            // Single quotes, absolute path.
            [
                "background: url('/valid.gif');",
                "background: url('/valid.gif')",
            ],

            // Single quotes, relative path.
            [
                "background: url('../wp-content/uploads/2018/10/valid.gif');",
                "background: url('../wp-content/uploads/2018/10/valid.gif')",
            ],

            // Error check: valid property not containing a URL.
            [
                'background: red',
                'background: red',
            ],

            /*
             * Invalid use cases.
             */

            // Attribute doesn't support URL properties.
            [
                'color: url( "http://example.com/invalid.gif" );',
                '',
            ],

            // Mismatched quotes.
            [
                'background-image: url( "http://example.com/valid.gif\' );',
                '',
            ],

            // Bad protocol, double quotes.
            [
                'background-image: url( "bad://example.com/invalid.gif" );',
                '',
            ],

            // Bad protocol, single quotes.
            [
                "background-image: url( 'bad://example.com/invalid.gif' );",
                '',
            ],

            // Bad protocol, single quotes.
            [
                "background-image: url( 'bad://example.com/invalid.gif' );",
                '',
            ],

            // Bad protocol, single quotes, strange spacing.
            [
                "background-image: url( '  \tbad://example.com/invalid.gif ' );",
                '',
            ],

            // Bad protocol, no quotes.
            [
                'background-image: url( bad://example.com/invalid.gif );',
                '',
            ],

            // No URL inside url().
            [
                'background-image: url();',
                '',
            ],

            // Malformed, no closing `)`.
            [
                'background-image: url( "http://example.com" ;',
                '',
            ],

            // Malformed, no closing `"`.
            [
                'background-image: url( "http://example.com );',
                '',
            ],
        ];
    }

    /**
     * Testing the safecss_filter_attr() function with the safecss_filter_attr_allow_css filter.
     *
     * @ticket 37134
     *
     * @dataProvider data_safecss_filter_attr_filtered
     *
     * @param string $css      A string of CSS rules.
     * @param string $expected Expected string of CSS rules.
     */
    public function test_safecss_filter_attr_filtered($css, $expected)
    {
        add_filter('safecss_filter_attr_allow_css', '__return_true');
        $this->assertSame($expected, safecss_filter_attr($css));
        remove_filter('safecss_filter_attr_allow_css', '__return_true');
    }

    /**
     * Data provider for test_safecss_filter_attr_filtered().
     *
     * @return array {
     *     @type array {
     *         @type string $css      A string of CSS rules.
     *         @type string $expected Expected string of CSS rules.
     *     }
     * }
     */
    public function data_safecss_filter_attr_filtered()
    {
        return [

            // A single attribute name, with a single value.
            [
                'css'      => 'margin-top: 2px',
                'expected' => 'margin-top: 2px',
            ],
            // Backslash \ can be allowed with the 'safecss_filter_attr_allow_css' filter.
            [
                'css'      => 'margin-top: \2px',
                'expected' => 'margin-top: \2px',
            ],
            // Curly bracket } can be allowed with the 'safecss_filter_attr_allow_css' filter.
            [
                'css'      => 'margin-bottom: 2px}',
                'expected' => 'margin-bottom: 2px}',
            ],
            // Parenthesis ) can be allowed with the 'safecss_filter_attr_allow_css' filter.
            [
                'css'      => 'margin-bottom: 2px)',
                'expected' => 'margin-bottom: 2px)',
            ],
            // Ampersand & can be allowed with the 'safecss_filter_attr_allow_css' filter.
            [
                'css'      => 'margin-bottom: 2px&',
                'expected' => 'margin-bottom: 2px&',
            ],
            // Expressions can be allowed with the 'safecss_filter_attr_allow_css' filter.
            [
                'css'      => 'height: expression( body.scrollTop + 50 + "px" )',
                'expected' => 'height: expression( body.scrollTop + 50 + "px" )',
            ],
            // RGB color values can be allowed with the 'safecss_filter_attr_allow_css' filter.
            [
                'css'      => 'color: rgb( 100, 100, 100 )',
                'expected' => 'color: rgb( 100, 100, 100 )',
            ],
            // RGBA color values can be allowed with the 'safecss_filter_attr_allow_css' filter.
            [
                'css'      => 'color: rgb( 100, 100, 100, .4 )',
                'expected' => 'color: rgb( 100, 100, 100, .4 )',
            ],
        ];
    }

    /**
     * Test filtering a standard img tag.
     *
     * @ticket 50731
     */
    public function test_wp_kses_img_tag_standard_attributes()
    {
        $html = [
            '<img',
            'loading="lazy"',
            'src="https://example.com/img.jpg"',
            'width="1000"',
            'height="1000"',
            'alt=""',
            'class="wp-image-1000"',
            '/>',
        ];

        $html = implode(' ', $html);

        $this->assertEqualHTML($html, wp_kses_post($html));
    }

    /**
     * Test filtering a standard main tag.
     *
     * @ticket 53156
     */
    public function test_wp_kses_main_tag_standard_attributes()
    {
        $test = [
            '<main',
            'class="wp-group-block"',
            'style="padding:10px"',
            '/>',
        ];

        $html = implode(' ', $test);

        $this->assertEqualHTML($html, wp_kses_post($html));
    }

    /**
     * Test that object tags are allowed under limited circumstances.
     *
     * @ticket 54261
     *
     * @dataProvider data_wp_kses_object_tag_allowed
     *
     * @param string $html     A string of HTML to test.
     * @param string $expected The expected result from KSES.
     */
    public function test_wp_kses_object_tag_allowed($html, $expected)
    {
        $this->assertEqualHTML($expected, wp_kses_post($html));
    }

    /**
     * Data provider for test_wp_kses_object_tag_allowed().
     */
    public function data_wp_kses_object_tag_allowed()
    {
        return [
            'valid value for type'                    => [
                '<object type="application/pdf" data="https://' . WP_TESTS_DOMAIN . '/foo.pdf" />',
                '<object type="application/pdf" data="https://' . WP_TESTS_DOMAIN . '/foo.pdf" />',
            ],
            'invalid value for type'                  => [
                '<object type="application/exe" data="https://' . WP_TESTS_DOMAIN . '/foo.exe" />',
                '',
            ],
            'multiple type attributes, last invalid'  => [
                '<object type="application/pdf" type="application/exe" data="https://' . WP_TESTS_DOMAIN . '/foo.pdf" />',
                '<object type="application/pdf" data="https://' . WP_TESTS_DOMAIN . '/foo.pdf" />',
            ],
            'multiple type attributes, first uppercase, last invalid' => [
                '<object TYPE="application/pdf" type="application/exe" data="https://' . WP_TESTS_DOMAIN . '/foo.pdf" />',
                '<object TYPE="application/pdf" data="https://' . WP_TESTS_DOMAIN . '/foo.pdf" />',
            ],
            'multiple type attributes, last upper case and invalid' => [
                '<object type="application/pdf" TYPE="application/exe" data="https://' . WP_TESTS_DOMAIN . '/foo.pdf" />',
                '<object type="application/pdf" data="https://' . WP_TESTS_DOMAIN . '/foo.pdf" />',
            ],
            'multiple type attributes, first invalid' => [
                '<object type="application/exe" type="application/pdf" data="https://' . WP_TESTS_DOMAIN . '/foo.pdf" />',
                '',
            ],
            'multiple type attributes, first upper case and invalid' => [
                '<object TYPE="application/exe" type="application/pdf" data="https://' . WP_TESTS_DOMAIN . '/foo.pdf" />',
                '',
            ],
            'multiple type attributes, first invalid, last uppercase' => [
                '<object type="application/exe" TYPE="application/pdf" data="https://' . WP_TESTS_DOMAIN . '/foo.pdf" />',
                '',
            ],
            'multiple object tags, last invalid'      => [
                '<object type="application/pdf" data="https://' . WP_TESTS_DOMAIN . '/foo.pdf" /><object type="application/exe" data="https://' . WP_TESTS_DOMAIN . '/foo.exe" />',
                '<object type="application/pdf" data="https://' . WP_TESTS_DOMAIN . '/foo.pdf" />',
            ],
            'multiple object tags, first invalid'     => [
                '<object type="application/exe" data="https://' . WP_TESTS_DOMAIN . '/foo.exe" /><object type="application/pdf" data="https://' . WP_TESTS_DOMAIN . '/foo.pdf" />',
                '<object type="application/pdf" data="https://' . WP_TESTS_DOMAIN . '/foo.pdf" />',
            ],
            'type attribute with partially incorrect value' => [
                '<object type="application/pdfa" data="https://' . WP_TESTS_DOMAIN . '/foo.pdf" />',
                '',
            ],
            'type attribute with empty value'         => [
                '<object type="" data="https://' . WP_TESTS_DOMAIN . '/foo.pdf" />',
                '',
            ],
            'type attribute with no value'            => [
                '<object type data="https://' . WP_TESTS_DOMAIN . '/foo.pdf" />',
                '',
            ],
            'no type attribute'                       => [
                '<object data="https://' . WP_TESTS_DOMAIN . '/foo.pdf" />',
                '',
            ],
            'different protocol in url'               => [
                '<object type="application/pdf" data="http://' . WP_TESTS_DOMAIN . '/foo.pdf" />',
                '<object type="application/pdf" data="http://' . WP_TESTS_DOMAIN . '/foo.pdf" />',
            ],
            'query string on url'                     => [
                '<object type="application/pdf" data="https://' . WP_TESTS_DOMAIN . '/foo.pdf?lol=.pdf" />',
                '',
            ],
            'fragment on url'                         => [
                '<object type="application/pdf" data="https://' . WP_TESTS_DOMAIN . '/foo.pdf#lol.pdf" />',
                '',
            ],
            'wrong extension'                         => [
                '<object type="application/pdf" data="https://' . WP_TESTS_DOMAIN . '/foo.php" />',
                '',
            ],
            'protocol-relative url'                   => [
                '<object type="application/pdf" data="//' . WP_TESTS_DOMAIN . '/foo.pdf" />',
                '',
            ],
            'unsupported protocol'                    => [
                '<object type="application/pdf" data="ftp://' . WP_TESTS_DOMAIN . '/foo.pdf" />',
                '',
            ],
            'relative url'                            => [
                '<object type="application/pdf" data="/cat/foo.pdf" />',
                '',
            ],
            'url with port number-like path'          => [
                '<object type="application/pdf" data="https://' . WP_TESTS_DOMAIN . '/cat:8888/foo.pdf" />',
                '<object type="application/pdf" data="https://' . WP_TESTS_DOMAIN . '/cat:8888/foo.pdf" />',
            ],
        ];
    }

    /**
     * Test that object tags are allowed when there is a port number in the URL.
     *
     * @ticket 54261
     *
     * @dataProvider data_wp_kses_object_data_url_with_port_number_allowed
     *
     * @param string $html     A string of HTML to test.
     * @param string $expected The expected result from KSES.
     */
    public function test_wp_kses_object_data_url_with_port_number_allowed($html, $expected)
    {
        add_filter('upload_dir', [ $this, 'wp_kses_upload_dir_filter' ], 10, 2);
        $this->assertEqualHTML($expected, wp_kses_post($html));
    }

    /**
     * Data provider for test_wp_kses_object_data_url_with_port_number_allowed().
     */
    public function data_wp_kses_object_data_url_with_port_number_allowed()
    {
        return [
            'url with port number'                   => [
                '<object type="application/pdf" data="https://example.org:8888/cat/foo.pdf" />',
                '<object type="application/pdf" data="https://example.org:8888/cat/foo.pdf" />',
            ],
            'url with port number and http protocol' => [
                '<object type="application/pdf" data="http://example.org:8888/cat/foo.pdf" />',
                '<object type="application/pdf" data="http://example.org:8888/cat/foo.pdf" />',
            ],
            'url with wrong port number'             => [
                '<object type="application/pdf" data="http://example.org:3333/cat/foo.pdf" />',
                '',
            ],
            'url without port number'                => [
                '<object type="application/pdf" data="http://example.org/cat/foo.pdf" />',
                '',
            ],
        ];
    }

    /**
     * Filter upload directory for tests using port number.
     *
     * @param  array $param See wp_upload_dir()
     * @return array        $param with a modified `url`.
     */
    public function wp_kses_upload_dir_filter($param)
    {
        // Take care to replace the entire domain, including cases where it already has a port number.
        $parsed         = parse_url($param['url']);
        $replace_domain = $parsed['host'];
        if (isset($parsed['port'])) {
            $replace_domain .= ':' . $parsed['port'];
        }

        $url_with_port_number = is_string($param['url']) ? str_replace($replace_domain, 'example.org:8888', $param['url']) : $param['url'];
        $param['url']         = $url_with_port_number;
        return $param;
    }

    /**
     * Test that object tags will continue to function if they've been added using the
     * 'wp_kses_allowed_html' filter.
     *
     * @ticket 54261
     */
    public function test_wp_kses_object_added_in_html_filter()
    {
        $html = <<<HTML
<object type="application/pdf" data="https://wordpress.org/foo.pdf" />
<object type="application/x-shockwave-flash" data="https://wordpress.org/foo.swf">
	<param name="foo" value="bar" />
</object>
HTML;

        add_filter('wp_kses_allowed_html', [ $this, 'filter_wp_kses_object_added_in_html_filter' ], 10, 2);

        $filtered_html = wp_kses_post($html);

        remove_filter('wp_kses_allowed_html', [ $this, 'filter_wp_kses_object_added_in_html_filter' ]);

        $this->assertEqualHTML($html, $filtered_html);
    }

    public function filter_wp_kses_object_added_in_html_filter($tags, $context)
    {
        if ('post' === $context) {
            $tags['object'] = [
                'type' => true,
                'data' => true,
            ];

            $tags['param'] = [
                'name'  => true,
                'value' => true,
            ];
        }

        return $tags;
    }

    /**
     * Ensures that `wp_kses()` preserves various kinds of HTML comments, both valid and invalid.
     *
     * @ticket 61009
     *
     * @dataProvider data_html_containing_various_kinds_of_html_comments
     *
     * @param string $html_comment    HTML containing a comment; must not be a valid comment
     *                                but must be syntax which a browser interprets as a comment.
     * @param string $expected_output How `wp_kses()` ought to transform the comment.
     */
    public function test_wp_kses_preserves_html_comments($html_comment, $expected_output)
    {
        $this->assertEqualHTML(
            $expected_output,
            wp_kses($html_comment, []),
            '<body>',
            'Failed to properly preserve HTML comment.'
        );
    }

    /**
     * Data provider.
     *
     * @return array[].
     */
    public static function data_html_containing_various_kinds_of_html_comments()
    {
        return [
            'Normative HTML comment'            => [ 'before<!-- this is a comment -->after', 'before<!-- this is a comment -->after' ],
            'Closing tag with invalid tag name' => [ 'before<//not a tag>after', 'before<//not a tag>after' ],
            'Incorrectly opened comment (Markup declaration)' => [ 'before<!also not a tag>after', 'before<!also not a tag>after' ],
        ];
    }

    /**
     * Test that attributes with a list of allowed values are filtered correctly.
     *
     * @ticket 54261
     *
     * @dataProvider data_wp_kses_allowed_values_list
     *
     * @param string $content      A string of HTML to test.
     * @param string $expected     The expected result from KSES.
     * @param array  $allowed_html The allowed HTML to pass to KSES.
     */
    public function test_wp_kses_allowed_values_list($content, $expected, $allowed_html)
    {
        $this->assertEqualHTML($expected, wp_kses($content, $allowed_html));
    }

    /**
     * Data provider for test_wp_kses_allowed_values_list().
     */
    public function data_wp_kses_allowed_values_list()
    {
        $data = [
            'valid dir attribute value'             => [
                '<p dir="ltr">foo</p>',
                '<p dir="ltr">foo</p>',
            ],
            'valid dir attribute value, upper case' => [
                '<p DIR="RTL">foo</p>',
                '<p DIR="RTL">foo</p>',
            ],
            'invalid dir attribute value'           => [
                '<p dir="up">foo</p>',
                '<p>foo</p>',
            ],
            'dir attribute with empty value'        => [
                '<p dir="">foo</p>',
                '<p>foo</p>',
            ],
            'dir attribute with no value'           => [
                '<p dir>foo</p>',
                '<p>foo</p>',
            ],
        ];

        return array_map(
            static function ($datum) {
                $datum[] = [
                    'p' => [
                        'dir' => [
                            'values' => [ 'ltr', 'rtl' ],
                        ],
                    ],
                ];

                return $datum;
            },
            $data
        );
    }

    /**
     * Test that attributes with the required flag are handled correctly.
     *
     * @ticket 54261
     *
     * @dataProvider data_wp_kses_required_attribute
     *
     * @param string $content      A string of HTML to test.
     * @param string $expected     The expected result from KSES.
     * @param array  $allowed_html The allowed HTML to pass to KSES.
     */
    public function test_wp_kses_required_attribute($content, $expected, $allowed_html)
    {
        $this->assertEqualHTML($expected, wp_kses($content, $allowed_html));
    }

    /**
     * Data provider for test_wp_kses_required_attribute().
     */
    public function data_wp_kses_required_attribute()
    {
        $data = [
            'valid dir attribute value'             => [
                '<p dir="ltr">foo</p>', // Test HTML.
                '<p dir="ltr">foo</p>', // Expected result when dir is not required.
                '<p dir="ltr">foo</p>', // Expected result when dir is required.
                '<p dir="ltr">foo</p>', // Expected result when dir is required, but has no value filter.
            ],
            'valid dir attribute value, upper case' => [
                '<p DIR="RTL">foo</p>',
                '<p DIR="RTL">foo</p>',
                '<p DIR="RTL">foo</p>',
                '<p DIR="RTL">foo</p>',
            ],
            'invalid dir attribute value'           => [
                '<p dir="up">foo</p>',
                '<p>foo</p>',
                '<p>foo</p>',
                '<p dir="up">foo</p>',
            ],
            'dir attribute with empty value'        => [
                '<p dir="">foo</p>',
                '<p>foo</p>',
                '<p>foo</p>',
                '<p dir="">foo</p>',
            ],
            'dir attribute with no value'           => [
                '<p dir>foo</p>',
                '<p>foo</p>',
                '<p>foo</p>',
                '<p dir>foo</p>',
            ],
            'dir attribute not set'                 => [
                '<p>foo</p>',
                '<p>foo</p>',
                '<p>foo</p>',
                '<p>foo</p>',
            ],
        ];

        $return_data = [];

        foreach ($data as $description => $datum) {
            // Test that the required flag defaults to false.
            $return_data[ "$description - required flag not set" ] = [
                $datum[0],
                $datum[1],
                [
                    'p' => [
                        'dir' => [
                            'values' => [ 'ltr', 'rtl' ],
                        ],
                    ],
                ],
            ];

            // Test when the attribute is not required, but has allowed values.
            $return_data[ "$description - required flag set to false" ] = [
                $datum[0],
                $datum[1],
                [
                    'p' => [
                        'dir' => [
                            'required' => false,
                            'values'   => [ 'ltr', 'rtl' ],
                        ],
                    ],
                ],
            ];

            // Test when the attribute is required, but has allowed values.
            $return_data[ "$description - required flag set to true" ] = [
                $datum[0],
                $datum[2],
                [
                    'p' => [
                        'dir' => [
                            'required' => true,
                            'values'   => [ 'ltr', 'rtl' ],
                        ],
                    ],
                ],
            ];

            // Test when the attribute is required, but has no allowed values.
            $return_data[ "$description - required flag set to true, no allowed values specified" ] = [
                $datum[0],
                $datum[3],
                [
                    'p' => [
                        'dir' => [
                            'required' => true,
                        ],
                    ],
                ],
            ];
        }

        return $return_data;
    }

    /**
     * Test that XML named entities are encoded correctly.
     *
     * @dataProvider data_wp_kses_xml_named_entities
     *
     * @ticket 54060
     * @covers ::wp_kses_xml_named_entities
     *
     * @param array  $input    The input to wp_kses_xml_named_entities().
     * @param string $expected The expected output.
     */
    public function test_wp_kses_xml_named_entities($input, $expected)
    {
        $this->assertSame($expected, wp_kses_xml_named_entities($input));
    }

    /**
     * Data provider for test_wp_kses_xml_named_entities().
     *
     * @return array Nested array of input, expected pairs.
     */
    public function data_wp_kses_xml_named_entities()
    {
        return [
            // Empty string value testing.
            'empty string'       => [
                'input'    => '',
                'expected' => '',
            ],

            // Empty string array value testing.
            'empty string array' => [
                'input'    => [ '', '' ],
                'expected' => '',
            ],

            // $allowedxmlentitynames values testing.
            'amp'                => [
                'input'    => [ '', 'amp' ],
                'expected' => '&amp;',
            ],
            'lt'                 => [
                'input'    => [ '', 'lt' ],
                'expected' => '&lt;',
            ],
            'gt'                 => [
                'input'    => [ '', 'gt' ],
                'expected' => '&gt;',
            ],

            // $allowedentitynames values testing.
            'nbsp'               => [
                'input'    => [ '', 'nbsp' ],
                'expected' => "\u{00A0}",
            ],
            'iexcl'              => [
                'input'    => [ '', 'iexcl' ],
                'expected' => '¡',
            ],
            'cent'               => [
                'input'    => [ '', 'cent' ],
                'expected' => '¢',
            ],

            // Some other value testing.
            'test'               => [
                'input'    => [ '', 'test' ],
                'expected' => '&amp;test;',
            ],

        ];
    }

    /**
     * Test that KSES globals are defined.
     *
     * @dataProvider data_kses_globals_are_defined
     *
     * @ticket 54060
     *
     * @param string $global_name The name of the global variable.
     */
    public function test_kses_globals_are_defined($global_name)
    {
        $this->assertArrayHasKey($global_name, $GLOBALS);
    }

    /**
     * Data provider for test_kses_globals_are_defined().
     *
     * @return array
     */
    public function data_kses_globals_are_defined()
    {
        $required_kses_globals = [
            'allowedposttags',
            'allowedtags',
            'allowedentitynames',
            'allowedxmlentitynames',
        ];

        return $this->text_array_to_dataprovider($required_kses_globals);
    }

    /**
     * Tests that the target attribute is preserved in various contexts.
     *
     * @dataProvider data_target_attribute_preserved_in_descriptions
     *
     * @ticket 12056
     *
     * @param string $context  The context to test ('user_description' or 'pre_term_description').
     * @param string $input    The input HTML string.
     * @param string $expected The expected output HTML string.
     */
    public function test_target_attribute_preserved_in_context($context, $input, $expected)
    {
        $allowed = wp_kses_allowed_html($context);
        $this->assertTrue(isset($allowed['a']['target']), "Target attribute not allowed in {$context}");
        $this->assertEqualHTML($expected, wp_kses($input, $context));
    }

    /**
     * Data provider for test_target_attribute_preserved_in_context.
     *
     * @return array
     */
    public function data_target_attribute_preserved_in_descriptions()
    {
        return [
            [
                'user_description',
                '<a href="https://example.com" target="_blank">Example</a>',
                '<a href="https://example.com" target="_blank">Example</a>',
            ],
            [
                'pre_term_description',
                '<a href="https://example.com" target="_blank">Example</a>',
                '<a href="https://example.com" target="_blank">Example</a>',
            ],
        ];
    }

    /**
     * Tests that specific attributes are preserved in various contexts.
     *
     * @dataProvider data_allowed_attributes_in_descriptions
     *
     * @ticket 12056
     *
     * @param string $context    The context to test ('user_description' or 'pre_term_description').
     * @param array  $attributes List of attributes to check for.
     */
    public function test_specific_attributes_preserved_in_context($context, $attributes)
    {
        $allowed = wp_kses_allowed_html($context);
        foreach ($attributes as $attribute) {
            $this->assertTrue(isset($allowed['a'][ $attribute ]), "{$attribute} attribute not allowed in {$context}");
        }
    }

    /**
     * Data provider for test_specific_attributes_preserved_in_context.
     *
     * @return array
     */
    public function data_allowed_attributes_in_descriptions()
    {
        return [
            [
                'user_description',
                [ 'target', 'href', 'rel' ],
            ],
            [
                'pre_term_description',
                [ 'target', 'href', 'rel' ],
            ],
        ];
    }
}
