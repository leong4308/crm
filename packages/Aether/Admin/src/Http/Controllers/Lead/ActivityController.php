<?php

namespace Aether\Admin\Http\Controllers\Lead;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Aether\Activity\Repositories\ActivityRepository;
use Aether\Admin\Http\Controllers\Controller;
use Aether\Admin\Http\Resources\ActivityResource;
use Aether\Email\Repositories\AttachmentRepository;
use Aether\Email\Repositories\EmailRepository;

class ActivityController extends Controller
{
    /**
     * Cree una nueva instancia de controlador.
     *
     * @return void
     */
    public function __construct(
        protected ActivityRepository $activityRepository,
        protected EmailRepository $emailRepository,
        protected AttachmentRepository $attachmentRepository
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
            ->leftJoin('lead_activities', 'activities.id', '=', 'lead_activities.activity_id')
            ->where('lead_activities.lead_id', $id)
            ->get();

        return ActivityResource::collection($this->concatEmailAsActivities($id, $activities));
    }

    /**
     * Almacene un recurso recién creado en el almacenamiento.
     */
    public function concatEmailAsActivities($leadId, $activities)
    {
        $childColumns = [
            'child.id', 'child.parent_id', 'child.subject', 'child.reply',
            DB::raw('child.folders::text as folders'),
            DB::raw('child."from"::text as "from"'),
            DB::raw('child.reply_to::text as reply_to'),
            DB::raw('child.cc::text as cc'),
            DB::raw('child.bcc::text as bcc'),
            'child.lead_id', 'child.person_id', 'child.unique_id',
            'child.source', 'child.is_read', 'child.created_at', 'child.updated_at',
        ];

        $parentColumns = [
            'parent.id', 'parent.parent_id', 'parent.subject', 'parent.reply',
            DB::raw('parent.folders::text as folders'),
            DB::raw('parent."from"::text as "from"'),
            DB::raw('parent.reply_to::text as reply_to'),
            DB::raw('parent.cc::text as cc'),
            DB::raw('parent.bcc::text as bcc'),
            'parent.lead_id', 'parent.person_id', 'parent.unique_id',
            'parent.source', 'parent.is_read', 'parent.created_at', 'parent.updated_at',
        ];

        $emails = DB::table('emails as child')
            ->select($childColumns)
            ->join('emails as parent', 'child.parent_id', '=', 'parent.id')
            ->where('parent.lead_id', $leadId)
            ->union(DB::table('emails as parent')->select($parentColumns)->whereNull('parent.parent_id')->where('parent.lead_id', $leadId))
            ->get();

        return $activities->concat($emails->map(function ($email) {
            return (object) [
                'id' => $email->id,
                'parent_id' => $email->parent_id,
                'title' => $email->subject,
                'type' => 'email',
                'is_done' => 1,
                'comment' => $email->reply,
                'schedule_from' => null,
                'schedule_to' => null,
                'user' => auth()->guard('user')->user(),
                'participants' => [],
                'location' => null,
                'additional' => [
                    'folders' => json_decode($email->folders),
                    'from' => json_decode($email->from),
                    'to' => json_decode($email->reply_to),
                    'cc' => json_decode($email->cc),
                    'bcc' => json_decode($email->bcc),
                ],
                'files' => $this->attachmentRepository->findWhere(['email_id' => $email->id])->map(function ($attachment) {
                    return (object) [
                        'id' => $attachment->id,
                        'name' => $attachment->name,
                        'path' => $attachment->path,
                        'url' => $attachment->url,
                        'created_at' => $attachment->created_at,
                        'updated_at' => $attachment->updated_at,
                    ];
                }),
                'created_at' => $email->created_at,
                'updated_at' => $email->updated_at,
            ];
        }))->sortByDesc('id')->sortByDesc('created_at');
    }
}
