<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('device_users', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->string('email', 1024)->nullable();
            $table->string('password', 255)->nullable();
            $table->text('preferences')->nullable();
            $table->integer('fencer_id')->nullable();
            $table->foreign('fencer_id')->references('fencer_id')->on('TD_Fencer');
            $table->timestamps(6);

            $table->index(['uuid']);
        });

        Schema::create('device_ids', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->string('link_code', 20)->nullable();
            $table->text('platform')->nullable();
            $table->unsignedBigInteger('device_user_id');
            $table->foreign('device_user_id')->references('id')->on('device_users');
            $table->timestamps(6);

            $table->index(['uuid']);
        });

        Schema::create('device_feeds', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->string('type', 5);
            $table->string('title', 255)->nullable();
            $table->text('content')->nullable();
            $table->integer('content_id')->nullable();
            $table->unsignedBigInteger('device_user_id')->nullable();
            $table->foreign('device_user_id')->references('id')->on('device_users');
            $table->timestamps(6);

            $table->index(['uuid']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('device_feeds', function (Blueprint $table) {
            $table->dropForeign(['device_user_id']);
        });
        Schema::dropIfExists('device_feeds');

        Schema::table('device_ids', function (Blueprint $table) {
            $table->dropForeign(['device_user_id']);
        });
        Schema::dropIfExists('device_ids');

        Schema::table('device_users', function (Blueprint $table) {
            $table->dropForeign(['fencer_id']);
        });
        Schema::dropIfExists('device_users');
    }
};
