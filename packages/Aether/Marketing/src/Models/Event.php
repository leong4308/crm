<?php

namespace Aether\Marketing\Models;

use Illuminate\Database\Eloquent\Model;
use Aether\Marketing\Contracts\Event as EventContract;

class Event extends Model implements EventContract
{
    /**
     * La tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'marketing_events';

    /**
     * Los atributos que se pueden rellenar.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'date',
    ];

    public function campaigns()
    {
        return $this->hasMany(CampaignProxy::modelClass(), 'marketing_event_id');
    }
}
