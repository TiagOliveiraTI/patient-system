<?php

namespace App\Models;

use App\Models\Address;
use App\Validators\CpfValidator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Patient extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'patients';

    protected $fillable = [
        'cpf',
        'photo',
        'name',
        'mother_name',
        'birth_date',
        'cns',
        'address_id'
    ];

    public function address(): HasOne
    {
        return $this->hasOne(Address::class, 'id', 'address_id');
    }

    public static function rules(): array
    {
        return [
            'cpf' => 'required|unique:patients|cpf',
            'foto' => 'nullable',
            'nome' => 'required|max:255',
            'nome_mae' => 'required|max:255',
            'data_nascimento' => 'required|date',
            'cns' => 'required|cns',
            'endereco' => 'required',
        ];
    }
}
