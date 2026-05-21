<?php

namespace Aether\EmailTemplate\Models;

use Aether\EmailTemplate\Contracts\EmailTemplate as EmailTemplateContract;
use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model implements EmailTemplateContract
{
    protected $fillable = [
        'name',
        'subject',
        'content',
    ];
}
