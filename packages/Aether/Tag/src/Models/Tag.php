<?php

namespace Aether\Tag\Models;

use Illuminate\Database\Eloquent\Model;
use Aether\Tag\Contracts\Tag as TagContract;
use Aether\User\Models\UserProxy;

class Tag extends Model implements TagContract
{
    protected $table = 'tags';

    /**
     * Los atributos que se pueden asignar en masa.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'color',
        'user_id',
    ];

    /**
     * Obtenga el usuario propietario de la etiqueta.
     */
    public function user()
    {
        return $this->belongsTo(UserProxy::modelClass());
    }
}
