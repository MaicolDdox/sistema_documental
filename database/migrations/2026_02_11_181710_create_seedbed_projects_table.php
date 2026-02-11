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

        Schema::create('seedbed_projects', function (Blueprint $table) {
            $table->id();
            $table->string('name', 300);
            $table->text('description')->nullable();
            $table->foreignId('seedbed_id')->constrained();
            $table->foreignId('advisor_id')->constrained('users');
            $table->foreignId('research_line_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('technology_line_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('thematic_area_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('project_modality_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('research_type_id')->nullable()->constrained()->onDelete('set null');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->enum('status', ["proposal","in_development","finished","on_hold","canceled"])->default('proposal');
            $table->boolean('linked_to_macro_project')->default(false);
            $table->string('macro_project_code', 100)->nullable();
            $table->string('macro_project_name', 300)->nullable();
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seedbed_projects');
    }
};
