<?php

namespace Aether\Attribute\Repositories;

use Aether\Core\Eloquent\Repository;

class AttributeOptionRepository extends Repository
{
    /**
     * Especificar el nombre de la clase del modelo
     *
     * @return mixed
     */
    public function model()
    {
        return 'Aether\Attribute\Contracts\AttributeOption';
    }
}
