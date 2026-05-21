<?php

namespace Aether\Core;

use Aether\Core\Exceptions\ViterNotFound;
use Illuminate\Support\Facades\Vite as BaseVite;

class Vite
{
    /**
     * Devuelve la URL del activo.
     *
     * @return string
     */
    public function asset(string $filename, string $namespace = 'admin')
    {
        $viters = config('aether-vite.viters');

        if (empty($viters[$namespace])) {
            throw new ViterNotFound($namespace);
        }

        $url = trim($filename, '/');

        $viteUrl = trim($viters[$namespace]['package_assets_directory'], '/').'/'.$url;

        return BaseVite::useHotFile($viters[$namespace]['hot_file'])
            ->useBuildDirectory($viters[$namespace]['build_directory'])
            ->asset($viteUrl);
    }

    /**
     * Establecer éter vite.
     *
     * @return mixed
     */
    public function set(mixed $entryPoints, string $namespace = 'admin')
    {
        $viters = config('aether-vite.viters');

        if (empty($viters[$namespace])) {
            throw new ViterNotFound($namespace);
        }

        return BaseVite::useHotFile($viters[$namespace]['hot_file'])
            ->useBuildDirectory($viters[$namespace]['build_directory'])
            ->withEntryPoints($entryPoints);
    }
}
