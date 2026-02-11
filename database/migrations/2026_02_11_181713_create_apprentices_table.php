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

        Schema::create('apprentices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('full_name', 200);
            $table->enum('document_type', ["cc","ti","foreign_id"]);
            $table->string('document_number', 50)->unique();
            $table->enum('gender', ["male","female","other"])->nullable();
            $table->date('birth_date')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('mobile', 20)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('blood_type', 10)->nullable();
            $table->string('health_provider', 100)->nullable();
            $table->foreignId('training_cohort_id')->constrained();
            $table->string('support_type', 200)->nullable();
            $table->foreignId('seedbed_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('seedbed_project_id')->nullable()->constrained()->onDelete('set null');
            $table->string('project_file_path', 500)->nullable();
            $table->enum('status', ["active","inactive","graduated","withdrawn"])->default('active');
            $table->date('admission_date')->nullable();
            $table->date('withdrawal_date')->nullable();
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('apprentices');
    }
};
