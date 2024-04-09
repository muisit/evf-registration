<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Models\Fencer;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('rankings', function (Blueprint $table) {
            $table->id();
            $table->dateTime('ranking_date');
            $table->integer('event_id')->nullable();
            $table->foreign('event_id')->references('event_id')->on('TD_Event');
            $table->timestamp('updated_at')->nullable()->default(null);
            $table->timestamp('created_at')->nullable()->default(null);
            $table->integer('category_id');
            $table->foreign('category_id')->references('category_id')->on('TD_Category');
            $table->integer('weapon_id');
            $table->foreign('weapon_id')->references('weapon_id')->on('TD_Weapon');

            $table->index(['ranking_date']);
            $table->index(['event_id']);
        });
        
        Schema::create('ranking_positions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ranking_id');
            $table->foreign('ranking_id')->references('id')->on('rankings');
            $table->integer('fencer_id');
            $table->foreign('fencer_id')->references('fencer_id')->on('TD_Fencer');

            $table->unsignedInteger('position');
            $table->decimal('points', 8, 2);
            $table->text('settings')->nullable();

            $table->index(['ranking_id', 'position'], 'ranking_by_pos');
            $table->index(['fencer_id']);
        });

        Schema::table('TD_Fencer', function (Blueprint $table) {
            $table->uuid('uuid')->nullable();
            $table->index(['uuid']);
        });

        $ids = Fencer::select('fencer_id')->get()->pluck('fencer_id');
        foreach ($ids as $id) {
            Fencer::where('fencer_id', $id)->update(['uuid' => Str::uuid()->toString()]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('TD_Fencer', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });

        Schema::table('ranking_positions', function (Blueprint $table) {
            $table->dropForeign(['ranking_id']);
            $table->dropForeign(['fencer_id']);
        });
        Schema::dropIfExists('ranking_positions');

        Schema::table('rankings', function (Blueprint $table) {
            $table->dropForeign(['event_id']);
            $table->dropForeign(['category_id']);
            $table->dropForeign(['weapon_id']);
        });
        Schema::dropIfExists('rankings');
    }
};
