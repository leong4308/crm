<?php

namespace Aether\Activity\Repositories;

use Aether\Core\Eloquent\Repository;

class ParticipantRepository extends Repository
{
    /**
     * Especificar el nombre de la clase del modelo
     *
     * @return mixed
     */
    public function model()
    {
        return 'Aether\Activity\Contracts\Participant';
    }
}
