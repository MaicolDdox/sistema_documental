<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_modalities', function (Blueprint $table) {
            $table->foreignId('training_center_id')->nullable()->after('id')
                ->constrained('training_centers')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('project_modalities', function (Blueprint $table) {
            $table->dropConstrainedForeignId('training_center_id');
        });
    }
};
