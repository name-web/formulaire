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
        'snapshot_data',
        'status',
        'failure_reason',
        'last_error',
        'sent_at',
        'imported_at',
    ];

    protected $casts = [
        'client_data' => 'array',
        'project_data' => 'array',
        'answers_data' => 'array',
        'files_data' => 'array',
        'snapshot_data' => 'array',
        'sent_at' => 'datetime',
        'imported_at' => 'datetime',
    ];

    public static function boot(): void
    {
        parent::boot();

        static::creating(function (self $model) {
            $model->uuid = $model->uuid ?? (string) Str::uuid();
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
            'imported_at' => now(),
        ]);
    }
}
