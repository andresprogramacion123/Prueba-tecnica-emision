<?php

namespace App\Models;

use App\Enums\EstadoCaso;
use Database\Factories\CasoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['numero_expediente', 'cliente_id', 'fecha_inicio', 'fecha_archivo', 'estado'])]
class Caso extends Model
{
    /** @use HasFactory<CasoFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'casos';

    protected $attributes = [
        'estado' => EstadoCaso::EnTramite->value,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'date',
            'fecha_archivo' => 'date',
            'estado' => EstadoCaso::class,
        ];
    }

    /**
     * @return BelongsTo<Cliente, $this>
     */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    /**
     * @return BelongsToMany<Abogado, $this>
     */
    public function abogados(): BelongsToMany
    {
        return $this->belongsToMany(Abogado::class, 'caso_abogado')->withTimestamps();
    }
}
