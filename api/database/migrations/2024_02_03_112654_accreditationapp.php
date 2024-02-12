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
        Schema::create('accreditation_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20);
            $table->string('type', 25);
            $table->integer('accreditation_id')->nullable();
            $table->foreign('accreditation_id')->references('id')->on('TD_Accreditation');
            $table->integer('event_id')->nullable();
            $table->foreign('event_id')->references('event_id')->on('TD_Event');
            $table->timestamps(6);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accreditation_codes', function (Blueprint $table) {
            $table->dropForeign(['accreditation_id']);
            $table->dropForeign(['event_id']);
        });
        Schema::dropIfExists('accreditation_codes');
    }
};
