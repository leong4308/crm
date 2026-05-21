<?php

namespace Aether\Admin\Http\Controllers\Settings\Warehouse;

use Aether\Admin\DataGrids\Product\ProductDataGrid;
use Aether\Admin\DataGrids\Settings\WarehouseDataGrid;
use Aether\Admin\Http\Controllers\Controller;
use Aether\Admin\Http\Requests\AttributeForm;
use Aether\Warehouse\Repositories\WarehouseRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Event;
use Illuminate\View\View;
use Prettus\Repository\Criteria\RequestCriteria;

class WarehouseController extends Controller
{
    /**
     * Cree una nueva instancia de controlador.
     *
     * @return void
     */
    public function __construct(protected WarehouseRepository $warehouseRepository)
    {
        request()->request->add(['entity_type' => 'warehouses']);
    }

    /**
     * Mostrar una lista del recurso.
     */
    public function index(): View|JsonResponse
    {
        if (request()->ajax()) {
            return datagrid(WarehouseDataGrid::class)->process();
        }

        return view('admin::settings.warehouses.index');
    }

    /**
     * Buscar resultados de ubicación
     */
    public function search(): JsonResponse
    {
        $results = $this->warehouseRepository
            ->pushCriteria(app(RequestCriteria::class))
            ->all();

        return response()->json($results);
    }

    /**
     * Mostrar una lista del recurso del producto.
     */
    public function products(int $id)
    {
        if (request()->ajax()) {
            return datagrid(ProductDataGrid::class)->process();
        }

        $warehouse = $this->warehouseRepository->findOrFail($id);

        return view('admin::settings.warehouses.products', compact('warehouse'));
    }

    /**
     * Mostrar el formulario para crear un nuevo recurso.
     */
    public function create(): View
    {
        return view('admin::settings.warehouses.create');
    }

    /**
     * Almacene un recurso recién creado en el almacenamiento.
     */
    public function store(AttributeForm $request): RedirectResponse
    {
        Event::dispatch('settings.warehouse.create.before');

        $warehouse = $this->warehouseRepository->create($request->all());

        Event::dispatch('settings.warehouse.create.after', $warehouse);

        session()->flash('success', trans('admin::app.settings.warehouses.index.create-success'));

        return redirect()->route('admin.settings.warehouses.index');
    }

    /**
     * Muestra el formulario para ver el recurso especificado.
     */
    public function view(int $id): View
    {
        $warehouse = $this->warehouseRepository->findOrFail($id);

        return view('admin::settings.warehouses.view', compact('warehouse'));
    }

    /**
     * Muestra el formulario para editar el recurso especificado.
     *
     * @param  int  $id
     * @return View
     */
    public function edit($id)
    {
        $warehouse = $this->warehouseRepository->findOrFail($id);

        return view('admin::settings.warehouses.edit', compact('warehouse'));
    }

    /**
     * Actualice el recurso especificado en el almacenamiento.
     */
    public function update(AttributeForm $request, int $id): RedirectResponse|JsonResponse
    {
        Event::dispatch('settings.warehouse.update.before', $id);

        $warehouse = $this->warehouseRepository->update($request->all(), $id);

        Event::dispatch('settings.warehouse.update.after', $warehouse);

        if (request()->ajax()) {
            return response()->json([
                'data' => $warehouse,
                'message' => trans('admin::app.settings.warehouses.index.update-success'),
            ]);
        }

        session()->flash('success', trans('admin::app.settings.warehouses.index.update-success'));

        return redirect()->route('admin.settings.warehouses.index');
    }

    /**
     * Elimine el recurso especificado del almacenamiento.
     */
    public function destroy(int $id): JsonResponse
    {
        $this->warehouseRepository->findOrFail($id);

        try {
            Event::dispatch('settings.warehouse.delete.before', $id);

            $this->warehouseRepository->delete($id);

            Event::dispatch('settings.warehouse.delete.after', $id);

            return response()->json([
                'message' => trans('admin::app.settings.warehouses.index.delete-success'),
            ], 200);
        } catch (\Exception $exception) {
            return response()->json([
                'message' => trans('admin::app.settings.warehouses.index.delete-success'),
            ], 400);
        }
    }
}
