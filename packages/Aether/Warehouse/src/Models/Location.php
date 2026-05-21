<?php

namespace Aether\Warehouse\Models;

use Illuminate\Database\Eloquent\Model;
use Aether\Warehouse\Contracts\Location as LocationContract;

class Location extends Model implements LocationContract
{
    /**
     * La tabla asociada al modelo.
     */
    protected $table = 'warehouse_locations';

    /**
     * Los atributos que se pueden asignar en masa.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'warehouse_id',
    ];

    /**
     * Obtenga el almacén propietario de la ubicación.
     */
    public function warehouse()
    {
        return $this->belongsTo(WarehouseProxy::modelClass());
    }
}
