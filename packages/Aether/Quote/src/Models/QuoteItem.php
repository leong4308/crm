<?php

namespace Aether\Quote\Models;

use Aether\Quote\Contracts\QuoteItem as QuoteItemContract;
use Illuminate\Database\Eloquent\Model;

class QuoteItem extends Model implements QuoteItemContract
{
    protected $table = 'quote_items';

    /**
     * Los atributos que se pueden asignar en masa.
     *
     * @var array
     */
    protected $fillable = [
        'sku',
        'name',
        'quantity',
        'price',
        'coupon_code',
        'discount_percent',
        'discount_amount',
        'tax_percent',
        'tax_amount',
        'total',
        'product_id',
        'quote_id',
    ];

    /**
     * Obtenga el registro de cotización asociado con el artículo de cotización.
     */
    public function quote()
    {
        return $this->belongsTo(QuoteProxy::modelClass());
    }
}
