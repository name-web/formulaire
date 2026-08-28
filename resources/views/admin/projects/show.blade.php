<x-layouts.admin :title="'Projet #' . $project->id">
    <div class="admin-header">
        <div>
            <h1 class="admin-title">Projet #{{ $project->id }}</h1>
            <p class="admin-subtitle">{{ $project->client->name ?? 'Client inconnu' }} — {{ $project->created_at->format('d/m/Y H:i') }}</p>
        </div>
        <div style="display:flex;gap:10px;">
            <a href="{{ route('admin.projects.index') }}" class="admin-btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> Retour</a>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:2fr 1fr;gap:24px;align-items:start;">
        <div>
            {{-- Answers --}}
            <div class="admin-card">
                <h3 style="font-size:15px;font-weight:800;color:white;margin-bottom:20px;">
                    <i class="fa-solid fa-clipboard-list" style="color:var(--blue-light);margin-right:8px;"></i>
                    Réponses du client
                </h3>

                @if($answers->isEmpty())
                    <p style="color:var(--text-muted);font-size:13px;text-align:center;padding:30px 0;">Aucune réponse enregistrée.</p>
                @else
                    @foreach($answers as $item)
                        @php
                            $question = $item['question'];
                            $answer = $item['answer'];
                            $displayValue = $item['displayValue'];
                        @endphp
                        <div style="padding:16px 0;{{ !$loop->last ? 'border-bottom:1px solid rgba(255,255,255,0.06);' : '' }}">
                            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
                                <div style="font-size:13px;font-weight:700;color:rgba(255,255,255,0.85);">
                                    {{ $question->title ?? 'Question inconnue' }}
                                    @if($question?->is_required)
                                        <span style="color:#f87171;font-size:10px;margin-left:4px;">*</span>
                                    @endif
                                </div>
                                <span class="admin-badge {{ match($question->type) {
                                    'text' => 'badge-in_progress',
                                    'textarea' => 'badge-in_progress',
                                    'select' => 'badge-pending',
                                    'multiselect' => 'badge-pending',
                                    'yes_no' => 'badge-completed',
                                    'file' => 'badge-admin',
                                    default => 'badge-user',
                                } }}" style="font-size:9px;">{{ $question->type }}</span>
                            </div>
                            <div style="font-size:14px;color:rgba(255,255,255,0.75);line-height:1.6;">
                                @if(!empty($displayValue))
                                    @if($question->type === 'multiselect' && str_contains($displayValue, ','))
                                        <div style="display:flex;flex-wrap:wrap;gap:6px;">
                                            @foreach(explode(', ', $displayValue) as $tag)
                                                <span class="admin-badge badge-in_progress" style="font-size:11px;">{{ $tag }}</span>
                                            @endforeach
                                        </div>
                                    @elseif($question->type === 'yes_no')
                                        @if($answer->text_value === 'yes')
                                            <span style="display:inline-flex;align-items:center;gap:6px;color:#34d399;"><i class="fa-solid fa-check-circle"></i> Oui</span>
                                        @else
                                            <span style="display:inline-flex;align-items:center;gap:6px;color:#f87171;"><i class="fa-solid fa-times-circle"></i> Non</span>
                                        @endif
                                    @elseif($question->type === 'file' && $answer->file_path)
                                        <span style="display:inline-flex;align-items:center;gap:6px;color:var(--blue-light);"><i class="fa-solid fa-paperclip"></i> Fichier joint</span>
                                    @else
                                        {{ $displayValue }}
                                    @endif
                                @else
                                    <span style="color:var(--text-muted);font-style:italic;">Non répondu</span>
                                @endif
                            </div>
                            @if(!empty($answer->other_value))
                                <div style="margin-top:6px;font-size:12px;color:var(--text-muted);">
                                    Précision : <span style="color:rgba(255,255,255,0.7);">{{ $answer->other_value }}</span>
                                </div>
                            @endif
                        </div>
                    @endforeach
                @endif
            </div>

            {{-- Files --}}
            @if($project->files->isNotEmpty())
                <div class="admin-card">
                    <h3 style="font-size:15px;font-weight:800;color:white;margin-bottom:20px;">
                        <i class="fa-solid fa-paperclip" style="color:var(--blue-light);margin-right:8px;"></i>
                        Fichiers joints
                    </h3>
                    <div style="display:flex;flex-direction:column;gap:8px;">
                        @foreach($project->files as $file)
                            <div style="display:flex;align-items:center;gap:12px;padding:12px 16px;border-radius:12px;background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.06);">
                                <i class="fa-solid fa-file" style="color:var(--blue-light);font-size:16px;"></i>
                                <div style="flex:1;">
                                    <div style="font-size:13px;color:rgba(255,255,255,0.85);font-weight:600;">{{ $file->original_name }}</div>
                                    <div style="font-size:11px;color:var(--text-muted);">{{ $file->mime_type }} — {{ round($file->size / 1024) }} KB</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <div>
            {{-- Status --}}
            <div class="admin-card">
                <h3 style="font-size:15px;font-weight:800;color:white;margin-bottom:16px;">
                    <i class="fa-solid fa-gear" style="color:var(--blue-light);margin-right:8px;"></i>
                    Gestion
                </h3>
                <form method="POST" action="{{ route('admin.projects.update-status', $project) }}">
                    @csrf
                    @method('PATCH')
                    <label class="admin-label">Statut du projet</label>
                    <select name="status" class="admin-input" style="margin-bottom:12px;">
                        <option value="pending" {{ $project->status === 'pending' ? 'selected' : '' }}>En attente</option>
                        <option value="in_progress" {{ $project->status === 'in_progress' ? 'selected' : '' }}>En cours</option>
                        <option value="completed" {{ $project->status === 'completed' ? 'selected' : '' }}>Terminé</option>
                        <option value="cancelled" {{ $project->status === 'cancelled' ? 'selected' : '' }}>Annulé</option>
                    </select>
                    <button type="submit" class="admin-btn btn-primary" style="width:100%;justify-content:center;"><i class="fa-solid fa-check"></i> Mettre à jour</button>
                </form>

                <div style="margin-top:20px;padding-top:16px;border-top:1px solid rgba(255,255,255,0.06);">
                    <form method="POST" action="{{ route('admin.projects.destroy', $project) }}" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce projet ?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="admin-btn btn-danger" style="width:100%;justify-content:center;"><i class="fa-solid fa-trash"></i> Supprimer</button>
                    </form>
                </div>
            </div>

            {{-- Client Info --}}
            <div class="admin-card">
                <h3 style="font-size:15px;font-weight:800;color:white;margin-bottom:16px;">
                    <i class="fa-solid fa-user" style="color:var(--blue-light);margin-right:8px;"></i>
                    Client
                </h3>
                @if($project->client)
                    <div style="display:flex;flex-direction:column;gap:10px;font-size:13px;">
                        <div>
                            <span style="color:var(--text-muted);font-size:10px;text-transform:uppercase;letter-spacing:0.06em;">Nom</span>
                            <div style="color:rgba(255,255,255,0.85);font-weight:600;">{{ $project->client->name }}</div>
                        </div>
                        <div>
                            <span style="color:var(--text-muted);font-size:10px;text-transform:uppercase;letter-spacing:0.06em;">Email</span>
                            <div style="color:rgba(255,255,255,0.75);">{{ $project->client->email }}</div>
                        </div>
                        @if($project->client->phone)
                            <div>
                                <span style="color:var(--text-muted);font-size:10px;text-transform:uppercase;letter-spacing:0.06em;">Téléphone</span>
                                <div style="color:rgba(255,255,255,0.75);">{{ $project->client->phone }}</div>
                            </div>
                        @endif
                        @if($project->client->company_name)
                            <div>
                                <span style="color:var(--text-muted);font-size:10px;text-transform:uppercase;letter-spacing:0.06em;">Entreprise</span>
                                <div style="color:rgba(255,255,255,0.75);">{{ $project->client->company_name }}</div>
                            </div>
                        @endif
                        @if($project->client->city || $project->client->country)
                            <div>
                                <span style="color:var(--text-muted);font-size:10px;text-transform:uppercase;letter-spacing:0.06em;">Localisation</span>
                                <div style="color:rgba(255,255,255,0.75);">{{ $project->client->city }}{{ $project->client->city && $project->client->country ? ', ' : '' }}{{ $project->client->country }}</div>
                            </div>
                        @endif
                    </div>
                    <a href="{{ route('admin.clients.show', $project->client) }}" class="admin-btn btn-secondary btn-sm" style="width:100%;justify-content:center;margin-top:16px;"><i class="fa-solid fa-arrow-right"></i> Voir le profil client</a>
                @else
                    <p style="color:var(--text-muted);font-size:13px;">Client inconnu</p>
                @endif
            </div>

            {{-- Project Type --}}
            <div class="admin-card">
                <h3 style="font-size:15px;font-weight:800;color:white;margin-bottom:16px;">
                    <i class="fa-solid fa-tags" style="color:var(--blue-light);margin-right:8px;"></i>
                    Type de projet
                </h3>
                @php
                    $types = is_array($project->project_type) ? $project->project_type : [$project->project_type];
                @endphp
                <div style="display:flex;flex-wrap:wrap;gap:6px;">
                    @foreach($types as $type)
                        @php $typeData = config("project_types.types.{$type}"); @endphp
                        @if($typeData)
                            <div style="display:flex;align-items:center;gap:8px;padding:8px 14px;border-radius:10px;background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);">
                                <i class="{{ $typeData['icon'] }}" style="color:{{ $typeData['color'] }};font-size:13px;"></i>
                                <span style="font-size:12px;color:rgba(255,255,255,0.8);font-weight:600;">{{ $typeData['label'] }}</span>
                            </div>
                        @else
                            <span class="admin-badge badge-user">{{ $type }}</span>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-layouts.admin>
