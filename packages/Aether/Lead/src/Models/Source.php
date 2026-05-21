<?php

namespace Aether\Lead\Models;

use Illuminate\Database\Eloquent\Model;
use Aether\Lead\Contracts\Source as SourceContract;

class Source extends Model implements SourceContract
{
    protected $table = 'lead_sources';

    /**
     * Los atributos que se pueden asignar en masa.
     *
     * @var array
     */
    protected $fillable = [
        'name',
    ];

    /**
     * Obtenga las pistas.
     */
    public function leads()
    {
        return $this->hasMany(LeadProxy::modelClass(), 'lead_source_id', 'id');
    }
}
