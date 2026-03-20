<?php

declare(strict_types=1);

/**
 * @group sanitize_sql_orderby
 *
 * @covers ::sanitize_sql_orderby
 */
class Tests_Formatting_SanitizeOrderby extends WP_UnitTestCase
{
    /**
     * @dataProvider data_sanitize_sql_orderby_valid
     */
    public function test_sanitize_sql_orderby_valid($orderby)
    {
        $this->assertSame($orderby, sanitize_sql_orderby($orderby));
    }
    public function data_sanitize_sql_orderby_valid()
    {
        return [
            [ '1' ],
            [ '1 ASC' ],
            [ '1 ASC, 2' ],
            [ '1 ASC, 2 DESC' ],
            [ '1 ASC, 2 DESC, 3' ],
            [ '       1      DESC' ],
            [ 'field ASC' ],
            [ 'field1 ASC, field2' ],
            [ 'field_1 ASC, field_2 DESC' ],
            [ 'field1, field2 ASC' ],
            [ '`field1`' ],
            [ '`field1` ASC' ],
            [ '`field` ASC, `field2`' ],
            [ 'RAND()' ],
            [ '   RAND(  )   ' ],
        ];
    }

    /**
     * @dataProvider data_sanitize_sql_orderby_invalid
     */
    public function test_sanitize_sql_orderby_invalid($orderby)
    {
        $this->assertFalse(sanitize_sql_orderby($orderby));
    }
    public function data_sanitize_sql_orderby_invalid()
    {
        return [
            [ '' ],
            [ '1 2' ],
            [ '1, 2 3' ],
            [ '1 DESC, ' ],
            [ 'field-1' ],
            [ 'field DESC,' ],
            [ 'field1 field2' ],
            [ 'field RAND()' ],
            [ 'RAND() ASC' ],
            [ '`field1` ASC, `field2' ],
            [ 'field, !@#$%^' ],
        ];
    }
}
