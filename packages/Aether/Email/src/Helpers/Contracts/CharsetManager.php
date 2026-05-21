<?php

namespace Aether\Email\Helpers\Contracts;

interface CharsetManager
{
    /**
     * Decodifica la cadena de Charset.
     *
     * @return string
     */
    public function decodeCharset($encodedString, $charset);

    /**
     * Obtener alias de juego de caracteres.
     *
     * @return string
     */
    public function getCharsetAlias($charset);
}
