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
        Schema::table('device_ids', function (Blueprint $table) {
            $table->text('email')->nullable();
            $table->dateTime('verification_code_sent')->nullable();
            $table->string('verification_code', 10)->nullable();
            $table->dropColumn('link_code');
        });
        Schema::table('device_users', function (Blueprint $table) {
            $table->dateTime('email_verified_at')->nullable();
            $table->dropColumn('password');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('device_ids', function (Blueprint $table) {
            $table->dropColumn('email');
            $table->dropColumn('verification_code');
            $table->dropColumn('verification_code_sent');
            $table->string('link_code', 20)->nullable();
        });
        Schema::table('device_users', function (Blueprint $table) {
            $table->dropColumn('email_verified_at');
            $table->string('password', 255)->nullable();
        });
    }
};
