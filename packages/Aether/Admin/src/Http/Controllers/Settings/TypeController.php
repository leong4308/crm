<?php

namespace Aether\Admin\Http\Controllers\Settings;

use Aether\Admin\DataGrids\Settings\TypeDataGrid;
use Aether\Admin\Http\Controllers\Controller;
use Aether\Lead\Repositories\TypeRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Event;
use Illuminate\View\View;

class TypeController extends Controller
{
    /**
     * Cree una nueva instancia de controlador.
     *
     * @return void
     */
    public function __construct(protected TypeRepository $typeRepository) {}

    /**
     * Mostrar un listado del tipo.
     */
    public function index(): View|JsonResponse
    {
        if (request()->ajax()) {
            return datagrid(TypeDataGrid::class)->process();
        }

        return view('admin::settings.types.index');
    }

    /**
     * Almacene un tipo recién creado en el almacenamiento.
     */
    public function store(): JsonResponse
    {
        $this->validate(request(), [
            'name' => ['required', 'unique:lead_types,name'],
        ]);

        Event::dispatch('settings.type.create.before');

        $type = $this->typeRepository->create(request()->only(['name']));

        Event::dispatch('settings.type.create.after', $type);

        return new JsonResponse([
            'data' => $type,
            'message' => trans('admin::app.settings.types.index.create-success'),
        ]);
    }

    /**
     * Muestra el formulario para editar el tipo especificado.
     */
    public function edit(int $id): View|JsonResponse
    {
        $type = $this->typeRepository->findOrFail($id);

        return new JsonResponse([
            'data' => $type,
        ]);
    }

    /**
     * Actualice el tipo especificado en el almacenamiento.
     */
    public function update(int $id): JsonResponse
    {
        $this->validate(request(), [
            'name' => 'required|unique:lead_types,name,'.$id,
        ]);

        Event::dispatch('settings.type.update.before', $id);

        $type = $this->typeRepository->update(request()->only(['name']), $id);

        Event::dispatch('settings.type.update.after', $type);

        return new JsonResponse([
            'data' => $type,
            'message' => trans('admin::app.settings.types.index.update-success'),
        ]);
    }

    /**
     * Retire el tipo especificado del almacenamiento.
     */
    public function destroy(int $id): JsonResponse
    {
        $type = $this->typeRepository->findOrFail($id);

        try {
            Event::dispatch('settings.type.delete.before', $id);

            $type->delete($id);

            Event::dispatch('settings.type.delete.after', $id);

            return new JsonResponse([
                'message' => trans('admin::app.settings.types.index.delete-success'),
            ], 200);
        } catch (\Exception $exception) {
            return new JsonResponse([
                'message' => trans('admin::app.settings.types.index.delete-failed'),
            ], 400);
        }
    }
}
