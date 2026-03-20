<?php

declare(strict_types=1);

/**
 * @group ms-required
 * @group multisite
 */
class Tests_Multisite_IsEmailAddressUnsafe extends WP_UnitTestCase
{
    public function test_string_domain_list_should_be_split_on_line_breaks()
    {
        update_site_option('banned_email_domains', "foo.com\nbar.org\nbaz.gov");
        $this->assertTrue(is_email_address_unsafe('foo@bar.org'));
        $this->assertFalse(is_email_address_unsafe('foo@example.org'));
    }

    /**
     * @dataProvider data_unsafe
     * @ticket 25046
     * @ticket 21570
     */
    public function test_unsafe_emails($banned, $email)
    {
        update_site_option('banned_email_domains', $banned);
        $this->assertTrue(is_email_address_unsafe($email));
    }

    /**
     * @dataProvider data_safe
     * @ticket 25046
     * @ticket 21570
     */
    public function test_safe_emails($banned, $email)
    {
        update_site_option('banned_email_domains', $banned);
        $this->assertFalse(is_email_address_unsafe($email));
    }

    public function data_unsafe()
    {
        return [
            // 25046
            'case_insensitive_1' => [
                [ 'baR.com' ],
                'test@Bar.com',
            ],
            'case_insensitive_2' => [
                [ 'baR.com' ],
                'tEst@bar.com',
            ],
            'case_insensitive_3' => [
                [ 'barfoo.COM' ],
                'test@barFoo.com',
            ],
            'case_insensitive_4' => [
                [ 'baR.com' ],
                'tEst@foo.bar.com',
            ],
            'case_insensitive_5' => [
                [ 'BAZ.com' ],
                'test@baz.Com',
            ],

            // 21570
            [
                [ 'bar.com', 'foo.co' ],
                'test@bar.com',
            ],
            'subdomain_1'        => [
                [ 'bar.com', 'foo.co' ],
                'test@foo.bar.com',
            ],
            [
                [ 'bar.com', 'foo.co' ],
                'test@foo.co',
            ],
            'subdomain_2'        => [
                [ 'bar.com', 'foo.co' ],
                'test@subdomain.foo.co',
            ],
        ];
    }

    public function data_safe()
    {
        return [
            // 25046
            [
                [ 'baR.com', 'Foo.co', 'barfoo.COM', 'BAZ.com' ],
                'test@Foobar.com',
            ],
            [
                [ 'baR.com', 'Foo.co', 'barfoo.COM', 'BAZ.com' ],
                'test@Foo-bar.com',
            ],
            [
                [ 'baR.com', 'Foo.co', 'barfoo.COM', 'BAZ.com' ],
                'tEst@foobar.com',
            ],
            [
                [ 'baR.com', 'Foo.co', 'barfoo.COM', 'BAZ.com' ],
                'test@Subdomain.Foo.com',
            ],
            [
                [ 'baR.com', 'Foo.co', 'barfoo.COM', 'BAZ.com' ],
                'test@feeBAz.com',
            ],

            // 21570
            [
                [ 'bar.com', 'foo.co' ],
                'test@foobar.com',
            ],
            [
                [ 'bar.com', 'foo.co' ],
                'test@foo-bar.com',
            ],
            [
                [ 'bar.com', 'foo.co' ],
                'test@foo.com',
            ],
            [
                [ 'bar.com', 'foo.co' ],
                'test@subdomain.foo.com',
            ],
        ];
    }

    public function test_email_with_only_top_level_domain_returns_safe()
    {
        update_site_option('banned_email_domains', 'bar.com');
        $safe = is_email_address_unsafe('email@localhost');
        delete_site_option('banned_email_domains');

        $this->assertFalse($safe);
    }

    public function test_invalid_email_without_domain_returns_safe()
    {
        update_site_option('banned_email_domains', 'bar.com');
        $safe = is_email_address_unsafe('invalid-email');
        delete_site_option('bar.com');

        $this->assertFalse($safe);
    }
}
