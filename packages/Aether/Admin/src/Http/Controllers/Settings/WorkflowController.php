<?php

namespace Aether\Admin\Http\Controllers\Settings;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Event;
use Illuminate\View\View;
use Aether\Admin\DataGrids\Settings\WorkflowDataGrid;
use Aether\Admin\Http\Controllers\Controller;
use Aether\Automation\Repositories\WorkflowRepository;

class WorkflowController extends Controller
{
    /**
     * Cree una nueva instancia de controlador.
     *
     * @return void
     */
    public function __construct(protected WorkflowRepository $workflowRepository) {}

    /**
     * Mostrar una lista del flujo de trabajo.
     */
    public function index(): View|JsonResponse
    {
        if (request()->ajax()) {
            return datagrid(WorkflowDataGrid::class)->process();
        }

        return view('admin::settings.workflows.index');
    }

    /**
     * Mostrar el formulario para crear un nuevo recurso.
     */
    public function create(): View
    {
        return view('admin::settings.workflows.create');
    }

    /**
     * Almacene un flujo de trabajo recién creado en el almacenamiento.
     */
    public function store(): RedirectResponse
    {
        $this->validate(request(), [
            'name' => 'required',
        ]);

        Event::dispatch('settings.workflow.create.before');

        $workflow = $this->workflowRepository->create(request()->all());

        Event::dispatch('settings.workflow.create.after', $workflow);

        session()->flash('success', trans('admin::app.settings.workflows.index.create-success'));

        return redirect()->route('admin.settings.workflows.index');
    }

    /**
     * Muestra el formulario para editar el flujo de trabajo especificado.
     */
    public function edit(int $id): View
    {
        $workflow = $this->workflowRepository->findOrFail($id);

        return view('admin::settings.workflows.edit', compact('workflow'));
    }

    /**
     * Actualice el flujo de trabajo especificado en el almacenamiento.
     */
    public function update(int $id): RedirectResponse
    {
        $this->validate(request(), [
            'name' => 'required',
        ]);

        Event::dispatch('settings.workflow.update.before', $id);

        $workflow = $this->workflowRepository->update(request()->all(), $id);

        Event::dispatch('settings.workflow.update.after', $workflow);

        session()->flash('success', trans('admin::app.settings.workflows.index.update-success'));

        return redirect()->route('admin.settings.workflows.index');
    }

    /**
     * Elimine el flujo de trabajo especificado del almacenamiento.
     */
    public function destroy(int $id): JsonResponse
    {
        $workflow = $this->workflowRepository->findOrFail($id);

        try {
            Event::dispatch('settings.workflow.delete.before', $id);

            $workflow->delete($id);

            Event::dispatch('settings.workflow.delete.after', $id);

            return response()->json([
                'message' => trans('admin::app.settings.workflows.index.delete-success'),
            ], 200);
        } catch (\Exception $exception) {
            return response()->json([
                'message' => trans('admin::app.settings.workflows.index.delete-failed'),
            ], 400);
        }

        return response()->json([
            'message' => trans('admin::app.settings.workflows.index.delete-failed'),
        ], 400);
    }
}
