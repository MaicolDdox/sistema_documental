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

        Schema::create('seedbed_member_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seedbed_id')->constrained();
            $table->foreignId('apprentice_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('advisor_id')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('member_type', ["apprentice","advisor","collaborator"]);
            $table->string('period', 50)->nullable();
            $table->text('notes')->nullable();
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
        Schema::dropIfExists('seedbed_member_reports');
    }
};
