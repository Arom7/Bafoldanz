<?php

namespace App\Models;

use Database\Factories\StudentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Alumno ("bailarín" en la UI). No guarda sucursal ni grupo:
 * ambos se obtienen a través de su inscripción.
 */
#[Fillable(['name', 'fee', 'payment_day', 'is_promo', 'active', 'enrolled_at', 'retired_at'])]
class Student extends Model
{
    /** @use HasFactory<StudentFactory> */
    use HasFactory, SoftDeletes;

    protected $attributes = [
        'active' => true,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fee' => 'decimal:2',
            'payment_day' => 'integer',
            'is_promo' => 'boolean',
            'active' => 'boolean',
            'enrolled_at' => 'date',
            'retired_at' => 'date',
        ];
    }

    /**
     * Inscripción actual del alumno (una sola por alumno).
     *
     * @return HasOne<Registration, $this>
     */
    public function registration(): HasOne
    {
        return $this->hasOne(Registration::class);
    }

    /**
     * Grupo actual del alumno, derivado de su inscripción.
     */
    protected function group(): Attribute
    {
        return Attribute::get(fn () => $this->registration?->group);
    }

    /**
     * Sucursal del alumno, derivada de su grupo.
     */
    protected function branch(): Attribute
    {
        return Attribute::get(fn () => $this->group?->branch);
    }

    /**
     * @return HasMany<Payment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * @return HasMany<Attendance, $this>
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * Alumnos activos (no retirados).
     *
     * @param  Builder<self>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('active', true);
    }

    /**
     * Alumnos retirados.
     *
     * @param  Builder<self>  $query
     */
    public function scopeRetired(Builder $query): void
    {
        $query->where('active', false);
    }

    /**
     * Alumnos cuyo mes está dentro de la ventana de cobro
     * (enrolled_at <= fin del mes y, si retirado, retired_at >= inicio del mes).
     *
     * @param  Builder<self>  $query
     */
    public function scopeInBillingWindowForMonth(Builder $query, string $month): void
    {
        $start = $month.'-01';
        $end = date('Y-m-t', strtotime($start));

        $query->where('enrolled_at', '<=', $end)
            ->where(function (Builder $q) use ($start) {
                $q->whereNull('retired_at')->orWhere('retired_at', '>=', $start);
            });
    }

    public function fee(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => number_format($value, 0, '.', ''),
        );
    }
}
