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
        Schema::table('TD_Document', function (Blueprint $table) {
            $table->foreignId('event_id');
            $table->foreign('event_id')->references('event_id')->on('TD_Event')
                ->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('TD_Document', function (Blueprint $table) {
            $table->dropForeign(['event_id']);
        });
    }
};
