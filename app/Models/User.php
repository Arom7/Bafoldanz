<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password', 'is_director'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_director' => 'boolean',
        ];
    }

    /**
     * Pagos cobrados por esta directora.
     *
     * @return HasMany<Payment, $this>
     */
    public function receivedPayments(): HasMany
    {
        return $this->hasMany(Payment::class, 'received_by');
    }

    /**
     * Movimientos registrados por este usuario.
     *
     * @return HasMany<Movement, $this>
     */
    public function createdMovements(): HasMany
    {
        return $this->hasMany(Movement::class, 'created_by');
    }

    /**
     * Salidas atribuidas a esta directora (dato informativo).
     *
     * @return HasMany<Movement, $this>
     */
    public function directorMovements(): HasMany
    {
        return $this->hasMany(Movement::class, 'director_id');
    }
}
