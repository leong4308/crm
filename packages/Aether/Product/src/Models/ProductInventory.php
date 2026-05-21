<?php

namespace Aether\Product\Models;

use Aether\Product\Contracts\ProductInventory as ProductInventoryContract;
use Aether\Warehouse\Models\LocationProxy;
use Aether\Warehouse\Models\WarehouseProxy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductInventory extends Model implements ProductInventoryContract
{
    /**
     * Los atributos que se pueden asignar en masa.
     *
     * @var array
     */
    protected $fillable = [
        'in_stock',
        'allocated',
        'product_id',
        'warehouse_id',
        'warehouse_location_id',
    ];

    /**
     * Interactuar con el nombre.
     */
    protected function onHand(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->in_stock - $this->allocated,
            set: fn ($value) => $this->in_stock - $this->allocated
        );
    }

    /**
     * Obtenga el producto propietario del inventario de productos.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(ProductProxy::modelClass());
    }

    /**
     * Obtenga la familia de atributos del producto propietaria del producto.
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(WarehouseProxy::modelClass());
    }

    /**
     * Obtenga la familia de atributos del producto propietaria del producto.
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(LocationProxy::modelClass(), 'warehouse_location_id');
    }
}
