<?php

namespace Aether\Contact\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Aether\Activity\Models\ActivityProxy;
use Aether\Activity\Traits\LogsActivity;
use Aether\Attribute\Traits\CustomAttribute;
use Aether\Contact\Contracts\Person as PersonContract;
use Aether\Contact\Database\Factories\PersonFactory;
use Aether\Lead\Models\LeadProxy;
use Aether\Tag\Models\TagProxy;
use Aether\User\Models\UserProxy;

class Person extends Model implements PersonContract
{
    use CustomAttribute, HasFactory, LogsActivity;

    /**
     * Nombre de la tabla.
     *
     * @var string
     */
    protected $table = 'persons';

    /**
     * Carga ansiosa.
     *
     * @var string
     */
    protected $with = 'organization';

    /**
     * Los atributos que se pueden convertir.
     *
     * @var array
     */
    protected $casts = [
        'emails' => 'array',
        'contact_numbers' => 'array',
    ];

    /**
     * Los atributos que se pueden asignar en masa.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'emails',
        'contact_numbers',
        'job_title',
        'user_id',
        'organization_id',
        'unique_id',
    ];

    /**
     * Obtenga el usuario propietario del cliente potencial.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(UserProxy::modelClass());
    }

    /**
     * Obtenga la organización propietaria de la persona.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(OrganizationProxy::modelClass());
    }

    /**
     * Obtenga las actividades.
     */
    public function activities(): BelongsToMany
    {
        return $this->belongsToMany(ActivityProxy::modelClass(), 'person_activities');
    }

    /**
     * Las etiquetas que pertenecen a la persona.
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(TagProxy::modelClass(), 'person_tags');
    }

    /**
     * Obtenga las pistas para la persona.
     */
    public function leads(): HasMany
    {
        return $this->hasMany(LeadProxy::modelClass(), 'person_id');
    }

    /**
     * Cree una nueva instancia de fábrica para el modelo.
     */
    protected static function newFactory(): PersonFactory
    {
        return PersonFactory::new();
    }
}
