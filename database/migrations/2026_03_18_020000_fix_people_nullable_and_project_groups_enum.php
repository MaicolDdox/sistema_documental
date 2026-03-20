<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('people', function (Blueprint $table) {
            // email_institucional: hace nullable para no requerir en creación de investigadores
            $table->string('email_institucional')->nullable()->change();

            // segundo_apellido: también nullable por consistencia
            $table->string('segundo_apellido')->nullable()->change();

            // genero: nullable
            $table->enum('genero', ['masculino', 'femenino', 'prefiero no decirlo'])->nullable()->change();

            // celular: nullable
            $table->bigInteger('celular')->nullable()->change();
        });

        // tipo_participacion en project_groups: agregar 'principal' al enum
        Schema::table('project_groups', function (Blueprint $table) {
            $table->enum('tipo_participacion', ['origen', 'aliado', 'cooperacion', 'principal'])
                ->default('origen')
                ->change();
        });
    }

    public function down(): void
    {
        Schema::table('people', function (Blueprint $table) {
            $table->string('email_institucional')->nullable(false)->change();
        });
    }
};
