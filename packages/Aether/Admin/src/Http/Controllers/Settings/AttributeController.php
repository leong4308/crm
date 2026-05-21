<?php

namespace Aether\Admin\Http\Controllers\Settings;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Aether\Admin\DataGrids\Settings\AttributeDataGrid;
use Aether\Admin\Http\Controllers\Controller;
use Aether\Admin\Http\Requests\MassDestroyRequest;
use Aether\Attribute\Repositories\AttributeRepository;
use Aether\Attribute\Repositories\AttributeValueRepository;
use Aether\Core\Contracts\Validations\Code;

class AttributeController extends Controller
{
    /**
     * Cree una nueva instancia de controlador.
     *
     * @return void
     */
    public function __construct(
        protected AttributeRepository $attributeRepository,
        protected AttributeValueRepository $attributeValueRepository
    ) {}

    /**
     * Mostrar una lista del recurso.
     */
    public function index(): View|JsonResponse
    {
        if (request()->ajax()) {
            return datagrid(AttributeDataGrid::class)->process();
        }

        return view('admin::settings.attributes.index');
    }

    /**
     * Mostrar el formulario para crear un nuevo recurso.
     */
    public function create(): View
    {
        return view('admin::settings.attributes.create');
    }

    /**
     * Almacene un recurso recién creado en el almacenamiento.
     */
    public function store(): RedirectResponse|JsonResponse
    {
        $this->validate(request(), [
            'code' => ['required', 'unique:attributes,code,NULL,NULL,entity_type,'.request('entity_type'), new Code],
            'name' => 'required',
            'type' => 'required',
        ]);

        Event::dispatch('settings.attribute.create.before');

        $attribute = $this->attributeRepository->create(request()->all());

        Event::dispatch('settings.attribute.create.after', $attribute);

        if (request()->ajax()) {
            return response()->json([
                'data' => $attribute,
                'message' => trans('admin::app.settings.attributes.index.create-success'),
            ]);
        }

        session()->flash('success', trans('admin::app.settings.attributes.index.create-success'));

        return redirect()->route('admin.settings.attributes.index');
    }

    /**
     * Muestra el formulario para editar el recurso especificado.
     */
    public function edit(int $id): View
    {
        $attribute = $this->attributeRepository->findOrFail($id);

        return view('admin::settings.attributes.edit', compact('attribute'));
    }

    /**
     * Actualice el recurso especificado en el almacenamiento.
     */
    public function update($id): RedirectResponse
    {
        $this->validate(request(), [
            'code' => ['required', 'unique:attributes,code,NULL,NULL,entity_type,'.$id, new Code],
            'name' => 'required',
            'type' => 'required',
        ]);

        Event::dispatch('settings.attribute.update.before', $id);

        $attribute = $this->attributeRepository->update(request()->all(), $id);

        Event::dispatch('settings.attribute.update.after', $attribute);

        session()->flash('success', trans('admin::app.settings.attributes.index.update-success'));

        return redirect()->route('admin.settings.attributes.index');
    }

    /**
     * Elimine el recurso especificado del almacenamiento.
     */
    public function destroy(int $id): JsonResponse
    {
        $attribute = $this->attributeRepository->findOrFail($id);

        if (! $attribute->is_user_defined) {
            return response()->json([
                'message' => trans('admin::app.settings.attributes.index.user-define-error'),
            ], 400);
        }

        try {
            Event::dispatch('settings.attribute.delete.before', $id);

            $this->attributeRepository->delete($id);

            Event::dispatch('settings.attribute.delete.after', $id);

            return response()->json([
                'status' => true,
                'message' => trans('admin::app.settings.attributes.index.delete-success'),
            ], 200);
        } catch (\Exception $exception) {
            return response()->json([
                'message' => trans('admin::app.settings.attributes.index.delete-failed'),
            ], 400);
        }
    }

    /**
     * Verifique la validación única.
     *
     * @return void
     */
    public function checkUniqueValidation()
    {
        $attribute = $this->attributeRepository->findOneWhere([
            'code' => request('attribute_code'),
        ]);

        return response()->json([
            'validated' => $this->attributeValueRepository->isValueUnique(
                request('entity_id'),
                request('entity_type'),
                $attribute,
                request('attribute_value'),
            ),
        ]);
    }

    /**
     * Resultados de búsqueda de atributos de búsqueda
     */
    public function lookup($lookup): JsonResponse
    {
        $results = $this->attributeRepository->getLookUpOptions($lookup, request()->input('query'));

        return response()->json($results);
    }

    /**
     * Resultados de búsqueda de atributos de búsqueda
     */
    public function lookupEntity(string $lookup): JsonResponse
    {
        $result = $this->attributeRepository->getLookUpEntity($lookup, request()->input('query'));

        return response()->json($result);
    }

    /**
     * Elimina en masa los recursos especificados.
     */
    public function massDestroy(MassDestroyRequest $massDestroyRequest): JsonResponse
    {
        $count = 0;

        $attributes = $this->attributeRepository->findWhereIn('id', $massDestroyRequest->input('indices'));

        foreach ($attributes as $attribute) {
            $attribute = $this->attributeRepository->find($attribute->id);

            if (! $attribute->is_user_defined) {
                continue;
            }

            Event::dispatch('settings.attribute.delete.before', $attribute->id);

            $this->attributeRepository->delete($attribute->id);

            Event::dispatch('settings.attribute.delete.after', $attribute->id);

            $count++;
        }

        if (! $count) {
            return response()->json([
                'message' => trans('admin::app.settings.attributes.index.mass-delete-failed'),
            ], 400);
        }

        return response()->json([
            'message' => trans('admin::app.settings.attributes.index.delete-success'),
        ]);
    }

    /**
     * Obtenga opciones de atributos asociadas con el atributo.
     *
     * @return View
     */
    public function getAttributeOptions(int $id)
    {
        $attribute = $this->attributeRepository->findOrFail($id);

        return $attribute->options()->orderBy('sort_order')->get();
    }

    /**
     * Descargar imagen o archivo
     */
    public function download()
    {
        if (! request('path')) {
            return false;
        }

        return Storage::download(request('path'));
    }
}
