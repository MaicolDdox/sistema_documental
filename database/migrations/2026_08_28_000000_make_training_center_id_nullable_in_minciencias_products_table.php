<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Hallazgo de /code-review sobre BUG-20260813-029: la migración anterior
 * agregó training_center_id como NOT NULL vía ALTER TABLE sobre
 * minciencias_products, que ya existía (creada 2 días antes). En este
 * entorno corrió sin problema porque la tabla estaba vacía, pero un ALTER
 * TABLE ADD COLUMN NOT NULL sin default falla si la tabla ya tiene filas
 * — frágil ante un futuro entorno con datos.
 *
 * La regla de negocio (obligatorio elegir centro al crear el producto)
 * sigue viva en MincienciasProductoController::validarProducto() —esto
 * solo relaja la restricción a nivel de esquema para que la migración sea
 * segura de re-ejecutar contra una base ya poblada.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('minciencias_products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('training_center_id');
        });

        Schema::table('minciencias_products', function (Blueprint $table) {
            $table->foreignId('training_center_id')->nullable()->after('user_id')
                ->constrained('training_centers')->onDelete('CASCADE');
        });
    }

    public function down(): void
    {
        Schema::table('minciencias_products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('training_center_id');
        });

        Schema::table('minciencias_products', function (Blueprint $table) {
            $table->foreignId('training_center_id')->after('user_id')
                ->constrained('training_centers')->onDelete('CASCADE');
        });
    }
};
