<?php

namespace Aether\Automation\Repositories;

use Aether\Automation\Contracts\Workflow;
use Aether\Core\Eloquent\Repository;

class WorkflowRepository extends Repository
{
    /**
     * Especifique el nombre de la clase del modelo.
     */
    public function model(): string
    {
        return Workflow::class;
    }
}
