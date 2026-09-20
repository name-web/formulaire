<?php

namespace App\Services\Activity;

use App\Models\Client;
use App\Models\ClientActivity;
use App\Models\Project;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Str;

/**
 * Journal d'activité central.
 *
 * Toutes les écritures passent par ce service : les contrôleurs ne créent
 * jamais une ligne à la main, ce qui garantit des libellés homogènes, des
 * métadonnées comparables et un seul endroit à faire évoluer.
 *
 * Le journal est append-only : on enregistre une action, on ne modifie ni ne
 * duplique jamais le prospect ou le client concerné.
 */
class ActivityJournal
{
    /**
     * Longueur maximale d'un libellé (la colonne `description` fait 255).
     */
    private const DESCRIPTION_LIMIT = 200;

    /**
     * Enregistre une activité rattachée à un prospect/client existant.
     *
     * @param  array<string, mixed>  $meta
     */
    public function record(Client $client, string $type, ?User $actor, string $description, array $meta = []): ClientActivity
    {
        return $client->activities()->create([
            'user_id' => $actor?->getKey(),
            'type' => $type,
            'description' => Str::limit($description, self::DESCRIPTION_LIMIT),
            'meta' => $meta,
        ]);
    }

    /**
     * Consultation d'une fiche.
     *
     * Une même fiche rechargée par le même utilisateur ne remplit pas le
     * journal : la consultation est ignorée si elle est trop récente.
     * Retourne null quand l'activité n'a pas été enregistrée.
     */
    public function view(Client $client, User $actor): ?ClientActivity
    {
        if ($this->hasRecentView($client, $actor)) {
            return null;
        }

        return $this->record(
            $client,
            ClientActivity::TYPE_VIEWED,
            $actor,
            'Consultation de la fiche '.($client->isClient() ? 'client.' : 'prospect.'),
            ['status' => $client->status],
        );
    }

    /**
     * Ajout d'une note de suivi.
     */
    public function commentAdded(Client $client, User $actor, string $content): ClientActivity
    {
        $excerpt = Str::limit(trim($content), 120);

        return $this->record(
            $client,
            ClientActivity::TYPE_COMMENT_ADDED,
            $actor,
            'Note de suivi ajoutée : « '.$excerpt.' »',
            ['excerpt' => $excerpt],
        );
    }

    /**
     * Relance programmée (prochaine action datée).
     */
    public function followUpScheduled(Client $client, User $actor, ?string $action, ?string $date): ClientActivity
    {
        $formattedDate = $date ? Carbon::parse($date)->format('d/m/Y') : null;

        return $this->record(
            $client,
            ClientActivity::TYPE_FOLLOW_UP_SCHEDULED,
            $actor,
            'Relance programmée'
                .($formattedDate ? " pour le {$formattedDate}" : '')
                .($action ? " : {$action}" : ''),
            ['action' => $action, 'date' => $date],
        );
    }

    /**
     * Relance effectuée : la prochaine action planifiée est clôturée.
     */
    public function followUpCompleted(Client $client, User $actor, ?string $action): ClientActivity
    {
        return $this->record(
            $client,
            ClientActivity::TYPE_FOLLOW_UP_COMPLETED,
            $actor,
            'Relance effectuée'.($action ? " : {$action}" : '').' — prochaine action clôturée.',
            ['action' => $action],
        );
    }

    /**
     * Modification métier libre (action de suivi réécrite, informations mises à jour…).
     *
     * @param  array<string, mixed>  $meta
     */
    public function updated(Client $client, User $actor, string $description, array $meta = []): ClientActivity
    {
        return $this->record($client, ClientActivity::TYPE_UPDATED, $actor, $description, $meta);
    }

    /**
     * Conversion prospect → client.
     */
    public function converted(Client $client, User $actor): ClientActivity
    {
        return $this->record(
            $client,
            ClientActivity::TYPE_CONVERTED,
            $actor,
            'Prospect converti en client.',
            ['previous_status' => 'prospect'],
        );
    }

    /**
     * Changement de statut du projet (devis) d'un prospect ou d'un client.
     */
    public function projectStatusChanged(Client $client, User $actor, Project $project, ?string $from, ?string $to): ClientActivity
    {
        $fromLabel = $from ? Project::getStatusConfig($from)['label'] : '—';
        $toLabel = $to ? Project::getStatusConfig($to)['label'] : '—';

        return $this->record(
            $client,
            ClientActivity::TYPE_STATUS_CHANGED,
            $actor,
            "Statut du projet #{$project->getKey()} : {$fromLabel} → {$toLabel}.",
            ['project_id' => $project->getKey(), 'from' => $from, 'to' => $to],
        );
    }

    /**
     * Attribution ou réattribution d'un prospect par le Super Admin.
     *
     * Retourne null quand rien n'a changé : aucune ligne n'est alors écrite.
     */
    public function assignmentChanged(
        Client $client,
        User $actor,
        ?User $previousDeveloper,
        ?User $newDeveloper,
        ?User $previousCommercial,
        ?User $newCommercial,
    ): ?ClientActivity {
        $changes = [];

        if ($previousDeveloper?->getKey() !== $newDeveloper?->getKey()) {
            $changes[] = 'développeur : '.($previousDeveloper->name ?? 'non attribué')
                .' → '.($newDeveloper->name ?? 'non attribué');
        }

        if ($previousCommercial?->getKey() !== $newCommercial?->getKey()) {
            $changes[] = 'commercial : '.($previousCommercial->name ?? 'non attribué')
                .' → '.($newCommercial->name ?? 'non attribué');
        }

        if ($changes === []) {
            return null;
        }

        return $this->record(
            $client,
            ClientActivity::TYPE_ASSIGNMENT_CHANGED,
            $actor,
            'Attribution modifiée ('.implode(', ', $changes).').',
            [
                'developer_from' => $previousDeveloper?->name,
                'developer_to' => $newDeveloper?->name,
                'commercial_from' => $previousCommercial?->name,
                'commercial_to' => $newCommercial?->name,
            ],
        );
    }

    /**
     * Une consultation identique (même auteur, même fiche) a-t-elle déjà été
     * enregistrée récemment ?
     */
    private function hasRecentView(Client $client, User $actor): bool
    {
        $minutes = (int) config('activity.view_dedup_minutes', 30);

        return ClientActivity::query()
            ->where('client_id', $client->getKey())
            ->where('user_id', $actor->getKey())
            ->ofType(ClientActivity::TYPE_VIEWED)
            ->where('created_at', '>=', now()->subMinutes($minutes))
            ->exists();
    }
}
