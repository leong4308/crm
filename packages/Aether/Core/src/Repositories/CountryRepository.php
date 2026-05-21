<?php

namespace Aether\Core\Repositories;

use Prettus\Repository\Traits\CacheableRepository;
use Aether\Core\Eloquent\Repository;

class CountryRepository extends Repository
{
    use CacheableRepository;

    /**
     * Especificar el nombre de la clase del modelo
     *
     * @return mixed
     */
    public function model()
    {
        return 'Aether\Core\Contracts\Country';
    }
}
