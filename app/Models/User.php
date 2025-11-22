<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the identifier that will be stored in the JWT token.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Get the custom claims that will be added to the JWT token.
     *
     * @return array
     */
    public function getJWTCustomClaims(): array
    {
        return [];
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'last_name_primary',
        'last_name_secondary',
        'phone',
        'role',
        'email',
        'password',
    ];

    public function setNameAttribute($value) {
        $this->attributes['name'] = strtoupper($value);
    }

    public function setLastNamePrimaryAttribute($value) {
        $this->attributes['last_name_primary'] = strtoupper($value);
    }

    public function setLastNameSecondaryAttribute($value) {
        $this->attributes['last_name_secondary'] = strtoupper($value);
    }

    public function isVeterinarian(): bool
    {
        return strtolower($this->role ?? '') === 'veterinario';
    }



    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @var array<string,string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Accessor for full name (convenience).
     */
    public function getFullNameAttribute(): string
    {
        $parts = array_filter([
            $this->name ?? null,
            $this->last_name_primary ?? null,
            $this->last_name_secondary ?? null,
        ]);

        return implode(' ', $parts);
    }
}
