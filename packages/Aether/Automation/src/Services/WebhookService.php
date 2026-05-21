<?php

namespace Aether\Automation\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Message;
use Aether\Contact\Repositories\PersonRepository;

class WebhookService
{
    /**
     * La instancia del cliente GuzzleHttp.
     */
    protected Client $client;

    /**
     * Cree una nueva instancia de servicio de webhook.
     */
    public function __construct(protected PersonRepository $personRepository)
    {
        $this->client = new Client([
            'timeout' => 30,
            'connect_timeout' => 10,
            'verify' => true,
            'http_errors' => false,
        ]);
    }

    /**
     * Activa el webhook.
     */
    public function triggerWebhook(mixed $data): array
    {
        if (
            ! isset($data['method'])
            || ! isset($data['end_point'])
        ) {
            return [
                'status' => 'error',
                'response' => 'Missing required fields: method or end_point',
            ];
        }

        $headers = isset($data['headers']) ? $this->parseJsonField($data['headers']) : [];
        $payload = isset($data['payload']) ? $data['payload'] : null;
        $data['end_point'] = $this->appendQueryParams($data['end_point'], $data['query_params'] ?? '');

        $formattedHeaders = $this->formatHeaders($headers);

        $options = $this->buildRequestOptions($data['method'], $formattedHeaders, $payload);

        try {
            $response = $this->client->request(
                strtoupper($data['method']),
                $data['end_point'],
                $options,
            );

            return [
                'status' => 'success',
                'response' => $response->getBody()->getContents(),
                'status_code' => $response->getStatusCode(),
                'headers' => $response->getHeaders(),
            ];
        } catch (RequestException $e) {
            return [
                'status' => 'error',
                'response' => $e->hasResponse() ? Message::toString($e->getResponse()) : $e->getMessage(),
                'status_code' => $e->hasResponse() ? $e->getResponse()->getStatusCode() : null,
            ];
        }
    }

    /**
     * Analiza el campo JSON de forma segura.
     */
    protected function parseJsonField(mixed $field): array
    {
        if (is_array($field)) {
            return $field;
        }

        if (is_string($field)) {
            $decoded = json_decode($field, true);

            if (
                json_last_error() === JSON_ERROR_NONE
                && is_array($decoded)
            ) {
                return $decoded;
            }
        }

        return [];
    }

    /**
     * Cree opciones de solicitud según el método y el tipo de contenido.
     */
    protected function buildRequestOptions(string $method, array $headers, mixed $payload): array
    {
        $options = [];

        if (! empty($headers)) {
            $options['headers'] = $headers;
        }

        if (
            $payload !== null
            && ! in_array(strtoupper($method), ['GET', 'HEAD'])
        ) {
            $contentType = $this->getContentType($headers);

            switch ($contentType) {
                case 'application/json':
                    $options['json'] = $this->prepareJsonPayload($payload);

                    break;

                case 'application/x-www-form-urlencoded':
                    $options['form_params'] = $this->prepareFormPayload($payload);

                    break;

                case 'multipart/form-data':
                    $options['multipart'] = $this->prepareMultipartPayload($payload);

                    break;

                case 'text/plain':
                case 'text/xml':
                case 'application/xml':
                    $options['body'] = $this->prepareRawPayload($payload);

                    break;

                default:
                    $options = array_merge($options, $this->autoDetectPayloadFormat($payload));

                    break;
            }
        }

        return $options;
    }

    /**
     * Prepare la carga útil JSON.
     */
    protected function prepareJsonPayload(mixed $payload): mixed
    {
        if (is_string($payload)) {
            $decoded = json_decode($payload, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }

            return $payload;
        }

        if (is_array($payload)) {
            return $this->formatPayload($payload);
        }

        return $payload;
    }

    /**
     * Preparar la carga útil del formulario.
     */
    protected function prepareFormPayload(mixed $payload): array
    {
        if (is_string($payload)) {
            $decoded = json_decode($payload, true);

            if (
                json_last_error() === JSON_ERROR_NONE
                && is_array($decoded)
            ) {
                return $this->formatPayload($decoded);
            }

            parse_str($payload, $parsed);

            return $parsed ?: [];
        }

        if (is_array($payload)) {
            return $this->formatPayload($payload);
        }

        return [];
    }

    /**
     * Prepare la carga útil de varias partes.
     */
    protected function prepareMultipartPayload(mixed $payload): array
    {
        $formattedPayload = $this->prepareFormPayload($payload);

        return $this->buildMultipartData($formattedPayload);
    }

    /**
     * Prepare la carga útil sin procesar.
     */
    protected function prepareRawPayload(mixed $payload): string
    {
        if (is_string($payload)) {
            return $payload;
        }

        if (is_array($payload)) {
            return json_encode($payload);
        }

        return (string) $payload;
    }

    /**
     * Formato de carga útil de detección automática cuando no se especifica ningún tipo de contenido.
     */
    protected function autoDetectPayloadFormat(mixed $payload): array
    {
        if (is_string($payload)) {
            $decoded = json_decode($payload, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                return ['json' => $decoded];
            }

            if (
                strpos($payload, '=') !== false
                && strpos($payload, '&') !== false
            ) {
                parse_str($payload, $parsed);

                return ['form_params' => $parsed];
            }

            return ['body' => $payload];
        }

        if (is_array($payload)) {
            $formatted = $this->formatPayload($payload);

            return ['json' => $formatted];
        }

        return ['body' => (string) $payload];
    }

    /**
     * Obtenga el tipo de contenido de los encabezados.
     */
    protected function getContentType(array $headers): string
    {
        foreach ($headers as $key => $value) {
            if (strtolower($key) === 'content-type') {
                $contentType = strtolower(trim(explode(';', $value)[0]));

                return $contentType;
            }
        }

        return '';
    }

    /**
     * Cree una matriz de datos de varias partes.
     */
    protected function buildMultipartData(array $payload): array
    {
        $multipart = [];

        foreach ($payload as $key => $value) {
            $multipart[] = [
                'name' => $key,
                'contents' => is_array($value) ? json_encode($value) : (string) $value,
            ];
        }

        return $multipart;
    }

    /**
     * Formatear la matriz de encabezados.
     */
    protected function formatHeaders(array $headers): array
    {
        if (empty($headers)) {
            return [];
        }

        $formattedHeaders = [];

        if ($this->isKeyValuePairArray($headers)) {
            foreach ($headers as $header) {
                if (
                    isset($header['key'])
                    && array_key_exists('value', $header)
                ) {
                    if (
                        isset($header['disabled'])
                        && $header['disabled']
                    ) {
                        continue;
                    }

                    if (
                        isset($header['enabled'])
                        && ! $header['enabled']
                    ) {
                        continue;
                    }

                    $formattedHeaders[$header['key']] = $header['value'];
                }
            }
        } else {
            $formattedHeaders = $headers;
        }

        return $formattedHeaders;
    }

    /**
     * Formatee cualquier carga útil entrante en una matriz asociativa limpia.
     */
    protected function formatPayload(mixed $payload): array
    {
        if (empty($payload)) {
            return [];
        }

        if (
            is_array($payload)
            && isset($payload['key'])
            && array_key_exists('value', $payload)
        ) {
            return [$payload['key'] => $payload['value']];
        }

        if (
            is_array($payload)
            && array_is_list($payload)
            && $this->isKeyValuePairArray($payload)
        ) {
            $formatted = [];

            foreach ($payload as $item) {
                if (
                    isset($item['key'])
                    && array_key_exists('value', $item)
                ) {
                    if (
                        isset($item['disabled'])
                        && $item['disabled']
                    ) {
                        continue;
                    }

                    if (
                        isset($item['enabled'])
                        && ! $item['enabled']
                    ) {
                        continue;
                    }

                    $formatted[$item['key']] = $item['value'];
                }
            }

            return $formatted;
        }

        return is_array($payload) ? $payload : [];
    }

    /**
     * Compruebe si la matriz es una matriz de par clave-valor.
     */
    protected function isKeyValuePairArray(array $array): bool
    {
        if (empty($array)) {
            return false;
        }

        if (
            isset($array['key'])
            && array_key_exists('value', $array)
        ) {
            return true;
        }

        if (array_is_list($array)) {
            return collect($array)->every(fn ($item) => is_array($item) && isset($item['key']) && array_key_exists('value', $item)
            );
        }

        return false;
    }

    /**
     * Agregue parámetros de consulta a la URL del punto final.
     */
    protected function appendQueryParams(string $endPoint, string $queryParamsJson): string
    {
        $queryParams = json_decode($queryParamsJson, true);

        if (
            json_last_error() !== JSON_ERROR_NONE
            || ! is_array($queryParams)
        ) {
            return $endPoint;
        }

        $queryArray = [];

        foreach ($queryParams as $param) {
            if (
                isset($param['key'])
                && array_key_exists('value', $param)
            ) {
                $queryArray[$param['key']] = $param['value'];
            }
        }

        $queryString = http_build_query($queryArray);

        $glue = str_contains($endPoint, '?') ? '&' : '?';

        return $endPoint.($queryString ? $glue.$queryString : '');
    }
}
