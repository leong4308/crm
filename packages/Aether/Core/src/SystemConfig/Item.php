<?php

namespace Aether\Core\SystemConfig;

use Illuminate\Support\Collection;

class Item
{
    /**
     * Cree una nueva instancia de artículo.
     */
    public function __construct(
        public Collection $children,
        public ?array $fields,
        public ?string $icon,
        public ?string $info,
        public string $key,
        public string $name,
        public ?string $route = null,
        public ?int $sort = null
    ) {}

    /**
     * Obtener el nombre del elemento de configuración.
     */
    public function getName(): string
    {
        return $this->name ?? '';
    }

    /**
     * Opciones de formato.
     */
    private function formatOptions($options)
    {
        return is_array($options) ? $options : (is_string($options) ? $options : []);
    }

    /**
     * Obtener campos del elemento de configuración.
     */
    public function getFields(): Collection
    {
        return collect($this->fields)->map(function ($field) {
            return new ItemField(
                item_key: $this->key,
                name: $field['name'],
                title: $field['title'],
                info: $field['info'] ?? null,
                type: $field['type'],
                depends: $field['depends'] ?? null,
                path: $field['path'] ?? null,
                validation: $field['validation'] ?? null,
                default: $field['default'] ?? null,
                channel_based: $field['channel_based'] ?? null,
                locale_based: $field['locale_based'] ?? null,
                options: $this->formatOptions($field['options'] ?? null),
                tinymce: $field['tinymce'] ?? false,
                is_visible: true,
            );
        });
    }

    /**
     * Obtener el nombre del elemento de configuración.
     */
    public function getInfo(): ?string
    {
        return $this->info;
    }

    /**
     * Obtener ruta actual.
     */
    public function getRoute(): string
    {
        return $this->route;
    }

    /**
     * Obtenga la URL del elemento de configuración.
     */
    public function getUrl(): string
    {
        return route($this->getRoute());
    }

    /**
     * Obtenga la clave del elemento de configuración.
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * Obtener icono.
     */
    public function getIcon(): ?string
    {
        return $this->icon;
    }

    /**
     * Verifique que el elemento de configuración del clima tenga hijos o no.
     */
    public function haveChildren(): bool
    {
        return $this->children->isNotEmpty();
    }

    /**
     * Obtenga hijos del elemento de configuración.
     */
    public function getChildren(): Collection
    {
        if (! $this->haveChildren()) {
            return collect();
        }

        return $this->children;
    }
}
