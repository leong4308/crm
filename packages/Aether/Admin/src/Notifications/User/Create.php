<?php

namespace Aether\Admin\Notifications\User;

use Illuminate\Mail\Mailable;

class Create extends Mailable
{
    /**
     * @param  object  $user
     * @return void
     */
    public function __construct(public $user) {}

    /**
     * Cree la representación de correo de la notificación.
     */
    public function build()
    {
        return $this
            ->to($this->user->email)
            ->subject(trans('admin::app.emails.common.user.create-subject'))
            ->view('admin::emails.users.create', [
                'user_name' => $this->user->name,
            ]);
    }
}
