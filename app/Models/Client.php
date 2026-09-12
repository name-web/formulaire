<?php

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\ClientFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Client extends Model
{
    /** @use HasFactory<ClientFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'status',
        'assigned_developer_id',
        'next_action',
        'next_action_date',
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
            'next_action_date' => 'date',
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

    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class)->latest('issued_at');
    }

    /**
     * The developer in charge of this prospect.
     */
    public function assignedDeveloper(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_developer_id');
    }

    /**
     * Most recent follow-up comment, used as the "last activity" marker.
     */
    public function latestComment(): HasOne
    {
        return $this->hasOne(ClientComment::class)->latestOfMany();
    }

    public function scopeProspects(Builder $query): Builder
    {
        return $query->where('status', 'prospect');
    }

    public function scopeClients(Builder $query): Builder
    {
        return $query->where('status', 'client');
    }

    public function scopeAssignedTo(Builder $query, User|int $developer): Builder
    {
        return $query->where('assigned_developer_id', $developer instanceof User ? $developer->getKey() : $developer);
    }

    public function scopeFollowUpDueToday(Builder $query): Builder
    {
        return $query->whereDate('next_action_date', today());
    }

    public function scopeFollowUpOverdue(Builder $query): Builder
    {
        return $query->whereDate('next_action_date', '<', today());
    }

    public function isProspect(): bool
    {
        return $this->status === 'prospect';
    }

    public function isClient(): bool
    {
        return $this->status === 'client';
    }

    public function hasFollowUp(): bool
    {
        return $this->next_action_date !== null;
    }

    public function isFollowUpDueToday(): bool
    {
        return $this->next_action_date?->isToday() ?? false;
    }

    public function isFollowUpOverdue(): bool
    {
        return $this->hasFollowUp()
            && ! $this->isFollowUpDueToday()
            && $this->next_action_date->isPast();
    }

    /**
     * Date of the last recorded follow-up activity (latest comment).
     */
    public function getLastActivityAtAttribute(): ?Carbon
    {
        return $this->latestComment?->created_at;
    }
}
