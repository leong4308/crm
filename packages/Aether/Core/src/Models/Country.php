<?php

namespace Aether\Core\Models;

use Aether\Core\Contracts\Country as CountryContract;
use Illuminate\Database\Eloquent\Model;

class Country extends Model implements CountryContract
{
    public $timestamps = false;
}
