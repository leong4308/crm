<?php

namespace Aether\Lead\Repositories;

use Aether\Core\Eloquent\Repository;

class ProductRepository extends Repository
{
    /**
     * Especificar el nombre de la clase del modelo
     *
     * @return mixed
     */
    public function model()
    {
        return 'Aether\Lead\Contracts\Product';
    }
}
