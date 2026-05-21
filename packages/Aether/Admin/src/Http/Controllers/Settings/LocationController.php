<?php

namespace Aether\Admin\Http\Controllers\Settings;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Event;
use Prettus\Repository\Criteria\RequestCriteria;
use Aether\Admin\Http\Controllers\Controller;
use Aether\Admin\Http\Requests\AttributeForm;
use Aether\Warehouse\Repositories\LocationRepository;

class LocationController extends Controller
{
    /**
     * Cree una nueva instancia de controlador.
     *
     * @return void
     */
    public function __construct(protected LocationRepository $locationRepository) {}

    /**
     * Buscar resultados de ubicación
     *
     * @return Response
     */
    public function search()
    {
        $results = $this->locationRepository
            ->pushCriteria(app(RequestCriteria::class))
            ->all();

        return response()->json([
            'data' => $results,
        ]);
    }

    /**
     * Almacene un recurso recién creado en el almacenamiento.
     */
    public function store(AttributeForm $request): JsonResponse
    {
        Event::dispatch('settings.location.create.before');

        $location = $this->locationRepository->create(request()->all());

        Event::dispatch('settings.location.create.after', $location);

        return new JsonResponse([
            'data' => $location,
            'message' => trans('admin::app.settings.warehouses.view.locations.create-success'),
        ]);
    }

    /**
     * Elimine el recurso especificado del almacenamiento.
     *
     * @return Response
     */
    public function destroy(int $id): JsonResponse
    {
        $this->locationRepository->findOrFail($id);

        try {
            Event::dispatch('settings.location.delete.before', $id);

            $this->locationRepository->delete($id);

            Event::dispatch('settings.location.delete.after', $id);

            return new JsonResponse([
                'message' => trans('admin::app.settings.warehouses.view.locations.delete-success'),
            ], 200);
        } catch (\Exception $exception) {
            return new JsonResponse([
                'message' => trans('admin::app.settings.warehouses.view.locations.delete-failed'),
            ], 400);
        }
    }
}
