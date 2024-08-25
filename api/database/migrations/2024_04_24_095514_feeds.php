<?php

use App\Models\DeviceUser;
use App\Models\DeviceFeed;
use App\Models\WPPost;
use App\Models\WPOption;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('device_user_feeds', function (Blueprint $table) {
            $table->unsignedBigInteger('device_user_id');
            $table->foreign('device_user_id')->references('id')->on('device_users');
            $table->unsignedBigInteger('device_feed_id');
            $table->foreign('device_feed_id')->references('id')->on('device_feeds');

            $table->primary(['device_user_id', 'device_feed_id']);
        });

        Schema::table('device_feeds', function (Blueprint $table) {
            $table->string('locale', 10)->default('en');
            $table->string('content_model', 20)->nullable();
            $table->string('content_url', 1024)->nullable();
            $table->integer('fencer_id')->nullable();
            $table->foreign('fencer_id')->references('fencer_id')->on('TD_Fencer');
        });

        // loop over all posts and create new original feeds
        $posts = WPPost::where('ID', '>', 0)->isPost()->where('post_status', 'publish')->get();
        $feeds = [];
        foreach ($posts as $post) {
            // remove any existing feed for post models with this content_id
            // these should not have any foreign key relations during this migration
            DeviceFeed::where('content_id', $post->getKey())->where('content_model', 'post')->delete();

            $feed = new DeviceFeed();
            $feed->type = DeviceFeed::NEWS; // determines icon
            $feed->title = $post->post_title;
            $feed->content = $feed->fromWPContent($post->post_content);
            $feed->locale = 'en';
            $feed->content_id = $post->getKey();
            $feed->content_model = 'post'; // determines origin
            $feed->content_url = $feed->createPermalink($post);
            $feed->device_user_id = null; // WP posts are never linked to a single user
            $feed->created_at = (new Carbon($post->post_date))->toDateTimeString();
            $feed->updated_at = (new Carbon($post->post_modified))->toDateTimeString();
            $feed->save();
            $feeds[] = $feed->getKey();
        }

        // loop over all device users and link them to these new feeds
        $users = DeviceUser::where('id', '>', 0)->get();
        foreach ($users as $user) {
            $user->feeds()->attach($feeds);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('device_user_feeds', function (Blueprint $table) {
            $table->dropForeign(['device_user_id']);
            $table->dropForeign(['device_feed_id']);
        });
        Schema::dropIfExists('device_user_feeds');

        Schema::table('device_feeds', function (Blueprint $table) {
            $table->dropColumn('content_model');
            $table->dropColumn('content_url');
            $table->dropColumn('locale');
            $table->dropForeign(['fencer_id']);
            $table->dropColumn('fencer_id');
        });
    }
};
