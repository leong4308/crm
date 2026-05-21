<?php

namespace Aether\Email\Enums;

enum SupportedFolderEnum: string
{
    /**
     * Bandeja de entrada.
     */
    case INBOX = 'inbox';

    /**
     * Importante.
     */
    case IMPORTANT = 'important';

    /**
     * Sembrado de estrellas.
     */
    case STARRED = 'starred';

    /**
     * Borrador.
     */
    case DRAFT = 'draft';

    /**
     * Bandeja de salida.
     */
    case OUTBOX = 'outbox';

    /**
     * Enviado.
     */
    case SENT = 'sent';

    /**
     * Correo basura.
     */
    case SPAM = 'spam';

    /**
     * Basura.
     */
    case TRASH = 'trash';
}
