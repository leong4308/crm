<?php

namespace Aether\Admin\Http\Controllers\Settings;

use Aether\Admin\DataGrids\Settings\SourceDataGrid;
use Aether\Admin\Http\Controllers\Controller;
use Aether\Lead\Repositories\SourceRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Event;
use Illuminate\View\View;

class SourceController extends Controller
{
    /**
     * Cree una nueva instancia de controlador.
     *
     * @return void
     */
    public function __construct(protected SourceRepository $sourceRepository) {}

    /**
     * Mostrar una lista del recurso.
     */
    public function index(): View|JsonResponse
    {
        if (request()->ajax()) {
            return datagrid(SourceDataGrid::class)->process();
        }

        return view('admin::settings.sources.index');
    }

    /**
     * Almacene un recurso recién creado en el almacenamiento.
     */
    public function store(): JsonResponse
    {
        $this->validate(request(), [
            'name' => ['required', 'unique:lead_sources,name'],
        ]);

        Event::dispatch('settings.source.create.before');

        $source = $this->sourceRepository->create(request()->only(['name']));

        Event::dispatch('settings.source.create.after', $source);

        return new JsonResponse([
            'data' => $source,
            'message' => trans('admin::app.settings.sources.index.create-success'),
        ]);
    }

    /**
     * Muestra el formulario para editar el recurso especificado.
     */
    public function edit(int $id): View|JsonResponse
    {
        $source = $this->sourceRepository->findOrFail($id);

        return new JsonResponse([
            'data' => $source,
        ]);
    }

    /**
     * Actualice el recurso especificado en el almacenamiento.
     */
    public function update(int $id): JsonResponse
    {
        $this->validate(request(), [
            'name' => 'required|unique:lead_sources,name,'.$id,
        ]);

        Event::dispatch('settings.source.update.before', $id);

        $source = $this->sourceRepository->update(request()->only(['name']), $id);

        Event::dispatch('settings.source.update.after', $source);

        return new JsonResponse([
            'data' => $source,
            'message' => trans('admin::app.settings.sources.index.update-success'),
        ]);
    }

    /**
     * Elimine el recurso especificado del almacenamiento.
     */
    public function destroy(int $id): JsonResponse
    {
        $source = $this->sourceRepository->findOrFail($id);

        if ($source->leads()->count() > 0) {
            return new JsonResponse([
                'message' => trans('admin::app.settings.sources.index.delete-failed-associated-leads'),
            ], 400);
        }

        try {
            Event::dispatch('settings.source.delete.before', $id);

            $source->delete();

            Event::dispatch('settings.source.delete.after', $id);

            return new JsonResponse([
                'message' => trans('admin::app.settings.sources.index.delete-success'),
            ], 200);
        } catch (Exception $exception) {
            return new JsonResponse([
                'message' => trans('admin::app.settings.sources.index.delete-failed'),
            ], 400);
        }
    }
}
