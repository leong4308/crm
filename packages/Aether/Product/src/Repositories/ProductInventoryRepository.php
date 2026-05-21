<?php

namespace Aether\Product\Repositories;

use Aether\Core\Eloquent\Repository;

class ProductInventoryRepository extends Repository
{
    /**
     * Especificar el nombre de la clase del modelo
     *
     * @return mixed
     */
    public function model()
    {
        return 'Aether\Product\Contracts\ProductInventory';
    }
}
