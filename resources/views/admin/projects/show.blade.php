<x-layouts.admin :title="'Projet '.$project->id">

            {{-- Intro --}}
    <section class="intro">
        <div>
            <div class="intro-label">
                <span></span>
                Détail
            </div>
            <h1>Projet {{ $project->id }}</h1>
            <p>{{ $project->client->name ?? 'Prospect inconnu' }} — {{ $project->created_at->format('d/m/Y H:i') }}</p>
            @php
                $types = is_array($project->project_type) ? $project->project_type : [$project->project_type];
                $typeLabels = collect($types)->map(fn ($t) => config("project_types.types.{$t}.label", $t))->implode(' / ');
            @endphp
            <div style="margin-top:8px;display:flex;gap:6px;flex-wrap:wrap;">
                @foreach($types as $type)
                    @php $typeData = config("project_types.types.{$type}"); @endphp
                    @if($typeData)
                        <span class="admin-badge badge-in_progress" style="font-size:12px;">
                            <i class="{{ $typeData['icon'] }}" style="margin-right:4px;"></i>{{ $typeData['label'] }}
                        </span>
                    @endif
                @endforeach
            </div>
        </div>
        <a href="{{ route('admin.projects.index') }}" class="top-btn"><i class="fa-solid fa-arrow-left" style="margin-right:4px;"></i> Retour</a>
    </section>

    {{-- Content grid --}}
    <section class="content" style="align-items:start;">

        {{-- Left: answers + files --}}
        <div style="display:flex;flex-direction:column;gap:18px;">

            @if($answersPaginator->isEmpty())
                <div class="card">
                    <div class="empty-state">
                        <i class="fa-regular fa-clipboard"></i>
                        <p>Aucune réponse enregistrée.</p>
                    </div>
                </div>
            @else
                <div style="margin-bottom:6px;color:var(--muted);font-size:13px;">
                    {{ $answersPaginator->total() }} réponse{{ $answersPaginator->total() > 1 ? 's' : '' }} — page {{ $answersPaginator->currentPage() }}/{{ $answersPaginator->lastPage() }}
                </div>
                @foreach($groupedAnswers as $sectionLabel => $sectionAnswers)
                    <div class="card">
                        <div class="card-header">
                            <div>
                                <div class="card-title">
                                    <i class="fa-solid {{ \App\Models\Project::getSectionIcon($sectionLabel) }}" style="color:var(--yellow);margin-right:6px;"></i>
                                    {{ $sectionLabel }}
                                </div>
                                <div class="card-subtitle">{{ $sectionAnswers->count() }} question{{ $sectionAnswers->count() > 1 ? 's' : '' }}</div>
                            </div>
                        </div>

                        @foreach($sectionAnswers as $item)
                            @php
                                $question = $item['question'];
                                $answer = $item['answer'];
                                $displayValue = $item['displayValue'];
                                $selectValues = $item['selectValues'];
                            @endphp
                            <div style="padding:16px 22px;{{ !$loop->last ? 'border-bottom:1px solid var(--line);' : '' }}">
                                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
                                    <div style="font-size:15px;font-weight:800;color:var(--black);">
                                        {{ $question->title }}
                                        @if($question->is_required)
                                            <span style="color:var(--yellow);margin-left:3px;">*</span>
                                        @endif
                                    </div>
                                    <span class="admin-badge badge-user">{{ $question->type }}</span>
                                </div>
                                <div style="font-size:14px;color:var(--text-muted);line-height:1.6;word-break:break-word;">
                                    @if(!empty($displayValue))
                                        @if($question->type === 'multiselect' && count($selectValues) >= 1)
                                            <div style="display:flex;flex-wrap:wrap;gap:6px;">
                                                @foreach($selectValues as $tag)
                                                    <span class="admin-badge badge-in_progress">{{ $tag }}</span>
                                                @endforeach
                                            </div>

                                        @elseif($question->type === 'select')
                                            <span class="admin-badge badge-pending">{{ $displayValue }}</span>

                                        @elseif($question->type === 'yes_no')
                                            @php $yesVal = strtolower((string) $answer->text_value); @endphp
                                            @if($yesVal === 'yes' || $yesVal === 'oui')
                                                <span style="display:inline-flex;align-items:center;gap:6px;color:var(--black);font-weight:600;"><i class="fa-solid fa-check-circle" style="color:var(--yellow);"></i> Oui</span>
                                            @else
                                                <span style="display:inline-flex;align-items:center;gap:6px;color:var(--muted);font-weight:600;"><i class="fa-solid fa-times-circle"></i> Non</span>
                                            @endif

                                        @elseif($question->type === 'file' && $answer->file_path)
                                            <a href="{{ asset('storage/' . $answer->file_path) }}" target="_blank" style="display:inline-flex;align-items:center;gap:6px;color:var(--yellow);text-decoration:none;font-weight:600;">
                                                <i class="fa-solid fa-paperclip"></i> Fichier joint
                                            </a>

                                        @elseif($question->type === 'number')
                                            <span style="font-weight:700;">{{ $displayValue }}</span>

                                        @else
                                            {{ $displayValue }}
                                        @endif
                                    @else
                                        <span style="color:var(--muted);font-style:italic;">Non répondu</span>
                                    @endif
                                </div>
                                @if(!empty($answer->other_value) && $question->type !== 'multiselect')
                                    <div style="margin-top:6px;font-size:13px;color:var(--muted);">
                                        Précision : {{ $answer->other_value }}
                                    </div>
                                @endif
                                @if($question->help_text)
                                    <div style="margin-top:5px;font-size:13px;color:var(--muted);">
                                        <i class="fa-solid fa-circle-info" style="margin-right:4px;"></i>{{ $question->help_text }}
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endforeach

                @if($answersPaginator->hasPages())
                    <div class="pagination">
                        @if($answersPaginator->onFirstPage())
                            <span class="disabled"><i class="fa-solid fa-chevron-left"></i></span>
                        @else
                            <a href="{{ $answersPaginator->previousPageUrl() }}"><i class="fa-solid fa-chevron-left"></i></a>
                        @endif

                        @foreach($answersPaginator->getUrlRange(max(1, $answersPaginator->currentPage() - 2), min($answersPaginator->lastPage(), $answersPaginator->currentPage() + 2)) as $page => $url)
                            <a href="{{ $url }}" class="{{ $page == $answersPaginator->currentPage() ? 'active' : '' }}">{{ $page }}</a>
                        @endforeach

                        @if($answersPaginator->hasMorePages())
                            <a href="{{ $answersPaginator->nextPageUrl() }}"><i class="fa-solid fa-chevron-right"></i></a>
                        @else
                            <span class="disabled"><i class="fa-solid fa-chevron-right"></i></span>
                        @endif
                    </div>
                @endif
            @endif

            {{-- Files --}}
            @if($project->files->isNotEmpty())
                <div class="card">
                    <div class="card-header">
                        <div>
                            <div class="card-title">
                                <i class="fa-solid fa-paperclip" style="color:var(--yellow);margin-right:6px;"></i>
                                Fichiers joints
                            </div>
                            <div class="card-subtitle">{{ $project->files->count() }} fichier{{ $project->files->count() > 1 ? 's' : '' }}</div>
                        </div>
                    </div>
                    <div style="padding:14px 22px;display:flex;flex-direction:column;gap:10px;">
                        @foreach($project->files as $file)
                            <a href="{{ asset('storage/' . $file->stored_path) }}" target="_blank" style="display:flex;align-items:center;gap:12px;padding:12px 16px;border-radius:5px;border:1px solid var(--line);text-decoration:none;transition:all .2s;min-width:0;">
                                <i class="fa-solid fa-file" style="color:var(--yellow);font-size:16px;flex-shrink:0;"></i>
                                <div style="flex:1;min-width:0;">
                                    <div style="font-size:14px;color:var(--black);font-weight:700;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $file->original_name }}</div>
                                    <div style="font-size:12px;color:var(--muted);">{{ $file->mime_type }} — {{ round($file->size / 1024) }} KB</div>
                                </div>
                                <i class="fa-solid fa-arrow-up-right-from-square" style="color:var(--muted);font-size:13px;"></i>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        {{-- Right panel --}}
        <div style="display:flex;flex-direction:column;gap:18px;position:sticky;top:90px;">

            {{-- Status Management --}}
            <div class="card">
                <div class="side-section">
                    <div class="side-heading">
                        <i class="fa-solid fa-gear" style="color:var(--yellow);margin-right:5px;"></i>
                        Gestion
                    </div>
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

                    <div style="margin-top:16px;padding-top:16px;border-top:1px solid var(--line);">
                        <form method="POST" action="{{ route('admin.projects.destroy', $project) }}" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce projet ?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="admin-btn btn-danger" style="width:100%;justify-content:center;"><i class="fa-solid fa-trash"></i> Supprimer</button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Prospect Info --}}
            <div class="card">
                <div class="side-section">
                    <div class="side-heading">
                        <i class="fa-solid fa-user" style="color:var(--yellow);margin-right:5px;"></i>
                        Prospect
                    </div>
                    @if($project->client)
                        <div style="display:flex;flex-direction:column;gap:12px;">
                            <div>
                                <div style="color:var(--muted);font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.7px;">Nom</div>
                                <div style="color:var(--black);font-size:14px;font-weight:700;">{{ $project->client->name }}</div>
                            </div>

                            @if($project->client->phone)
                                <div>
                                    <div style="color:var(--muted);font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.7px;">Téléphone</div>
                                    <div style="color:var(--text-muted);font-size:14px;">{{ $project->client->phone }}</div>
                                </div>
                            @endif
                            @if($project->client->whatsapp)
                                <div>
                                    <div style="color:var(--muted);font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.7px;">WhatsApp</div>
                                    <div style="color:var(--text-muted);font-size:14px;">{{ $project->client->whatsapp }}</div>
                                </div>
                            @endif
                            @if($project->client->company_name)
                                <div>
                                    <div style="color:var(--muted);font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.7px;">Entreprise</div>
                                    <div style="color:var(--text-muted);font-size:14px;">{{ $project->client->company_name }}</div>
                                </div>
                            @endif
                            @if($project->client->city || $project->client->country)
                                <div>
                                    <div style="color:var(--muted);font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.7px;">Localisation</div>
                                    <div style="color:var(--text-muted);font-size:14px;">{{ $project->client->city }}{{ $project->client->city && $project->client->country ? ', ' : '' }}{{ $project->client->country }}</div>
                                </div>
                            @endif
                        </div>
                        <a href="{{ route('admin.clients.show', $project->client) }}" class="quick-action" style="margin-top:16px;text-align:center;"><i class="fa-solid fa-arrow-right" style="margin-right:4px;"></i> Voir le profil prospect</a>
                    @else
                        <p style="color:var(--muted);font-size:14px;">Prospect inconnu</p>
                    @endif
                </div>
            </div>

            {{-- Project Type --}}
            <div class="card">
                <div class="side-section">
                    <div class="side-heading">
                        <i class="fa-solid fa-tags" style="color:var(--yellow);margin-right:5px;"></i>
                        Type de projet
                    </div>
                    @php
                        $types = is_array($project->project_type) ? $project->project_type : [$project->project_type];
                    @endphp
                    <div style="display:flex;flex-direction:column;gap:8px;">
                        @foreach($types as $type)
                            @php $typeData = config("project_types.types.{$type}"); @endphp
                            @if($typeData)
                                <div style="display:flex;align-items:center;gap:10px;padding:10px 14px;border-radius:5px;border:1px solid var(--line);">
                                    <i class="{{ $typeData['icon'] }}" style="color:var(--yellow);font-size:14px;"></i>
                                    <span style="font-size:14px;color:var(--black);font-weight:700;">{{ $typeData['label'] }}</span>
                                </div>
                            @else
                                <span class="admin-badge badge-user">{{ $type }}</span>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Project Summary --}}
            <div class="card">
                <div class="side-section">
                    <div class="side-heading">
                        <i class="fa-solid fa-chart-simple" style="color:var(--yellow);margin-right:5px;"></i>
                        Résumé
                    </div>
                    <div style="display:flex;flex-direction:column;gap:12px;">
                        <div style="display:flex;justify-content:space-between;align-items:center;">
                            <span style="color:var(--muted);font-size:13px;">Statut</span>
                            @php $sc = \App\Models\Project::getStatusConfig($project->status); @endphp
                            <span class="admin-badge {{ $sc['class'] }}">{{ $sc['label'] }}</span>
                        </div>
                        <div style="display:flex;justify-content:space-between;align-items:center;">
                            <span style="color:var(--muted);font-size:13px;">Réponses</span>
                            <span style="color:var(--text-muted);font-size:14px;font-weight:700;">{{ $answersPaginator->total() }}</span>
                        </div>
                        <div style="display:flex;justify-content:space-between;align-items:center;">
                            <span style="color:var(--muted);font-size:13px;">Fichiers</span>
                            <span style="color:var(--text-muted);font-size:14px;font-weight:700;">{{ $project->files->count() }}</span>
                        </div>
                        <div style="display:flex;justify-content:space-between;align-items:center;">
                            <span style="color:var(--muted);font-size:13px;">Sections</span>
                            <span style="color:var(--text-muted);font-size:14px;font-weight:700;">{{ $groupedAnswers->count() }}</span>
                        </div>
                        <div style="display:flex;justify-content:space-between;align-items:center;">
                            <span style="color:var(--muted);font-size:13px;">Créé le</span>
                            <span style="color:var(--text-muted);font-size:14px;">{{ $project->created_at->format('d/m/Y') }}</span>
                        </div>
                        @if($project->submitted_at)
                            <div style="display:flex;justify-content:space-between;align-items:center;">
                                <span style="color:var(--muted);font-size:13px;">Soumis le</span>
                                <span style="color:var(--text-muted);font-size:14px;">{{ $project->submitted_at->format('d/m/Y H:i') }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </section>

</x-layouts.admin>
