<?php

namespace Aether\Email\Helpers;

class Attachment
{
    /**
     * Contenido.
     *
     * @var File Content
     */
    private $content = null;

    /**
     * Crear una instancia auxiliar
     */
    public function __construct(
        public $filename,
        public $contentType,
        public $stream,
        public $contentDisposition = 'attachment',
        public $contentId = '',
        public $headers = []
    ) {}

    /**
     * Recupere el nombre del archivo adjunto.
     *
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Recupera el tipo de contenido del archivo adjunto.
     *
     * @return string
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * Recuperar la disposición del contenido del archivo adjunto.
     *
     * @return string
     */
    public function getContentDisposition()
    {
        return $this->contentDisposition;
    }

    /**
     * Recupere el ID del contenido del archivo adjunto.
     *
     * @return string
     */
    public function getContentID()
    {
        return $this->contentId;
    }

    /**
     * Recupere los encabezados de los archivos adjuntos.
     *
     * @return string
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Lea el contenido unos pocos bytes a la vez hasta que esté completo.
     *
     * Una vez leído hasta el final, siempre devuelve falso.
     *
     * @param  int  $bytes
     * @return string
     */
    public function read($bytes = 2082)
    {
        return feof($this->stream) ? false : fread($this->stream, $bytes);
    }

    /**
     * Recupere el contenido del archivo de una sola vez.
     *
     * Una vez que recupere el contenido, no podrá utilizar MimeMailParser_attachment::read().
     *
     * @return string
     */
    public function getContent()
    {
        if ($this->content === null) {
            fseek($this->stream, 0);

            while (($buf = $this->read()) !== false) {
                $this->content .= $buf;
            }
        }

        return $this->content;
    }
}
