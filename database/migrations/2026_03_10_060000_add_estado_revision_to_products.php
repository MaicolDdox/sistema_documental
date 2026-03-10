<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Estado de revisión por el líder: pendiente, aprobado, rechazado
            $table->string('estado_revision')->default('pendiente')->after('estado');
            // Observación/comentario del líder al revisar
            $table->text('observacion_revision')->nullable()->after('estado_revision');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['estado_revision', 'observacion_revision']);
        });
    }
};
