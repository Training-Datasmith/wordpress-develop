<?php

declare(strict_types=1);
/**
 * @group block-supports
 *
 * @covers ::wp_mark_auto_generate_control_attributes
 */
class Tests_Block_Supports_Auto_Register extends WP_UnitTestCase
{
    /**
     * Tests that attributes are marked when autoRegister is enabled.
     *
     * @ticket 64639
     */
    public function test_marks_attributes_with_auto_register_flag()
    {
        $settings = [
            'supports'   => [ 'autoRegister' => true ],
            'attributes' => [
                'title' => [ 'type' => 'string' ],
                'count' => [ 'type' => 'integer' ],
            ],
        ];

        $result = wp_mark_auto_generate_control_attributes($settings);

        $this->assertTrue($result['attributes']['title']['autoGenerateControl']);
        $this->assertTrue($result['attributes']['count']['autoGenerateControl']);
    }

    /**
     * Tests that attributes are not marked without autoRegister flag.
     *
     * @ticket 64639
     */
    public function test_does_not_mark_attributes_without_auto_register()
    {
        $settings = [
            'attributes' => [
                'title' => [ 'type' => 'string' ],
            ],
        ];

        $result = wp_mark_auto_generate_control_attributes($settings);

        $this->assertArrayNotHasKey('autoGenerateControl', $result['attributes']['title']);
    }

    /**
     * Tests that attributes with source are excluded.
     *
     * @ticket 64639
     */
    public function test_excludes_attributes_with_source()
    {
        $settings = [
            'supports'   => [ 'autoRegister' => true ],
            'attributes' => [
                'title'   => [ 'type' => 'string' ],
                'content' => [
                    'type'   => 'string',
                    'source' => 'html',
                ],
            ],
        ];

        $result = wp_mark_auto_generate_control_attributes($settings);

        $this->assertTrue($result['attributes']['title']['autoGenerateControl']);
        $this->assertArrayNotHasKey('autoGenerateControl', $result['attributes']['content']);
    }

    /**
     * Tests that attributes with role: local are excluded.
     *
     * Example: The 'blob' attribute in media blocks (image, video, file, audio)
     * stores a temporary blob URL during file upload. This is internal state
     * that shouldn't be shown in the inspector or saved to the database.
     *
     * @ticket 64639
     */
    public function test_excludes_attributes_with_role_local()
    {
        $settings = [
            'supports'   => [ 'autoRegister' => true ],
            'attributes' => [
                'title' => [ 'type' => 'string' ],
                'blob'  => [
                    'type' => 'string',
                    'role' => 'local',
                ],
            ],
        ];

        $result = wp_mark_auto_generate_control_attributes($settings);

        $this->assertTrue($result['attributes']['title']['autoGenerateControl']);
        $this->assertArrayNotHasKey('autoGenerateControl', $result['attributes']['blob']);
    }

    /**
     * Tests that empty attributes are handled gracefully.
     *
     * @ticket 64639
     */
    public function test_handles_empty_attributes()
    {
        $settings = [
            'supports' => [ 'autoRegister' => true ],
        ];

        $result = wp_mark_auto_generate_control_attributes($settings);

        $this->assertSame($settings, $result);
    }

    /**
     * Tests that only allowed attributes are marked.
     *
     * @ticket 64639
     */
    public function test_excludes_unsupported_types()
    {
        $settings = [
            'supports'   => [ 'autoRegister' => true ],
            'attributes' => [
                // Supported types
                'text'     => [ 'type' => 'string' ],
                'price'    => [ 'type' => 'number' ],
                'count'    => [ 'type' => 'integer' ],
                'enabled'  => [ 'type' => 'boolean' ],
                // Unsupported types
                'metadata' => [ 'type' => 'object' ],
                'items'    => [ 'type' => 'array' ],
                'config'   => [ 'type' => 'null' ],
                'unknown'  => [ 'type' => 'unknown' ],
            ],
        ];

        $result = wp_mark_auto_generate_control_attributes($settings);

        $this->assertTrue($result['attributes']['text']['autoGenerateControl']);
        $this->assertTrue($result['attributes']['price']['autoGenerateControl']);
        $this->assertTrue($result['attributes']['count']['autoGenerateControl']);
        $this->assertTrue($result['attributes']['enabled']['autoGenerateControl']);
        $this->assertArrayNotHasKey('autoGenerateControl', $result['attributes']['metadata']);
        $this->assertArrayNotHasKey('autoGenerateControl', $result['attributes']['items']);
        $this->assertArrayNotHasKey('autoGenerateControl', $result['attributes']['config']);
        $this->assertArrayNotHasKey('autoGenerateControl', $result['attributes']['unknown']);
    }
}
