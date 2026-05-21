<?php

namespace Aether\Email\Models;

use Aether\Contact\Models\PersonProxy;
use Aether\Email\Contracts\Email as EmailContract;
use Aether\Lead\Models\LeadProxy;
use Aether\Tag\Models\TagProxy;
use Illuminate\Database\Eloquent\Model;

class Email extends Model implements EmailContract
{
    /**
     * La tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'emails';

    /**
     * Los atributos que se deben emitir.
     *
     * @var array
     */
    protected $casts = [
        'folders' => 'array',
        'sender' => 'array',
        'from' => 'array',
        'reply_to' => 'array',
        'cc' => 'array',
        'bcc' => 'array',
        'reference_ids' => 'array',
    ];

    /**
     * Los atributos que se añaden.
     *
     * @var array
     */
    protected $appends = [
        'time_ago',
    ];

    /**
     * Los atributos que se pueden asignar en masa.
     *
     * @var array
     */
    protected $fillable = [
        'subject',
        'source',
        'name',
        'user_type',
        'is_read',
        'folders',
        'from',
        'sender',
        'reply_to',
        'cc',
        'bcc',
        'unique_id',
        'message_id',
        'reference_ids',
        'reply',
        'person_id',
        'parent_id',
        'lead_id',
        'created_at',
        'updated_at',
    ];

    /**
     * Obtenga el correo electrónico de los padres.
     */
    public function parent()
    {
        return $this->belongsTo(EmailProxy::modelClass(), 'parent_id');
    }

    /**
     * Consigue el liderazgo.
     */
    public function lead()
    {
        return $this->belongsTo(LeadProxy::modelClass());
    }

    /**
     * Recibe los correos electrónicos.
     */
    public function emails()
    {
        return $this->hasMany(EmailProxy::modelClass(), 'parent_id');
    }

    /**
     * Consigue a la persona propietaria del hilo.
     */
    public function person()
    {
        return $this->belongsTo(PersonProxy::modelClass());
    }

    /**
     * Las etiquetas que pertenecen al cliente potencial.
     */
    public function tags()
    {
        return $this->belongsToMany(TagProxy::modelClass(), 'email_tags');
    }

    /**
     * Obtenga los archivos adjuntos.
     */
    public function attachments()
    {
        return $this->hasMany(AttachmentProxy::modelClass(), 'email_id');
    }

    /**
     * Consigue el tiempo atrás.
     */
    public function getTimeAgoAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }
}
