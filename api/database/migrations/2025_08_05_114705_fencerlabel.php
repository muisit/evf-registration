<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('fencer_labels', function (Blueprint $table) {
            $table->id();
            $table->integer('fencer_id');
            $table->foreign('fencer_id')->references('fencer_id')->on('TD_Fencer');
            $table->string('label', 300);
            $table->string('type', 10);
            $table->index(['label']);
            $table->index(['fencer_id']);
            $table->index(['type']);
        });

        DB::statement("INSERT INTO `fencer_labels` (fencer_id, label, type) SELECT fencer_id, fencer_firstname, 'first' FROM `TD_Fencer` where not fencer_firstname is NULL;");
        DB::statement("INSERT INTO `fencer_labels` (fencer_id, label, type) SELECT fencer_id, fencer_surname, 'last' FROM `TD_Fencer` where not fencer_surname IS NULL;");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fencer_labels', function (Blueprint $table) {
            $table->dropForeign(['fencer_id']);
        });
        Schema::dropIfExists('fencer_labels');
    }
};
