<?php

namespace Aether\Tag\Repositories;

use Aether\Core\Eloquent\Repository;

class TagRepository extends Repository
{
    /**
     * Campos buscables
     */
    protected $fieldSearchable = [
        'name',
        'color',
        'user_id',
    ];

    /**
     * Especificar el nombre de la clase del modelo
     *
     * @return mixed
     */
    public function model()
    {
        return 'Aether\Tag\Contracts\Tag';
    }
}
