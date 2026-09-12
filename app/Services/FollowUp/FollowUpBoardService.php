<?php

namespace App\Services\FollowUp;

use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class FollowUpBoardService
{
    /**
     * Build the follow-up board of a developer.
     *
     * The same board is displayed to the developer (actions enabled) and to
     * the Super Admin (read-only), so both always read the same data.
     *
     * @return array{
     *     dueToday: Collection<int, Client>,
     *     overdue: Collection<int, Client>,
     *     inProgress: Collection<int, Client>,
     *     converted: Collection<int, Client>
     * }
     */
    public function forDeveloper(User $developer): array
    {
        return [
            'dueToday' => $this->query($developer)->prospects()->followUpDueToday()->orderBy('next_action_date')->get(),
            'overdue' => $this->query($developer)->prospects()->followUpOverdue()->orderBy('next_action_date')->get(),
            'inProgress' => $this->query($developer)->prospects()->latest()->get(),
            'converted' => $this->query($developer)->clients()->latest('updated_at')->get(),
        ];
    }

    /**
     * @return Builder<Client>
     */
    private function query(User $developer): Builder
    {
        return Client::query()
            ->assignedTo($developer)
            ->with(['assignedDeveloper', 'latestComment'])
            ->withCount('projects');
    }
}
