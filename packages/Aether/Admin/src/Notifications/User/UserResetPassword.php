<?php

namespace Aether\Admin\Notifications\User;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class UserResetPassword extends ResetPassword
{
    /**
     * Cree la representación de correo de la notificación.
     *
     * @param  mixed  $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable)
    {
        if (static::$toMailCallback) {
            return call_user_func(static::$toMailCallback, $notifiable, $this->token);
        }

        return (new MailMessage)
            ->view('admin::emails.users.forget-password', [
                'user_name' => $notifiable->name,
                'token' => $this->token,
            ]);
    }
}
