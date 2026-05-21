<?php

namespace Aether\Marketing\Models;

use Aether\EmailTemplate\Models\EmailTemplateProxy;
use Aether\Marketing\Contracts\Campaign as CampaignContract;
use Illuminate\Database\Eloquent\Model;

class Campaign extends Model implements CampaignContract
{
    /**
     * Defina la tabla para el modelo.
     *
     * @var string
     */
    protected $table = 'marketing_campaigns';

    /**
     * Los atributos que se pueden rellenar.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'subject',
        'status',
        'marketing_template_id',
        'marketing_event_id',
        'spooling',
    ];

    /**
     * Obtenga la plantilla de correo electrónico
     */
    public function email_template()
    {
        return $this->belongsTo(EmailTemplateProxy::modelClass(), 'marketing_template_id');
    }

    /**
     * Obtener el evento
     */
    public function event()
    {
        return $this->belongsTo(EventProxy::modelClass(), 'marketing_event_id');
    }
}
