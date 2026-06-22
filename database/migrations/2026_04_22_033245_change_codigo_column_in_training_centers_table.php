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
        Schema::table('training_centers', function (Blueprint $table) {
            // smallInteger solo admite hasta 32767; los códigos SENA pueden superar eso.
            $table->unsignedInteger('codigo')->change();
        });
    }

    public function down(): void
    {
        Schema::table('training_centers', function (Blueprint $table) {
            $table->smallInteger('codigo')->change();
        });
    }
};
