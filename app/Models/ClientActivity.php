<?php

namespace App\Models;

use Database\Factories\ClientActivityFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Ligne du journal d'activité central.
 *
 * Une activité est toujours rattachée à UN prospect/client existant : le
 * journal ne duplique jamais la donnée prospect, il ne fait que l'historiser.
 * Les lignes sont ajoutées par App\Services\Activity\ActivityJournal.
 */
class ClientActivity extends Model
{
    /** @use HasFactory<ClientActivityFactory> */
    use HasFactory;

    public const TYPE_VIEWED = 'viewed';

    public const TYPE_COMMENT_ADDED = 'comment_added';

    public const TYPE_STATUS_CHANGED = 'status_changed';

    public const TYPE_FOLLOW_UP_SCHEDULED = 'follow_up_scheduled';

    public const TYPE_FOLLOW_UP_COMPLETED = 'follow_up_completed';

    public const TYPE_CONVERTED = 'converted';

    public const TYPE_UPDATED = 'updated';

    public const TYPE_ASSIGNMENT_CHANGED = 'assignment_changed';

    protected $fillable = [
        'client_id',
        'user_id',
        'type',
        'description',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Auteur de l'action ; null si le compte a été supprimé depuis.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Du plus récent au plus ancien, à la seconde près.
     *
     * @param  Builder<ClientActivity>  $query
     * @return Builder<ClientActivity>
     */
    public function scopeChronological(Builder $query): Builder
    {
        return $query->latest('created_at')->latest('id');
    }

    /**
     * @param  Builder<ClientActivity>  $query
     * @return Builder<ClientActivity>
     */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    /**
     * @param  Builder<ClientActivity>  $query
     * @return Builder<ClientActivity>
     */
    public function scopeOfGroup(Builder $query, string $group): Builder
    {
        $types = collect(self::typeConfigs())
            ->filter(fn (array $config) => $config['group'] === $group)
            ->keys()
            ->all();

        return $query->whereIn('type', $types);
    }

    /**
     * @param  Builder<ClientActivity>  $query
     * @return Builder<ClientActivity>
     */
    public function scopeByUser(Builder $query, User|int $user): Builder
    {
        return $query->where('user_id', $user instanceof User ? $user->getKey() : $user);
    }

    /**
     * @param  Builder<ClientActivity>  $query
     * @return Builder<ClientActivity>
     */
    public function scopeBetween(Builder $query, ?string $from, ?string $to): Builder
    {
        return $query
            ->when($from, fn (Builder $q) => $q->whereDate('created_at', '>=', $from))
            ->when($to, fn (Builder $q) => $q->whereDate('created_at', '<=', $to));
    }

    public function getTypeLabelAttribute(): string
    {
        return self::typeConfig($this->type)['label'];
    }

    public function getTypeIconAttribute(): string
    {
        return self::typeConfig($this->type)['icon'];
    }

    public function getTypeBadgeAttribute(): string
    {
        return self::typeConfig($this->type)['badge'];
    }

    /**
     * A client stays green and a prospect yellow, exactly as everywhere else.
     */
    public function getClientStatusAttribute(): string
    {
        return $this->client?->status ?? 'prospect';
    }

    /**
     * @return array{label: string, group: string, icon: string, badge: string}
     */
    public static function typeConfig(string $type): array
    {
        return config("activity.types.{$type}", [
            'label' => ucfirst(str_replace('_', ' ', $type)),
            'group' => 'autre',
            'icon' => 'fa-solid fa-circle-dot',
            'badge' => 'badge-user',
        ]);
    }

    /**
     * @return array<string, array{label: string, group: string, icon: string, badge: string}>
     */
    public static function typeConfigs(): array
    {
        return config('activity.types', []);
    }

    /**
     * Familles d'actions proposées dans les filtres de la page Activités.
     *
     * @return array<string, string>
     */
    public static function groups(): array
    {
        $labels = [
            'consultation' => 'Consultations',
            'suivi' => 'Notes de suivi',
            'statut' => 'Changements de statut',
            'relance' => 'Relances',
            'conversion' => 'Conversions',
            'modification' => 'Modifications',
            'attribution' => 'Attributions',
        ];

        return collect(self::typeConfigs())
            ->groupBy('group')
            ->mapWithKeys(fn ($types, string $group) => [$group => $labels[$group] ?? $types->first()['label'] ?? $group])
            ->all();
    }
}
