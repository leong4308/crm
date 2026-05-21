<?php

namespace Aether\Admin\Http\Controllers\Settings;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Event;
use Illuminate\View\View;
use Aether\Admin\Http\Controllers\Controller;
use Aether\Attribute\Repositories\AttributeRepository;
use Aether\Contact\Repositories\PersonRepository;
use Aether\Lead\Repositories\LeadRepository;
use Aether\Lead\Repositories\PipelineRepository;
use Aether\Lead\Repositories\SourceRepository;
use Aether\Lead\Repositories\TypeRepository;
use Aether\WebForm\DataGrids\WebFormDataGrid;
use Aether\WebForm\Repositories\WebFormRepository;

class WebFormController extends Controller
{
    /**
     * Cree una nueva instancia de controlador.
     *
     * @return void
     */
    public function __construct(
        protected AttributeRepository $attributeRepository,
        protected WebFormRepository $webFormRepository,
        protected PersonRepository $personRepository,
        protected LeadRepository $leadRepository,
        protected PipelineRepository $pipelineRepository,
        protected SourceRepository $sourceRepository,
        protected TypeRepository $typeRepository
    ) {}

    /**
     * Muestra una lista de la plantilla de correo electrónico.
     */
    public function index(): View|JsonResponse
    {
        if (request()->ajax()) {
            return datagrid(WebFormDataGrid::class)->process();
        }

        return view('admin::settings.web-forms.index');
    }

    /**
     * Mostrar el formulario para crear un nuevo recurso.
     */
    public function create(): View
    {
        $tempAttributes = $this->attributeRepository->findWhereIn('entity_type', ['persons', 'leads']);

        $attributes = [];

        foreach ($tempAttributes as $attribute) {
            if (
                $attribute->entity_type == 'persons'
                && in_array($attribute->code, ['name', 'emails', 'contact_numbers'])
            ) {
                $attributes['default'][] = $attribute;
            } else {
                $attributes['other'][] = $attribute;
            }
        }

        return view('admin::settings.web-forms.create', compact('attributes'));
    }

    /**
     * Almacene las plantillas de correo electrónico recién creadas en el almacenamiento.
     */
    public function store(): RedirectResponse
    {
        $this->validate(request(), [
            'title' => 'required',
            'submit_button_label' => 'required',
            'submit_success_action' => 'required',
            'submit_success_content' => 'required',
        ]);

        Event::dispatch('settings.web_forms.create.before');

        $data = request()->all();

        $webForm = $this->webFormRepository->create($data);

        Event::dispatch('settings.web_forms.create.after', $webForm);

        session()->flash('success', trans('admin::app.settings.webforms.index.create-success'));

        return redirect()->route('admin.settings.web_forms.index');
    }

    /**
     * Muestra el formulario para editar la plantilla de correo electrónico especificada.
     */
    public function edit(int $id): View
    {
        $webForm = $this->webFormRepository->findOrFail($id);

        $attributes = $this->attributeRepository->findWhere([
            ['entity_type', 'IN', ['persons', 'leads']],
            ['id', 'NOTIN', $webForm->attributes()->pluck('attribute_id')->toArray()],
        ]);

        return view('admin::settings.web-forms.edit', compact('webForm', 'attributes'));
    }

    /**
     * Actualice la plantilla de correo electrónico especificada en el almacenamiento.
     */
    public function update(int $id): RedirectResponse
    {
        $this->validate(request(), [
            'title' => 'required',
            'submit_button_label' => 'required',
            'submit_success_action' => 'required',
            'submit_success_content' => 'required',
        ]);

        Event::dispatch('settings.web_forms.update.before', $id);

        $data = request()->all();

        $webForm = $this->webFormRepository->update($data, $id);

        Event::dispatch('settings.web_forms.update.after', $webForm);

        session()->flash('success', trans('admin::app.settings.webforms.index.update-success'));

        return redirect()->route('admin.settings.web_forms.index');
    }

    /**
     * Elimine la plantilla de correo electrónico especificada del almacenamiento.
     */
    public function destroy(int $id): JsonResponse
    {
        $webForm = $this->webFormRepository->findOrFail($id);

        try {
            Event::dispatch('settings.web_forms.delete.before', $id);

            $webForm->delete($id);

            Event::dispatch('settings.web_forms.delete.after', $id);

            return response()->json([
                'message' => trans('admin::app.settings.webforms.index.delete-success'),
            ]);
        } catch (\Exception $exception) {
            return response()->json([
                'message' => trans('admin::app.settings.webforms.index.delete-failed'),
            ], 400);
        }
    }
}
