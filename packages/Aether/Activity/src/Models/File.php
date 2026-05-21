<?php

namespace Aether\Activity\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Aether\Activity\Contracts\File as FileContract;

class File extends Model implements FileContract
{
    /**
     * La tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'activity_files';

    /**
     * Los atributos que se deben agregar al modelo.
     *
     * @var array
     */
    protected $appends = ['url'];

    /**
     * Los atributos que se pueden asignar en masa.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'path',
        'activity_id',
    ];

    /**
     * Obtener la URL de la imagen del producto.
     */
    public function url()
    {
        return Storage::url($this->path);
    }

    /**
     * Obtener la URL de la imagen del producto.
     */
    public function getUrlAttribute()
    {
        return $this->url();
    }

    /**
     * Obtenga la actividad propietaria del archivo.
     */
    public function activity()
    {
        return $this->belongsTo(ActivityProxy::modelClass());
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $array = parent::toArray();

        $array['url'] = $this->url;

        return $array;
    }
}
