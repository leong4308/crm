<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Laravel CORS Options
    |--------------------------------------------------------------------------
    |
    | The allowed_methods and allowed_headers options are case-insensitive.
    |
    | You don't need to provide both allowed_origins and allowed_origins_patterns.
    | If one of the strings passed matches, it is considered a valid origin.
    |
    | If ['*'] is provided to allowed_methods, allowed_origins or allowed_headers
    | all methods / origins / headers are allowed.
    |
    */

    /*
     * Puede habilitar CORS para 1 o varias rutas.
     * Ejemplo: ['api/*']
     */
    'paths' => [
        'admin/web-forms/forms/*',
    ],

    /*
    * Coincide con el método de solicitud. `['*']` permite todos los métodos.
    */
    'allowed_methods' => ['*'],

    /*
     * Coincide con el origen de la solicitud. `['*']` permite todos los orígenes. Se pueden utilizar comodines, por ejemplo, `*.midominio.com`
     */
    'allowed_origins' => ['*'],

    /*
     * Patrones que se pueden usar con `preg_match` para hacer coincidir el origen.
     */
    'allowed_origins_patterns' => [],

    /*
     * Establece el encabezado de respuesta Access-Control-Allow-Headers. `['*']` permite todos los encabezados.
     */
    'allowed_headers' => ['*'],

    /*
     * Establece el encabezado de respuesta Access-Control-Expose-Headers con estos encabezados.
     */
    'exposed_headers' => [],

    /*
     * Establece el encabezado de respuesta Access-Control-Max-Age cuando > 0.
     */
    'max_age' => 0,

    /*
     * Establece el encabezado Access-Control-Allow-Credentials.
     */
    'supports_credentials' => false,
];
