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
        Schema::table('people', function (Blueprint $table) {
            $table->dropForeign(['entity_position_id']);
            $table->dropForeign(['linkage_type_id']);
            $table->dropForeign(['training_program_id']);
        });
        Schema::table('people', function (Blueprint $table) {
            $table->unsignedBigInteger('entity_position_id')->nullable()->change();
            $table->unsignedBigInteger('linkage_type_id')->nullable()->change();
            $table->unsignedBigInteger('training_program_id')->nullable()->change();
        });
        Schema::table('people', function (Blueprint $table) {
            $table->foreign('entity_position_id')->references('id')->on('entity_positions')->onDelete('set null');
            $table->foreign('linkage_type_id')->references('id')->on('linkage_types')->onDelete('set null');
            $table->foreign('training_program_id')->references('id')->on('training_programs')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('people', function (Blueprint $table) {
            $table->dropForeign(['entity_position_id']);
            $table->dropForeign(['linkage_type_id']);
            $table->dropForeign(['training_program_id']);
        });
        Schema::table('people', function (Blueprint $table) {
            $table->unsignedBigInteger('entity_position_id')->nullable(false)->change();
            $table->unsignedBigInteger('linkage_type_id')->nullable(false)->change();
            $table->unsignedBigInteger('training_program_id')->nullable(false)->change();
        });
        Schema::table('people', function (Blueprint $table) {
            $table->foreign('entity_position_id')->references('id')->on('entity_positions')->onDelete('cascade');
            $table->foreign('linkage_type_id')->references('id')->on('linkage_types')->onDelete('cascade');
            $table->foreign('training_program_id')->references('id')->on('training_programs')->onDelete('cascade');
        });
    }
};
