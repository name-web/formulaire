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
            {{-- Answers grouped by section --}}
            @if($answers->isEmpty())
                <div class="admin-card">
                    <div style="text-align:center;padding:36px 0;">
                        <i class="fa-solid fa-clipboard-question" style="font-size:32px;color:var(--text-muted);margin-bottom:12px;display:block;"></i>
                        <p style="color:var(--text-muted);font-size:13px;">Aucune réponse enregistrée.</p>
                    </div>
                </div>
            @else
                @foreach($groupedAnswers as $sectionLabel => $sectionAnswers)
                    <div class="admin-card">
                        <h3 style="font-size:15px;font-weight:800;color:white;margin-bottom:18px;">
                            <i class="fa-solid {{ \App\Models\Project::getSectionIcon($sectionLabel) }}" style="color:var(--blue-light);margin-right:8px;"></i>
                            {{ $sectionLabel }}
                            <span style="font-size:11px;color:var(--text-muted);font-weight:600;margin-left:8px;">{{ $sectionAnswers->count() }}</span>
                        </h3>

                        @foreach($sectionAnswers as $item)
                            @php
                                $question = $item['question'];
                                $answer = $item['answer'];
                                $displayValue = $item['displayValue'];
                                $selectValues = $item['selectValues'];
                            @endphp
                            <div style="padding:14px 0;{{ !$loop->last ? 'border-bottom:1px solid rgba(255,255,255,0.06);' : '' }}">
                                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;">
                                    <div style="font-size:13px;font-weight:700;color:rgba(255,255,255,0.85);">
                                        {{ $question->title }}
                                        @if($question->is_required)
                                            <span style="color:#f87171;font-size:10px;margin-left:4px;">*</span>
                                        @endif
                                    </div>
                                    <span class="admin-badge badge-user" style="font-size:9px;">{{ $question->type }}</span>
                                </div>
                                <div style="font-size:14px;color:rgba(255,255,255,0.75);line-height:1.6;">
                                    @if(!empty($displayValue))
                                        {{-- Multiselect: display as badges --}}
                                        @if($question->type === 'multiselect' && count($selectValues) > 1)
                                            <div style="display:flex;flex-wrap:wrap;gap:6px;">
                                                @foreach($selectValues as $tag)
                                                    <span class="admin-badge badge-in_progress" style="font-size:11px;">{{ $tag }}</span>
                                                @endforeach
                                            </div>

                                        {{-- Select: single badge --}}
                                        @elseif($question->type === 'select')
                                            <span class="admin-badge badge-pending" style="font-size:12px;">{{ $displayValue }}</span>

                                        {{-- Yes/No: icon + label --}}
                                        @elseif($question->type === 'yes_no')
                                            @php $yesVal = strtolower((string) $answer->text_value); @endphp
                                            @if($yesVal === 'yes' || $yesVal === 'oui')
                                                <span style="display:inline-flex;align-items:center;gap:6px;color:#34d399;font-weight:600;"><i class="fa-solid fa-check-circle"></i> Oui</span>
                                            @else
                                                <span style="display:inline-flex;align-items:center;gap:6px;color:#f87171;font-weight:600;"><i class="fa-solid fa-times-circle"></i> Non</span>
                                            @endif

                                        {{-- File: link to download --}}
                                        @elseif($question->type === 'file' && $answer->file_path)
                                            <a href="{{ asset('storage/' . $answer->file_path) }}" target="_blank" style="display:inline-flex;align-items:center;gap:6px;color:var(--blue-light);text-decoration:none;font-weight:600;">
                                                <i class="fa-solid fa-paperclip"></i> Fichier joint
                                            </a>

                                        {{-- Number: formatted --}}
                                        @elseif($question->type === 'number')
                                            <span style="font-weight:600;">{{ $displayValue }}</span>

                                        {{-- Default: plain text --}}
                                        @else
                                            {{ $displayValue }}
                                        @endif
                                    @else
                                        <span style="color:var(--text-muted);font-style:italic;font-size:12px;">Non répondu</span>
                                    @endif
                                </div>
                                @if(!empty($answer->other_value) && $question->type !== 'multiselect')
                                    <div style="margin-top:6px;font-size:12px;color:var(--text-muted);">
                                        Précision : <span style="color:rgba(255,255,255,0.7);">{{ $answer->other_value }}</span>
                                    </div>
                                @endif
                                @if($question->help_text)
                                    <div style="margin-top:4px;font-size:11px;color:var(--text-muted);">
                                        <i class="fa-solid fa-circle-info" style="margin-right:4px;"></i>{{ $question->help_text }}
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endforeach
            @endif

            {{-- Files --}}
            @if($project->files->isNotEmpty())
                <div class="admin-card">
                    <h3 style="font-size:15px;font-weight:800;color:white;margin-bottom:18px;">
                        <i class="fa-solid fa-paperclip" style="color:var(--blue-light);margin-right:8px;"></i>
                        Fichiers joints ({{ $project->files->count() }})
                    </h3>
                    <div style="display:flex;flex-direction:column;gap:8px;">
                        @foreach($project->files as $file)
                            <a href="{{ asset('storage/' . $file->stored_path) }}" target="_blank" style="display:flex;align-items:center;gap:12px;padding:12px 16px;border-radius:12px;background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.06);text-decoration:none;transition:all 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.07)'" onmouseout="this.style.background='rgba(255,255,255,0.04)'">
                                <i class="fa-solid fa-file" style="color:var(--blue-light);font-size:16px;"></i>
                                <div style="flex:1;">
                                    <div style="font-size:13px;color:rgba(255,255,255,0.85);font-weight:600;">{{ $file->original_name }}</div>
                                    <div style="font-size:11px;color:var(--text-muted);">{{ $file->mime_type }} — {{ round($file->size / 1024) }} KB</div>
                                </div>
                                <i class="fa-solid fa-arrow-up-right-from-square" style="color:var(--text-muted);font-size:12px;"></i>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <div>
            {{-- Status Management --}}
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
                        @foreach(config('project_status.statuses') as $key => $sc)
                            <option value="{{ $key }}" {{ $project->status === $key ? 'selected' : '' }}>{{ $sc['label'] }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="admin-btn btn-primary" style="width:100%;justify-content:center;"><i class="fa-solid fa-check"></i> Mettre à jour</button>
                </form>

                <div style="margin-top:16px;padding-top:14px;border-top:1px solid rgba(255,255,255,0.06);">
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
                        @if($project->client->whatsapp)
                            <div>
                                <span style="color:var(--text-muted);font-size:10px;text-transform:uppercase;letter-spacing:0.06em;">WhatsApp</span>
                                <div style="color:rgba(255,255,255,0.75);">{{ $project->client->whatsapp }}</div>
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

            {{-- Project Summary --}}
            <div class="admin-card">
                <h3 style="font-size:15px;font-weight:800;color:white;margin-bottom:16px;">
                    <i class="fa-solid fa-chart-simple" style="color:var(--blue-light);margin-right:8px;"></i>
                    Résumé
                </h3>
                <div style="display:flex;flex-direction:column;gap:8px;font-size:12px;">
                    <div style="display:flex;justify-content:space-between;">
                        <span style="color:var(--text-muted);">Statut</span>
                        @php $sc = \App\Models\Project::getStatusConfig($project->status); @endphp
                        <span class="admin-badge {{ $sc['class'] }}" style="font-size:10px;">{{ $sc['label'] }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;">
                        <span style="color:var(--text-muted);">Réponses</span>
                        <span style="color:rgba(255,255,255,0.75);font-weight:600;">{{ $project->answers_count }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;">
                        <span style="color:var(--text-muted);">Fichiers</span>
                        <span style="color:rgba(255,255,255,0.75);font-weight:600;">{{ $project->files->count() }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;">
                        <span style="color:var(--text-muted);">Sections</span>
                        <span style="color:rgba(255,255,255,0.75);font-weight:600;">{{ $groupedAnswers->count() }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;">
                        <span style="color:var(--text-muted);">Créé le</span>
                        <span style="color:rgba(255,255,255,0.75);">{{ $project->created_at->format('d/m/Y') }}</span>
                    </div>
                    @if($project->submitted_at)
                        <div style="display:flex;justify-content:space-between;">
                            <span style="color:var(--text-muted);">Soumis le</span>
                            <span style="color:rgba(255,255,255,0.75);">{{ $project->submitted_at->format('d/m/Y H:i') }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-layouts.admin>
