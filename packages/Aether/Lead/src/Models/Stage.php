<?php

namespace Aether\Lead\Models;

use Aether\Lead\Contracts\Stage as StageContract;
use Illuminate\Database\Eloquent\Model;

class Stage extends Model implements StageContract
{
    public $timestamps = false;

    protected $table = 'lead_pipeline_stages';

    /**
     * Los atributos que se pueden asignar en masa.
     *
     * @var array
     */
    protected $fillable = [
        'code',
        'name',
        'probability',
        'sort_order',
        'lead_pipeline_id',
    ];

    /**
     * Obtenga la canalización propietaria de la etapa de canalización.
     */
    public function pipeline()
    {
        return $this->belongsTo(PipelineProxy::modelClass(), 'lead_pipeline_id');
    }

    /**
     * Obtenga las pistas.
     */
    public function leads()
    {
        return $this->hasMany(LeadProxy::modelClass(), 'lead_pipeline_stage_id');
    }
}
