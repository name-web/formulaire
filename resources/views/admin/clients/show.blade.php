<x-layouts.admin :title="'Client — ' . $client->name">
    <div class="admin-header">
        <div>
            <h1 class="admin-title">{{ $client->name }}</h1>
            <p class="admin-subtitle">{{ $client->email }} — {{ $client->company_name }}</p>
        </div>
        <a href="{{ route('admin.clients.index') }}" class="admin-btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> Retour</a>
    </div>

    <div style="display:grid;grid-template-columns:1fr 2fr;gap:24px;align-items:start;">
        <div class="admin-card">
            <h3 style="font-size:15px;font-weight:800;color:white;margin-bottom:20px;">
                <i class="fa-solid fa-user" style="color:var(--blue-light);margin-right:8px;"></i>
                Informations
            </h3>
            <div style="display:flex;flex-direction:column;gap:14px;font-size:13px;">
                <div>
                    <span style="color:var(--text-muted);font-size:10px;text-transform:uppercase;letter-spacing:0.06em;">Nom</span>
                    <div style="color:rgba(255,255,255,0.85);font-weight:600;">{{ $client->name }}</div>
                </div>
                <div>
                    <span style="color:var(--text-muted);font-size:10px;text-transform:uppercase;letter-spacing:0.06em;">Email</span>
                    <div style="color:rgba(255,255,255,0.75);">{{ $client->email }}</div>
                </div>
                @if($client->phone)
                    <div>
                        <span style="color:var(--text-muted);font-size:10px;text-transform:uppercase;letter-spacing:0.06em;">Téléphone</span>
                        <div style="color:rgba(255,255,255,0.75);">{{ $client->phone }}</div>
                    </div>
                @endif
                @if($client->whatsapp)
                    <div>
                        <span style="color:var(--text-muted);font-size:10px;text-transform:uppercase;letter-spacing:0.06em;">WhatsApp</span>
                        <div style="color:rgba(255,255,255,0.75);">{{ $client->whatsapp }}</div>
                    </div>
                @endif
                @if($client->company_name)
                    <div>
                        <span style="color:var(--text-muted);font-size:10px;text-transform:uppercase;letter-spacing:0.06em;">Entreprise</span>
                        <div style="color:rgba(255,255,255,0.75);">{{ $client->company_name }}</div>
                    </div>
                @endif
                <div>
                    <span style="color:var(--text-muted);font-size:10px;text-transform:uppercase;letter-spacing:0.06em;">Localisation</span>
                    <div style="color:rgba(255,255,255,0.75);">{{ $client->city }}{{ $client->city && $client->country ? ', ' : '' }}{{ $client->country }}</div>
                </div>
                <div>
                    <span style="color:var(--text-muted);font-size:10px;text-transform:uppercase;letter-spacing:0.06em;">Inscrit le</span>
                    <div style="color:rgba(255,255,255,0.75);">{{ $client->created_at->format('d/m/Y à H:i') }}</div>
                </div>
            </div>
        </div>

        <div class="admin-card">
            <h3 style="font-size:15px;font-weight:800;color:white;margin-bottom:20px;">
                <i class="fa-solid fa-folder-open" style="color:var(--blue-light);margin-right:8px;"></i>
                Projets ({{ $client->projects->count() }})
            </h3>

            @if($client->projects->isEmpty())
                <p style="color:var(--text-muted);font-size:13px;text-align:center;padding:30px 0;">Aucun projet soumis.</p>
            @else
                <div style="display:flex;flex-direction:column;gap:12px;">
                    @foreach($client->projects as $project)
                        @php
                            $typeLabels = $project->type_labels;
                            $sc = \App\Models\Project::getStatusConfig($project->status);
                        @endphp
                        <a href="{{ route('admin.projects.show', $project) }}" style="display:flex;align-items:center;gap:16px;padding:16px;border-radius:14px;background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.06);text-decoration:none;transition:all 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.06)'" onmouseout="this.style.background='rgba(255,255,255,0.03)'">
                            <div style="width:42px;height:42px;border-radius:12px;background:rgba(255,183,3,0.12);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                <i class="fa-solid fa-folder" style="color:var(--blue-light);font-size:15px;"></i>
                            </div>
                            <div style="flex:1;">
                                <div style="font-size:14px;font-weight:700;color:rgba(255,255,255,0.85);margin-bottom:4px;">Projet #{{ $project->id }}</div>
                                <div style="font-size:12px;color:var(--text-secondary);">{{ $typeLabels }}</div>
                            </div>
                            <span class="admin-badge {{ $sc['class'] }}">{{ $sc['label'] }}</span>
                            <span style="font-size:11px;color:var(--text-muted);">{{ $project->created_at->format('d/m/Y') }}</span>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-layouts.admin>
