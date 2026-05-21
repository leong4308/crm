<?php

namespace Aether\Core\Menu;

use Illuminate\Support\Collection;

class MenuItem
{
    /**
     * Cree una nueva instancia de MenuItem.
     *
     * @return void
     */
    public function __construct(
        private string $key,
        private string $name,
        private string $route,
        private string $url,
        private int $sort,
        private string $icon,
        private string $info,
        private Collection $children,
    ) {}

    /**
     * Establecer el nombre del elemento del menú.
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Obtener el nombre del elemento del menú.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Establecer la posición del elemento del menú.
     */
    public function setPosition(int $sort): self
    {
        $this->sort = $sort;

        return $this;
    }

    /**
     * Obtener la posición del elemento del menú.
     */
    public function getPosition()
    {
        return $this->sort;
    }

    /**
     * Establecer icono del elemento del menú.
     */
    public function setIcon(string $icon): self
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * Obtenga el ícono del elemento del menú.
     */
    public function getIcon(): string
    {
        return $this->icon;
    }

    /**
     * Establecer información del elemento del menú.
     */
    public function setInfo(string $info): self
    {
        $this->info = $info;

        return $this;
    }

    /**
     * Obtener información del elemento del menú.
     */
    public function getInfo(): string
    {
        return $this->info;
    }

    /**
     * Establecer la ruta del elemento del menú.
     */
    public function setRoute(string $route): self
    {
        $this->route = $route;

        return $this;
    }

    /**
     * Obtener ruta actual.
     */
    public function getRoute(): string
    {
        return $this->route;
    }

    /**
     * Establecer la URL del elemento del menú.
     */
    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Obtenga la URL del elemento del menú.
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * Establezca la clave del elemento del menú.
     */
    public function setKey(string $key): self
    {
        $this->key = $key;

        return $this;
    }

    /**
     * Obtenga la clave del elemento del menú.
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * Establecer hijos del elemento del menú.
     */
    public function setChildren(Collection $children): self
    {
        $this->children = $children;

        return $this;
    }

    /**
     * Verifique que el elemento del menú del clima tenga niños o no.
     */
    public function haveChildren(): bool
    {
        return $this->children->isNotEmpty();
    }

    /**
     * Obtenga hijos del elemento del menú.
     */
    public function getChildren(): Collection
    {
        if (! $this->haveChildren()) {
            return collect();
        }

        return $this->children;
    }

    /**
     * Verifique que el elemento del menú meteorológico esté activo o no.
     */
    public function isActive(): bool
    {
        if (request()->fullUrlIs($this->getUrl().'*')) {
            return true;
        }

        if ($this->haveChildren()) {
            foreach ($this->getChildren() as $child) {
                if ($child->isActive()) {
                    return true;
                }
            }
        }

        return false;
    }
}
