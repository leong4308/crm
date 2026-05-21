<?php

namespace Aether\Automation\Models;

use Aether\Automation\Contracts\Webhook as ContractsWebhook;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Webhook extends Model implements ContractsWebhook
{
    use HasFactory;

    /**
     * Los atributos que se pueden asignar en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'entity_type',
        'description',
        'method',
        'end_point',
        'query_params',
        'headers',
        'payload_type',
        'raw_payload_type',
        'payload',
    ];

    /**
     * Los atributos que se deben convertir a tipos nativos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'query_params' => 'array',
        'headers' => 'array',
        'payload' => 'array',
    ];
}
