<?php

namespace Aether\Admin\Http\Controllers\Contact\Persons;

use Aether\Admin\Http\Controllers\Controller;
use Aether\Contact\Repositories\PersonRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Event;

class TagController extends Controller
{
    /**
     * Cree una nueva instancia de controlador.
     *
     * @return void
     */
    public function __construct(protected PersonRepository $personRepository) {}

    /**
     * Almacene un recurso recién creado en el almacenamiento.
     */
    public function attach(int $id): JsonResponse
    {
        Event::dispatch('persons.tag.create.before', $id);

        $person = $this->personRepository->find($id);

        if (! $person->tags->contains(request()->input('tag_id'))) {
            $person->tags()->attach(request()->input('tag_id'));
        }

        Event::dispatch('persons.tag.create.after', $person);

        return response()->json([
            'message' => trans('admin::app.contacts.persons.view.tags.create-success'),
        ]);
    }

    /**
     * Elimine el recurso especificado del almacenamiento.
     */
    public function detach(int $personId): JsonResponse
    {
        Event::dispatch('persons.tag.delete.before', $personId);

        $person = $this->personRepository->find($personId);

        $person->tags()->detach(request()->input('tag_id'));

        Event::dispatch('persons.tag.delete.after', $person);

        return response()->json([
            'message' => trans('admin::app.contacts.persons.view.tags.destroy-success'),
        ]);
    }
}
