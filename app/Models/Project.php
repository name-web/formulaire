<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    protected $fillable = [
        'client_id',
        'title',
        'project_type',
        'description',
        'status',
        'submitted_at',
    ];

    protected $casts = [
        'project_type' => 'array',
        'submitted_at' => 'datetime',
    ];

    public static function getStatusConfig(string $status): array
    {
        return config("project_status.statuses.{$status}") ?? ['label' => ucfirst($status), 'class' => 'badge-user', 'icon' => 'fa-circle'];
    }

    public static function getSectionIcon(string $label): string
    {
        return config("section_icons.icons.{$label}") ?? config('section_icons.default_icon', 'fa-clipboard-list');
    }

    public static function getStatuses(): array
    {
        return config('project_status.statuses', []);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(ProjectFile::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(ProjectNote::class);
    }

    public function getTypeLabelsAttribute(): string
    {
        $types = is_array($this->project_type) ? $this->project_type : [$this->project_type];

        return collect($types)
            ->map(fn ($t) => config("project_types.types.{$t}.label", $t))
            ->implode(', ');
    }

    public function getStatusLabelAttribute(): string
    {
        return self::getStatusConfig($this->status)['label'];
    }

    public function getStatusClassAttribute(): string
    {
        return self::getStatusConfig($this->status)['class'];
    }
}
