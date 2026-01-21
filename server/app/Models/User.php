<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    // Ha nálatok camelCase mezők vannak, itt a user táblában jellemzően snake_case.
    // A role migration alapján role biztosan van.
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => 'integer',
        ];
    }

    // --- Relationships ---
    public function barber()
    {
        return $this->hasOne(Barber::class, 'userId');
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'userId');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'userId');
    }

    // --- Roles (javaslat) ---
    public const ROLE_ADMIN = 1;
    public const ROLE_BARBER = 2;
    public const ROLE_CUSTOMER = 3;

    public function isAdmin(): bool
    {
        return (int) $this->role === self::ROLE_ADMIN;
    }

    public function isBarber(): bool
    {
        return (int) $this->role === self::ROLE_BARBER;
    }

    public function isCustomer(): bool
    {
        return (int) $this->role === self::ROLE_CUSTOMER;
    }
}
