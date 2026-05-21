<?php

use Aether\Core\Acl;
use Aether\Core\Core;
use Aether\Core\Menu;
use Aether\Core\SystemConfig;
use Aether\Core\ViewRenderEventManager;
use Aether\Core\Vite;

if (! function_exists('core')) {
    /**
     * Ayudante central.
     */
    function core(): Core
    {
        return app('core');
    }
}

if (! function_exists('menu')) {
    /**
     * Ayudante de menú.
     */
    function menu(): Menu
    {
        return app('menu');
    }
}

if (! function_exists('acl')) {
    /**
     * Ayudante de Acl.
     */
    function acl(): Acl
    {
        return app('acl');
    }
}

if (! function_exists('system_config')) {
    /**
     * Ayudante de configuración del sistema.
     */
    function system_config(): SystemConfig
    {
        return app('system_config');
    }
}

if (! function_exists('view_render_event')) {
    /**
     * Ver ayudante de eventos de renderizado.
     */
    function view_render_event($eventName, $params = null)
    {
        app()->singleton(ViewRenderEventManager::class);

        $viewEventManager = app()->make(ViewRenderEventManager::class);

        $viewEventManager->handleRenderEvent($eventName, $params);

        return $viewEventManager->render();
    }
}

if (! function_exists('vite')) {
    /**
     * Ayudante de Vite.
     */
    function vite(): Vite
    {
        return app(Vite::class);
    }
}
