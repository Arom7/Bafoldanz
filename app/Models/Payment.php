<?php

namespace App\Models;

use Database\Factories\PaymentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

#[Fillable(['student_id', 'month', 'amount', 'method', 'paid_at', 'received_by', 'voided_at', 'voided_by', 'void_reason'])]
class Payment extends Model
{
    /**
     * Métodos de cobro válidos en v1 (enum de la base de datos).
     */
    public const METHODS = ['Efectivo', 'Pago por QR'];

    /** @use HasFactory<PaymentFactory> */
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
            'paid_at' => 'date',
            'voided_at' => 'datetime',
        ];
    }

    /**
     * `month` se guarda como DATE (primer día del mes) y se
     * lee/escribe en la app como `YYYY-MM`.
     */
    protected function month(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value !== null
                ? Carbon::parse($value)->format('Y-m')
                : null,
            set: fn (string $value) => [
                'month' => Carbon::parse(
                    strlen($value) === 7 ? $value.'-01' : $value,
                )->startOfDay()->toDateString(),
            ],
        );
    }

    /**
     * @return BelongsTo<Student, $this>
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Directora que cobró (obligatoria).
     *
     * @return BelongsTo<User, $this>
     */
    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    /**
     * Directora que anuló el pago, si corresponde.
     *
     * @return BelongsTo<User, $this>
     */
    public function voidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'voided_by');
    }
}
