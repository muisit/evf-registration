<?php

namespace Tests\Support\Data;

use DB;
use App\Models\DeviceFeed as Model;
use Carbon\Carbon;

class DeviceFeed extends Fixture
{
    public const FEED1 = 66711;
    public const NEWS1 = 66712;
    public const RESULT1 = 66713;
    public const RANKING1 = 66714;
    public const FEED2 = 66715;
    public const NOSUCHID = 66892;

    protected static function wasBooted($cls)
    {
        $count = Model::where('id', '>', 0)->count();
        return $count > 0;
    }

    protected static function clear()
    {
        DB::table('device_user_feeds')->delete();
        DB::table(Model::tableName())->delete();
    }

    protected static function boot()
    {
        self::booted();
        $model = Model::create([
            'id' => self::FEED1,
            'uuid' => 'this-is-a-uuid',
            'type' => Model::NOTIFICATION,
            'title' => 'you have been notified',
            'content' => 'This is a generic notification',
            'content_id' => null,
            'content_model' => null,
            'locale' => 'en',
            'created_at' => '2020-01-01 12:34:56',
            'updated_at' => '2020-01-01 12:34:56',
        ]);
        $model->save();
        $model->users()->attach([DeviceUser::DEVICEUSER1]);

        $model = Model::create([
            'id' => self::NEWS1,
            'uuid' => 'this-is-a-uuid2',
            'type' => Model::NEWS,
            'title' => 'News item title',
            'content' => 'News item content',
            'content_id' => WPPost::BLOG1,
            'content_model' => 'post',
            'locale' => 'en',
            'created_at' => '2020-01-02 12:34:56',
            'updated_at' => '2020-01-02 12:34:56',
        ]);
        $model->save();
        $model->users()->attach([DeviceUser::DEVICEUSER1, DeviceUser::DEVICEUSER2]);

        $model = Model::create([
            'id' => self::RESULT1,
            'uuid' => 'this-is-a-uuid3',
            'type' => Model::RESULT,
            'title' => 'You ended 3rd in Faches',
            'content' => 'You ended up 3rd in the Foil V2 competition in Fâches, France on 23 January 2024',
            'content_id' => null,
            'content_model' => 'result',
            'locale' => 'en',
            'created_at' => '2020-01-03 12:34:56',
            'updated_at' => '2020-01-08 12:34:56',
        ]);
        $model->save();
        $model->users()->attach([DeviceUser::DEVICEUSER1]);

        $model = Model::create([
            'id' => self::RANKING1,
            'uuid' => 'this-is-a-uuid4',
            'type' => Model::RANKING,
            'title' => 'Ranking position: 42nd',
            'content' => 'You are 42nd in the Foil V1 ranking of 2024-02-01',
            'content_id' => 48, // TODO: ranking cache id
            'content_model' => 'ranking',
            'locale' => 'en',
            'created_at' => '2020-01-04 12:34:56',
            'updated_at' => '2020-01-04 12:34:56',
        ]);
        $model->save();
        $model->users()->attach([DeviceUser::DEVICEUSER1, DeviceUser::DEVICEUSER2]);

        $model = Model::create([
            'id' => self::FEED2,
            'uuid' => 'this-is-a-uuid5',
            'type' => Model::NOTIFICATION,
            'title' => 'you have been notified again',
            'content' => 'This is still a generic notification',
            'content_id' => null,
            'content_model' => null,
            'locale' => 'en',
            'created_at' => '2020-01-05 12:34:56',
            'updated_at' => '2020-01-05 12:34:56',
        ]);
        $model->save();
        $model->users()->attach([DeviceUser::DEVICEUSER1, DeviceUser::DEVICEUSER2]);
    }
}
