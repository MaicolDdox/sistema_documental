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

        Schema::create('research_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('investigator_id')->constrained('users');
            $table->foreignId('research_group_id')->constrained();
            $table->enum('project_origin_type', ["sgps","installed_capacity","formative_research","center_initiative","allied_entity_articulation"]);
            $table->string('origin_project_code', 100)->nullable();
            $table->foreignId('minciencias_typology_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('product_type_id')->nullable()->constrained()->onDelete('set null');
            $table->string('name', 300);
            $table->text('description')->nullable();
            $table->year('publication_year')->nullable();
            $table->string('training_program_name', 200)->nullable();
            $table->boolean('has_repository')->default(false);
            $table->string('repository_url', 500)->nullable();
            $table->enum('review_status', ["draft","submitted","in_review","approved","rejected"])->default('draft');
            $table->dateTime('submitted_at')->nullable();
            $table->dateTime('reviewed_at')->nullable();
            $table->index('review_status');
            $table->index('publication_year');
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('research_products');
    }
};
