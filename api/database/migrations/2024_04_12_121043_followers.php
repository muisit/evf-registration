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
        Schema::create('followers', function (Blueprint $table) {
            $table->id();
            $table->text('preferences')->nullable();

            $table->unsignedBigInteger('device_user_id');
            $table->foreign('device_user_id')->references('id')->on('device_users');

            $table->integer('fencer_id')->nullable();
            $table->foreign('fencer_id')->references('fencer_id')->on('TD_Fencer');

            $table->index(['device_user_id']);
            $table->index(['fencer_id']);
        });
        //
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('followers', function (Blueprint $table) {
            $table->dropForeign(['device_user_id']);
            $table->dropForeign(['fencer_id']);
        });
        Schema::dropIfExists('followers');
    }
};
