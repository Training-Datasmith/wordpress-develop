<?php

declare(strict_types=1);

/**
 * @group formatting
 *
 * @covers ::like_escape
 */
class Tests_Formatting_LikeEscape extends WP_UnitTestCase
{
    /**
     * @ticket 10041
     * @expectedDeprecated like_escape
     */
    public function test_like_escape()
    {

        $inputs   = [
            'howdy%',              // Single percent.
            'howdy_',              // Single underscore.
            'howdy\\',             // Single slash.
            'howdy\\howdy%howdy_', // The works.
        ];
        $expected = [
            'howdy\\%',
            'howdy\\_',
            'howdy\\',
            'howdy\\howdy\\%howdy\\_',
        ];

        foreach ($inputs as $key => $input) {
            $this->assertSame($expected[ $key ], like_escape($input));
        }
    }
}
