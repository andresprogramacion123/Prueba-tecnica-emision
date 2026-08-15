<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['cedula', 'nombre', 'telefono', 'email', 'direccion'])]
class Cliente extends Model
{
    use SoftDeletes;

    protected $table = 'clientes';

    /**
     * @return HasMany<Caso, $this>
     */
    public function casos(): HasMany
    {
        return $this->hasMany(Caso::class);
    }
}
