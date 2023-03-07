<?php

namespace App\Models;

use Ramsey\Uuid\Uuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Address extends Model
{
    use HasUuids;
    use HasFactory;

    protected $table = 'addresses';


    protected $fillable = [
        "zip_code",
        "street",
        "number",
        "complement",
        "neighborhood",
        "city",
        "stateCode",
        "ibge",
        "gia",
        "ddd",
        "siafi"
    ];
}
