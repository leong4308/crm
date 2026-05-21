<?php

namespace Aether\Admin\Notifications\User;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Aether\User\Contracts\User;

class UserUpdatePassword extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Cree una nueva instancia de administrador.
     *
     * @param  User  $user
     * @return void
     */
    public function __construct(public $user) {}

    /**
     * Construye el mensaje.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from(core()->getSenderEmailDetails()['email'], core()->getSenderEmailDetails()['name'])
            ->to($this->user->email, $this->user->name)
            ->subject(trans('shop::app.mail.update-password.subject'))
            ->view('shop::emails.users.update-password', ['user' => $this->user]);
    }
}
