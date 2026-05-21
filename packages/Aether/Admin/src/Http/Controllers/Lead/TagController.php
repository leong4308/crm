<?php

namespace Aether\Admin\Http\Controllers\Lead;

use Aether\Admin\Http\Controllers\Controller;
use Aether\Lead\Repositories\LeadRepository;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Event;

class TagController extends Controller
{
    /**
     * Cree una nueva instancia de controlador.
     *
     * @return void
     */
    public function __construct(protected LeadRepository $leadRepository) {}

    /**
     * Almacene un recurso recién creado en el almacenamiento.
     *
     * @param  int  $id
     * @return Response
     */
    public function attach($id)
    {
        Event::dispatch('leads.tag.create.before', $id);

        $lead = $this->leadRepository->find($id);

        if (! $lead->tags->contains(request()->input('tag_id'))) {
            $lead->tags()->attach(request()->input('tag_id'));
        }

        Event::dispatch('leads.tag.create.after', $lead);

        return response()->json([
            'message' => trans('admin::app.leads.view.tags.create-success'),
        ]);
    }

    /**
     * Elimine el recurso especificado del almacenamiento.
     *
     * @param  int  $leadId
     * @return Response
     */
    public function detach($leadId)
    {
        Event::dispatch('leads.tag.delete.before', $leadId);

        $lead = $this->leadRepository->find($leadId);

        $lead->tags()->detach(request()->input('tag_id'));

        Event::dispatch('leads.tag.delete.after', $lead);

        return response()->json([
            'message' => trans('admin::app.leads.view.tags.destroy-success'),
        ]);
    }
}
