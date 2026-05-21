<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecute las migraciones.
     */
    public function up(): void
    {
        Schema::table('lead_pipelines', function (Blueprint $table) {
            $table->string('name')->unique()->change();
        });
    }

    /**
     * Revertir las migraciones.
     */
    public function down(): void
    {
        Schema::table('lead_pipelines', function (Blueprint $table) {
            $table->dropUnique(['name']);

            $table->string('name')->change();
        });
    }
};
