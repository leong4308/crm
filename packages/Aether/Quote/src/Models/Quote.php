<?php

namespace Aether\Quote\Models;

use Illuminate\Database\Eloquent\Model;
use Aether\Attribute\Traits\CustomAttribute;
use Aether\Contact\Models\PersonProxy;
use Aether\Lead\Models\LeadProxy;
use Aether\Quote\Contracts\Quote as QuoteContract;
use Aether\User\Models\UserProxy;

class Quote extends Model implements QuoteContract
{
    use CustomAttribute;

    protected $table = 'quotes';

    protected $casts = [
        'billing_address' => 'array',
        'shipping_address' => 'array',
        'expired_at' => 'datetime',
    ];

    /**
     * Los atributos que se pueden asignar en masa.
     *
     * @var array
     */
    protected $fillable = [
        'subject',
        'description',
        'billing_address',
        'shipping_address',
        'discount_percent',
        'discount_amount',
        'tax_amount',
        'adjustment_amount',
        'sub_total',
        'grand_total',
        'expired_at',
        'user_id',
        'person_id',
    ];

    /**
     * Obtenga el registro de artículos de cotización asociados con la cotización.
     */
    public function items()
    {
        return $this->hasMany(QuoteItemProxy::modelClass());
    }

    /**
     * Obtenga el usuario propietario de la cotización.
     */
    public function user()
    {
        return $this->belongsTo(UserProxy::modelClass());
    }

    /**
     * Consiga la persona propietaria de la cotización.
     */
    public function person()
    {
        return $this->belongsTo(PersonProxy::modelClass());
    }

    /**
     * Los leads que pertenecen a la cotización.
     */
    public function leads()
    {
        return $this->belongsToMany(LeadProxy::modelClass(), 'lead_quotes');
    }
}
