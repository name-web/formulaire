<?php

namespace App\Models;

use Database\Factories\QuoteFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quote extends Model
{
    /** @use HasFactory<QuoteFactory> */
    use HasFactory;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_SENT = 'sent';

    public const STATUS_ACCEPTED = 'accepted';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_PAID = 'paid';

    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'number',
        'client_id',
        'project_id',
        'sequence',
        'issued_at',
        'due_at',
        'subject',
        'description',
        'payment_terms',
        'notes',
        'prestations',
        'calculation',
        'status',
        'subtotal',
        'discount',
        'total',
    ];

    protected function casts(): array
    {
        return [
            'issued_at' => 'date',
            'due_at' => 'date',
            'subtotal' => 'integer',
            'discount' => 'integer',
            'total' => 'integer',
            'prestations' => 'array',
            'calculation' => 'array',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Projet à l'origine du devis, renseigné pour les devis créés par le
     * nouveau formulaire.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Le devis provient-il du nouveau formulaire (calcul conservé) ?
     */
    public function isFromForm(): bool
    {
        return ! empty($this->calculation);
    }

    public function customFields(): HasMany
    {
        return $this->hasMany(QuoteCustomField::class)->orderBy('sort_order')->orderBy('id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuoteItem::class)->orderBy('sort_order')->orderBy('id');
    }

    public function sections(): HasMany
    {
        return $this->hasMany(QuoteSection::class)->orderBy('sort_order')->orderBy('id');
    }

    public static function getStatusConfig(string $status): array
    {
        return config("quote_status.statuses.{$status}") ?? ['label' => ucfirst($status), 'class' => 'badge-user', 'icon' => 'fa-circle'];
    }

    public static function getStatuses(): array
    {
        return config('quote_status.statuses', []);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::getStatusConfig($this->status)['label'];
    }

    public function getStatusClassAttribute(): string
    {
        return self::getStatusConfig($this->status)['class'];
    }

    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    public function scopeStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeSearch(Builder $query, string $search): Builder
    {
        $escaped = str_replace(['%', '_'], ['\%', '\_'], $search);

        return $query->where(function ($q) use ($escaped) {
            $q->whereRaw('number LIKE ? ESCAPE ?', ["%{$escaped}%", '\\'])
                ->orWhereRaw('subject LIKE ? ESCAPE ?', ["%{$escaped}%", '\\'])
                ->orWhereHas('client', fn ($client) => $client
                    ->whereRaw('name LIKE ? ESCAPE ?', ["%{$escaped}%", '\\'])
                    ->orWhereRaw('company_name LIKE ? ESCAPE ?', ["%{$escaped}%", '\\']));
        });
    }
}
