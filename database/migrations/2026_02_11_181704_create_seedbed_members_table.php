<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::create('seedbed_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seedbed_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained();
            $table->enum('seedbed_role', ["leader","advisor","collaborator"]);
            $table->date('joined_on');
            $table->date('left_on')->nullable();
            $table->enum('status', ["active","inactive"])->default('active');
            $table->unique(['seedbed_id', 'user_id']);
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seedbed_members');
    }
};
