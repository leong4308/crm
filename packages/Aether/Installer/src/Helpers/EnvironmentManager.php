<?php

namespace Aether\Installer\Helpers;

use Exception;

class EnvironmentManager
{
    /**
     * Crea una instancia auxiliar.
     *
     * @return void
     */
    public function __construct(protected DatabaseManager $databaseManager) {}

    /**
     * Generar archivo ENV e instalación.
     *
     * @param [object] $request
     */
    public function generateEnv($request)
    {
        $envExamplePath = base_path('.env.example');

        $envPath = base_path('.env');

        if (! file_exists($envPath)) {
            if (file_exists($envExamplePath)) {
                copy($envExamplePath, $envPath);
            } else {
                touch($envPath);
            }
        }

        try {
            $response = $this->setEnvConfiguration($request->all());

            $this->databaseManager->generateKey();

            return $response;
        } catch (Exception $e) {
            return $e;
        }
    }

    /**
     * Establezca la configuración del archivo ENV.
     *
     * @return string
     */
    public function setEnvConfiguration(array $request)
    {
        $envDBParams = [];

        /**
         * Actualice los parámetros con datos de formulario.
         */
        if (isset($request['db_hostname'])) {
            $envDBParams['DB_HOST'] = $request['db_hostname'];
            $envDBParams['DB_DATABASE'] = $request['db_name'];
            $envDBParams['DB_PREFIX'] = $request['db_prefix'] ?? '';
            $envDBParams['DB_USERNAME'] = $request['db_username'];
            $envDBParams['DB_PASSWORD'] = $request['db_password'];
            $envDBParams['DB_CONNECTION'] = $request['db_connection'];
            $envDBParams['DB_PORT'] = (int) $request['db_port'];
        }

        if (isset($request['app_name'])) {
            $envDBParams['APP_NAME'] = $request['app_name'] ?? null;
            $envDBParams['APP_URL'] = $request['app_url'];
            $envDBParams['APP_LOCALE'] = $request['app_locale'];
            $envDBParams['APP_TIMEZONE'] = $request['app_timezone'];
            $envDBParams['APP_CURRENCY'] = $request['app_currency'];
        }

        $data = file_get_contents(base_path('.env'));

        foreach ($envDBParams as $key => $value) {
            if (preg_match('/\s/', $value)) {
                $value = '"'.$value.'"';
            }

            $data = preg_replace("/$key=(.*)/", "$key=$value", $data);
        }

        try {
            file_put_contents(base_path('.env'), $data);
        } catch (Exception $e) {
            return false;
        }

        return true;
    }
}
