<?php

namespace Aether\Core\Models;

use Aether\Core\Contracts\CountryState as CountryStateContract;
use Illuminate\Database\Eloquent\Model;

class CountryState extends Model implements CountryStateContract
{
    public $timestamps = false;
}
