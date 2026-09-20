<x-layouts.admin :title="'Client — ' . $client->name">

    <section class="intro">
        <div>
            <div class="intro-label">
                <span></span>
                Détail
            </div>
            <h1 class="client-name-is-client">{{ $client->name }}</h1>
            <p>{{ $client->phone }}</p>
        </div>
        <div style="display:flex;gap:8px;align-items:center;">
            <a href="{{ route('admin.quotes.create', ['client_id' => $client->id]) }}" class="top-btn primary"><i class="fa-solid fa-file-invoice" style="margin-right:4px;"></i> Créer un devis</a>
            <a href="{{ route('admin.client-clients.index') }}" class="top-btn"><i class="fa-solid fa-arrow-left" style="margin-right:4px;"></i> Retour</a>
        </div>
    </section>

    <section class="content content-reverse" style="align-items:start;">

        {{-- Left: Client info --}}
        <div style="display:flex;flex-direction:column;gap:18px;">
            <div class="card">
                <div class="side-section">
                    <div class="side-heading side-heading-client">
                        <i class="fa-solid fa-user" style="color:currentColor;margin-right:5px;"></i>
                        Informations
                    </div>
                    <div style="display:flex;flex-direction:column;gap:14px;">
                        <div>
                            <div style="color:var(--muted);font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.7px;">Nom</div>
                            <div style="color:var(--text);font-size:14px;font-weight:700;">{{ $client->name }}</div>
                        </div>

                        <div>
                            <div style="color:var(--muted);font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.7px;">Statut</div>
                            <span class="admin-badge badge-client" style="font-size:12px;font-weight:700;">CLIENT</span>
                        </div>

                        @if($client->phone)
                            <div>
                                <div style="color:var(--muted);font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.7px;">Téléphone</div>
                                <div style="color:var(--text-muted);font-size:14px;">{{ $client->phone }}</div>
                            </div>
                        @endif
                        @if($client->whatsapp)
                            <div>
                                <div style="color:var(--muted);font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.7px;">WhatsApp</div>
                                <div style="color:var(--text-muted);font-size:14px;">{{ $client->whatsapp }}</div>
                            </div>
                        @endif
                        @if($client->company_name)
                            <div>
                                <div style="color:var(--muted);font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.7px;">Entreprise</div>
                                <div style="color:var(--text-muted);font-size:14px;">{{ $client->company_name }}</div>
                            </div>
                        @endif
                        @if($client->city || $client->country)
                            <div>
                                <div style="color:var(--muted);font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.7px;">Localisation</div>
                                <div style="color:var(--text-muted);font-size:14px;">{{ $client->city }}{{ $client->city && $client->country ? ', ' : '' }}{{ $client->country }}</div>
                            </div>
                        @endif
                        <div>
                            <div style="color:var(--muted);font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.7px;">Adresse IP</div>
                            <div style="color:var(--text-muted);font-size:14px;">{{ $client->ip_address ?? 'Non disponible' }}</div>
                        </div>
                        <div>
                            <div style="color:var(--muted);font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.7px;">Inscrit le</div>
                            <div style="color:var(--text-muted);font-size:14px;">{{ $client->created_at?->format('d/m/Y à H:i') ?? '—' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right: Projects + Comments --}}
        <div style="display:flex;flex-direction:column;gap:18px;">
            <div class="card">
                <div class="card-header">
                    <div>
                        <div class="card-title">
                            <i class="fa-solid fa-folder-open" style="color:#176139;margin-right:6px;"></i>
                            Projets
                        </div>
                        <div class="card-subtitle">{{ $client->projects->count() }} projet{{ $client->projects->count() > 1 ? 's' : '' }}</div>
                    </div>
                </div>

                @if($client->projects->isEmpty())
                    <div class="empty-state">
                        <i class="fa-regular fa-folder-open"></i>
                        <p>Aucun projet soumis.</p>
                    </div>
                @else
                    <div style="padding:14px 22px;display:flex;flex-direction:column;gap:10px;">
                        @foreach($client->projects as $project)
                            @php
                                $typeLabels = $project->type_labels;
                                $sc = \App\Models\Project::getStatusConfig($project->status);
                            @endphp
                            <a href="{{ route('admin.projects.show', $project) }}" style="display:flex;align-items:center;gap:14px;padding:14px 18px;border-radius:6px;border:1px solid var(--line);text-decoration:none;transition:all .2s;min-width:0;">
                                <div style="width:40px;height:40px;border-radius:6px;background:#176139;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                    <i class="fa-solid fa-folder" style="color:#fff;font-size:14px;"></i>
                                </div>
                                <div style="flex:1;min-width:0;overflow:hidden;">
                                    <div style="font-size:15px;font-weight:700;color:var(--text);margin-bottom:3px;">Projet {{ $project->id }}</div>
                                    <div style="font-size:13px;color:var(--text-secondary);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $typeLabels }}</div>
                                </div>
                                <span class="admin-badge {{ $sc['class'] }}">{{ $sc['label'] }}</span>
                                <span class="date">{{ $project->created_at?->format('d/m/Y') ?? '—' }}</span>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Comments --}}
            <div class="card">
                <div class="card-header">
                    <div>
                        <div class="card-title">
                            <i class="fa-solid fa-comments" style="color:#176139;margin-right:6px;"></i>
                            Commentaires de suivi
                        </div>
                        <div class="card-subtitle">{{ $client->comments->count() }} commentaire{{ $client->comments->count() > 1 ? 's' : '' }}</div>
                    </div>
                </div>

                {{-- Add comment form --}}
                <div style="padding:18px 22px;border-bottom:1px solid var(--line);">
                    <form method="POST" action="/admin/clients/{{ $client->id }}/comments">
                        @csrf
                        <textarea name="content" class="admin-input" rows="3" placeholder="Ajouter un commentaire de suivi..." required style="height:auto;min-height:80px;padding:12px 16px;resize:vertical;">{{ old('content') }}</textarea>
                        @error('content')<div style="color:#dc2626;font-size:12px;margin-top:4px;">{{ $message }}</div>@enderror
                        <div style="display:flex;justify-content:flex-end;margin-top:10px;">
                            <button type="submit" class="admin-btn btn-primary btn-sm">
                                <i class="fa-solid fa-paper-plane" style="font-size:11px;"></i> Ajouter le commentaire
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Comments timeline --}}
                @if($client->comments->isEmpty())
                    <div class="empty-state" style="padding:36px 22px;">
                        <i class="fa-regular fa-comment-dots"></i>
                        <p>Aucun commentaire pour le moment.</p>
                    </div>
                @else
                    <div style="padding:18px 22px;display:flex;flex-direction:column;gap:0;">
                        @foreach($client->comments as $comment)
                            <div style="display:flex;gap:12px;position:relative;padding-bottom:18px;{{ !$loop->last ? 'padding-left:14px;border-left:2px solid var(--line);margin-left:5px;' : 'padding-left:14px;margin-left:5px;'}}">
                                <div style="position:absolute;left:-7px;top:2px;width:12px;height:12px;border-radius:50%;background:#176139;border:2px solid white;flex-shrink:0;z-index:1;"></div>
                                <div style="flex:1;min-width:0;">
                                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px;flex-wrap:wrap;">
                                        <span style="font-size:13px;font-weight:700;color:var(--text);">{{ $comment->user->name }}</span>
                                        <span style="font-size:11px;color:var(--muted);">{{ $comment->created_at->format('d/m/Y à H:i') }}</span>
                                        @if($comment->user_id === auth()->id())
                                            <form method="POST" action="/admin/clients/{{ $client->id }}/comments/{{ $comment->id }}" style="margin-left:auto;" onsubmit="return confirm('Supprimer ce commentaire ?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" style="background:none;border:none;color:var(--muted);cursor:pointer;font-size:12px;padding:2px 4px;transition:color .2s;" onmouseover="this.style.color='#dc2626'" onmouseout="this.style.color='var(--muted)'">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                    <div style="font-size:13px;color:var(--text);line-height:1.6;white-space:pre-wrap;">{{ $comment->content }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

    </section>

</x-layouts.admin>
