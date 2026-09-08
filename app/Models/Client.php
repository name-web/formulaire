<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    protected $fillable = [
        'name',
        'status',
        'company_name',
        'phone',
        'whatsapp',
        'city',
        'country',
        'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'string',
        ];
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(ClientComment::class)->latest();
    }

    public function scopeProspects(Builder $query): Builder
    {
        return $query->where('status', 'prospect');
    }

    public function scopeClients(Builder $query): Builder
    {
        return $query->where('status', 'client');
    }

    public function isProspect(): bool
    {
        return $this->status === 'prospect';
    }

    public function isClient(): bool
    {
        return $this->status === 'client';
    }
}
