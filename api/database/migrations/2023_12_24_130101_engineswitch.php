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
        DB::statement("ALTER TABLE TD_Document ENGINE=InnoDB;");
        DB::statement("ALTER TABLE wp_options ENGINE=InnoDB;");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
