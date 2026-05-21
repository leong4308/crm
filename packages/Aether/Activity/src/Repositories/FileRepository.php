<?php

namespace Aether\Activity\Repositories;

use Aether\Activity\Contracts\File;
use Aether\Core\Eloquent\Repository;

class FileRepository extends Repository
{
    /**
     * Especifique el nombre de la clase de modelo.
     *
     * @return mixed
     */
    public function model()
    {
        return File::class;
    }
}
