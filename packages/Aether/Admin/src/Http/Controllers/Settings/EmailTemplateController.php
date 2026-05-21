<?php

namespace Aether\Admin\Http\Controllers\Settings;

use Aether\Admin\DataGrids\Settings\EmailTemplateDataGrid;
use Aether\Admin\Http\Controllers\Controller;
use Aether\Automation\Helpers\Entity;
use Aether\EmailTemplate\Repositories\EmailTemplateRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Event;
use Illuminate\View\View;

class EmailTemplateController extends Controller
{
    /**
     * Cree una nueva instancia de controlador.
     *
     * @return void
     */
    public function __construct(
        protected EmailTemplateRepository $emailTemplateRepository,
        protected Entity $workflowEntityHelper
    ) {}

    /**
     * Muestra una lista de la plantilla de correo electrónico.
     */
    public function index(): View|JsonResponse
    {
        if (request()->ajax()) {
            return datagrid(EmailTemplateDataGrid::class)->process();
        }

        return view('admin::settings.email-templates.index');
    }

    /**
     * Mostrar el formulario para crear un nuevo recurso.
     *
     * @return View
     */
    public function create()
    {
        $placeholders = $this->workflowEntityHelper->getEmailTemplatePlaceholders();

        return view('admin::settings.email-templates.create', compact('placeholders'));
    }

    /**
     * Almacene las plantillas de correo electrónico recién creadas en el almacenamiento.
     */
    public function store(): RedirectResponse
    {
        $this->validate(request(), [
            'name' => 'required|unique:email_templates,name',
            'subject' => 'required',
            'content' => 'required',
        ]);

        Event::dispatch('settings.email_templates.create.before');

        $emailTemplate = $this->emailTemplateRepository->create(request()->all());

        Event::dispatch('settings.email_templates.create.after', $emailTemplate);

        session()->flash('success', trans('admin::app.settings.email-template.index.create-success'));

        return redirect()->route('admin.settings.email_templates.index');
    }

    /**
     * Muestra el formulario para editar la plantilla de correo electrónico especificada.
     */
    public function edit(int $id): View
    {
        $emailTemplate = $this->emailTemplateRepository->findOrFail($id);

        $placeholders = $this->workflowEntityHelper->getEmailTemplatePlaceholders();

        return view('admin::settings.email-templates.edit', compact('emailTemplate', 'placeholders'));
    }

    /**
     * Actualice la plantilla de correo electrónico especificada en el almacenamiento.
     */
    public function update(int $id): RedirectResponse
    {
        $this->validate(request(), [
            'name' => 'required|unique:email_templates,name,'.$id,
            'subject' => 'required',
            'content' => 'required',
        ]);

        Event::dispatch('settings.email_templates.update.before', $id);

        $emailTemplate = $this->emailTemplateRepository->update(request()->all(), $id);

        Event::dispatch('settings.email_templates.update.after', $emailTemplate);

        session()->flash('success', trans('admin::app.settings.email-template.index.update-success'));

        return redirect()->route('admin.settings.email_templates.index');
    }

    /**
     * Elimine la plantilla de correo electrónico especificada del almacenamiento.
     */
    public function destroy(int $id): JsonResponse
    {
        $emailTemplate = $this->emailTemplateRepository->findOrFail($id);

        try {
            Event::dispatch('settings.email_templates.delete.before', $id);

            $emailTemplate->delete($id);

            Event::dispatch('settings.email_templates.delete.after', $id);

            return response()->json([
                'message' => trans('admin::app.settings.email-template.index.delete-success'),
            ], 200);
        } catch (\Exception $exception) {
            return response()->json([
                'message' => trans('admin::app.settings.email-template.index.delete-failed'),
            ], 400);
        }

        return response()->json([
            'message' => trans('admin::app.settings.email-template.index.delete-failed'),
        ], 400);
    }
}
