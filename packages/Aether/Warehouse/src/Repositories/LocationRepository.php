<?php

namespace Aether\Warehouse\Repositories;

use Aether\Core\Eloquent\Repository;

class LocationRepository extends Repository
{
    /**
     * Campos buscables
     */
    protected $fieldSearchable = [
        'name',
        'warehouse_id',
    ];

    /**
     * Especificar el nombre de la clase del modelo
     *
     * @return mixed
     */
    public function model()
    {
        return 'Aether\Warehouse\Contracts\Location';
    }
}
