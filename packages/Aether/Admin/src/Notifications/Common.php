<?php

namespace Aether\Admin\Notifications;

use Illuminate\Mail\Mailable;

class Common extends Mailable
{
    /**
     * Cree una nueva instancia de notificación.
     *
     * @return void
     */
    public function __construct(public $data) {}

    /**
     * Cree la representación de correo de la notificación.
     */
    public function build()
    {
        $message = $this
            ->to($this->data['to'])
            ->subject($this->data['subject'])
            ->view('admin::emails.common.index', [
                'body' => $this->data['body'],
            ]);

        if (isset($this->data['attachments'])) {
            foreach ($this->data['attachments'] as $attachment) {
                $message->attachData($attachment['content'], $attachment['name'], [
                    'mime' => $attachment['mime'],
                ]);
            }
        }

        return $message;
    }
}
