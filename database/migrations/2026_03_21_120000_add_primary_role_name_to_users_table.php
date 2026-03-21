<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('primary_role_name', 100)->nullable()->after('training_center_id')
                ->comment('Rol principal preferido; si es null se usa la prioridad del sistema.');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('primary_role_name');
        });
    }
};
