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

        Schema::create('training_cohorts', function (Blueprint $table) {
            $table->id();
            $table->string('cohort_number', 50)->unique();
            $table->foreignId('training_program_id')->constrained();
            $table->date('start_date');
            $table->date('end_date');
            $table->string('shift', 50)->nullable();
            $table->enum('status', ["active","finished","canceled"])->default('active');
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('training_cohorts');
    }
};
