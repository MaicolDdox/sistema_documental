<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_evidences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')
                ->constrained('projects')
                ->onDelete('CASCADE');
            $table->string('archivo')->nullable();
            $table->string('url_archivo')->nullable();
            $table->text('descripccion');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_evidences');
    }
};
