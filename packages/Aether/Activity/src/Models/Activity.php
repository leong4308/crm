<?php

namespace Aether\Activity\Models;

use Aether\Activity\Contracts\Activity as ActivityContract;
use Aether\Contact\Models\PersonProxy;
use Aether\Lead\Models\LeadProxy;
use Aether\Product\Models\ProductProxy;
use Aether\User\Models\UserProxy;
use Aether\Warehouse\Models\WarehouseProxy;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model implements ActivityContract
{
    /**
     * Definir el nombre de la tabla de la propiedad
     *
     * @var string
     */
    protected $table = 'activities';

    /**
     * Definir las relaciones que deben tocarse al guardar
     *
     * @var array
     */
    protected $with = ['user'];

    /**
     * Atributos de transmisión hasta la fecha y hora
     *
     * @var array
     */
    protected $casts = [
        'schedule_from' => 'datetime',
        'schedule_to' => 'datetime',
    ];

    /**
     * Los atributos que se pueden asignar en masa.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'type',
        'location',
        'comment',
        'additional',
        'schedule_from',
        'schedule_to',
        'is_done',
        'user_id',
    ];

    /**
     * Obtenga el usuario propietario de la actividad.
     */
    public function user()
    {
        return $this->belongsTo(UserProxy::modelClass());
    }

    /**
     * Los participantes que pertenecen a la actividad.
     */
    public function participants()
    {
        return $this->hasMany(ParticipantProxy::modelClass());
    }

    /**
     * Obtenga el archivo asociado a la actividad.
     */
    public function files()
    {
        return $this->hasMany(FileProxy::modelClass(), 'activity_id');
    }

    /**
     * Los leads que pertenecen a la actividad.
     */
    public function leads()
    {
        return $this->belongsToMany(LeadProxy::modelClass(), 'lead_activities');
    }

    /**
     * La Persona que pertenece a la actividad.
     */
    public function persons()
    {
        return $this->belongsToMany(PersonProxy::modelClass(), 'person_activities');
    }

    /**
     * Los leads que pertenecen a la actividad.
     */
    public function products()
    {
        return $this->belongsToMany(ProductProxy::modelClass(), 'product_activities');
    }

    /**
     * El Almacén que pertenece a la actividad.
     */
    public function warehouses()
    {
        return $this->belongsToMany(WarehouseProxy::modelClass(), 'warehouse_activities');
    }
}
