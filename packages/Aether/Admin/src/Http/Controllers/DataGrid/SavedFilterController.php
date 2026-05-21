<?php

namespace Aether\Admin\Http\Controllers\DataGrid;

use Illuminate\Support\Facades\Event;
use Aether\Admin\Http\Controllers\Controller;
use Aether\DataGrid\Repositories\SavedFilterRepository;

class SavedFilterController extends Controller
{
    /**
     * Cree una nueva instancia de controlador.
     */
    public function __construct(protected SavedFilterRepository $savedFilterRepository) {}

    /**
     * Guarde los filtros en la base de datos.
     */
    public function store()
    {
        $userId = auth()->guard()->user()->id;

        $this->validate(request(), [
            'name' => 'required|unique:datagrid_saved_filters,name,NULL,id,src,'.request('src').',user_id,'.$userId,
        ]);

        Event::dispatch('datagrid.saved_filter.create.before');

        $savedFilter = $this->savedFilterRepository->create([
            'user_id' => $userId,
            'name' => request('name'),
            'src' => request('src'),
            'applied' => request('applied'),
        ]);

        Event::dispatch('datagrid.saved_filter.create.after', $savedFilter);

        return response()->json([
            'data' => $savedFilter,
            'message' => trans('admin::app.components.datagrid.toolbar.filter.saved-success'),
        ]);
    }

    /**
     * Recupera los filtros guardados.
     */
    public function get()
    {
        $savedFilters = $this->savedFilterRepository->findWhere([
            'src' => request()->get('src'),
            'user_id' => auth()->guard()->user()->id,
        ]);

        return response()->json(['data' => $savedFilters]);
    }

    /**
     * Actualice el filtro guardado.
     */
    public function update(int $id)
    {
        $userId = auth()->guard()->user()->id;

        $this->validate(request(), [
            'name' => 'required|unique:datagrid_saved_filters,name,'.$id.',id,src,'.request('src').',user_id,'.$userId,
        ]);

        $savedFilter = $this->savedFilterRepository->findOneWhere([
            'id' => $id,
            'user_id' => auth()->guard()->user()->id,
        ]);

        if (! $savedFilter) {
            return response()->json([], 404);
        }

        Event::dispatch('datagrid.saved_filter.update.before', $id);

        $updatedFilter = $this->savedFilterRepository->update(request()->only([
            'name',
            'src',
            'applied',
        ]), $id);

        Event::dispatch('datagrid.saved_filter.update.after', $updatedFilter);

        return response()->json([
            'data' => $updatedFilter,
            'message' => trans('admin::app.components.datagrid.toolbar.filter.updated-success'),
        ]);
    }

    /**
     * Elimina el filtro guardado.
     */
    public function destroy(int $id)
    {
        Event::dispatch('datagrid.saved_filter.delete.before', $id);

        $success = $this->savedFilterRepository->deleteWhere([
            'id' => $id,
            'user_id' => auth()->guard()->user()->id,
        ]);

        Event::dispatch('datagrid.saved_filter.delete.after', $id);

        if (! $success) {
            return response()->json([
                'message' => trans('admin::app.components.datagrid.toolbar.filter.delete-error'),
            ]);
        }

        return response()->json([
            'message' => trans('admin::app.components.datagrid.toolbar.filter.delete-success'),
        ]);
    }
}
