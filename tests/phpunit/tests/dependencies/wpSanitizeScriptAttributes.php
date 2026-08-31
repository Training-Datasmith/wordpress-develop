<?php

declare(strict_types=1);

/**
 * Test wp_sanitize_script_attributes().
 *
 * @group dependencies
 * @group scripts
 * @covers ::wp_sanitize_script_attributes
 */
class Tests_Dependencies_wpSanitizeScriptAttributes extends WP_UnitTestCase
{
    public function test_sanitize_script_attributes_type_set()
    {
        $this->setExpectedDeprecated('wp_sanitize_script_attributes');
        $this->assertSame(
            ' type="application/javascript" src="https://DOMAIN.TLD/PATH/FILE.js" nomodule',
            wp_sanitize_script_attributes(
                [
                    'type'     => 'application/javascript',
                    'src'      => 'https://DOMAIN.TLD/PATH/FILE.js',
                    'async'    => false,
                    'nomodule' => true,
                ]
            )
        );
    }

    public function test_sanitize_script_attributes_type_not_set()
    {
        $this->setExpectedDeprecated('wp_sanitize_script_attributes');
        $this->assertSame(
            ' src="https://DOMAIN.TLD/PATH/FILE.js" nomodule',
            wp_sanitize_script_attributes(
                [
                    'src'      => 'https://DOMAIN.TLD/PATH/FILE.js',
                    'async'    => false,
                    'nomodule' => true,
                ]
            )
        );
    }

    public function test_sanitize_script_attributes_no_attributes()
    {
        $this->setExpectedDeprecated('wp_sanitize_script_attributes');
        $this->assertSame(
            '',
            wp_sanitize_script_attributes([])
        );
    }

    public function test_sanitize_script_attributes_relative_src()
    {
        $this->setExpectedDeprecated('wp_sanitize_script_attributes');
        $this->assertSame(
            ' src="PATH/FILE.js" nomodule',
            wp_sanitize_script_attributes(
                [
                    'src'      => 'PATH/FILE.js',
                    'async'    => false,
                    'nomodule' => true,
                ]
            )
        );
    }

    public function test_sanitize_script_attributes_only_false_boolean_attributes()
    {
        $this->setExpectedDeprecated('wp_sanitize_script_attributes');
        $this->assertSame(
            '',
            wp_sanitize_script_attributes(
                [
                    'async'    => false,
                    'nomodule' => false,
                ]
            )
        );
    }

    public function test_sanitize_script_attributes_only_true_boolean_attributes()
    {
        $this->setExpectedDeprecated('wp_sanitize_script_attributes');
        $this->assertSame(
            ' async nomodule',
            wp_sanitize_script_attributes(
                [
                    'async'    => true,
                    'nomodule' => true,
                ]
            )
        );
    }
}
