<?php

namespace Aether\Admin\Listeners;

use Aether\Email\Repositories\EmailRepository;

class Person
{
    /**
     * Cree una nueva instancia de controlador.
     *
     * @return void
     */
    public function __construct(protected EmailRepository $emailRepository) {}

    /**
     * @param  \Aether\Contact\Models\Person  $person
     * @return void
     */
    public function linkToEmail($person)
    {
        if (! request('email_id')) {
            return;
        }

        $this->emailRepository->update([
            'person_id' => $person->id,
        ], request('email_id'));
    }
}
