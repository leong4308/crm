<?php

namespace Aether\EmailTemplate\Models;

use Illuminate\Database\Eloquent\Model;
use Aether\EmailTemplate\Contracts\EmailTemplate as EmailTemplateContract;

class EmailTemplate extends Model implements EmailTemplateContract
{
    protected $fillable = [
        'name',
        'subject',
        'content',
    ];
}
