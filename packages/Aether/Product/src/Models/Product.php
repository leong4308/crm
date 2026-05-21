<?php

namespace Aether\Product\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Aether\Activity\Models\ActivityProxy;
use Aether\Activity\Traits\LogsActivity;
use Aether\Attribute\Traits\CustomAttribute;
use Aether\Product\Contracts\Product as ProductContract;
use Aether\Tag\Models\TagProxy;
use Aether\Warehouse\Models\LocationProxy;
use Aether\Warehouse\Models\WarehouseProxy;

class Product extends Model implements ProductContract
{
    use CustomAttribute, LogsActivity;

    /**
     * Los atributos que se pueden asignar en masa.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'sku',
        'description',
        'quantity',
        'price',
    ];

    /**
     * Obtenga los almacenes de productos propietarios del producto.
     */
    public function warehouses(): BelongsToMany
    {
        return $this->belongsToMany(WarehouseProxy::modelClass(), 'product_inventories');
    }

    /**
     * Obtenga las ubicaciones del producto al que pertenece el producto.
     */
    public function locations(): BelongsToMany
    {
        return $this->belongsToMany(LocationProxy::modelClass(), 'product_inventories', 'product_id', 'warehouse_location_id');
    }

    /**
     * Obtenga los inventarios de productos que posee el producto.
     */
    public function inventories(): HasMany
    {
        return $this->hasMany(ProductInventoryProxy::modelClass());
    }

    /**
     * Las etiquetas que pertenecen a los Productos.
     */
    public function tags()
    {
        return $this->belongsToMany(TagProxy::modelClass(), 'product_tags');
    }

    /**
     * Obtenga las actividades.
     */
    public function activities()
    {
        return $this->belongsToMany(ActivityProxy::modelClass(), 'product_activities');
    }
}
