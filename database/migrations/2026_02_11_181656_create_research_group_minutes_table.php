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

        Schema::create('research_group_minutes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('research_group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('research_product_id')->nullable()->constrained()->onDelete('set null');
            $table->string('file_path', 500);
            $table->text('description')->nullable();
            $table->date('minute_date');
            $table->string('minute_type', 100)->nullable();
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('research_group_minutes');
    }
};
