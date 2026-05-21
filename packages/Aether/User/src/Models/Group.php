<?php

namespace Aether\User\Models;

use Illuminate\Database\Eloquent\Model;
use Aether\User\Contracts\Group as GroupContract;

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
