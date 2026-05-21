<?php

namespace Aether\Marketing\Mail;

use Aether\Marketing\Contracts\Campaign;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class CampaignMail extends Mailable
{
    /**
     * Cree una nueva instancia de mensaje.
     *
     * @return void
     */
    public function __construct(
        public string $email,
        public Campaign $campaign
    ) {}

    /**
     * Consigue el sobre del mensaje.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            to: [
                new Address($this->email),
            ],
            subject: $this->campaign->subject,
        );
    }

    /**
     * Obtenga la definición del contenido del mensaje.
     */
    public function content(): Content
    {
        return new Content(
            htmlString: $this->campaign->email_template->content,
        );
    }
}
