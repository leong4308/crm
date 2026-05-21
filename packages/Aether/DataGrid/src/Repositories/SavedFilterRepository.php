<?php

namespace Aether\DataGrid\Repositories;

use Aether\Core\Eloquent\Repository;
use Aether\DataGrid\Contracts\SavedFilter;

class SavedFilterRepository extends Repository
{
    /**
     * Especifique el nombre de la clase de modelo.
     */
    public function model(): string
    {
        return SavedFilter::class;
    }
}
