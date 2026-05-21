<?php

namespace Aether\Automation\Models;

use Aether\Automation\Contracts\Workflow as WorkflowContract;
use Illuminate\Database\Eloquent\Model;

class Workflow extends Model implements WorkflowContract
{
    protected $casts = [
        'conditions' => 'array',
        'actions' => 'array',
    ];

    protected $fillable = [
        'name',
        'description',
        'entity_type',
        'event',
        'condition_type',
        'conditions',
        'actions',
    ];
}
