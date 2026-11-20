<?php

namespace App\Models;

use Database\Factories\MovementFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Salida de caja. Todas las filas son salidas de dinero;
 * el detalle se explica en `concept`. No hay cuentas ni transferencias.
 */
#[Fillable(['concept', 'amount', 'director_id', 'date', 'created_by', 'voided_at', 'voided_by', 'void_reason'])]
class Movement extends Model
{
    /** @use HasFactory<MovementFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'date' => 'date',
            'voided_at' => 'datetime',
        ];
    }

    /**
     * Directora que realizó la salida (dato informativo, opcional).
     *
     * @return BelongsTo<User, $this>
     */
    public function director(): BelongsTo
    {
        return $this->belongsTo(User::class, 'director_id');
    }

    /**
     * Usuario que registró la salida.
     *
     * @return BelongsTo<User, $this>
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Usuario que anuló la salida, si corresponde.
     *
     * @return BelongsTo<User, $this>
     */
    public function voidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'voided_by');
    }

    /**
     * Salidas vigentes (no anuladas).
     *
     * @param  Builder<self>  $query
     */
    public function scopeNotVoided(Builder $query): void
    {
        $query->whereNull('voided_at');
    }
}
