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

        Schema::create('research_product_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('research_group_id')->constrained();
            $table->foreignId('research_product_id')->constrained();
            $table->string('file_path', 500);
            $table->string('report_type', 100)->nullable();
            $table->text('description')->nullable();
            $table->date('report_date');
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('research_product_reports');
    }
};
