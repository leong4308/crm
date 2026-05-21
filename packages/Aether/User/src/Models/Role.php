<?php

namespace Aether\User\Models;

use Aether\User\Contracts\Role as RoleContract;
use Illuminate\Database\Eloquent\Model;

class Role extends Model implements RoleContract
{
    /**
     * Los atributos que se pueden asignar en masa.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'permission_type',
        'permissions',
    ];

    protected $casts = [
        'permissions' => 'array',
    ];

    /**
     * Consigue los usuarios.
     */
    public function users()
    {
        return $this->hasMany(UserProxy::modelClass());
    }
}
