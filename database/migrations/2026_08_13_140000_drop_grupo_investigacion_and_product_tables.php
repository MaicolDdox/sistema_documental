<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cierre del rediseño de roles (sub-tanda 2c): elimina la entidad "Grupo de
 * Investigación" y el modelo antiguo de Product/GroupProduct por completo.
 * El producto final ahora vive como project_evidences.tipo=producto_final
 * (ver migración 2026_08_13_090300). Los datos existentes no se preservan
 * — confirmado explícitamente: el sistema no tenía datos reales en producción
 * cuando se detectó el rediseño necesario.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Drop de FKs hacia research_groups antes de poder eliminar la tabla.
        Schema::table('seedlings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('research_group_id');
        });
        Schema::table('macro_projects', function (Blueprint $table) {
            $table->dropConstrainedForeignId('research_group_id');
        });

        // Hijos antes que padres.
        Schema::dropIfExists('group_product_reviews');
        Schema::dropIfExists('product_authors');
        Schema::dropIfExists('product_evidences');
        Schema::dropIfExists('group_products');
        Schema::dropIfExists('products');
        Schema::dropIfExists('research_group_users');
        Schema::dropIfExists('project_groups');
        // project_seedlings queda redundante: projects.seedling_id (relación
        // directa) reemplaza el many-to-many.
        Schema::dropIfExists('project_seedlings');
        Schema::dropIfExists('research_groups');
    }

    public function down(): void
    {
        Schema::create('research_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_center_id')->nullable()->constrained('training_centers')->nullOnDelete();
            $table->string('nombre');
            $table->string('codigo')->nullable();
            $table->string('logo')->nullable();
            $table->text('descripcion')->nullable();
            $table->enum('estado', ['activo', 'inactivo'])->default('activo');
            $table->timestamps();
        });

        Schema::create('project_seedlings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('seedling_id')->constrained('seedlings')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('project_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('research_group_id')->constrained('research_groups')->cascadeOnDelete();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('tipo_participacion')->nullable();
            $table->timestamps();
        });

        Schema::create('research_group_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('research_group_id')->constrained('research_groups')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('rol');
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->string('nombre');
            $table->string('archivo')->nullable();
            $table->string('archivo_nombre')->nullable();
            $table->string('url_repositorio')->nullable();
            $table->enum('estado', ['activo', 'inactivo'])->default('activo');
            $table->string('estado_revision')->default('pendiente');
            $table->text('observacion_revision')->nullable();
            $table->foreignId('assigned_investigator_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('group_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('tipo_proyecto_origen')->nullable();
            $table->string('campo_otro')->nullable();
            $table->string('codigo_proyecto_origen')->nullable();
            $table->string('titulo')->nullable();
            $table->text('descripcion')->nullable();
            $table->integer('anio_publicacion')->nullable();
            $table->string('nombre_programa_formacion_impacto')->nullable();
            $table->foreignId('minciencias_typology_id')->nullable()->constrained('minciencias_typologies')->nullOnDelete();
            $table->foreignId('minciencias_subcategory_id')->nullable()->constrained('minciencias_subcategories')->nullOnDelete();
            $table->foreignId('knowledge_grand_area_id')->nullable()->constrained('knowledge_grand_areas')->nullOnDelete();
            $table->foreignId('knowledge_area_id')->nullable()->constrained('knowledge_areas')->nullOnDelete();
            $table->boolean('tiene_repositorio')->default(false);
            $table->string('url_repositorio')->nullable();
            $table->string('evidencia')->nullable();
            $table->boolean('autoriza_datos')->default(false);
            $table->string('estado_revision')->default('pendiente');
            $table->text('observaciones_revision')->nullable();
            $table->timestamps();
        });

        Schema::create('product_evidences', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('archivo')->nullable();
            $table->string('url_archivo')->nullable();
            $table->text('descripcion')->nullable();
            $table->timestamps();
        });

        Schema::create('product_authors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('project_author_id')->constrained('project_authors')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('group_product_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_product_id')->constrained('group_products')->cascadeOnDelete();
            $table->foreignId('reviewer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('research_group_id')->constrained('research_groups')->cascadeOnDelete();
            $table->string('accion')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });

        Schema::table('macro_projects', function (Blueprint $table) {
            $table->foreignId('research_group_id')->nullable()->after('id')->constrained('research_groups')->nullOnDelete();
        });
        Schema::table('seedlings', function (Blueprint $table) {
            $table->foreignId('research_group_id')->nullable()->after('training_center_id')->constrained('research_groups')->nullOnDelete();
        });
    }
};
