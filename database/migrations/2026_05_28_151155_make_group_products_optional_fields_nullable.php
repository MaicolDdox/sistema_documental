<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('group_products', function (Blueprint $table) {
            $table->string('nombre_programa_formacion_impacto')->nullable()->change();
            $table->unsignedBigInteger('minciencias_typology_id')->nullable()->change();
            $table->unsignedBigInteger('minciencias_subcategory_id')->nullable()->change();
            $table->unsignedBigInteger('knowledge_grand_area_id')->nullable()->change();
            $table->unsignedBigInteger('knowledge_area_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('group_products', function (Blueprint $table) {
            $table->string('nombre_programa_formacion_impacto')->nullable(false)->change();
            $table->unsignedBigInteger('minciencias_typology_id')->nullable(false)->change();
            $table->unsignedBigInteger('minciencias_subcategory_id')->nullable(false)->change();
            $table->unsignedBigInteger('knowledge_grand_area_id')->nullable(false)->change();
            $table->unsignedBigInteger('knowledge_area_id')->nullable(false)->change();
        });
    }
};
