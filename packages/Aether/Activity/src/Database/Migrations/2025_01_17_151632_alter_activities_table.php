<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecute las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->dropForeign(['user_id']);

            $table->unsignedInteger('user_id')->nullable()->change();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Revertir las migraciones.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('activities', function (Blueprint $table) {
            $tablePrefix = DB::getTablePrefix();

            // Deshabilite las comprobaciones de claves externas temporalmente.
            DB::statement('SET FOREIGN_KEY_CHECKS=0');

            // Elimine la restricción de clave externa utilizando SQL sin formato.
            DB::statement('ALTER TABLE '.$tablePrefix.'activities DROP FOREIGN KEY activities_user_id_foreign');

            // Suelta el índice.
            DB::statement('ALTER TABLE '.$tablePrefix.'activities DROP INDEX activities_user_id_foreign');

            // Cambie la columna para que no admita valores NULL.
            $table->unsignedInteger('user_id')->nullable(false)->change();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            // Vuelva a habilitar las comprobaciones de claves externas.
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        });
    }
};
