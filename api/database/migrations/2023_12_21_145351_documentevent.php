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
            $table->string('type', 20)->nullable();
            $table->integer('type_id', false, 11)->nullable();
            $table->dropColumn('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('TD_Document', function (Blueprint $table) {
            $table->dropForeign(['event_id']);
            $table->dropColumn('event_id');
            $table->dropColumn('type');
            $table->dropColumn('type_id');
            $table->string('name', 200)->nullable();
        });
    }
};
