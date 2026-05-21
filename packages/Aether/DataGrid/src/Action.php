<?php

namespace Aether\DataGrid;

/**
 * Implementación inicial de la clase de acción. Estén atentos a más funciones próximamente.
 */
class Action
{
    /**
     * Cree una instancia de columna.
     */
    public function __construct(
        public string $index,
        public string $icon,
        public string $title,
        public string $method,
        public mixed $url,
    ) {}

    /**
     * Convertir a una matriz.
     */
    public function toArray()
    {
        return [
            'index' => $this->index,
            'icon' => $this->icon,
            'title' => $this->title,
            'method' => $this->method,
            'url' => $this->url,
        ];
    }
}
