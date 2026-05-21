<?php

namespace Aether\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Aether\Core\Contracts\CountryState as CountryStateContract;

class CountryState extends Model implements CountryStateContract
{
    public $timestamps = false;
}
