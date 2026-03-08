<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('macro_project_linkages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')
                ->constrained('projects')
                ->onDelete('CASCADE');
            $table->foreignId('research_group_id')
                ->comment('Un macro proyecto SIEMPRE debe pertenecer a un grupo de investigación')
                ->constrained('research_groups')
                ->onDelete('CASCADE');
            $table->integer('codigo');
            $table->string('nombre');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('macro_project_linkages');
    }
};
