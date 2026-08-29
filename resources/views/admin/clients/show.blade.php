<x-layouts.admin :title="'Client — ' . $client->name">

    <section class="intro">
        <div>
            <div class="intro-label">
                <span></span>
                Détail
            </div>
            <h1>{{ $client->name }}</h1>
            <p>{{ $client->email }}{{ $client->company_name ? ' — ' . $client->company_name : '' }}</p>
        </div>
        <a href="{{ route('admin.clients.index') }}" class="top-btn"><i class="fa-solid fa-arrow-left" style="margin-right:4px;"></i> Retour</a>
    </section>

    <section class="content content-reverse" style="align-items:start;">

        {{-- Left: Client info --}}
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
                            <div style="color:var(--black);font-size:14px;font-weight:700;">{{ $client->name }}</div>
                        </div>
                        <div>
                            <div style="color:var(--muted);font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.7px;">Email</div>
                            <div style="color:var(--text-muted);font-size:14px;">{{ $client->email }}</div>
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
                        <div>
                            <div style="color:var(--muted);font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.7px;">Localisation</div>
                            <div style="color:var(--text-muted);font-size:14px;">{{ $client->city }}{{ $client->city && $client->country ? ', ' : '' }}{{ $client->country }}</div>
                        </div>
                        <div>
                            <div style="color:var(--muted);font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.7px;">Inscrit le</div>
                            <div style="color:var(--text-muted);font-size:14px;">{{ $client->created_at->format('d/m/Y à H:i') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right: Projects --}}
        <div style="display:flex;flex-direction:column;gap:18px;">
            <div class="card">
                <div class="card-header">
                    <div>
                        <div class="card-title">
                            <i class="fa-solid fa-folder-open" style="color:var(--yellow);margin-right:6px;"></i>
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
                                <div style="width:40px;height:40px;border-radius:6px;background:var(--yellow);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                    <i class="fa-solid fa-folder" style="color:var(--black);font-size:14px;"></i>
                                </div>
                                <div style="flex:1;min-width:0;overflow:hidden;">
                                    <div style="font-size:15px;font-weight:700;color:var(--black);margin-bottom:3px;">Projet {{ $project->id }}</div>
                                    <div style="font-size:13px;color:var(--text-secondary);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $typeLabels }}</div>
                                </div>
                                <span class="admin-badge {{ $sc['class'] }}">{{ $sc['label'] }}</span>
                                <span class="date">{{ $project->created_at->format('d/m/Y') }}</span>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

    </section>

</x-layouts.admin>
