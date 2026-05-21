<?php

namespace Aether\Admin\Http\Controllers\Settings\Marketing;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Event;
use Illuminate\View\View;
use Aether\Admin\DataGrids\Settings\Marketing\EventDataGrid;
use Aether\Admin\Http\Controllers\Controller;
use Aether\Admin\Http\Requests\MassDestroyRequest;
use Aether\Marketing\Repositories\EventRepository;

class EventController extends Controller
{
    /**
     * Cree una nueva instancia de controlador.
     */
    public function __construct(protected EventRepository $eventRepository) {}

    /**
     * Mostrar una lista de los eventos de marketing.
     */
    public function index(): View|JsonResponse
    {
        if (request()->ajax()) {
            return datagrid(EventDataGrid::class)->process();
        }

        return view('admin::settings.marketing.events.index');
    }

    /**
     * Almacene un evento de marketing recién creado en el almacenamiento.
     */
    public function store(): JsonResponse
    {
        $validatedData = $this->validate(request(), [
            'name' => 'required|max:60',
            'description' => 'required',
            'date' => 'required|date|after_or_equal:today',
        ]);

        Event::dispatch('settings.marketing.events.create.before');

        $marketingEvent = $this->eventRepository->create($validatedData);

        Event::dispatch('settings.marketing.events.create.after', $marketingEvent);

        return response()->json([
            'message' => trans('admin::app.settings.marketing.events.index.create-success'),
            'data' => $marketingEvent,
        ]);
    }

    /**
     * Actualice el evento de marketing especificado almacenado.
     */
    public function update(int $id): JsonResponse
    {
        $validatedData = $this->validate(request(), [
            'name' => 'required|max:60',
            'description' => 'required',
            'date' => 'required|date|after_or_equal:today',
        ]);

        Event::dispatch('settings.marketing.events.update.before', $id);

        $marketingEvent = $this->eventRepository->update($validatedData, $id);

        Event::dispatch('settings.marketing.events.update.after', $marketingEvent);

        return response()->json([
            'message' => trans('admin::app.settings.marketing.events.index.update-success'),
            'data' => $marketingEvent,
        ]);
    }

    /**
     * Elimine el evento de marketing especificado del almacenamiento.
     */
    public function destroy(int $id): JsonResponse
    {
        $event = $this->eventRepository->findOrFail($id);

        if ($event->campaigns->isNotEmpty()) {
            return response()->json([
                'message' => trans('admin::app.settings.marketing.events.index.delete-failed-associated-campaigns'),
            ], 422);
        }

        Event::dispatch('settings.marketing.events.delete.before', $event);

        $this->eventRepository->delete($event->id);

        Event::dispatch('settings.marketing.events.delete.after', $event);

        return response()->json([
            'message' => trans('admin::app.settings.marketing.events.index.delete-success'),
        ]);
    }

    /**
     * Elimine los eventos de marketing especificados del almacenamiento.
     */
    public function massDestroy(MassDestroyRequest $request): JsonResponse
    {
        try {
            $events = $this->eventRepository->findWhereIn('id', $request->input('indices', []));

            $deletedCount = 0;

            $blockedCount = 0;

            foreach ($events as $event) {
                if (
                    $event->campaigns
                    && $event->campaigns->isNotEmpty()
                ) {
                    $blockedCount++;

                    continue;
                }

                Event::dispatch('settings.marketing.events.delete.before', $event);

                $this->eventRepository->delete($event->id);

                Event::dispatch('settings.marketing.events.delete.after', $event);

                $deletedCount++;
            }

            $statusCode = 200;

            switch (true) {
                case $deletedCount > 0 && $blockedCount === 0:
                    $message = trans('admin::app.settings.marketing.events.index.mass-delete-success');

                    break;

                case $deletedCount > 0 && $blockedCount > 0:
                    $message = trans('admin::app.settings.marketing.events.index.partial-delete-warning');

                    break;

                case $deletedCount === 0 && $blockedCount > 0:
                    $message = trans('admin::app.settings.marketing.events.index.none-delete-warning');

                    $statusCode = 400;

                    break;

                default:
                    $message = trans('admin::app.settings.marketing.events.index.no-selection');

                    $statusCode = 400;

                    break;
            }

            return response()->json(['message' => $message], $statusCode);
        } catch (Exception $e) {
            return response()->json([
                'message' => trans('admin::app.settings.marketing.events.index.mass-delete-failed'),
            ], 400);
        }
    }
}
