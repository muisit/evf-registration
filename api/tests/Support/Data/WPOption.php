<?php

namespace Tests\Support\Data;

use App\Models\WPOption as Model;
use DB;

class WPOption extends Fixture
{
    public const OPT1 = 87561;
    public const OPT2 = 87562;
    public const OPTROLES = 87563;

    protected static function wasBooted($cls)
    {
        $count = Model::where('option_id', '>', 0)->count();
        return $count == 3;
    }

    protected static function clear()
    {
        DB::table(Model::tableName())->delete();
    }

    protected static function boot()
    {
        self::booted();
        DB::table(env('WPDBPREFIX') . 'options')->insert([
            [
                'option_id' => self::OPT1,
                'option_name' => 'permalink_structure',
                'option_value' => '/%postname%/',
                'autoload' => true
            ],
            [
                'option_id' => self::OPT2,
                'option_name' => 'home',
                'option_value' => 'http://localhost',
                'autoload' => true
            ],
            [
                'option_id' => self::OPTROLES,
                'option_name' => 'wp_user_roles',
                'option_value' => 'a:6:{s:13:"administrator";a:2:{s:4:"name";s:13:"Administrator";s:12:"capabilities";a:5:{s:4:"read";b:1;s:13:"publish_posts";b:1;s:10:"edit_posts";b:1;s:14:"manage_ranking";b:1;s:19:"manage_registration";b:1;}}s:6:"editor";a:2:{s:4:"name";s:6:"Editor";s:12:"capabilities";a:5:{s:13:"publish_posts";b:1;s:10:"edit_posts";b:1;s:12:"delete_posts";b:1;s:14:"manage_ranking";b:1;s:19:"manage_registration";b:1;}}s:6:"author";a:2:{s:4:"name";s:6:"Author";s:12:"capabilities";a:5:{s:12:"upload_files";b:1;s:10:"edit_posts";b:1;s:20:"edit_published_posts";b:1;s:13:"publish_posts";b:1;s:4:"read";b:1;}}s:11:"contributor";a:2:{s:4:"name";s:11:"Contributor";s:12:"capabilities";a:2:{s:10:"edit_posts";b:1;s:4:"read";b:1;}}s:10:"subscriber";a:2:{s:4:"name";s:10:"Subscriber";s:12:"capabilities";a:1:{s:4:"read";b:1;}}s:6:"member";a:2:{s:4:"name";s:6:"Member";s:12:"capabilities";a:1:{s:4:"read";b:1;}}}',
                'autoload' => true
            ]
        ]);
    }
}
