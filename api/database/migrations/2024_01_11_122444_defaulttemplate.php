<?php

use App\Models\AccreditationTemplate;
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
        Schema::table('TD_Accreditation_Template', function (Blueprint $table) {
            $table->string('is_default', 1)->default('N');
        });
         // default templates as of 2024-01-11
        DB::table(AccreditationTemplate::tableName())->whereIn('id', [1, 5, 6, 7, 8])->update(['is_default' => 'Y']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('TD_Accreditation_Template', function (Blueprint $table) {
            $table->dropColumn('is_default');
        });
        AccreditationTemplate::whereIn('id', [1, 5, 6, 7, 8])->update(['event_id' => 27]);
    }
};
