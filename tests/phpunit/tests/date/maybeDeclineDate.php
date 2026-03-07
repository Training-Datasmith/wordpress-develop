<?php

declare(strict_types=1);

/**
 * @group date
 * @group datetime
 * @group i18n
 * @group functions
 *
 * @covers ::wp_maybe_decline_date
 */
class Tests_Date_MaybeDeclineDate extends WP_UnitTestCase
{
    /**
     * @var string
     */
    private $locale_original;

    /**
     * @var WP_Locale
     */
    private $wp_locale_original;

    public function set_up()
    {
        global $locale, $wp_locale;

        parent::set_up();

        $this->locale_original    = $locale;
        $this->wp_locale_original = clone $wp_locale;
    }

    public function tear_down()
    {
        global $locale, $wp_locale;

        $locale    = $this->locale_original;
        $wp_locale = $this->wp_locale_original;

        parent::tear_down();
    }

    /**
     * @ticket 36790
     * @ticket 37411
     * @ticket 48606
     * @ticket 48934
     * @dataProvider data_wp_maybe_decline_date
     */
    public function test_wp_maybe_decline_date($test_locale, $format, $input, $output)
    {
        global $locale, $wp_locale;

        add_filter('gettext_with_context', [ $this, 'filter__enable_months_names_declension' ], 10, 3);

        $month_names = $this->get_months_names($test_locale);

        $locale                    = $test_locale;
        $wp_locale->month          = $month_names['month'];
        $wp_locale->month_genitive = $month_names['month_genitive'];

        $declined_date = wp_maybe_decline_date($input, $format);

        remove_filter('gettext_with_context', [ $this, 'filter__enable_months_names_declension' ], 10);

        $this->assertSame($output, $declined_date);
    }

    public function filter__enable_months_names_declension($translation, $text, $context)
    {
        if ('decline months names: on or off' === $context) {
            $translation = 'on';
        }

        return $translation;
    }

    public function data_wp_maybe_decline_date()
    {
        return [
            [ 'ru_RU', 'j F', '21 Июнь', '21 июня' ],
            [ 'ru_RU', 'j F Y', '1 Январь 2016', '1 января 2016' ],
            [ 'ru_RU', 'F jS Y', 'Январь 1st 2016', '1 января 2016' ],
            [ 'ru_RU', 'F j Y', 'Январь 1 2016', '1 января 2016' ],
            [ 'ru_RU', 'F j–j Y', 'Январь 1–2 2016', '1–2 января 2016' ],
            [ 'ru_RU', 'F j y', 'Январь 1 16', '1 января 16' ],
            [ 'ru_RU', 'F y', 'Январь 16', 'Январь 16' ],
            [ 'ru_RU', 'l, d F Y H:i', 'Суббота, 19 Январь 2019 10:50', 'Суббота, 19 января 2019 10:50' ],
            [ 'pl_PL', 'j F', '1 Styczeń', '1 stycznia' ],
            [ 'hr', 'j. F', '1. Siječanj', '1. siječnja' ],
            [ 'ca', 'j F', '1 de abril', "1 d'abril" ],
            [ 'cs_CZ', 'j. F', '1. Červen', '1. června' ],
            [ 'cs_CZ', 'j. F', '1. Červenec', '1. července' ],
            [ 'it_IT', 'l j F Y', 'Lundeì 11 Novembre 2019', 'Lundeì 11 Novembre 2019' ],
            [ 'el', 'l, d F Y H:i', 'Σάββατο, 19 Ιανουάριος 2019 10:50', 'Σάββατο, 19 Ιανουαρίου 2019 10:50' ],
        ];
    }

    private function get_months_names($locale)
    {
        switch ($locale) {
            case 'ru_RU':
                $months = [
                    'month'          => [ 'Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь' ],
                    'month_genitive' => [ 'января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря' ],
                ];
                break;

            case 'pl_PL':
                $months = [
                    'month'          => [ 'Styczeń', 'Luty', 'Marzec', 'Kwiecień', 'Maj', 'Czerwiec', 'Lipiec', 'Sierpień', 'Wrzesień', 'Październik', 'Listopad', 'Grudzień' ],
                    'month_genitive' => [ 'stycznia', 'lutego', 'marca', 'kwietnia', 'maja', 'czerwca', 'lipca', 'sierpnia', 'września', 'października', 'listopada', 'grudnia' ],
                ];
                break;

            case 'hr':
                $months = [
                    'month'          => [ 'Siječanj', 'Veljača', 'Ožujak', 'Travanj', 'Svibanj', 'Lipanj', 'Srpanj', 'Kolovoz', 'Rujan', 'Listopad', 'Studeni', 'Prosinac' ],
                    'month_genitive' => [ 'siječnja', 'veljače', 'ožujka', 'ožujka', 'svibnja', 'lipnja', 'srpnja', 'kolovoza', 'rujna', 'listopada', 'studenoga', 'prosinca' ],
                ];
                break;

            case 'ca':
                $months = [
                    'month'          => [ 'gener', 'febrer', 'març', 'abril', 'maig', 'juny', 'juliol', 'agost', 'setembre', 'octubre', 'novembre', 'desembre' ],
                    'month_genitive' => [ 'gener', 'febrer', 'març', 'abril', 'maig', 'juny', 'juliol', 'agost', 'setembre', 'octubre', 'novembre', 'desembre' ],
                ];
                break;

            case 'cs_CZ':
                $months = [
                    'month'          => [ 'Leden', 'Únor', 'Březen', 'Duben', 'Květen', 'Červen', 'Červenec', 'Srpen', 'Září', 'Říjen', 'Listopad', 'Prosinec' ],
                    'month_genitive' => [ 'ledna', 'února', 'března', 'dubna', 'května', 'června', 'července', 'srpna', 'září', 'října', 'listopadu', 'prosince' ],
                ];
                break;

            case 'it_IT':
                $months = [
                    'month'          => [ 'Gennaio', 'Febbraio', 'Marzo', 'Aprile', 'Maggio', 'Giugno', 'Luglio', 'Agosto', 'Settembre', 'Ottobre', 'Novembre', 'Dicembre' ],
                    'month_genitive' => [ 'Gennaio', 'Febbraio', 'Marzo', 'Aprile', 'Maggio', 'Giugno', 'Luglio', 'Agosto', 'Settembre', 'Ottobre', 'Novembre', 'Dicembre' ],
                ];
                break;

            case 'el':
                $months = [
                    'month'          => [ 'Ιανουάριος', 'Φεβρουάριος', 'Μάρτιος', 'Απρίλιος', 'Μάιος', 'Ιούνιος', 'Ιούλιος', 'Αύγουστος', 'Σεπτέμβριος', 'Οκτώβριος', 'Νοέμβριος', 'Δεκέμβριος' ],
                    'month_genitive' => [ 'Ιανουαρίου', 'Φεβρουαρίου', 'Μαρτίου', 'Απριλίου', 'Μαΐου', 'Ιουνίου', 'Ιουλίου', 'Αυγούστου', 'Σεπτεμβρίου', 'Οκτωβρίου', 'Νοεμβρίου', 'Δεκεμβρίου' ],
                ];
                break;

            default:
                $months = [
                    'month'          => [ 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December' ],
                    'month_genitive' => [ 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December' ],
                ];
        }

        return $months;
    }
}
