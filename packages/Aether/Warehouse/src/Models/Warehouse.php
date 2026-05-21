<?php

namespace Aether\Warehouse\Models;

use Illuminate\Database\Eloquent\Model;
use Aether\Activity\Models\ActivityProxy;
use Aether\Activity\Traits\LogsActivity;
use Aether\Attribute\Traits\CustomAttribute;
use Aether\Tag\Models\TagProxy;
use Aether\Warehouse\Contracts\Warehouse as WarehouseContract;

class Warehouse extends Model implements WarehouseContract
{
    use CustomAttribute, LogsActivity;

    /**
     * Los atributos que se pueden asignar en masa.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'contact_name',
        'contact_emails',
        'contact_numbers',
        'contact_address',
    ];

    /**
     * Los atributos que se pueden convertir.
     *
     * @var array
     */
    protected $casts = [
        'contact_emails' => 'array',
        'contact_numbers' => 'array',
        'contact_address' => 'array',
    ];

    /**
     * Obtenga las ubicaciones del almacén.
     */
    public function locations()
    {
        return $this->hasMany(LocationProxy::modelClass());
    }

    /**
     * Las etiquetas que pertenecen al cliente potencial.
     */
    public function tags()
    {
        return $this->belongsToMany(TagProxy::modelClass(), 'warehouse_tags');
    }

    /**
     * Obtenga las actividades.
     */
    public function activities()
    {
        return $this->belongsToMany(ActivityProxy::modelClass(), 'warehouse_activities');
    }
}
