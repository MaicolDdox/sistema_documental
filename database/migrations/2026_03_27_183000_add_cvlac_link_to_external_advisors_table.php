<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('external_advisors', function (Blueprint $table) {
            if (! Schema::hasColumn('external_advisors', 'cvlac_link')) {
                $table->string('cvlac_link', 500)->nullable()->after('institucion');
            }
        });
    }

    public function down(): void
    {
        Schema::table('external_advisors', function (Blueprint $table) {
            if (Schema::hasColumn('external_advisors', 'cvlac_link')) {
                $table->dropColumn('cvlac_link');
            }
        });
    }
};
