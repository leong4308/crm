<?php

namespace Aether\Admin\Http\Middleware;

use Aether\Email\Enums\SupportedFolderEnum;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SanitizeUrl
{
    /**
     * Manejar una solicitud entrante.
     *
     * @param  Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($request->ajax()) {
            return $next($request);
        }

        $route = $request->route('route');

        $sanitizedRoute = Str::of($route)->ascii()->lower()->replaceMatches('/[^a-z0-9_-]/', '')->__toString();

        $request->route()->setParameter('route', $sanitizedRoute);

        /**
         * Incluir valores de ruta aceptables en la lista blanca para evitar entradas inesperadas
         */
        $allowedRoutes = [
            SupportedFolderEnum::INBOX->value,
            SupportedFolderEnum::IMPORTANT->value,
            SupportedFolderEnum::STARRED->value,
            SupportedFolderEnum::DRAFT->value,
            SupportedFolderEnum::OUTBOX->value,
            SupportedFolderEnum::SENT->value,
            SupportedFolderEnum::SPAM->value,
            SupportedFolderEnum::TRASH->value,
        ];

        if (! in_array($route, $allowedRoutes, true)) {
            abort(401, trans('admin::app.mail.invalid-route'));
        }

        return $next($request);
    }
}
