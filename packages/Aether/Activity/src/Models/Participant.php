<?php

namespace Aether\Activity\Models;

use Aether\Activity\Contracts\Participant as ParticipantContract;
use Aether\Contact\Models\PersonProxy;
use Aether\User\Models\UserProxy;
use Illuminate\Database\Eloquent\Model;

class Participant extends Model implements ParticipantContract
{
    public $timestamps = false;

    protected $table = 'activity_participants';

    protected $with = ['user', 'person'];

    /**
     * Los atributos que se pueden asignar en masa.
     *
     * @var array
     */
    protected $fillable = [
        'activity_id',
        'user_id',
        'person_id',
    ];

    /**
     * Obtenga la actividad que pertenece al participante.
     */
    public function activity()
    {
        return $this->belongsTo(ActivityProxy::modelClass());
    }

    /**
     * Obtenga el usuario propietario del participante.
     */
    public function user()
    {
        return $this->belongsTo(UserProxy::modelClass());
    }

    /**
     * Obtenga la persona propietaria del participante.
     */
    public function person()
    {
        return $this->belongsTo(PersonProxy::modelClass());
    }
}
