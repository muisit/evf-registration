<?php

namespace Tests\Support\Data;

use App\Models\WPUser as Model;
use DB;

class WPUser extends Fixture
{
    public const TESTUSER = 1045;
    public const TESTUSER2 = 2879; // organiser
    public const TESTUSER3 = 65535; // cashier
    public const TESTUSER4 = 123126; // accreditation
    public const TESTUSER5 = 8912;
    public const TESTUSERREGISTRAR = 89123; // registrar
    public const TESTUSERORGANISER = 89124; // organiser
    public const TESTUSERHOD = 3883;
    public const TESTUSERGENHOD = 3886;
    public const NOSUCHID = 894772;

    protected static function wasBooted($cls)
    {
        $count = Model::where('ID', '>', 0)->count();
        return $count > 0;
    }

    protected static function clear()
    {
        DB::table(Model::tableName())->delete();
    }

    protected static function boot()
    {
        self::booted();
        DB::table(env('WPDBPREFIX') . 'users')->insert([
            [
                'ID' => self::TESTUSER,
                'user_login' => 'test',
                'user_email' => 'test@example.com',
                'user_nicename' => 'Test',
                'display_name' => 'Test User',
                'user_pass' => '$P$BDFfwmFfX6UPB2PwIEPoQZOmRxLglO1' // password123
            ],
            [
                'ID' => self::TESTUSER2,
                'user_login' => 'test2',
                'user_email' => 'test2@example.com',
                'user_nicename' => 'Test2',
                'display_name' => 'Test User2',
                'user_pass' => '$P$BhcAyppZbEsO8p93h4kPSbYd1wVbMO1' // SuperSecretPassword
            ],
            [
                'ID' => self::TESTUSER3,
                'user_login' => 'test3',
                'user_email' => 'test3@example.com',
                'user_nicename' => 'Test3',
                'display_name' => 'Test User3',
                'user_pass' => '$P$BhcAyppZbEsO8p93h4kPSbYd1wVbMO1' // SuperSecretPassword
            ],
            [
                'ID' => self::TESTUSER4,
                'user_login' => 'test4',
                'user_email' => 'test4@example.com',
                'user_nicename' => 'Test4',
                'display_name' => 'Test User4',
                'user_pass' => '$P$BhcAyppZbEsO8p93h4kPSbYd1wVbMO1' // SuperSecretPassword
            ],
            [
                'ID' => self::TESTUSER5,
                'user_login' => 'test5',
                'user_email' => 'test5@example.com',
                'user_nicename' => 'Test5',
                'display_name' => 'Test User5',
                'user_pass' => '$P$BhcAyppZbEsO8p93h4kPSbYd1wVbMO1' // SuperSecretPassword
            ],
            [
                'ID' => self::TESTUSERHOD,
                'user_login' => 'germany',
                'user_email' => 'test6@example.com',
                'user_nicename' => 'Germany',
                'display_name' => 'Test User6',
                'user_pass' => '$P$BhcAyppZbEsO8p93h4kPSbYd1wVbMO1' // SuperSecretPassword
            ],
            [
                'ID' => self::TESTUSERGENHOD,
                'user_login' => 'testgenhod',
                'user_email' => 'test7@example.com',
                'user_nicename' => 'Test7',
                'display_name' => 'Test User7',
                'user_pass' => '$P$BhcAyppZbEsO8p93h4kPSbYd1wVbMO1' // SuperSecretPassword
            ],
            [
                'ID' => self::TESTUSERREGISTRAR,
                'user_login' => 'testregistrar',
                'user_email' => 'test8@example.com',
                'user_nicename' => 'Test8',
                'display_name' => 'Test User8',
                'user_pass' => '$P$BhcAyppZbEsO8p93h4kPSbYd1wVbMO1' // SuperSecretPassword
            ],
            [
                'ID' => self::TESTUSERORGANISER,
                'user_login' => 'testcashier',
                'user_email' => 'test9@example.com',
                'user_nicename' => 'Test9',
                'display_name' => 'Test User9',
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
            ],
            [
                'user_id' => self::TESTUSERHOD,
                'meta_key' => 'wp_capabilities',
                'meta_value' => serialize(['subscriber' => true])
            ],
            [
                'user_id' => self::TESTUSERGENHOD,
                'meta_key' => 'wp_capabilities',
                'meta_value' => serialize(['subscriber' => true])
            ]
        ]);

        DB::table(env('WPDBPREFIX') . 'options')->insert([
            [
                'option_id' => 1,
                'option_name' => env('WPDBPREFIX') . 'user_roles',
                'option_value' => serialize([
                        "administrator" => [
                            "name" => "Administrator",
                            "capabilities" => [
                                "read" => true,
                                "publish_posts" => true,
                                "edit_posts" => true,
                                "manage_ranking" => true,
                                "manage_registration" => true,
                            ]
                        ],
                        "editor" => [
                            "name" => "Editor",
                            "capabilities" => [
                                "publish_posts" => true,
                                "edit_posts" => true,
                                "delete_posts" => true,
                                "manage_ranking" => true,
                                "manage_registration" => true,
                            ]
                        ],
                        "author" => [
                            "name" => "Author",
                            "capabilities" => [
                                "upload_files" => true,
                                "edit_posts" => true,
                                "edit_published_posts" => true,
                                "publish_posts" => true,
                                "read" => true,
                            ]
                        ],
                        "contributor" => [
                            "name" => "Contributor",
                            "capabilities" => [
                                "edit_posts" => true,
                                "read" => true,
                            ]
                        ],
                        "subscriber" => [
                            "name" => "Subscriber",
                            "capabilities" => [
                                "read" => true,
                            ]
                        ],
                        "member" => [
                            "name" => "Member",
                            "capabilities" => [
                                "read" => true,
                            ]
                        ]
                ])
            ]
        ]);
    }
}
