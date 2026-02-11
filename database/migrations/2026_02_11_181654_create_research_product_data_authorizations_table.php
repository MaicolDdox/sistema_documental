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

        Schema::create('research_product_data_authorizations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('research_product_id')->constrained()->cascadeOnDelete()->unique();
            $table->boolean('authorized');
            $table->dateTime('authorized_at');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('research_product_data_authorizations');
    }
};
