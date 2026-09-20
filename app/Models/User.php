<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
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
            'is_active' => 'boolean',
        ];
    }

    public function comments(): HasMany
    {
        return $this->hasMany(ClientComment::class);
    }

    /**
     * Activités du journal d'activité central dont cet utilisateur est l'auteur.
     */
    public function activities(): HasMany
    {
        return $this->hasMany(ClientActivity::class);
    }

    /**
     * Prospects and clients followed by this developer.
     */
    public function assignedClients(): HasMany
    {
        return $this->hasMany(Client::class, 'assigned_developer_id');
    }

    /**
     * Prospects and clients assigned to this commercial.
     */
    public function assignedCommercialClients(): HasMany
    {
        return $this->hasMany(Client::class, 'assigned_commercial_id');
    }

    /**
     * The general admin dashboard accepts administrators and super admins.
     */
    public function isAdmin(): bool
    {
        return in_array($this->role, ['admin', 'super_admin'], true);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isDeveloper(): bool
    {
        return $this->role === 'developer';
    }

    public function isCommercial(): bool
    {
        return $this->role === 'commercial';
    }

    public function isActive(): bool
    {
        return $this->is_active;
    }
}
