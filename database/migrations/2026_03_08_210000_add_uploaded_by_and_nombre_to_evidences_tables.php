<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_evidences', function (Blueprint $table) {
            $table->string('nombre', 200)->after('id')->default('');
            $table->foreignId('uploaded_by')
                ->nullable()
                ->after('nombre')
                ->constrained('users')
                ->onDelete('set null');
        });

        Schema::table('product_evidences', function (Blueprint $table) {
            $table->string('nombre', 200)->after('id')->default('');
            $table->foreignId('uploaded_by')
                ->nullable()
                ->after('nombre')
                ->constrained('users')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('project_evidences', function (Blueprint $table) {
            $table->dropForeign(['uploaded_by']);
            $table->dropColumn(['nombre', 'uploaded_by']);
        });

        Schema::table('product_evidences', function (Blueprint $table) {
            $table->dropForeign(['uploaded_by']);
            $table->dropColumn(['nombre', 'uploaded_by']);
        });
    }
};
