<?php

namespace Aether\Admin\Http\Controllers;

use Aether\Core\Services\WhatsAppService;
use Aether\Lead\Repositories\LeadRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WhatsAppController extends Controller
{
    /**
     * @var WhatsAppService
     */
    protected $whatsappService;

    /**
     * @var LeadRepository
     */
    protected $leadRepository;

    /**
     * Create a new controller instance.
     */
    public function __construct(WhatsAppService $whatsappService, LeadRepository $leadRepository)
    {
        $this->whatsappService = $whatsappService;
        $this->leadRepository = $leadRepository;
    }

    /**
     * Send a WhatsApp message
     */
    public function send(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => 'required|string',
            'message' => 'required|string',
            'lead_id' => 'required|integer',
        ]);

        $response = $this->whatsappService->sendMessage($request->input('phone'), $request->input('message'));

        if ($response['success']) {
            return response()->json([
                'success' => true,
                'message' => trans('admin::app.whatsapp.send-success', [], 'es') ?? 'Mensaje enviado correctamente',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $response['error'] ?? 'Error al enviar mensaje',
        ], 500);
    }
}
