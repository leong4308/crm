<?php

namespace Aether\Core;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Aether\Core\Acl\AclItem;

class Acl
{
    /**
     * artículos de acl.
     */
    protected array $items = [];

    /**
     * Agregue un nuevo elemento de ACL.
     */
    public function addItem(AclItem $aclItem): void
    {
        $this->items[] = $aclItem;
    }

    /**
     * Obtenga todos los artículos de ACL.
     */
    public function getItems(): Collection
    {
        if (! $this->items) {
            $this->prepareAclItems();
        }

        return collect($this->items)
            ->sortBy('sort')
            ->values();
    }

    /**
     * Configuración de Acl.
     */
    private function getAclConfig(): array
    {
        static $aclConfig;

        if ($aclConfig) {
            return $aclConfig;
        }

        $aclConfig = config('acl');

        return $aclConfig;
    }

    /**
     * Consigue todos los roles.
     */
    public function getRoles(): Collection
    {
        static $roles;

        if ($roles) {
            return $roles;
        }

        $roles = collect($this->getAclConfig())
            ->mapWithKeys(function ($role) {
                if (is_array($role['route'])) {
                    return collect($role['route'])->mapWithKeys(function ($route) use ($role) {
                        return [$route => $role['key']];
                    });
                } else {
                    return [$role['route'] => $role['key']];
                }
            });

        return $roles;
    }

    /**
     * Preparar elementos ACL.
     */
    private function prepareAclItems(): void
    {
        $aclWithDotNotation = [];

        foreach ($this->getAclConfig() as $item) {
            $aclWithDotNotation[$item['key']] = $item;
        }

        $acl = Arr::undot(Arr::dot($aclWithDotNotation));

        foreach ($acl as $aclItemKey => $aclItem) {
            $subAclItems = $this->processSubAclItems($aclItem);

            $this->addItem(new AclItem(
                key: $aclItemKey,
                name: trans($aclItem['name']),
                route: $aclItem['route'],
                sort: $aclItem['sort'],
                children: $subAclItems,
            ));
        }
    }

    /**
     * Procesar elementos sub acl.
     */
    private function processSubAclItems($aclItem): Collection
    {
        return collect($aclItem)
            ->sortBy('sort')
            ->filter(fn ($value, $key) => is_array($value) && $key !== 'route')
            ->map(function ($subAclItem) {
                $subSubAclItems = $this->processSubAclItems($subAclItem);

                return new AclItem(
                    key: $subAclItem['key'],
                    name: trans($subAclItem['name']),
                    route: $subAclItem['route'],
                    sort: $subAclItem['sort'],
                    children: $subSubAclItems,
                );
            });
    }
}
