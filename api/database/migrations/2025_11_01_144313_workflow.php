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
        Schema::create('workflows', function (Blueprint $table) {
            $table->id();
            $table->text('sandbox');
        });
        DB::statement("ALTER TABLE evfregistration.TD_Result MODIFY COLUMN result_in_ranking enum('Y','N','E','D') CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT 'N' NULL;");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workflows');
    }
};
