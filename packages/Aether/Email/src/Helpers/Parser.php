<?php

namespace Aether\Email\Helpers;

use Aether\Email\Helpers\Contracts\CharsetManager;

class Parser
{
    /**
     * Recurso.
     */
    public $resource;

    /**
     * Un puntero de archivo al correo electrónico.
     */
    public $stream;

    /**
     * Datos.
     */
    public $data;

    /**
     * Recipiente.
     */
    public $container;

    /**
     * Entidad.
     */
    public $entity;

    /**
     * Archivos.
     */
    public $files;

    /**
     * Partes de un correo electrónico.
     */
    public $parts;

    /**
     * Objeto del administrador de conjuntos de caracteres.
     */
    public $charset;

    /**
     * Crea una nueva instancia.
     *
     * @return void
     */
    public function __construct(?CharsetManager $charset = null)
    {
        if (is_null($charset)) {
            $charset = new Charset;
        }

        $this->charset = $charset;
    }

    /**
     * Libere los recursos retenidos.
     *
     * @return void
     */
    public function __destruct()
    {
        // borrar el recurso del archivo de correo electrónico
        if (is_resource($this->stream)) {
            fclose($this->stream);
        }

        // borrar el recurso de análisis de correo
        if (is_resource($this->resource)) {
            mailparse_msg_free($this->resource);
        }
    }

    /**
     * Establezca la ruta del archivo que utilizamos para obtener el texto del correo electrónico.
     *
     * @param  string  $path
     * @return object
     */
    public function setPath($path)
    {
        $this->resource = mailparse_msg_parse_file($path);

        $this->stream = fopen($path, 'r');

        $this->parse();

        return $this;
    }

    /**
     * Configure el recurso de transmisión que utilizamos para recibir el texto del correo electrónico.
     *
     * @return object
     */
    public function setStream($stream)
    {
        // las transmisiones deben almacenarse en caché para archivarse primero
        $meta = @stream_get_meta_data($stream);

        if (
            ! $meta
            || ! $meta['mode']
            || $meta['mode'][0] != 'r'
            || $meta['eof']
        ) {
            throw new \Exception(
                'setStream() expects parameter stream to be readable stream resource.'
            );
        }

        $tmp_fp = tmpfile();

        if ($tmp_fp) {
            while (! feof($stream)) {
                fwrite($tmp_fp, fread($stream, 2028));
            }

            fseek($tmp_fp, 0);

            $this->stream = &$tmp_fp;
        } else {
            throw new \Exception(
                'Could not create temporary files for attachments. Your tmp directory may be un-writable by PHP.'
            );
        }

        fclose($stream);

        $this->resource = mailparse_msg_create();

        // analiza el mensaje de forma incremental (bajo uso de memoria pero más lento)
        while (! feof($this->stream)) {
            mailparse_msg_parse($this->resource, fread($this->stream, 2082));
        }

        $this->parse();

        return $this;
    }

    /**
     * Configure el texto del correo electrónico.
     *
     * @return object
     */
    public function setText($data)
    {
        $this->resource = \mailparse_msg_create();

        // no analiza incrementalmente, el acaparamiento rápido de memoria podría explotar
        mailparse_msg_parse($this->resource, $data);

        $this->data = $data;

        $this->parse();

        return $this;
    }

    /**
     * Analiza el mensaje en partes.
     *
     * @return void
     */
    private function parse()
    {
        $structure = mailparse_msg_get_structure($this->resource);

        $headerType = (stripos($this->data, 'Content-Language:') !== false) ? 'Content-Language:' : 'Content-Type:';

        if (count($structure) == 1) {
            $tempParts = explode(PHP_EOL, $this->data);

            foreach ($tempParts as $key => $part) {
                if (stripos($part, $headerType) !== false) {
                    break;
                }

                if (trim($part) == '') {
                    unset($tempParts[$key]);
                }
            }

            $data = implode(PHP_EOL, $tempParts);

            $this->resource = \mailparse_msg_create();

            mailparse_msg_parse($this->resource, $data);

            $this->data = $data;

            $structure = mailparse_msg_get_structure($this->resource);
        }

        $this->parts = [];

        foreach ($structure as $part_id) {
            $part = mailparse_msg_get_part($this->resource, $part_id);

            $this->parts[$part_id] = mailparse_msg_get_part_data($part);
        }
    }

    /**
     * Analizar el nombre del remitente.
     *
     * @return string
     */
    public function parseSenderName()
    {
        if (! $fromNameParts = mailparse_rfc822_parse_addresses($this->getHeader('from'))) {
            $fromNameParts = mailparse_rfc822_parse_addresses($this->getHeader('sender'));
        }

        return $fromNameParts[0]['display'] == $fromNameParts[0]['address']
            ? current(explode('@', $fromNameParts[0]['display']))
            : $fromNameParts[0]['display'];
    }

    /**
     * Analizar la dirección de correo electrónico.
     *
     * @param  string  $type
     * @return array
     */
    public function parseEmailAddress($type)
    {
        $emails = [];

        $addresses = mailparse_rfc822_parse_addresses($this->getHeader($type));

        if (count($addresses) > 1) {
            foreach ($addresses as $address) {
                if (filter_var($address['address'], FILTER_VALIDATE_EMAIL)) {
                    $emails[] = $address['address'];
                }
            }
        } elseif ($addresses) {
            $emails[] = $addresses[0]['address'];
        }

        return $emails;
    }

    /**
     * Recupere un encabezado de correo electrónico específico, sin conversión de caracteres.
     *
     * @return string
     */
    public function getRawHeader($name)
    {
        if (isset($this->parts[1])) {
            $headers = $this->getPart('headers', $this->parts[1]);

            return (isset($headers[$name])) ? $headers[$name] : false;
        } else {
            throw new \Exception(
                'setPath() or setText() or setStream() must be called before retrieving email headers.'
            );
        }
    }

    /**
     * Recuperar un encabezado de correo electrónico específico.
     *
     * @return string
     */
    public function getHeader($name)
    {
        $rawHeader = $this->getRawHeader($name);

        if ($rawHeader === false) {
            return false;
        }

        return $this->decodeHeader($rawHeader);
    }

    /**
     * Recupera todos los encabezados de correo.
     *
     * @return array
     */
    public function getHeaders()
    {
        if (isset($this->parts[1])) {
            $headers = $this->getPart('headers', $this->parts[1]);

            foreach ($headers as $name => &$value) {
                if (is_array($value)) {
                    foreach ($value as &$v) {
                        $v = $this->decodeSingleHeader($v);
                    }
                } else {
                    $value = $this->decodeSingleHeader($value);
                }
            }

            return $headers;
        } else {
            throw new \Exception(
                'setPath() or setText() or setStream() must be called before retrieving email headers.'
            );
        }
    }

    /**
     * Obtener del nombre.
     *
     * @return string
     */
    public function getFromName()
    {
        $headers = $this->getHeaders();

        return $headers['from'];
    }

    /**
     * Extraiga texto MIME de varias partes.
     *
     * @return string
     */
    public function extractMultipartMIMEText($part, $source, $encodingType)
    {
        $boundary = trim($part['content-boundary']);
        $boundary = substr($boundary, strpos($boundary, '----=') + strlen('----='));

        preg_match_all('/------=(3D_.*)\sContent-Type:\s(.*)\s*boundary=3D"----=(3D_.*)"/', $source, $matches);

        $delimeter = array_shift($matches);
        $content_delimeter = end($delimeter);

        [$relations, $content_types, $boundaries] = $matches;

        $messageToProcess = substr($source, stripos($source, (string) $content_delimeter) + strlen($content_delimeter));

        array_unshift($boundaries, $boundary);

        // extraer el texto
        foreach (array_reverse($boundaries) as $index => $boundary) {
            $processedEmailSegments = [];
            $emailSegments = explode('------='.$boundary, $messageToProcess);

            // Retire las piezas vacías
            foreach ($emailSegments as $emailSegment) {
                if (! empty(trim($emailSegment))) {
                    $processedEmailSegments[] = trim($emailSegment);
                }
            }

            // Quitar partes no relacionadas
            array_pop($processedEmailSegments);

            for ($i = 0; $i < $index; $i++) {
                if (count($processedEmailSegments) > 1) {
                    array_shift($processedEmailSegments);
                }
            }

            // Analizar cada parte para texto|contenido html
            foreach ($processedEmailSegments as $emailSegment) {
                $emailSegment = quoted_printable_decode(quoted_printable_decode($emailSegment));

                if (stripos($emailSegment, 'content-type: text/plain;') !== false
                    || stripos($emailSegment, 'content-type: text/html;') !== false
                ) {
                    $search = 'content-transfer-encoding: '.$encodingType;

                    return substr($emailSegment, stripos($emailSegment, $search) + strlen($search));
                }
            }
        }

        return '';
    }

    /**
     * Devuelve el cuerpo del mensaje de correo electrónico en el formato especificado.
     *
     * @return mixed
     */
    public function getMessageBody($type = 'text')
    {
        $textBody = $htmlBody = $body = false;

        $mime_types = [
            'text' => 'text/plain',
            'text' => 'text/plain; (error)',
            'html' => 'text/html',
        ];

        if (in_array($type, array_keys($mime_types))) {
            foreach ($this->parts as $key => $part) {
                if (in_array($this->getPart('content-type', $part), $mime_types)
                    && $this->getPart('content-disposition', $part) != 'attachment'
                ) {
                    $headers = $this->getPart('headers', $part);
                    $encodingType = array_key_exists('content-transfer-encoding', $headers) ? $headers['content-transfer-encoding'] : '';

                    if ($this->getPart('content-type', $part) == 'text/plain') {
                        $textBody .= $this->decodeContentTransfer($this->getPartBody($part), $encodingType);
                        $textBody = nl2br($this->charset->decodeCharset($textBody, $this->getPartCharset($part)));
                    } elseif ($this->getPart('content-type', $part) == 'text/plain; (error)') {
                        if (empty($part['headers']) || ! isset($part['headers']['from'])) {
                            $parentKey = explode('.', $key)[0];
                            if (isset($this->parts[$parentKey]) && isset($this->parts[$parentKey]['headers']['from'])) {
                                $part_from_sender = is_array($this->parts[$parentKey]['headers']['from'])
                                    ? $this->parts[$parentKey]['headers']['from'][0]
                                    : $this->parts[$parentKey]['headers']['from'];
                            } else {
                                continue;
                            }
                        } else {
                            $part_from_sender = is_array($part['headers']['from'])
                                ? $part['headers']['from'][0]
                                : $part['headers']['from'];
                        }
                        $mail_part_addresses = mailparse_rfc822_parse_addresses($part_from_sender);

                        if (! empty($mail_part_addresses[0]['address'])
                            && strrpos($mail_part_addresses[0]['address'], 'pcsms.com') !== false
                        ) {
                            $last_header = end($headers);
                            $partMessage = substr($this->data, strrpos($this->data, $last_header) + strlen($last_header), $part['ending-pos-body']);
                            $textBody .= $this->decodeContentTransfer($partMessage, $encodingType);
                            $textBody = nl2br($this->charset->decodeCharset($textBody, $this->getPartCharset($part)));
                        }
                    } elseif ($this->getPart('content-type', $part) == 'multipart/mixed'
                        || $this->getPart('content-type', $part) == 'multipart/related'
                    ) {
                        $emailContent = $this->extractMultipartMIMEText($part, $this->data, $encodingType);

                        $textBody .= $this->decodeContentTransfer($emailContent, $encodingType);
                        $textBody = nl2br($this->charset->decodeCharset($textBody, $this->getPartCharset($part)));
                    } else {
                        $htmlBody .= $this->decodeContentTransfer($this->getPartBody($part), $encodingType);
                        $htmlBody = $this->charset->decodeCharset($htmlBody, $this->getPartCharset($part));
                    }
                }
            }

            $body = $htmlBody ?: $textBody;

            if (is_array($this->files)) {
                foreach ($this->files as $file) {
                    if ($file['contentId']) {
                        $body = str_replace('cid:'.preg_replace('/[<>]/', '', $file['contentId']), $file['path'], $body);
                        $path = $file['path'];
                    }
                }
            }
        } else {
            throw new \Exception('Invalid type specified for getMessageBody(). "type" can either be text or html.');
        }

        return $body;
    }

    /**
     * Obtener el cuerpo del mensaje de texto.
     *
     * @return string
     */
    public function getTextMessageBody()
    {
        $textBody = null;

        foreach ($this->parts as $key => $part) {
            if ($this->getPart('content-disposition', $part) != 'attachment') {
                $headers = $this->getPart('headers', $part);
                $encodingType = array_key_exists('content-transfer-encoding', $headers) ? $headers['content-transfer-encoding'] : '';

                if ($this->getPart('content-type', $part) == 'text/plain') {
                    $textBody .= $this->decodeContentTransfer($this->getPartBody($part), $encodingType);
                    $textBody = nl2br($this->charset->decodeCharset($textBody, $this->getPartCharset($part)));
                } elseif ($this->getPart('content-type', $part) == 'text/plain; (error)') {
                    $part_from_sender = is_array($part['headers']['from']) ? $part['headers']['from'][0] : $part['headers']['from'];
                    $mail_part_addresses = mailparse_rfc822_parse_addresses($part_from_sender);

                    if (! empty($mail_part_addresses[0]['address'])
                        && strrpos($mail_part_addresses[0]['address'], 'pcsms.com') !== false
                    ) {
                        $last_header = end($headers);
                        $partMessage = substr($this->data, strrpos($this->data, $last_header) + strlen($last_header), $part['ending-pos-body']);
                        $textBody .= $this->decodeContentTransfer($partMessage, $encodingType);
                        $textBody = nl2br($this->charset->decodeCharset($textBody, $this->getPartCharset($part)));
                    }
                } elseif ($this->getPart('content-type', $part) == 'multipart/mixed'
                    || $this->getPart('content-type', $part) == 'multipart/related'
                ) {
                    $emailContent = $this->extractMultipartMIMEText($part, $this->data, $encodingType);

                    $textBody .= $this->decodeContentTransfer($emailContent, $encodingType);
                    $textBody = nl2br($this->charset->decodeCharset($textBody, $this->getPartCharset($part)));
                }
            }
        }

        return $textBody;
    }

    /**
     * Devuelve el contenido de los archivos adjuntos en orden de aparición.
     *
     * @return array
     */
    public function getAttachments()
    {
        $attachments = [];
        $dispositions = ['attachment', 'inline'];
        $non_attachment_types = ['text/plain', 'text/html', 'text/plain; (error)'];
        $nonameIter = 0;

        foreach ($this->parts as $part) {
            $disposition = $this->getPart('content-disposition', $part);
            $filename = 'noname';

            if (isset($part['disposition-filename'])) {
                $filename = $this->decodeHeader($part['disposition-filename']);
            } elseif (isset($part['content-name'])) {
                // si no tenemos una disposición pero tenemos un nombre de contenido, es un archivo adjunto válido.
                // simulamos la presencia de una disposición de archivo adjunto con un nombre de archivo de disposición
                $filename = $this->decodeHeader($part['content-name']);
                $disposition = 'attachment';
            } elseif (! in_array($part['content-type'], $non_attachment_types, true)
                && substr($part['content-type'], 0, 10) !== 'multipart/'
            ) {
                // Si no podemos obtenerlo mediante getMessageBody(), asumimos que es un archivo adjunto.
                $disposition = 'attachment';
            }

            if (in_array($disposition, $dispositions) === true && isset($filename) === true) {
                if ($filename == 'noname') {
                    $nonameIter++;
                    $filename = 'noname'.$nonameIter;
                }

                $headersAttachments = $this->getPart('headers', $part);
                $contentidAttachments = $this->getPart('content-id', $part);

                if (! $contentidAttachments
                    && $disposition == 'inline'
                    && ! strpos($this->getPart('content-type', $part), 'image/')
                    && ! stripos($filename, 'noname') == false
                ) {
                    // saltar
                } else {
                    $attachments[] = new Attachment(
                        $filename,
                        $this->getPart('content-type', $part),
                        $this->getAttachmentStream($part),
                        $disposition,
                        $contentidAttachments,
                        $headersAttachments
                    );
                }
            }
        }

        return ! empty($attachments) ? $attachments : $this->extractMultipartMIMEAttachments();
    }

    /**
     * Extraiga archivos adjuntos de MIME multiparte.
     *
     * @return array
     */
    public function extractMultipartMIMEAttachments()
    {
        $attachmentCollection = $processedAttachmentCollection = [];

        foreach ($this->parts as $part) {
            $boundary = isset($part['content-boundary']) ? trim($part['content-boundary']) : '';
            $boundary = substr($boundary, strpos($boundary, '----=') + strlen('----='));

            preg_match_all('/------=(3D_.*)\sContent-Type:\s(.*)\s*boundary=3D"----=(3D_.*)"/', $this->data, $matches);

            $delimeter = array_shift($matches);
            $content_delimeter = end($delimeter);

            [$relations, $content_types, $boundaries] = $matches;
            $messageToProcess = substr($this->data, stripos($this->data, (string) $content_delimeter) + strlen($content_delimeter));

            array_unshift($boundaries, $boundary);

            // extraer el texto
            foreach (array_reverse($boundaries) as $index => $boundary) {
                $processedEmailSegments = [];
                $emailSegments = explode('------='.$boundary, $messageToProcess);

                // Retire las piezas vacías
                foreach ($emailSegments as $emailSegment) {
                    if (! empty(trim($emailSegment))) {
                        $processedEmailSegments[] = trim($emailSegment);
                    }
                }

                // Quitar partes no relacionadas
                array_pop($processedEmailSegments);

                for ($i = 0; $i < $index; $i++) {
                    if (count($processedEmailSegments) > 1) {
                        array_shift($processedEmailSegments);
                    }
                }

                // Analizar cada parte para texto|contenido html
                foreach ($processedEmailSegments as $emailSegment) {
                    $emailSegment = quoted_printable_decode(quoted_printable_decode($emailSegment));

                    if (stripos($emailSegment, 'content-type: text/plain;') === false
                        && stripos($emailSegment, 'content-type: text/html;') === false
                    ) {
                        $attachmentParts = explode("\n\n", $emailSegment);

                        if (! empty($attachmentParts) && count($attachmentParts) == 2) {
                            $attachmentDetails = explode("\n", $attachmentParts[0]);

                            $attachmentDetails = array_map(function ($item) {
                                return trim($item);
                            }, $attachmentDetails);

                            $attachmentData = trim($attachmentParts[1]);

                            $attachmentCollection[] = [
                                'details' => $attachmentDetails,
                                'data' => $attachmentData,
                            ];
                        }
                    }
                }
            }
        }

        foreach ($attachmentCollection as $attachmentDetails) {
            $stream = '';

            $resourceDetails = [
                'name' => '',
                'fileName' => '',
                'contentType' => '',
                'encodingType' => 'base64',
                'contentDisposition' => 'inline',
                'contentId' => '',
            ];

            foreach ($attachmentDetails['details'] as $attachmentDetail) {
                if (stripos($attachmentDetail, 'Content-Type: ') === 0) {
                    $resourceDetails['contentType'] = substr($attachmentDetail, strlen('Content-Type: '));
                } elseif (stripos($attachmentDetail, 'name="') === 0) {
                    $resourceDetails['name'] = substr($attachmentDetail, strlen('name="'), -1);
                } elseif (stripos($attachmentDetail, 'Content-Transfer-Encoding: ') === 0) {
                    $resourceDetails['encodingType'] = substr($attachmentDetail, strlen('Content-Transfer-Encoding: '));
                } elseif (stripos($attachmentDetail, 'Content-ID: ') === 0) {
                    $resourceDetails['contentId'] = substr($attachmentDetail, strlen('Content-ID: '));
                } elseif (stripos($attachmentDetail, 'filename="') === 0) {
                    $resourceDetails['fileName'] = substr($attachmentDetail, strlen('filename="'), -1);
                } elseif (stripos($attachmentDetail, 'Content-Disposition: ') === 0) {
                    $resourceDetails['contentDisposition'] = substr($attachmentDetail, strlen('Content-Disposition: '), -1);
                }
            }

            $resourceDetails['name'] = empty($resourceDetails['name']) ? $resourceDetails['fileName'] : $resourceDetails['name'];
            $resourceDetails['fileName'] = empty($resourceDetails['fileName']) ? $resourceDetails['name'] : $resourceDetails['fileName'];

            $temp_fp = tmpfile();

            fwrite($temp_fp, base64_decode($attachmentDetails['data']), strlen($attachmentDetails['data']));
            fseek($temp_fp, 0, SEEK_SET);

            $processedAttachmentCollection[] = new Attachment(
                $resourceDetails['fileName'],
                $resourceDetails['contentType'],
                $temp_fp,
                $resourceDetails['contentDisposition'],
                $resourceDetails['contentId'], []
            );
        }

        return $processedAttachmentCollection;
    }

    /**
     * Lea el cuerpo del archivo adjunto y guarde el recurso de archivo temporal.
     *
     * @return string
     */
    private function getAttachmentStream(&$part)
    {
        $temp_fp = tmpfile();

        $headers = $this->getPart('headers', $part);
        $encodingType = array_key_exists('content-transfer-encoding', $headers)
            ? $headers['content-transfer-encoding']
            : '';

        if ($temp_fp) {
            if ($this->stream) {
                $start = $part['starting-pos-body'];
                $end = $part['ending-pos-body'];

                fseek($this->stream, $start, SEEK_SET);

                $len = $end - $start;
                $written = 0;

                while ($written < $len) {
                    $write = $len;
                    $part = fread($this->stream, $write);

                    fwrite($temp_fp, $this->decodeContentTransfer($part, $encodingType));

                    $written += $write;
                }
            } elseif ($this->data) {
                $attachment = $this->decodeContentTransfer($this->getPartBodyFromText($part), $encodingType);

                fwrite($temp_fp, $attachment, strlen($attachment));
            }

            fseek($temp_fp, 0, SEEK_SET);
        } else {
            throw new \Exception(
                'Could not create temporary files for attachments. Your tmp directory may be unwritable by PHP.'
            );
        }

        return $temp_fp;
    }

    /**
     * Decodifica la cadena de Content-Transfer-Encoding.
     *
     * @return string
     */
    private function decodeContentTransfer($encodedString, $encodingType)
    {
        $encodingType = strtolower($encodingType);

        if ($encodingType == 'base64') {
            return base64_decode($encodedString);
        } elseif ($encodingType == 'quoted-printable') {
            return quoted_printable_decode($encodedString);
        } else {
            return $encodedString; // 8bit, 7bit, binary
        }
    }

    /**
     * Encabezado de decodificación.
     *
     * @param  string|array  $input
     * @return string
     */
    private function decodeHeader($input)
    {
        // A veces tenemos 2 etiquetas De, por lo que solo tomamos la primera.
        if (is_array($input)) {
            return $this->decodeSingleHeader($input[0]);
        }

        return $this->decodeSingleHeader($input);
    }

    /**
     * Decodifica un solo encabezado (= cadena).
     *
     * @param  string
     * @return string
     */
    private function decodeSingleHeader($input)
    {
        // Eliminar espacios en blanco entre palabras codificadas
        $input = preg_replace('/(=\?[^?]+\?(q|b)\?[^?]*\?=)(\s)+=\?/i', '\1=?', $input);

        // Para cada palabra codificada...
        while (preg_match('/(=\?([^?]+)\?(q|b)\?([^?]*)\?=)/i', $input, $matches)) {
            $encoded = $matches[1];
            $charset = $matches[2];
            $encoding = $matches[3];
            $text = $matches[4];

            switch (strtolower($encoding)) {
                case 'b':
                    $text = $this->decodeContentTransfer($text, 'base64');
                    break;

                case 'q':
                    $text = str_replace('_', ' ', $text);

                    preg_match_all('/=([a-f0-9]{2})/i', $text, $matches);

                    foreach ($matches[1] as $value) {
                        $text = str_replace('='.$value, chr(hexdec($value)), $text);
                    }

                    break;
            }

            $text = $this->charset->decodeCharset($text, $this->charset->getCharsetAlias($charset));

            $input = str_replace($encoded, $text, $input);
        }

        return $input;
    }

    /**
     * Devuelve el juego de caracteres de la parte MIME.
     *
     * @return string|false
     */
    private function getPartCharset($part)
    {
        if (isset($part['charset'])) {
            return $charset = $this->charset->getCharsetAlias($part['charset']);
        } else {
            return false;
        }
    }

    /**
     * Recupera una parte MIME especificada.
     *
     * @return string|array
     */
    private function getPart($type, $parts)
    {
        return (isset($parts[$type])) ? $parts[$type] : false;
    }

    /**
     * Recuperar el cuerpo de una parte MIME.
     *
     * @return string
     */
    private function getPartBody(&$part)
    {
        $body = '';

        if ($this->stream) {
            $body = $this->getPartBodyFromFile($part);
        } elseif ($this->data) {
            $body = $this->getPartBodyFromText($part);
        }

        return $body;
    }

    /**
     * Recuperar el cuerpo de una parte MIME del archivo.
     *
     * @return string
     */
    private function getPartBodyFromFile(&$part)
    {
        $start = $part['starting-pos-body'];

        $end = $part['ending-pos-body'];

        fseek($this->stream, $start, SEEK_SET);

        return fread($this->stream, $end - $start);
    }

    /**
     * Recupera el cuerpo de una parte MIME del texto.
     *
     * @return string
     */
    private function getPartBodyFromText(&$part)
    {
        $start = $part['starting-pos-body'];

        $end = $part['ending-pos-body'];

        return substr($this->data, $start, $end - $start);
    }
}
