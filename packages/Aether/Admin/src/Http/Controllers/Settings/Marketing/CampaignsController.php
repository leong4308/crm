<?php

namespace Aether\Admin\Http\Controllers\Settings\Marketing;

use Aether\Admin\DataGrids\Settings\Marketing\CampaignDatagrid;
use Aether\Admin\Http\Controllers\Controller;
use Aether\Admin\Http\Requests\MassDestroyRequest;
use Aether\EmailTemplate\Repositories\EmailTemplateRepository;
use Aether\Marketing\Repositories\CampaignRepository;
use Aether\Marketing\Repositories\EventRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Event;
use Illuminate\View\View;

class CampaignsController extends Controller
{
    /**
     * Cree una nueva instancia de controlador.
     */
    public function __construct(
        protected CampaignRepository $campaignRepository,
        protected EventRepository $eventRepository,
        protected EmailTemplateRepository $emailTemplateRepository,
    ) {}

    /**
     * Mostrar una lista de las campañas de marketing.
     */
    public function index(): View|JsonResponse
    {
        if (request()->isXmlHttpRequest()) {
            return datagrid(CampaignDatagrid::class)->process();
        }

        return view('admin::settings.marketing.campaigns.index');
    }

    /**
     * Obtenga eventos de marketing.
     */
    public function getEvents(): JsonResponse
    {
        $events = $this->eventRepository->get(['id', 'name']);

        return response()->json([
            'data' => $events,
        ]);
    }

    /**
     * Obtenga plantillas de correo electrónico.
     */
    public function getEmailTemplates(): JsonResponse
    {
        $emailTemplates = $this->emailTemplateRepository->get(['id', 'name']);

        return response()->json([
            'data' => $emailTemplates,
        ]);
    }

    /**
     * Almacene una campaña de marketing recién creada en el almacenamiento.
     */
    public function store(): JsonResponse
    {
        $validatedData = $this->validate(request(), [
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'marketing_template_id' => 'required|exists:email_templates,id',
            'marketing_event_id' => 'required|exists:marketing_events,id',
            'status' => 'sometimes|required|in:0,1',
        ]);

        Event::dispatch('settings.marketing.campaigns.create.before');

        $marketingCampaign = $this->campaignRepository->create($validatedData);

        Event::dispatch('settings.marketing.campaigns.create.after', $marketingCampaign);

        return response()->json([
            'message' => trans('admin::app.settings.marketing.campaigns.index.create-success'),
        ]);
    }

    /**
     * Muestra el recurso especificado.
     */
    public function show(int $id): JsonResponse
    {
        $campaign = $this->campaignRepository->findOrFail($id);

        return response()->json([
            'data' => $campaign,
        ]);
    }

    /**
     * Actualice la campaña de marketing especificada almacenada.
     */
    public function update(int $id): JsonResponse
    {
        $validatedData = $this->validate(request(), [
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'marketing_template_id' => 'required|exists:email_templates,id',
            'marketing_event_id' => 'required|exists:marketing_events,id',
            'status' => 'sometimes|required|in:0,1',
        ]);

        Event::dispatch('settings.marketing.campaigns.update.before', $id);

        $marketingCampaign = $this->campaignRepository->update($validatedData, $id);

        Event::dispatch('settings.marketing.campaigns.update.after', $marketingCampaign);

        return response()->json([
            'message' => trans('admin::app.settings.marketing.campaigns.index.update-success'),
        ]);
    }

    /**
     * Elimine la campaña de marketing especificada del almacenamiento.
     */
    public function destroy(int $id): JsonResponse
    {
        Event::dispatch('settings.marketing.campaigns.delete.before', $id);

        $this->campaignRepository->delete($id);

        Event::dispatch('settings.marketing.campaigns.delete.after', $id);

        return response()->json([
            'message' => trans('admin::app.settings.marketing.campaigns.index.delete-success'),
        ]);
    }

    /**
     * Elimine las campañas de marketing especificadas del almacenamiento.
     */
    public function massDestroy(MassDestroyRequest $massDestroyRequest): JsonResponse
    {
        $campaigns = $this->campaignRepository->findWhereIn('id', $massDestroyRequest->input('indices'));

        foreach ($campaigns as $campaign) {
            Event::dispatch('settings.marketing.campaigns.delete.before', $campaign);

            $this->campaignRepository->delete($campaign->id);

            Event::dispatch('settings.marketing.campaigns.delete.after', $campaign);
        }

        return response()->json([
            'message' => trans('admin::app.settings.marketing.campaigns.index.mass-delete-success'),
        ]);
    }
}
