<x-layouts.admin :title="'Prospect — ' . $prospect->name">

    <section class="intro">
        <div>
            <div class="intro-label">
                <span></span>
                Suivi prospect
            </div>
            <h1>{{ $prospect->name }}</h1>
            <p>{{ $prospect->company_name ?? $prospect->phone ?? 'Prospect assigné' }}</p>
        </div>
        <div style="display:flex;gap:8px;align-items:center;">
            @if($prospect->isProspect())
                <form method="POST" action="{{ route('developer.prospects.convert', $prospect) }}" onsubmit="return confirm('Convertir ce prospect en client ? Son historique et ses commentaires sont conservés.');">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="top-btn primary"><i class="fa-solid fa-check" style="margin-right:4px;"></i> Rendre client</button>
                </form>
            @endif
            <a href="{{ route('developer.prospects.index') }}" class="top-btn"><i class="fa-solid fa-arrow-left" style="margin-right:4px;"></i> Retour</a>
        </div>
    </section>

    <section class="content content-reverse" style="align-items:start;">

        {{-- Left: prospect information --}}
        <div style="display:flex;flex-direction:column;gap:18px;">
            <div class="card">
                <div class="side-section">
                    <div class="side-heading">
                        <i class="fa-solid fa-user" style="color:var(--yellow);margin-right:5px;"></i>
                        Informations
                    </div>
                    <div style="display:flex;flex-direction:column;gap:14px;">
                        <div>
                            <div style="color:var(--muted);font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.7px;">Nom</div>
                            <div style="color:var(--text);font-size:14px;font-weight:700;">{{ $prospect->name }}</div>
                        </div>

                        <div>
                            <div style="color:var(--muted);font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.7px;">Statut</div>
                            @if($prospect->isClient())
                                <span class="admin-badge badge-client" style="font-size:12px;font-weight:700;">CLIENT</span>
                            @else
                                <span class="admin-badge badge-pending" style="font-size:12px;font-weight:700;">PROSPECT</span>
                            @endif
                        </div>

                        @if($prospect->phone)
                            <div>
                                <div style="color:var(--muted);font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.7px;">Téléphone</div>
                                <div style="color:var(--text-muted);font-size:14px;">{{ $prospect->phone }}</div>
                            </div>
                        @endif
                        @if($prospect->whatsapp)
                            <div>
                                <div style="color:var(--muted);font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.7px;">WhatsApp</div>
                                <div style="color:var(--text-muted);font-size:14px;">{{ $prospect->whatsapp }}</div>
                            </div>
                        @endif
                        @if($prospect->company_name)
                            <div>
                                <div style="color:var(--muted);font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.7px;">Entreprise</div>
                                <div style="color:var(--text-muted);font-size:14px;">{{ $prospect->company_name }}</div>
                            </div>
                        @endif
                        @if($prospect->city || $prospect->country)
                            <div>
                                <div style="color:var(--muted);font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.7px;">Localisation</div>
                                <div style="color:var(--text-muted);font-size:14px;">{{ $prospect->city }}{{ $prospect->city && $prospect->country ? ', ' : '' }}{{ $prospect->country }}</div>
                            </div>
                        @endif
                        <div>
                            <div style="color:var(--muted);font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.7px;">Développeur responsable</div>
                            <div style="color:var(--text-muted);font-size:14px;">{{ $prospect->assignedDeveloper->name ?? '—' }}</div>
                        </div>
                        <div>
                            <div style="color:var(--muted);font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.7px;">Dernière activité</div>
                            <div style="color:var(--text-muted);font-size:14px;">{{ $prospect->last_activity_at?->format('d/m/Y à H:i') ?? 'Aucun suivi enregistré' }}</div>
                        </div>
                        <div>
                            <div style="color:var(--muted);font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.7px;">Inscrit le</div>
                            <div style="color:var(--text-muted);font-size:14px;">{{ $prospect->created_at->format('d/m/Y à H:i') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right: follow-up, comments, projects --}}
        <div style="display:flex;flex-direction:column;gap:18px;">

            {{-- Next action --}}
            <div class="card">
                <div class="card-header">
                    <div>
                        <div class="card-title">
                            <i class="fa-solid fa-calendar-check" style="color:var(--yellow);margin-right:6px;"></i>
                            Prochaine action
                        </div>
                        <div class="card-subtitle">Planifiez le prochain contact</div>
                    </div>
                    @if($prospect->isFollowUpOverdue())
                        <span class="admin-badge badge-submitted">EN RETARD</span>
                    @elseif($prospect->isFollowUpDueToday())
                        <span class="admin-badge badge-pending">AUJOURD'HUI</span>
                    @elseif($prospect->hasFollowUp())
                        <span class="admin-badge badge-user">{{ $prospect->next_action_date->format('d/m/Y') }}</span>
                    @else
                        <span class="admin-badge badge-user">NON PLANIFIÉE</span>
                    @endif
                </div>

                <div style="padding:18px 22px;">
                    <form method="POST" action="{{ route('developer.prospects.follow-up.update', $prospect) }}">
                        @csrf
                        @method('PATCH')
                        <div style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
                            <div style="flex:1;min-width:220px;">
                                <label class="admin-label" for="next_action">Action à mener</label>
                                <input type="text" id="next_action" name="next_action" class="admin-input" placeholder="Appeler le prospect, envoyer le devis..." value="{{ old('next_action', $prospect->next_action) }}">
                                @error('next_action')<div style="color:#dc2626;font-size:12px;margin-top:6px;">{{ $message }}</div>@enderror
                            </div>
                            <div style="min-width:180px;">
                                <label class="admin-label" for="next_action_date">Date prévue</label>
                                <input type="date" id="next_action_date" name="next_action_date" class="admin-input" value="{{ old('next_action_date', $prospect->next_action_date?->format('Y-m-d')) }}">
                                @error('next_action_date')<div style="color:#dc2626;font-size:12px;margin-top:6px;">{{ $message }}</div>@enderror
                            </div>
                            <div>
                                <button type="submit" class="admin-btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Enregistrer</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Comments / history --}}
            <div class="card">
                <div class="card-header">
                    <div>
                        <div class="card-title">
                            <i class="fa-solid fa-comments" style="color:var(--yellow);margin-right:6px;"></i>
                            Commentaires de suivi
                        </div>
                        <div class="card-subtitle">{{ $prospect->comments->count() }} commentaire{{ $prospect->comments->count() > 1 ? 's' : '' }}</div>
                    </div>
                </div>

                <div style="padding:18px 22px;border-bottom:1px solid var(--line);">
                    <form method="POST" action="{{ route('developer.prospects.comments.store', $prospect) }}">
                        @csrf
                        <textarea name="content" class="admin-input" rows="3" placeholder="Ajouter un suivi..." required style="height:auto;min-height:80px;padding:12px 16px;resize:vertical;">{{ old('content') }}</textarea>
                        @error('content')<div style="color:#dc2626;font-size:12px;margin-top:4px;">{{ $message }}</div>@enderror
                        <div style="display:flex;justify-content:flex-end;margin-top:10px;">
                            <button type="submit" class="admin-btn btn-primary btn-sm">
                                <i class="fa-solid fa-paper-plane" style="font-size:11px;"></i> Ajouter le suivi
                            </button>
                        </div>
                    </form>
                </div>

                @if($prospect->comments->isEmpty())
                    <div class="empty-state" style="padding:36px 22px;">
                        <i class="fa-regular fa-comment-dots"></i>
                        <p>Aucun commentaire pour le moment.</p>
                    </div>
                @else
                    <div style="padding:18px 22px;display:flex;flex-direction:column;gap:0;">
                        @foreach($prospect->comments as $comment)
                            <div style="display:flex;gap:12px;position:relative;padding-bottom:18px;{{ !$loop->last ? 'padding-left:14px;border-left:2px solid var(--line);margin-left:5px;' : 'padding-left:14px;margin-left:5px;'}}">
                                <div style="position:absolute;left:-7px;top:2px;width:12px;height:12px;border-radius:50%;background:var(--yellow);border:2px solid white;flex-shrink:0;z-index:1;"></div>
                                <div style="flex:1;min-width:0;">
                                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px;flex-wrap:wrap;">
                                        <span style="font-size:13px;font-weight:700;color:var(--text);">{{ $comment->user->name ?? '—' }}</span>
                                        <span style="font-size:11px;color:var(--muted);">{{ $comment->created_at->format('d/m/Y à H:i') }}</span>
                                    </div>
                                    <div style="font-size:13px;color:var(--text);line-height:1.6;white-space:pre-wrap;">{{ $comment->content }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Projects --}}
            <div class="card">
                <div class="card-header">
                    <div>
                        <div class="card-title">
                            <i class="fa-solid fa-folder-open" style="color:var(--yellow);margin-right:6px;"></i>
                            Projets
                        </div>
                        <div class="card-subtitle">{{ $prospect->projects->count() }} projet{{ $prospect->projects->count() > 1 ? 's' : '' }}</div>
                    </div>
                </div>

                @if($prospect->projects->isEmpty())
                    <div class="empty-state">
                        <i class="fa-regular fa-folder-open"></i>
                        <p>Aucun projet soumis.</p>
                    </div>
                @else
                    <div style="padding:14px 22px;display:flex;flex-direction:column;gap:10px;">
                        @foreach($prospect->projects as $project)
                            @php $projectStatus = \App\Models\Project::getStatusConfig($project->status); @endphp
                            <div style="display:flex;align-items:center;gap:14px;padding:14px 18px;border-radius:6px;border:1px solid var(--line);min-width:0;">
                                <div style="width:40px;height:40px;border-radius:6px;background:var(--yellow);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                    <i class="fa-solid fa-folder" style="color:var(--text);font-size:14px;"></i>
                                </div>
                                <div style="flex:1;min-width:0;overflow:hidden;">
                                    <div style="font-size:15px;font-weight:700;color:var(--text);margin-bottom:3px;">Projet {{ $project->id }}</div>
                                    <div style="font-size:13px;color:var(--text-secondary);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $project->type_labels }}</div>
                                </div>
                                <span class="admin-badge {{ $projectStatus['class'] }}">{{ $projectStatus['label'] }}</span>
                                <span class="date">{{ $project->created_at->format('d/m/Y') }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

        </div>

    </section>

</x-layouts.admin>
