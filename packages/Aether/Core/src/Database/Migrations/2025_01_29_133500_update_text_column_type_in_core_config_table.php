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
        Schema::table('core_config', function (Blueprint $table) {
            $table->text('value')->change();
        });
    }

    /**
     * Revertir las migraciones.
     */
    public function down(): void
    {
        Schema::table('core_config', function (Blueprint $table) {
            $table->string('value')->change();
        });
    }
};
