<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * project_evidences pasa a ser la única fuente de "producto final" del
 * rediseño (reemplaza products/group_products, que se eliminan en una
 * migración posterior). tipo=desarrollo no usa las columnas de revisión;
 * tipo=producto_final las usa para el flujo de 2 etapas:
 * Líder de Semillero (lider) -> Director de Semilleros (director).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_evidences', function (Blueprint $table) {
            $table->string('tipo')->default('desarrollo')->after('project_id');

            $table->string('estado_revision_lider')->nullable()->after('descripcion');
            $table->text('observacion_lider')->nullable()->after('estado_revision_lider');
            $table->foreignId('revisado_lider_por')->nullable()
                ->after('observacion_lider')->constrained('users')->nullOnDelete();
            $table->timestamp('revisado_lider_at')->nullable()->after('revisado_lider_por');

            $table->string('estado_revision_director')->nullable()->after('revisado_lider_at');
            $table->text('observacion_director')->nullable()->after('estado_revision_director');
            $table->foreignId('revisado_director_por')->nullable()
                ->after('observacion_director')->constrained('users')->nullOnDelete();
            $table->timestamp('revisado_director_at')->nullable()->after('revisado_director_por');
        });
    }

    public function down(): void
    {
        Schema::table('project_evidences', function (Blueprint $table) {
            $table->dropConstrainedForeignId('revisado_lider_por');
            $table->dropConstrainedForeignId('revisado_director_por');
            $table->dropColumn([
                'tipo',
                'estado_revision_lider',
                'observacion_lider',
                'revisado_lider_at',
                'estado_revision_director',
                'observacion_director',
                'revisado_director_at',
            ]);
        });
    }
};
