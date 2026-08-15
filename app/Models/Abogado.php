<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['cedula', 'nombre', 'telefono', 'email', 'tarjeta_profesional'])]
class Abogado extends Model
{
    use SoftDeletes;

    protected $table = 'abogados';

    /**
     * @return BelongsToMany<Caso, $this>
     */
    public function casos(): BelongsToMany
    {
        return $this->belongsToMany(Caso::class, 'caso_abogado')->withTimestamps();
    }
}
