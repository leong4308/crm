<?php

namespace Aether\Marketing\Repositories;

use Aether\Core\Eloquent\Repository;
use Aether\Marketing\Contracts\Campaign;

class CampaignRepository extends Repository
{
    /**
     * Especifique el nombre de la clase del modelo.
     */
    public function model(): string
    {
        return Campaign::class;
    }
}
