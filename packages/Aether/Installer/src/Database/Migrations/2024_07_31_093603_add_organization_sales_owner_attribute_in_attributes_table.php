<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Ejecute las migraciones.
     */
    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement("SELECT setval('attributes_id_seq', coalesce((SELECT MAX(id) FROM attributes), 1))");
        }

        $now = Carbon::now();

        DB::table('attributes')
            ->insert([
                [
                    'code' => 'user_id',
                    'name' => trans('installer::app.seeders.attributes.organizations.sales-owner'),
                    'type' => 'lookup',
                    'entity_type' => 'organizations',
                    'lookup_type' => 'users',
                    'validation' => null,
                    'sort_order' => '5',
                    'is_required' => '0',
                    'is_unique' => '0',
                    'quick_add' => '1',
                    'is_user_defined' => '0',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ]);
    }

    /**
     * Revertir las migraciones.
     */
    public function down(): void {}
};
