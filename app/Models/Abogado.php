<?php

namespace App\Models;

use Database\Factories\AbogadoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['cedula', 'nombre', 'telefono', 'email', 'tarjeta_profesional'])]
class Abogado extends Model
{
    /** @use HasFactory<AbogadoFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'abogados';

    /**
     * @return BelongsToMany<Caso, $this>
     */
    public function casos(): BelongsToMany
    {
        return $this->belongsToMany(Caso::class, 'caso_abogado')->withTimestamps();
    }
}
