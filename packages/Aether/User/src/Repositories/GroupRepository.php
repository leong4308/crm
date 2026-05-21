<?php

namespace Aether\User\Repositories;

use Aether\Core\Eloquent\Repository;

class GroupRepository extends Repository
{
    /**
     * Especificar el nombre de la clase del modelo
     *
     * @return mixed
     */
    public function model()
    {
        return 'Aether\User\Contracts\Group';
    }
}
