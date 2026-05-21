<?php

namespace Aether\EmailTemplate\Repositories;

use Aether\Core\Eloquent\Repository;

class EmailTemplateRepository extends Repository
{
    /**
     * Especificar el nombre de la clase del modelo
     *
     * @return mixed
     */
    public function model()
    {
        return 'Aether\EmailTemplate\Contracts\EmailTemplate';
    }
}
