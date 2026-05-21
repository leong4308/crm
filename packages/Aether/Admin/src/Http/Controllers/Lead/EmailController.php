<?php

namespace Aether\Admin\Http\Controllers\Lead;

use Aether\Admin\Http\Controllers\Mail\EmailController as BaseEmailController;
use Aether\Admin\Http\Resources\ActivityResource;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Event;

class EmailController extends BaseEmailController
{
    /**
     * Almacene un recurso recién creado en el almacenamiento.
     *
     * @return Response
     */
    public function store()
    {
        $response = json_decode(parent::store()->getContent(), true);

        return response()->json([
            'data' => $this->transformToActivity($response['data']),
            'message' => $response['message'],
        ]);

        return $response;
    }

    /**
     * Almacene un recurso recién creado en el almacenamiento.
     *
     * @param  int  $id
     * @return Response
     */
    public function detach($id)
    {
        Event::dispatch('email.update.before', request()->input('email_id'));

        $email = $this->emailRepository->update([
            'lead_id' => null,
        ], request()->input('email_id'));

        Event::dispatch('email.update.after', $email);

        return response()->json([
            'message' => trans('admin::app.mail.update-success'),
        ]);
    }

    /**
     * Transforme los datos del correo electrónico en recursos de actividad.
     *
     * @param  array  $data
     * @return ActivityResource
     */
    public function transformToActivity($data)
    {
        return new ActivityResource((object) [
            'id' => $data['id'],
            'parent_id' => $data['parent_id'],
            'title' => $data['subject'],
            'type' => 'email',
            'is_done' => 1,
            'comment' => $data['reply'],
            'schedule_from' => null,
            'schedule_to' => null,
            'user' => auth()->guard('user')->user(),
            'participants' => [],
            'location' => null,
            'additional' => json_encode([
                'folders' => $data['folders'],
                'from' => $data['from'],
                'to' => $data['reply_to'],
                'cc' => $data['cc'],
                'bcc' => $data['bcc'],
            ]),
            'files' => array_map(function ($attachment) {
                return (object) $attachment;
            }, $data['attachments']),
            'created_at' => $data['created_at'],
            'updated_at' => $data['updated_at'],
        ]);
    }
}
