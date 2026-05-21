<?php

namespace Aether\Email\InboundEmailProcessor\Contracts;

interface InboundEmailProcessor
{
    /**
     * Procesar mensajes de todas las carpetas.
     *
     * @return mixed
     */
    public function processMessagesFromAllFolders();

    /**
     * Procesar el correo electrónico entrante.
     *
     * @param  mixed|null  $content
     */
    public function processMessage($content = null): void;
}
