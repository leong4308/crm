<?php

namespace Aether\Admin\Http\Controllers\Settings;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Event;
use Illuminate\View\View;
use Aether\Admin\DataGrids\Settings\WebhookDataGrid;
use Aether\Admin\Http\Controllers\Controller;
use Aether\Admin\Requests\WebhookRequest;
use Aether\Automation\Repositories\WebhookRepository;

class WebhookController extends Controller
{
    public function __construct(protected WebhookRepository $webhookRepository) {}

    /**
     * Mostrar el listado del recurso.
     */
    public function index(): View|JsonResponse
    {
        if (request()->ajax()) {
            return datagrid(WebhookDataGrid::class)->process();
        }

        return view('admin::settings.webhook.index');
    }

    /**
     * Mostrar el formulario para crear un nuevo recurso.
     */
    public function create(): View
    {
        return view('admin::settings.webhook.create');
    }

    /**
     * Guarde el recurso recién creado en el almacenamiento.
     */
    public function store(WebhookRequest $webhookRequest): RedirectResponse
    {
        Event::dispatch('settings.webhook.create.before');

        $webhook = $this->webhookRepository->create($webhookRequest->validated());

        Event::dispatch('settings.webhook.create.after', $webhook);

        session()->flash('success', trans('admin::app.settings.webhooks.index.create-success'));

        return redirect()->route('admin.settings.webhooks.index');
    }

    /**
     * Guarde el recurso recién creado en el almacenamiento.
     */
    public function edit(int $id): View
    {
        $webhook = $this->webhookRepository->findOrFail($id);

        return view('admin::settings.webhook.edit', compact('webhook'));
    }

    /**
     * Actualice el recurso especificado en el almacenamiento.
     */
    public function update(WebhookRequest $webhookRequest, int $id): RedirectResponse
    {
        Event::dispatch('settings.webhook.update.before', $id);

        $webhook = $this->webhookRepository->update($webhookRequest->validated(), $id);

        Event::dispatch('settings.webhook.update.after', $webhook);

        session()->flash('success', trans('admin::app.settings.webhooks.index.update-success'));

        return redirect()->route('admin.settings.webhooks.index');
    }

    /**
     * Elimine el recurso especificado del almacenamiento.
     */
    public function destroy(int $id): JsonResponse
    {
        $webhook = $this->webhookRepository->findOrFail($id);

        Event::dispatch('settings.webhook.delete.before', $id);

        $webhook?->delete();

        Event::dispatch('settings.webhook.delete.after', $id);

        return response()->json([
            'message' => trans('admin::app.settings.webhooks.index.delete-success'),
        ]);
    }
}
