<?php

namespace Aether\Core\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    /**
     * URL base de la API de WhatsApp en Node.js
     *
     * @var string
     */
    protected $baseUrl;
    protected $sessionId;
    protected $apiKey;

    public function __construct()
    {
        $this->baseUrl = env('OPENWA_BASE_URL', 'http://localhost:2785/api');
        $this->sessionId = env('OPENWA_SESSION_ID', '8037d126-7653-42dd-ab7c-47f9709e6c24');
        $this->apiKey = env('OPENWA_API_KEY');
    }

    public function sendMessage($phone, $message)
    {
        try {
            // Limpiar el número de teléfono
            $phone = preg_replace('/[^0-9]/', '', $phone);

            // Corregir números de México para WhatsApp (52 + 10 dígitos -> 521 + 10 dígitos)
            if (str_starts_with($phone, '52') && strlen($phone) === 12 && $phone[2] !== '1') {
                $phone = '521' . substr($phone, 2);
            }

            $chatId = $phone . '@c.us';

            $response = Http::withHeaders([
                'X-API-Key' => $this->apiKey,
                'Content-Type' => 'application/json'
            ])->post("{$this->baseUrl}/sessions/{$this->sessionId}/messages/send-text", [
                'chatId'   => $chatId,
                'text'     => $message,
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data'    => $response->json(),
                ];
            }

            Log::error('Error enviando WhatsApp: ' . $response->body());

            return [
                'success' => false,
                'error'   => 'La API devolvió un error: ' . $response->status(),
                'details' => $response->json(),
            ];
        } catch (\Exception $e) {
            Log::error('Excepción enviando WhatsApp: ' . $e->getMessage());

            return [
                'success' => false,
                'error'   => 'No se pudo conectar a la API de WhatsApp. Asegúrate de que el servidor OpenWA esté corriendo.',
            ];
        }
    }

    /**
     * Verificar estado de la conexión de WhatsApp
     *
     * @return array
     */
    public function status()
    {
        try {
            $response = Http::withHeaders([
                'X-API-Key' => $this->apiKey,
            ])->get("{$this->baseUrl}/sessions/{$this->sessionId}");

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data'    => $response->json(),
                ];
            }

            return [
                'success' => false,
                'error'   => 'Error al obtener estado',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error'   => 'API desconectada',
            ];
        }
    }
}
