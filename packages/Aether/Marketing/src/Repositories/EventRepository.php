<?php

namespace Aether\Marketing\Repositories;

use Aether\Core\Eloquent\Repository;
use Aether\Marketing\Contracts\Event;

class EventRepository extends Repository
{
    /**
     * Especifique el nombre de la clase del modelo.
     */
    public function model(): string
    {
        return Event::class;
    }
}
