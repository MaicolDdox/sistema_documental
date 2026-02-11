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

        Schema::create('seedbed_progress_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seedbed_id')->constrained();
            $table->foreignId('leader_id')->constrained('users');
            $table->foreignId('seedbed_project_id')->nullable()->constrained()->onDelete('set null');
            $table->date('report_date');
            $table->string('reported_period', 50)->nullable();
            $table->decimal('progress_percentage', 5, 2)->nullable();
            $table->text('notes')->nullable();
            $table->text('recommendations')->nullable();
            $table->enum('status', ["draft","submitted","reviewed","approved"])->default('draft');
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seedbed_progress_reports');
    }
};
