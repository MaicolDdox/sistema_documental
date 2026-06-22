<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('external_advisors', function (Blueprint $table) {
            $table->foreignId('training_center_id')
                ->nullable()
                ->after('user_id')
                ->constrained('training_centers')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('external_advisors', function (Blueprint $table) {
            $table->dropForeign(['training_center_id']);
            $table->dropColumn('training_center_id');
        });
    }
};
