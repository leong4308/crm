<?php

namespace App\Console\Commands;

use Aether\Contact\Models\Person;
use Aether\Core\Services\WhatsAppService;
use Illuminate\Console\Command;

class SendHotelPromo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-hotel-promo';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a 15-day free trial promo of Hotel Management System via WhatsApp';

    /**
     * Execute the console command.
     */
    public function handle(WhatsAppService $waService)
    {
        $this->info('Obteniendo contactos...');
        $persons = Person::all();
        $count = 0;

        foreach ($persons as $person) {
            $name = $person->name;
            // First name only for a more personal touch
            $firstName = explode(' ', trim($name))[0];

            $numbers = $person->contact_numbers;

            if (empty($numbers) || ! is_array($numbers)) {
                $this->warn("{$name} no tiene número de teléfono.");

                continue;
            }

            $phone = $numbers[0]['value'] ?? null;

            if (! $phone) {
                continue;
            }

            // Clean phone number
            $phone = preg_replace('/[^0-9]/', '', $phone);

            $message = "Hola {$firstName}, espero que estés muy bien.\n\n"
                     ."Me pongo en contacto contigo para ofrecerte una prueba totalmente *gratuita por 15 días* de nuestro nuevo y potente *Sistema de Gestión de Hoteles*.\n\n"
                     ."Con esta herramienta podrás:\n"
                     ."✅ Controlar reservas y disponibilidad en tiempo real.\n"
                     ."✅ Gestionar precios por temporada y tipo de habitación.\n"
                     ."✅ Disfrutar de un panel de control moderno, rápido y muy intuitivo.\n\n"
                     ."Es la herramienta ideal para digitalizar y optimizar la administración de tu negocio sin ningún costo inicial. Si te interesa probarlo, solo responde a este mensaje para activar tu acceso inmediato.\n\n"
                     .'¡Que tengas un excelente día!';

            $this->info("Enviando mensaje a {$name} ({$phone})...");
            $response = $waService->sendMessage($phone, $message);

            if ($response['success']) {
                $this->info("✓ Mensaje enviado a {$name}");
                $count++;
            } else {
                $this->error("✗ Error al enviar a {$name}: ".json_encode($response['details'] ?? $response));
            }

            // Pequeña pausa para no saturar la API (y evitar el limitador de tasa)
            sleep(3);
        }

        $this->info("¡Proceso completado! Se enviaron {$count} mensajes promocionales exitosamente.");
    }
}
