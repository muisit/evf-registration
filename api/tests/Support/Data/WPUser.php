<?php

namespace Tests\Support\Data;

use DB;

class WPUser extends Fixture
{
    public const TESTUSER = 1045;
    public const TESTUSER2 = 2879;
    public const TESTUSER3 = 65535;
    public const TESTUSER4 = 123126;

    protected static function boot()
    {
        self::booted();
        DB::table(env('WPDBPREFIX') . 'users')->insert([
            [
                'ID' => self::TESTUSER,
                'user_email' => 'test@example.com',
                'user_nicename' => 'Test',
                'display_name' => 'Test User',
                'user_pass' => '$P$BDFfwmFfX6UPB2PwIEPoQZOmRxLglO1' // password123
            ],
            [
                'ID' => self::TESTUSER2,
                'user_email' => 'test2@example.com',
                'user_nicename' => 'Test2',
                'display_name' => 'Test User2',
                'user_pass' => '$P$BhcAyppZbEsO8p93h4kPSbYd1wVbMO1' // SuperSecretPassword
            ],
            [
                'ID' => self::TESTUSER3,
                'user_email' => 'test3@example.com',
                'user_nicename' => 'Test3',
                'display_name' => 'Test User3',
                'user_pass' => '$P$BhcAyppZbEsO8p93h4kPSbYd1wVbMO1' // SuperSecretPassword
            ],
            [
                'ID' => self::TESTUSER4,
                'user_email' => 'test4@example.com',
                'user_nicename' => 'Test4',
                'display_name' => 'Test User4',
                'user_pass' => '$P$BhcAyppZbEsO8p93h4kPSbYd1wVbMO1' // SuperSecretPassword
            ]
        ]);

        DB::table(env('WPDBPREFIX') . 'usermeta')->insert([
            [
                'user_id' => self::TESTUSER,
                'meta_key' => 'wp_capabilities',
                'meta_value' => serialize(['administrator' => true])
            ],
            [
                'user_id' => self::TESTUSER2,
                'meta_key' => 'wp_capabilities',
                'meta_value' => serialize(['subscriber' => true, 'author' => true, 'editor' => true, 'contributor' => true])
            ],
            [
                'user_id' => self::TESTUSER3,
                'meta_key' => 'wp_capabilities',
                'meta_value' => serialize(['subscriber' => true])
            ],
            [
                'user_id' => self::TESTUSER4,
                'meta_key' => 'wp_capabilities',
                'meta_value' => serialize(['subscriber' => true])
            ]
        ]);
    }
}
