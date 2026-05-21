<?php

namespace Aether\Installer\Helpers;

use Aether\Installer\Database\Seeders\DatabaseSeeder as CRMV1DatabaseSeeder;
use Exception;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DatabaseManager
{
    /**
     * Verifique la conexión de la base de datos.
     */
    public function isInstalled()
    {
        if (! file_exists(base_path('.env'))) {
            return false;
        }

        try {
            DB::connection()->getPDO();

            $isConnected = (bool) DB::connection()->getDatabaseName();

            if (! $isConnected) {
                return false;
            }

            $hasUserTable = Schema::hasTable('users');

            if (! $hasUserTable) {
                return false;
            }

            $userCount = DB::table('users')->count();

            if (! $userCount) {
                return false;
            }

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Elimine todas las tablas y migre en la base de datos.
     *
     * @return void|string
     */
    public function migration()
    {
        try {
            Artisan::call('migrate:fresh');

            return response()->json([
                'success' => true,
                'message' => 'Tables is migrated successfully.',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Sembrar la base de datos.
     *
     * @return void|string
     */
    public function seeder($data)
    {
        try {
            app(CRMV1DatabaseSeeder::class)->run([
                'default_locale' => $data['parameter']['default_locales'],
                'default_currency' => $data['parameter']['default_currency'],
            ]);

            $this->storageLink();
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Enlace de almacenamiento.
     */
    private function storageLink()
    {
        Artisan::call('storage:link');
    }

    /**
     * Generar nueva clave de aplicación
     */
    public function generateKey()
    {
        try {
            Artisan::call('key:generate');
        } catch (Exception $e) {
        }
    }
}
