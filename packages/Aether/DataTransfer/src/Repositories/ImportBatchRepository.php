<?php

namespace Aether\DataTransfer\Repositories;

use Aether\Core\Eloquent\Repository;
use Aether\DataTransfer\Contracts\ImportBatch;

class ImportBatchRepository extends Repository
{
    /**
     * Especifique el nombre de la clase de modelo.
     */
    public function model(): string
    {
        return ImportBatch::class;
    }
}
