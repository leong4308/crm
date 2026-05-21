<?php

namespace Aether\Automation\Repositories;

use Aether\Automation\Contracts\Webhook;
use Aether\Core\Eloquent\Repository;

class WebhookRepository extends Repository
{
    /**
     * Especifique el nombre de la clase del modelo.
     */
    public function model(): string
    {
        return Webhook::class;
    }
}
