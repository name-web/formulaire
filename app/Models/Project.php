<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Project extends Model
{
    /**
     * Projets créés avec l'ancien questionnaire (questions / réponses en base).
     */
    public const FORM_VERSION_LEGACY = 'legacy';

    /**
     * Projets créés avec le nouveau formulaire de devis automatique.
     */
    public const FORM_VERSION_CURRENT = 'v2';

    protected $fillable = [
        'client_id',
        'title',
        'project_type',
        'description',
        'status',
        'form_version',
        'form_data',
        'submitted_at',
    ];

    protected $casts = [
        'project_type' => 'array',
        'form_data' => 'array',
        'submitted_at' => 'datetime',
    ];

    /**
     * Le projet a-t-il été créé avec le nouveau formulaire ?
     */
    public function usesCurrentForm(): bool
    {
        return $this->form_version === self::FORM_VERSION_CURRENT;
    }

    /**
     * Le projet provient-il de l'ancien questionnaire ?
     */
    public function usesLegacyForm(): bool
    {
        return ! $this->usesCurrentForm();
    }

    /**
     * Une valeur des réponses du nouveau formulaire.
     */
    public function formValue(string $key, mixed $default = null): mixed
    {
        return $this->form_data[$key] ?? $default;
    }

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

    /**
     * Devis rattachés au projet (dont le devis provisoire du nouveau formulaire).
     */
    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class)->latest('issued_at');
    }

    /**
     * Devis provisoire généré automatiquement par le nouveau formulaire.
     */
    public function provisionalQuote(): HasOne
    {
        return $this->hasOne(Quote::class)->whereNotNull('calculation')->oldestOfMany();
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
