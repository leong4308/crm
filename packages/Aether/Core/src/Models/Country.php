<?php

namespace Aether\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Aether\Core\Contracts\Country as CountryContract;

class Country extends Model implements CountryContract
{
    public $timestamps = false;
}
