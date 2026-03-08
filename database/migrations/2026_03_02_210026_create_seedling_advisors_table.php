<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seedling_advisors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seedling_id')
                ->constrained('seedlings')
                ->onDelete('CASCADE');
            $table->foreignId('external_advisor_id')
                ->comment('Referencia a external_advisors, que puede o no tener user_id')
                ->constrained('external_advisors')
                ->onDelete('CASCADE');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seedling_advisors');
    }
};
