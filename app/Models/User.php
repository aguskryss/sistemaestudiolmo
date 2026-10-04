<?php

namespace App\Models;

use App\Enums\Rol;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    protected $attributes = ['rol' => 'miembro', 'activo' => true];

    /**
     * Rol y activo quedan fuera a propósito: solo un admin los cambia, de forma explícita.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'two_factor_confirmed_at' => 'datetime',
            'password' => 'hashed',
            'rol' => Rol::class,
            'activo' => 'boolean',
        ];
    }

    public function esAdmin(): bool
    {
        return $this->rol === Rol::Admin;
    }

    public function obrasACargo(): HasMany
    {
        return $this->hasMany(Obra::class, 'responsable_id');
    }

    public function recordatorios(): HasMany
    {
        return $this->hasMany(Recordatorio::class);
    }
}
