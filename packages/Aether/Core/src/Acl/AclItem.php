<?php

namespace Aether\Core\Acl;

use Illuminate\Support\Collection;

class AclItem
{
    /**
     * Cree una nueva instancia de AclItem.
     */
    public function __construct(
        public string $key,
        public string $name,
        public array|string $route,
        public int $sort,
        public Collection $children,
    ) {}
}
