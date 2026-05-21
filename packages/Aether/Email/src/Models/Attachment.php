<?php

namespace Aether\Email\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Aether\Email\Contracts\Attachment as AttachmentContract;

class Attachment extends Model implements AttachmentContract
{
    /**
     * Los atributos que se pueden asignar en masa.
     *
     * @var string
     */
    protected $table = 'email_attachments';

    /**
     * Los atributos que se añaden.
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
        'size',
        'content_type',
        'content_id',
        'email_id',
    ];

    /**
     * Recibe el correo electrónico.
     */
    public function email()
    {
        return $this->belongsTo(EmailProxy::modelClass());
    }

    /**
     * Obtener la URL de la imagen del producto.
     */
    public function url()
    {
        return Storage::url($this->path);
    }

    /**
     * Accessor para el atributo 'url'.
     */
    public function getUrlAttribute()
    {
        return $this->url();
    }
}
