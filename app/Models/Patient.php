<?php

namespace App\Models;

use App\Models\Address;
use App\Validators\CpfValidator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Patient extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'patients';

    protected $fillable = [
        'cpf',
        'photo',
        'name',
        'mom_name',
        'birth_date',
        'cns',
        'address_id'
    ];

    public function addresses()
    {
        return $this->morphMany(Address::class, 'id');
    }
}
