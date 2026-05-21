<?php

namespace Aether\Installer\Listeners;

use Aether\User\Repositories\UserRepository;
use GuzzleHttp\Client;

class Installer
{
    /**
     * Punto final de API
     *
     * @var string
     */
    protected const API_ENDPOINT = 'https://updates.aethercrm.com/api/updates';

    /**
     * Cree una nueva instancia de escucha.
     *
     * @return void
     */
    public function __construct(protected UserRepository $userRepository) {}

    /**
     * Después de que CRM v1 se haya instalado correctamente
     *
     * @return void
     */
    public function installed()
    {
        $user = $this->userRepository->first();

        $httpClient = new Client;

        try {
            $httpClient->request('POST', self::API_ENDPOINT, [
                'headers' => [
                    'Accept' => 'application/json',
                ],
                'json' => [
                    'domain' => config('app.url'),
                    'email' => $user?->email,
                    'name' => $user?->name,
                    'country_code' => config('app.default_country') ?? 'IN',
                ],
            ]);
        } catch (\Exception $e) {
            /**
             * Saltar el error
             */
        }
    }
}
