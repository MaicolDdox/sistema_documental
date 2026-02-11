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

        Schema::create('research_group_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('research_group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('researcher_id')->constrained('users');
            $table->date('joined_on');
            $table->date('left_on')->nullable();
            $table->enum('status', ["active","inactive"])->default('active');
            $table->text('notes')->nullable();
            $table->unique(['research_group_id', 'researcher_id']);
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('research_group_members');
    }
};
