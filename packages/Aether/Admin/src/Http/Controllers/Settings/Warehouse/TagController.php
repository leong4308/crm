<?php

namespace Aether\Admin\Http\Controllers\Settings\Warehouse;

use Aether\Admin\Http\Controllers\Controller;
use Aether\Warehouse\Repositories\WarehouseRepository;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Event;

class TagController extends Controller
{
    /**
     * Cree una nueva instancia de controlador.
     *
     * @return void
     */
    public function __construct(protected WarehouseRepository $warehouseRepository) {}

    /**
     * Almacene un recurso recién creado en el almacenamiento.
     *
     * @param  int  $id
     * @return Response
     */
    public function attach($id)
    {
        Event::dispatch('warehouse.tag.create.before', $id);

        $warehouse = $this->warehouseRepository->find($id);

        if (! $warehouse->tags->contains(request()->input('tag_id'))) {
            $warehouse->tags()->attach(request()->input('tag_id'));
        }

        Event::dispatch('warehouse.tag.create.after', $warehouse);

        return response()->json([
            'message' => trans('admin::app.warehouse.view.tags.create-success'),
        ]);
    }

    /**
     * Elimine el recurso especificado del almacenamiento.
     *
     * @param  int  $warehouseId
     * @return Response
     */
    public function detach($warehouseId)
    {
        Event::dispatch('warehouse.tag.delete.before', $warehouseId);

        $warehouse = $this->warehouseRepository->find($warehouseId);

        $warehouse->tags()->detach(request()->input('tag_id'));

        Event::dispatch('warehouse.tag.delete.after', $warehouse);

        return response()->json([
            'message' => trans('admin::app.leads.view.tags.destroy-success'),
        ]);
    }
}
