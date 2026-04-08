<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seedlings', function (Blueprint $table) {
            $table->foreignId('leader_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('seedlings', function (Blueprint $table) {
            $table->foreignId('leader_id')->nullable(false)->change();
        });
    }
};
