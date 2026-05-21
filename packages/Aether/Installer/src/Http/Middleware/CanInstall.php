<?php

namespace Aether\Installer\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Aether\Installer\Helpers\DatabaseManager;

class CanInstall
{
    /**
     * Maneja solicitudes si la aplicación ya está instalada y luego redirige al panel; de lo contrario, al instalador.
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $isInstallRoute = Str::contains($request->getPathInfo(), '/install');
        $isInstalled = $this->isAlreadyInstalled();

        if ($isInstallRoute && $isInstalled) {
            abort_if($request->ajax(), 403);

            return redirect()->route('admin.dashboard.index');
        }

        if (! $isInstallRoute && ! $isInstalled) {
            return redirect()->route('installer.index');
        }

        return $next($request);
    }

    /**
     * Compruebe si la aplicación ya está instalada.
     */
    public function isAlreadyInstalled(): bool
    {
        $installedPath = storage_path('installed');

        if (file_exists($installedPath)) {
            return true;
        }

        if (! app(DatabaseManager::class)->isInstalled()) {
            return false;
        }

        touch($installedPath);

        Event::dispatch('aether.installed');

        return true;
    }
}
