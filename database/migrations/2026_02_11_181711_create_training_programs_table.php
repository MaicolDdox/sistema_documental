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

        Schema::create('training_programs', function (Blueprint $table) {
            $table->id();
            $table->string('name', 200);
            $table->string('program_code', 50)->nullable()->unique();
            $table->enum('program_type', ["technologist","technician","complementary","specialization","other"]);
            $table->string('other_program_type', 100)->nullable();
            $table->foreignId('training_center_id')->nullable()->constrained()->onDelete('set null');
            $table->enum('status', ["active","inactive"])->default('active');
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('training_programs');
    }
};
