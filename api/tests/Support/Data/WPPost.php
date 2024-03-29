<?php

namespace Tests\Support\Data;

use App\Models\WPPost as Model;
use DB;

class WPPost extends Fixture
{
    public const BLOG1 = 1045;
    public const BLOG2 = 2879;
    public const BLOG3 = 65535;
    public const EVENT1 = 123126;
    public const EVENT2 = 8912;
    public const EVENT3 = 89123;
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
        DB::table(env('WPDBPREFIX') . 'posts')->insert([
            [
                'ID' => self::BLOG1,
                'post_author' => WPUser::TESTUSER,
                'post_date' => '2020-01-01 12:34:56',
                'post_date_gmt' => '2020-01-01 12:34:56',
                'post_content' => 'Blog content',
                'post_title' => 'Blog title',
                'post_excerpt' => '',
                'post_status' => 'publish',
                'comment_status' => 'closed',
                'ping_status' => 'closed',
                'post_password' => '',
                'post_name' => 'blog1',
                'to_ping' => '',
                'pinged' => '',
                'post_modified' => '2020-01-01 12:34:56',
                'post_modified_gmt' => '2020-01-01 12:34:56',
                'post_content_filtered' => '',
                'post_parent' => 0,
                'guid' => 'http://local/test/blog1',
                'menu_order' => 0,
                'post_type' => 'post',
                'post_mime_type' => '',
                'comment_count' => 0
            ],
            [
                'ID' => self::BLOG2,
                'post_author' => WPUser::TESTUSER,
                'post_date' => '2020-01-01 12:34:56',
                'post_date_gmt' => '2020-01-01 12:34:56',
                'post_content' => 'Blog content 2',
                'post_title' => 'Blog 2',
                'post_excerpt' => '',
                'post_status' => 'publish',
                'comment_status' => 'closed',
                'ping_status' => 'closed',
                'post_password' => '',
                'post_name' => 'blog2',
                'to_ping' => '',
                'pinged' => '',
                'post_modified' => '2020-01-01 12:34:56',
                'post_modified_gmt' => '2020-01-01 12:34:56',
                'post_content_filtered' => '',
                'post_parent' => 0,
                'guid' => 'http://local/test/blog2',
                'menu_order' => 0,
                'post_type' => 'post',
                'post_mime_type' => '',
                'comment_count' => 0
            ],
            [
                'ID' => self::BLOG3,
                'post_author' => WPUser::TESTUSER,
                'post_date' => '2020-01-01 12:34:56',
                'post_date_gmt' => '2020-01-01 12:34:56',
                'post_content' => 'Another blog content',
                'post_title' => 'Another Blog',
                'post_excerpt' => '',
                'post_status' => 'publish',
                'comment_status' => 'closed',
                'ping_status' => 'closed',
                'post_password' => '',
                'post_name' => 'blog3',
                'to_ping' => '',
                'pinged' => '',
                'post_modified' => '2020-01-01 12:34:56',
                'post_modified_gmt' => '2020-01-01 12:34:56',
                'post_content_filtered' => '',
                'post_parent' => 0,
                'guid' => 'http://local/test/blog3',
                'menu_order' => 0,
                'post_type' => 'post',
                'post_mime_type' => '',
                'comment_count' => 0
            ],
            [
                'ID' => self::EVENT1,
                'post_author' => WPUser::TESTUSER,
                'post_date' => '2020-01-01 12:34:56',
                'post_date_gmt' => '2020-01-01 12:34:56',
                'post_content' => '',
                'post_title' => 'Event 1',
                'post_excerpt' => '',
                'post_status' => 'publish',
                'comment_status' => 'closed',
                'ping_status' => 'closed',
                'post_password' => '',
                'post_name' => 'event1',
                'to_ping' => '',
                'pinged' => '',
                'post_modified' => '2020-01-01 12:34:56',
                'post_modified_gmt' => '2020-01-01 12:34:56',
                'post_content_filtered' => '',
                'post_parent' => 0,
                'guid' => 'http://local/test/event1',
                'menu_order' => 0,
                'post_type' => 'tribe_events',
                'post_mime_type' => '',
                'comment_count' => 0
            ],
            [
                'ID' => self::EVENT2,
                'post_author' => WPUser::TESTUSER,
                'post_date' => '2020-01-01 12:34:56',
                'post_date_gmt' => '2020-01-01 12:34:56',
                'post_content' => '',
                'post_title' => 'Event 2',
                'post_excerpt' => '',
                'post_status' => 'publish',
                'comment_status' => 'closed',
                'ping_status' => 'closed',
                'post_password' => '',
                'post_name' => 'event2',
                'to_ping' => '',
                'pinged' => '',
                'post_modified' => '2020-01-01 12:34:56',
                'post_modified_gmt' => '2020-01-01 12:34:56',
                'post_content_filtered' => '',
                'post_parent' => 0,
                'guid' => 'http://local/test/event2',
                'menu_order' => 0,
                'post_type' => 'tribe_events',
                'post_mime_type' => '',
                'comment_count' => 0
            ],
            [
                'ID' => self::EVENT3,
                'post_author' => WPUser::TESTUSER,
                'post_date' => '2020-01-01 12:34:56',
                'post_date_gmt' => '2020-01-01 12:34:56',
                'post_content' => '',
                'post_title' => 'Event 3',
                'post_excerpt' => '',
                'post_status' => 'publish',
                'comment_status' => 'closed',
                'ping_status' => 'closed',
                'post_password' => '',
                'post_name' => 'event3',
                'to_ping' => '',
                'pinged' => '',
                'post_modified' => '2020-01-01 12:34:56',
                'post_modified_gmt' => '2020-01-01 12:34:56',
                'post_content_filtered' => '',
                'post_parent' => 0,
                'guid' => 'http://local/test/event3',
                'menu_order' => 0,
                'post_type' => 'tribe_events',
                'post_mime_type' => '',
                'comment_count' => 0
            ],
        ]);

        $i = 1;
        DB::table(env('WPDBPREFIX') . 'postmeta')->insert([
            [
                'meta_id' => $i++,
                'post_id' => self::EVENT1,
                'meta_key' => '_EventStartDate',
                'meta_value' => '2020-03-10 12:00:00'
            ],
            [
                'meta_id' => $i++,
                'post_id' => self::EVENT1,
                'meta_key' => '_EventEndDate',
                'meta_value' => '2020-04-10 12:00:00'
            ],
            [
                'meta_id' => $i++,
                'post_id' => self::EVENT1,
                'meta_key' => '_EventUrl',
                'meta_value' => 'http://localhost/event1'
            ],
            [
                'meta_id' => $i++,
                'post_id' => self::EVENT1,
                'meta_key' => '_VenueCity',
                'meta_value' => 'Event1City'
            ],
            [
                'meta_id' => $i++,
                'post_id' => self::EVENT1,
                'meta_key' => '_VenueCountry',
                'meta_value' => 'France'
            ],
            [
                'meta_id' => $i++,
                'post_id' => self::EVENT1,
                'meta_key' => '_VenueUrl',
                'meta_value' => 'http://example.org/venue'
            ],
            [
                'meta_id' => $i++,
                'post_id' => self::EVENT1,
                'meta_key' => '_OrganizerWebsite',
                'meta_value' => 'http://example.org/organizer'
            ],
            [
                'meta_id' => $i++,
                'post_id' => self::EVENT1,
                'meta_key' => '_OrganizerEmail',
                'meta_value' => 'organizer@example.org'
            ],
            [
                'meta_id' => $i++,
                'post_id' => self::EVENT2,
                'meta_key' => '_EventStartDate',
                'meta_value' => '2020-04-10 12:00:00'
            ],
            [
                'meta_id' => $i++,
                'post_id' => self::EVENT2,
                'meta_key' => '_EventEndDate',
                'meta_value' => '2020-04-11 12:00:00'
            ],
            [
                'meta_id' => $i++,
                'post_id' => self::EVENT2,
                'meta_key' => '_EventUrl',
                'meta_value' => 'http://localhost/event2'
            ],
            [
                'meta_id' => $i++,
                'post_id' => self::EVENT2,
                'meta_key' => '_VenueCity',
                'meta_value' => 'Event1City'
            ],
            [
                'meta_id' => $i++,
                'post_id' => self::EVENT2,
                'meta_key' => '_VenueCountry',
                'meta_value' => 'France'
            ],
            [
                'meta_id' => $i++,
                'post_id' => self::EVENT2,
                'meta_key' => '_VenueUrl',
                'meta_value' => 'http://example.org/venue'
            ],
            [
                'meta_id' => $i++,
                'post_id' => self::EVENT2,
                'meta_key' => '_OrganizerWebsite',
                'meta_value' => 'http://example.org/organizer'
            ],
            [
                'meta_id' => $i++,
                'post_id' => self::EVENT2,
                'meta_key' => '_OrganizerEmail',
                'meta_value' => 'organizer@example.org'
            ],
            [
                'meta_id' => $i++,
                'post_id' => self::EVENT3,
                'meta_key' => '_EventStartDate',
                'meta_value' => '2021-03-10 12:00:00'
            ],
            [
                'meta_id' => $i++,
                'post_id' => self::EVENT3,
                'meta_key' => '_EventEndDate',
                'meta_value' => '2020-04-10 12:00:00'
            ],
            [
                'meta_id' => $i++,
                'post_id' => self::EVENT3,
                'meta_key' => '_EventUrl',
                'meta_value' => 'http://localhost/event3'
            ],
            [
                'meta_id' => $i++,
                'post_id' => self::EVENT3,
                'meta_key' => '_VenueCity',
                'meta_value' => 'Event3City'
            ],
            [
                'meta_id' => $i++,
                'post_id' => self::EVENT3,
                'meta_key' => '_VenueCountry',
                'meta_value' => 'Germany'
            ],
            [
                'meta_id' => $i++,
                'post_id' => self::EVENT3,
                'meta_key' => '_VenueUrl',
                'meta_value' => 'http://example.org/venue2'
            ],
            [
                'meta_id' => $i++,
                'post_id' => self::EVENT3,
                'meta_key' => '_OrganizerWebsite',
                'meta_value' => 'http://example.org/organizer2'
            ],
            [
                'meta_id' => $i++,
                'post_id' => self::EVENT3,
                'meta_key' => '_OrganizerEmail',
                'meta_value' => 'organizer2@example.org'
            ],
        ]);
    }
}
