<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PendingSubmission extends Model
{
    protected $fillable = [
        'uuid',
        'client_data',
        'project_data',
        'answers_data',
        'files_data',
        'status',
        'decision',
        'failure_reason',
        'last_error',
        'sent_at',
        'imported_at',
        'expires_at',
    ];

    protected $casts = [
        'client_data' => 'array',
        'project_data' => 'array',
        'answers_data' => 'array',
        'files_data' => 'array',
        'sent_at' => 'datetime',
        'imported_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public static function boot(): void
    {
        parent::boot();

        static::creating(function (self $model) {
            $model->uuid = $model->uuid ?? Str::uuid();
            $model->expires_at = $model->expires_at ?? now()->addHours(48);
        });
    }

    public function isCollecting(): bool
    {
        return $this->status === 'collecting';
    }

    public function isSending(): bool
    {
        return $this->status === 'sending';
    }

    public function isSent(): bool
    {
        return $this->status === 'sent';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function isImported(): bool
    {
        return $this->status === 'imported';
    }

    public function isExpired(): bool
    {
        return $this->status === 'expired';
    }

    public function isAccepted(): bool
    {
        return $this->decision === 'accepted';
    }

    public function isRejected(): bool
    {
        return $this->decision === 'rejected';
    }

    public function hasDecision(): bool
    {
        return $this->decision !== null;
    }

    public function markAsSending(): void
    {
        $this->update(['status' => 'sending']);
    }

    public function markAsSent(): void
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }

    public function markAsFailed(string $reason, ?string $error = null): void
    {
        $this->update([
            'status' => 'failed',
            'failure_reason' => $reason,
            'last_error' => $error,
        ]);
    }

    public function markAsImported(): void
    {
        $this->update([
            'status' => 'imported',
            'decision' => 'accepted',
            'imported_at' => now(),
        ]);
    }

    public function markAsRejected(?string $reason = null): void
    {
        $this->update([
            'decision' => 'rejected',
            'failure_reason' => $reason,
        ]);
    }

    public function markAsExpired(): void
    {
        $this->update(['status' => 'expired']);
    }
}
