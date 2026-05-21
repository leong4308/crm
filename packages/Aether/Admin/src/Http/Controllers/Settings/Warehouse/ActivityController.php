<?php

namespace Aether\Admin\Http\Controllers\Settings\Warehouse;

use Aether\Activity\Repositories\ActivityRepository;
use Aether\Admin\Http\Controllers\Controller;
use Aether\Admin\Http\Resources\ActivityResource;
use Aether\Email\Repositories\EmailRepository;
use Illuminate\Http\Response;

class ActivityController extends Controller
{
    /**
     * Cree una nueva instancia de controlador.
     *
     * @return void
     */
    public function __construct(
        protected ActivityRepository $activityRepository,
        protected EmailRepository $emailRepository
    ) {}

    /**
     * Mostrar una lista del recurso.
     *
     * @param  int  $id
     * @return Response
     */
    public function index($id)
    {
        $activities = $this->activityRepository
            ->leftJoin('warehouse_activities', 'activities.id', '=', 'warehouse_activities.activity_id')
            ->where('warehouse_activities.warehouse_id', $id)
            ->get();

        return ActivityResource::collection($this->concatEmail($activities));
    }

    /**
     * Almacene un recurso recién creado en el almacenamiento.
     */
    public function concatEmail($activities)
    {
        return $activities->sortByDesc('id')->sortByDesc('created_at');
    }
}
