<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('group_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('author_id')
                ->constrained('users')
                ->onDelete('CASCADE');
            $table->foreignId('product_id')
                ->constrained('products')
                ->onDelete('CASCADE');
            $table->enum('tipo_proyecto_origen', [
                'SGPS',
                'CAPACIDAD INSTALADA',
                'FORMATIVA',
                'INICIATIVA CENTRO',
                'ARTICULACION',
                'SEMILLEROS',
                'OTRO',
            ]);
            $table->string('campo_otro')
                ->comment('Activo solo si tipo_proyecto_origen = OTRO')
                ->nullable();
            $table->string('codigo_proyecto_origen')
                ->comment('Código si el origen es SGPS o CAPACIDAD INSTALADA; de lo contrario "0"');
            $table->string('titulo');
            $table->text('descripccion')->nullable();
            $table->year('anio_publicacion');
            $table->string('nombre_programa_formacion_impacto');
            $table->foreignId('minciencias_typology_id')
                ->constrained('minciencias_typologies')
                ->onDelete('CASCADE');
            $table->foreignId('minciencias_subcategory_id')
                ->constrained('minciencias_subcategories')
                ->onDelete('CASCADE');
            $table->foreignId('knowledge_grand_area_id')
                ->constrained('knowledge_grand_areas')
                ->onDelete('CASCADE');
            $table->foreignId('knowledge_area_id')
                ->constrained('knowledge_areas')
                ->onDelete('CASCADE');
            $table->boolean('tiene_repositorio');
            $table->string('url_repositorio')
                ->comment('URL del repositorio si tiene_repositorio = true')
                ->nullable();
            $table->string('evidencia')
                ->comment('Archivo de evidencia si tiene_repositorio = false')
                ->nullable();
            $table->boolean('autoriza_datos');
            $table->enum('estado_revision', ['pendiente', 'en_revision', 'aprobado', 'rechazado'])
                ->default('pendiente');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('group_products');
    }
};
