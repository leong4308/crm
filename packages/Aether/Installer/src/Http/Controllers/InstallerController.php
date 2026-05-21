<?php

namespace Aether\Installer\Http\Controllers;

use Aether\Installer\Helpers\DatabaseManager;
use Aether\Installer\Helpers\EnvironmentManager;
use Aether\Installer\Helpers\ServerRequirements;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\File;

class InstallerController extends Controller
{
    /**
     * Variable constante para la versión mínima de PHP
     *
     * @var string
     */
    const MIN_PHP_VERSION = '8.1.0';

    /**
     * Variable constante para ID de cliente estático
     *
     * @var int
     */
    const USER_ID = 1;

    /**
     * Crear una nueva instancia de controlador
     *
     * @return void
     */
    public function __construct(
        protected ServerRequirements $serverRequirements,
        protected EnvironmentManager $environmentManager,
        protected DatabaseManager $databaseManager
    ) {}

    /**
     * Instalador Ver página raíz
     */
    public function index()
    {
        $phpVersion = $this->serverRequirements->checkPHPversion(self::MIN_PHP_VERSION);

        $requirements = $this->serverRequirements->validate();

        if (request()->has('locale')) {
            return redirect()->route('installer.index');
        }

        return view('installer::installer.index', compact('requirements', 'phpVersion'));
    }

    /**
     * Configuración de archivos ENV
     */
    public function envFileSetup(Request $request): JsonResponse
    {
        $message = $this->environmentManager->generateEnv($request);

        return new JsonResponse(['data' => $message]);
    }

    /**
     * Ejecutar migración
     */
    public function runMigration(): mixed
    {
        return $this->databaseManager->migration();
    }

    /**
     * Ejecute la sembradora.
     *
     * @return void|string
     */
    public function runSeeder()
    {
        $allParameters = request()->allParameters;

        $parameter = [
            'parameter' => [
                'default_locales' => $allParameters['app_locale'] ?? null,
                'default_currency' => $allParameters['app_currency'] ?? null,
            ],
        ];

        $response = $this->environmentManager->setEnvConfiguration($allParameters);

        if ($response) {
            $seeder = $this->databaseManager->seeder($parameter);

            return $seeder;
        }
    }

    /**
     * Configuración de configuración de administrador.
     */
    public function adminConfigSetup(): bool
    {
        $password = password_hash(request()->input('password'), PASSWORD_BCRYPT, ['cost' => 10]);

        try {
            DB::table('users')->updateOrInsert(
                [
                    'id' => self::USER_ID,
                ], [
                    'name' => request()->input('admin'),
                    'email' => request()->input('email'),
                    'password' => $password,
                    'role_id' => 1,
                    'status' => 1,
                ]
            );

            $this->smtpConfigSetup();

            return true;
        } catch (\Throwable $th) {
            report($th);

            return false;
        }
    }

    /**
     * Configuración de conexión SMTP para correo
     */
    private function smtpConfigSetup()
    {
        $filePath = storage_path('installed');

        File::put($filePath, 'Your CRM v1 App is Successfully Installed');

        Event::dispatch('aether.installed');

        return $filePath;
    }
}
