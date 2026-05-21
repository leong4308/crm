<?php

namespace Aether\Contact\Models;

use Aether\Attribute\Traits\CustomAttribute;
use Aether\Contact\Contracts\Organization as OrganizationContract;
use Aether\User\Models\UserProxy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Organization extends Model implements OrganizationContract
{
    use CustomAttribute;

    protected $casts = [
        'address' => 'array',
    ];

    /**
     * Los atributos que se pueden asignar en masa.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'address',
        'user_id',
    ];

    /**
     * Consigue personas.
     *
     * @return HasMany
     */
    public function persons()
    {
        return $this->hasMany(PersonProxy::modelClass());
    }

    /**
     * Obtenga el usuario propietario del cliente potencial.
     */
    public function user()
    {
        return $this->belongsTo(UserProxy::modelClass());
    }
}
