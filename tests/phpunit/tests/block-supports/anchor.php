<?php

declare(strict_types=1);
/**
 * @group block-supports
 */
class Tests_Block_Supports_Anchor extends WP_UnitTestCase
{
    /**
     * @var string
     */
    public const TEST_BLOCK_NAME = 'test/anchor-block';

    public function tear_down()
    {
        unregister_block_type(self::TEST_BLOCK_NAME);
        parent::tear_down();
    }

    /**
     * Tests that anchor block support attribute registration works as expected.
     *
     * @covers ::wp_register_anchor_support
     *
     * @dataProvider data_wp_register_anchor_support
     *
     * @param bool                                      $support  Anchor block support configuration.
     * @param array<string, array<string, string>>|null $value    Attributes array for the block.
     * @param array<string, array<string, string>>      $expected Expected attributes for the block.
     */
    public function test_wp_register_anchor_support(bool $support, ?array $value, array $expected)
    {
        register_block_type(
            self::TEST_BLOCK_NAME,
            [
                'api_version' => 3,
                'supports'    => [ 'anchor' => $support ],
                'attributes'  => $value,
            ]
        );
        $registry   = WP_Block_Type_Registry::get_instance();
        $block_type = $registry->get_registered(self::TEST_BLOCK_NAME);
        $this->assertInstanceOf(WP_Block_Type::class, $block_type);
        wp_register_anchor_support($block_type);
        $actual = $block_type->attributes;
        $this->assertIsArray($actual);
        $expected = array_merge(WP_Block_Type::GLOBAL_ATTRIBUTES, $expected);
        $this->assertSameSetsWithIndex($expected, $actual);
    }

    /**
     * Tests that anchor block support is applied as expected.
     *
     * @covers ::wp_apply_anchor_support
     *
     * @dataProvider data_wp_apply_anchor_support
     *
     * @param bool                                 $support  Anchor block support configuration.
     * @param mixed                                $value    Anchor value for attribute object.
     * @param array<string, array<string, string>> $expected Expected anchor block support output.
     */
    public function test_wp_apply_anchor_support(bool $support, $value, array $expected)
    {
        register_block_type(
            self::TEST_BLOCK_NAME,
            [
                'api_version' => 3,
                'supports'    => [ 'anchor' => $support ],
            ]
        );
        $registry   = WP_Block_Type_Registry::get_instance();
        $block_type = $registry->get_registered(self::TEST_BLOCK_NAME);
        $this->assertInstanceOf(WP_Block_Type::class, $block_type);
        $block_attrs = [ 'anchor' => $value ];
        $actual      = wp_apply_anchor_support($block_type, $block_attrs);
        $this->assertSame($expected, $actual);
    }

    /**
     * Data provider for test_wp_register_anchor_support().
     *
     * @return array<string, array<string, mixed>>
     */
    public function data_wp_register_anchor_support(): array
    {
        return [
            'anchor attribute is registered when block supports anchor' => [
                'support'  => true,
                'value'    => null,
                'expected' => [
                    'anchor' => [
                        'type' => 'string',
                    ],
                ],
            ],
            'anchor attribute is not registered when block does not support anchor' => [
                'support'  => false,
                'value'    => null,
                'expected' => [],
            ],
            'anchor attribute is added to existing attributes' => [
                'support'  => true,
                'value'    => [
                    'foo' => [
                        'type' => 'string',
                    ],
                ],
                'expected' => [
                    'foo'    => [
                        'type' => 'string',
                    ],
                    'anchor' => [
                        'type' => 'string',
                    ],
                ],
            ],
            'existing anchor attribute is not overwritten' => [
                'support'  => true,
                'value'    => [
                    'anchor' => [
                        'type'    => 'string',
                        'default' => 'default-anchor',
                    ],
                ],
                'expected' => [
                    'anchor' => [
                        'type'    => 'string',
                        'default' => 'default-anchor',
                    ],
                ],
            ],
        ];
    }

    /**
     * Data provider for test_wp_apply_anchor_support().
     *
     * @return array<string, array<string, mixed>>
     */
    public function data_wp_apply_anchor_support(): array
    {
        return [
            'anchor id attribute is applied'          => [
                'support'  => true,
                'value'    => 'my-anchor',
                'expected' => [ 'id' => 'my-anchor' ],
            ],
            'anchor id attribute is not applied if block does not support it' => [
                'support'  => false,
                'value'    => 'my-anchor',
                'expected' => [],
            ],
            'empty anchor value returns empty array'  => [
                'support'  => true,
                'value'    => '',
                'expected' => [],
            ],
            'null anchor value returns empty array'   => [
                'support'  => true,
                'value'    => null,
                'expected' => [],
            ],
            'whitespace-only anchor value is applied' => [
                'support'  => true,
                'value'    => '   ',
                'expected' => [ 'id' => '   ' ],
            ],
            'anchor with hyphen and numbers'          => [
                'support'  => true,
                'value'    => 'section-123',
                'expected' => [ 'id' => 'section-123' ],
            ],
            'anchor with underscore'                  => [
                'support'  => true,
                'value'    => 'my_anchor_id',
                'expected' => [ 'id' => 'my_anchor_id' ],
            ],
            'anchor with colon (valid in HTML5)'      => [
                'support'  => true,
                'value'    => 'my:anchor',
                'expected' => [ 'id' => 'my:anchor' ],
            ],
            'anchor with period (valid in HTML5)'     => [
                'support'  => true,
                'value'    => 'my.anchor',
                'expected' => [ 'id' => 'my.anchor' ],
            ],
            'numeric anchor value'                    => [
                'support'  => true,
                'value'    => '123',
                'expected' => [ 'id' => '123' ],
            ],
            'zero string anchor value is applied'     => [
                'support'  => true,
                'value'    => '0',
                'expected' => [ 'id' => '0' ],
            ],
            'false value is treated as empty'         => [
                'support'  => true,
                'value'    => false,
                'expected' => [],
            ],
        ];
    }
}
