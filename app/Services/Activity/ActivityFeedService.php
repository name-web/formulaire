<?php

namespace App\Services\Activity;

use App\Models\ClientActivity;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Lecture du journal d'activité central.
 *
 * L'espace Admin et l'espace Super Admin lisent exactement les mêmes lignes :
 * seuls la navigation et les filtres proposés changent. La requête et les
 * filtres sont donc décrits une seule fois, ici.
 */
class ActivityFeedService
{
    /**
     * Requête chronologique (du plus récent au plus ancien) filtrée.
     *
     * @param  array{search?: string|null, type?: string|null, group?: string|null, user_id?: int|string|null, from?: string|null, to?: string|null}  $filters
     * @return Builder<ClientActivity>
     */
    public function query(array $filters = []): Builder
    {
        $search = $this->likePattern($filters['search'] ?? null);

        return ClientActivity::query()
            ->with(['client', 'user'])
            ->when($search !== null, function (Builder $query) use ($search) {
                $query->whereHas('client', function (Builder $client) use ($search) {
                    $client->whereRaw('name LIKE ? ESCAPE ?', [$search, '\\'])
                        ->orWhereRaw('company_name LIKE ? ESCAPE ?', [$search, '\\']);
                });
            })
            ->when(filled($filters['type'] ?? null), fn (Builder $query) => $query->ofType($filters['type']))
            ->when(filled($filters['group'] ?? null), fn (Builder $query) => $query->ofGroup($filters['group']))
            ->when(filled($filters['user_id'] ?? null), fn (Builder $query) => $query->byUser($filters['user_id']))
            ->between($filters['from'] ?? null, $filters['to'] ?? null)
            ->chronological();
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginate(array $filters = [], ?int $perPage = null): LengthAwarePaginator
    {
        return $this->query($filters)
            ->paginate($perPage ?? (int) config('activity.per_page', 20))
            ->withQueryString();
    }

    /**
     * Dernières activités importantes, pour les aperçus de dashboard.
     *
     * @return Collection<int, ClientActivity>
     */
    public function recent(?int $limit = null): Collection
    {
        return ClientActivity::query()
            ->with(['client', 'user'])
            ->chronological()
            ->take($limit ?? (int) config('activity.dashboard_preview_count', 6))
            ->get();
    }

    /**
     * Auteurs ayant réellement produit une activité (filtre « Auteur »).
     *
     * @return Collection<int, User>
     */
    public function authors(): Collection
    {
        return User::query()
            ->whereIn('id', ClientActivity::query()->select('user_id')->whereNotNull('user_id'))
            ->orderBy('name')
            ->get();
    }

    /**
     * Motif LIKE échappé pour une recherche littérale.
     */
    private function likePattern(?string $search): ?string
    {
        if ($search === null || trim($search) === '') {
            return null;
        }

        $escaped = str_replace(['%', '_'], ['\\%', '\\_'], trim($search));

        return "%{$escaped}%";
    }
}
