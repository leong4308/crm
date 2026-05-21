<?php

namespace Aether\Installer\Http\Controllers;

use Illuminate\Http\Response as IlluminateResponse;
use Illuminate\Support\Facades\Cache;
use Intervention\Image\Image;

class ImageCacheController
{
    /**
     * Plantilla de caché
     *
     * @var string
     */
    protected $template;

    /**
     * Logo
     *
     * @var string
     */
    const KRAYIN_LOGO = 'https://updates.aethercrm.com/aether.png';

    /**
     * Obtener respuesta HTTP del archivo de imagen aplicado de plantilla
     *
     * @param  string  $filename
     * @return Illuminate\Http\Response
     */
    public function getImage($filename)
    {
        try {
            $content = Cache::remember('aether-logo', 10080, function () {
                return $this->getImageFromUrl(self::KRAYIN_LOGO);
            });
        } catch (\Exception $e) {
            $content = '';
        }

        return $this->buildResponse($content);
    }

    /**
     * Iniciar desde la URL dada
     *
     * @param  string  $url
     * @return Image
     */
    public function getImageFromUrl($url)
    {
        $domain = config('app.url');

        $options = [
            'http' => [
                'method' => 'GET',
                'protocol_version' => 1.1, // force use HTTP 1.1 for service mesh environment with envoy
                'header' => "Accept-language: en\r\n".
                "Domain: $domain\r\n".
                "User-Agent: Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/97.0.4692.71 Safari/537.36\r\n",
            ],
        ];

        $context = stream_context_create($options);

        if ($data = @file_get_contents($url, false, $context)) {
            return $data;
        }

        throw new \Exception(
            'Unable to init from given url ('.$url.').'
        );
    }

    /**
     * Crea una respuesta HTTP a partir de datos de imagen dados
     *
     * @param  string  $content
     * @return Illuminate\Http\Response
     */
    protected function buildResponse($content)
    {
        /**
         * Definir tipo de mimo
         */
        $mime = finfo_buffer(finfo_open(FILEINFO_MIME_TYPE), $content);

        /**
         * Responda con 304 no modificado si el navegador tiene la imagen en caché
         */
        $eTag = md5($content);

        $notModified = isset($_SERVER['HTTP_IF_NONE_MATCH']) && $_SERVER['HTTP_IF_NONE_MATCH'] == $eTag;

        $content = $notModified ? null : $content;

        $statusCode = $notModified ? 304 : 200;

        /**
         * Devolver respuesta http
         */
        return new IlluminateResponse($content, $statusCode, [
            'Content-Type' => $mime,
            'Cache-Control' => 'max-age=10080, public',
            'Content-Length' => strlen($content),
            'Etag' => $eTag,
        ]);
    }
}
