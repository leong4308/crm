<?php

namespace Aether\User\Models;

use Aether\User\Contracts\Group as GroupContract;
use Illuminate\Database\Eloquent\Model;

class Group extends Model implements GroupContract
{
    /**
     * Los atributos que se pueden asignar en masa.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * Los usuarios que pertenecen al grupo.
     */
    public function users()
    {
        return $this->belongsToMany(UserProxy::modelClass(), 'user_groups');
    }
}
