<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * BUG-20260813-029 — el co-investigador no tiene training_center_id (no es
 * un rol "bound" a sede, ver TrainingCenterAccess::CENTRO_BOUND_ROLE_NAMES),
 * pero el administrador_sistema que aprueba su producto Minciencias sí lo
 * está. El propio co-investigador elige a qué centro queda vinculado el
 * producto al crearlo; eso determina qué administrador_sistema lo revisa.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('minciencias_products', function (Blueprint $table) {
            $table->foreignId('training_center_id')->after('user_id')
                ->constrained('training_centers')->onDelete('CASCADE');

            $table->string('estado_revision')->default('pendiente')->after('estado');
            $table->text('observacion_admin')->nullable()->after('estado_revision');
            $table->foreignId('revisado_por')->nullable()->after('observacion_admin')
                ->constrained('users')->nullOnDelete();
            $table->timestamp('revisado_at')->nullable()->after('revisado_por');
        });
    }

    public function down(): void
    {
        Schema::table('minciencias_products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('training_center_id');
            $table->dropConstrainedForeignId('revisado_por');
            $table->dropColumn(['estado_revision', 'observacion_admin', 'revisado_at']);
        });
    }
};
