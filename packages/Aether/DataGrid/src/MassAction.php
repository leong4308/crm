<?php

namespace Aether\DataGrid;

/**
 * Implementación inicial de la clase de acción de masas. Estén atentos a más funciones próximamente.
 */
class MassAction
{
    /**
     * Cree una instancia de columna.
     */
    public function __construct(
        public string $icon,
        public string $title,
        public string $method,
        public mixed $url,
        public array $options = [],
    ) {}

    /**
     * Convertir a una matriz.
     */
    public function toArray()
    {
        return [
            'icon' => $this->icon,
            'title' => $this->title,
            'method' => $this->method,
            'url' => $this->url,
            'options' => $this->options,
        ];
    }
}
