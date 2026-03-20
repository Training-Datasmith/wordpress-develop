<?php

declare(strict_types=1);

/**
 * @group l10n
 * @group i18n
 *
 * @covers ::translate_settings_using_i18n_schema
 */
class Tests_L10n_TranslateSettingsUsingI18nSchema extends WP_UnitTestCase
{
    /**
     * Returns Polish locale string.
     *
     * @return string
     */
    public function filter_set_locale_to_polish()
    {
        return 'pl_PL';
    }

    /**
     * @ticket 53238
     */
    public function test_translate_settings_using_i18n_schema()
    {
        $textdomain = 'notice';

        add_filter('locale', [ $this, 'filter_set_locale_to_polish' ]);
        load_textdomain($textdomain, WP_LANG_DIR . '/plugins/notice-pl_PL.mo');

        $i18n_schema = (object) [
            'title'    => 'block title',
            'keywords' => [ 'block keyword' ],
            'styles'   => [
                (object) [ 'label' => 'block style label' ],
            ],
            'context'  => (object) [
                '*' => (object) [
                    'variations' => [
                        (object) [
                            'title'       => 'block variation title',
                            'description' => 'block variation description',
                            'keywords'    => [ 'block variation keyword' ],
                        ],
                    ],
                ],
            ],
        ];
        $settings    = [
            'title'    => 'Notice',
            'keywords' => [
                'alert',
                'message',
            ],
            'styles'   => [
                [ 'label' => 'Default' ],
                [ 'label' => 'Other' ],
            ],
            'context'  => [
                'namespace' => [
                    'variations' => [
                        [
                            'title'       => 'Error',
                            'description' => 'Shows error.',
                            'keywords'    => [ 'failure' ],
                        ],
                    ],
                ],
            ],
        ];
        $result      = translate_settings_using_i18n_schema(
            $i18n_schema,
            $settings,
            $textdomain
        );

        unload_textdomain($textdomain);
        remove_filter('locale', [ $this, 'filter_set_locale_to_polish' ]);

        $this->assertSame('Powiadomienie', $result['title']);
        $this->assertSameSets([ 'ostrzeżenie', 'wiadomość' ], $result['keywords']);
        $this->assertSame(
            [
                [
                    'label' => 'Domyślny',
                ],
                [
                    'label' => 'Inny',
                ],
            ],
            $result['styles']
        );
        $this->assertSame(
            [
                [
                    'title'       => 'Błąd',
                    'description' => 'Wyświetla błąd.',
                    'keywords'    => [ 'niepowodzenie' ],
                ],
            ],
            $result['context']['namespace']['variations']
        );
    }
}
