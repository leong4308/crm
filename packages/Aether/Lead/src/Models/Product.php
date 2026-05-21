<?php

namespace Aether\Lead\Models;

use Aether\Lead\Contracts\Product as ProductContract;
use Aether\Product\Models\ProductProxy;
use Illuminate\Database\Eloquent\Model;

class Product extends Model implements ProductContract
{
    protected $table = 'lead_products';

    /**
     * Los atributos que se pueden asignar en masa.
     *
     * @var array
     */
    protected $fillable = [
        'quantity',
        'price',
        'amount',
        'product_id',
        'lead_id',
    ];

    /**
     * Obtener el producto posee el producto principal.
     */
    public function product()
    {
        return $this->belongsTo(ProductProxy::modelClass());
    }

    /**
     * Obtenga el cliente potencial propietario del producto principal.
     */
    public function lead()
    {
        return $this->belongsTo(LeadProxy::modelClass());
    }

    /**
     * Obtenga el nombre completo del cliente.
     */
    public function getNameAttribute()
    {
        return $this->product->name;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $array = parent::toArray();

        $array['name'] = $this->name;

        return $array;
    }
}
