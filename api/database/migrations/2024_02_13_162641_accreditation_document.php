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
        Schema::create('accreditation_documents', function (Blueprint $table) {
            $table->id();
            $table->integer('accreditation_id')->nullable();
            $table->foreign('accreditation_id')->references('id')->on('TD_Accreditation');
            $table->unsignedInteger('card')->nullable();
            $table->unsignedInteger('document_nr')->nullable();
            $table->text('payload')->nullable();
            $table->timestamps();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->foreign('created_by')->references('id')->on('accreditation_codes');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->foreign('updated_by')->references('id')->on('accreditation_codes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accreditation_documents', function (Blueprint $table) {
            $table->dropForeign(['accreditation_id']);
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
        });
        Schema::dropIfExists('accreditation_documents');
    }
};
