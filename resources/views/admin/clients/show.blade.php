<x-layouts.admin :title="'Prospect — ' . $client->name">

@php
    /*
     * Cette fiche est partagée avec l'espace privé Super Admin, où elle sert de
     * page d'observation. $context garde les liens et les actions dans l'espace
     * depuis lequel la page a été ouverte (même convention que _board.blade.php).
     */
    $isSuperAdminContext = ($context ?? 'admin') === 'super_admin';
    $backRoute = $isSuperAdminContext ? 'super_admin.prospects.index' : 'admin.clients.index';
@endphp

    <section class="intro">
        <div>
            <div class="intro-label">
                <span></span>
                Détail
            </div>
            <h1>{{ $client->name }}</h1>
            <p>{{ $client->phone }}</p>
        </div>
        <div style="display:flex;gap:8px;align-items:center;">
            {{-- Actions de gestion : uniquement dans le dashboard général. --}}
            @if($client->isProspect() && ! $isSuperAdminContext)
                <a href="{{ route('admin.quotes.create', ['client_id' => $client->id]) }}" class="top-btn primary"><i class="fa-solid fa-file-invoice" style="margin-right:4px;"></i> Créer un devis</a>
                <button type="button" class="top-btn primary" id="convertBtn" style="background:#176139;color:#fff;border:none;"><i class="fa-solid fa-check" style="margin-right:4px;"></i> Rendre client</button>
                <form method="POST" action="{{ route('admin.clients.convert', $client) }}" id="convertForm" style="display:none;">
                    @csrf
                    @method('PATCH')
                </form>
            @endif
            <a href="{{ route($backRoute) }}" class="top-btn"><i class="fa-solid fa-arrow-left" style="margin-right:4px;"></i> Retour</a>
        </div>
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
                            <div style="color:var(--muted);font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.7px;">Statut</div>
                            <span class="admin-badge badge-pending" style="font-size:12px;font-weight:700;">PROSPECT</span>
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
                            <div style="color:var(--text-muted);font-size:14px;">{{ $client->created_at->format('d/m/Y à H:i') }}</div>
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
                                // Le détail projet n'existe que dans le dashboard général :
                                // depuis le Super Admin, la ligne reste consultative.
                                $projectTag = $isSuperAdminContext ? 'div' : 'a';
                            @endphp
                            <{{ $projectTag }} @if(! $isSuperAdminContext) href="{{ route('admin.projects.show', $project) }}" @endif style="display:flex;align-items:center;gap:14px;padding:14px 18px;border-radius:6px;border:1px solid var(--line);text-decoration:none;transition:all .2s;min-width:0;">
                                <div style="width:40px;height:40px;border-radius:6px;background:var(--yellow);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                    <i class="fa-solid fa-folder" style="color:var(--black);font-size:14px;"></i>
                                </div>
                                <div style="flex:1;min-width:0;overflow:hidden;">
                                    <div style="font-size:15px;font-weight:700;color:var(--black);margin-bottom:3px;">Projet {{ $project->id }}</div>
                                    <div style="font-size:13px;color:var(--text-secondary);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $typeLabels }}</div>
                                </div>
                                <span class="admin-badge {{ $sc['class'] }}">{{ $sc['label'] }}</span>
                                <span class="date">{{ $project->created_at->format('d/m/Y') }}</span>
                            </{{ $projectTag }}>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Comments --}}
            <div class="card">
                <div class="card-header">
                    <div>
                        <div class="card-title">
                            <i class="fa-solid fa-comments" style="color:var(--yellow);margin-right:6px;"></i>
                            Commentaires de suivi
                        </div>
                        <div class="card-subtitle">{{ $client->comments->count() }} commentaire{{ $client->comments->count() > 1 ? 's' : '' }}</div>
                    </div>
                </div>

                {{-- Add comment form : le Super Admin consulte l'historique sans l'alimenter. --}}
                @unless($isSuperAdminContext)
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
                @endunless

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
                                <div style="position:absolute;left:-7px;top:2px;width:12px;height:12px;border-radius:50%;background:var(--yellow);border:2px solid white;flex-shrink:0;z-index:1;"></div>
                                <div style="flex:1;min-width:0;">
                                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px;flex-wrap:wrap;">
                                        <span style="font-size:13px;font-weight:700;color:var(--black);">{{ $comment->user->name }}</span>
                                        <span style="font-size:11px;color:var(--muted);">{{ $comment->created_at->format('d/m/Y à H:i') }}</span>
                                        @if($comment->user_id === auth()->id() && ! $isSuperAdminContext)
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

    {{-- Confirm modal --}}
    <div id="confirmOverlay" style="position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:500;display:flex;align-items:center;justify-content:center;opacity:0;pointer-events:none;transition:opacity .3s ease;">
        <div id="confirmBox" style="background:#fff;border-radius:12px;padding:48px 52px;max-width:560px;width:92%;box-shadow:0 20px 50px rgba(0,0,0,.18);transform:scale(.85);transition:transform .4s cubic-bezier(.175,.885,.32,1.275),opacity .3s ease;opacity:0;">
            <div id="confirmContent">
                <div style="display:flex;align-items:center;gap:14px;margin-bottom:22px;">
                    <div style="width:44px;height:44px;border-radius:50%;background:#17613910;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="fa-solid fa-user-check" style="color:#176139;font-size:18px;"></i>
                    </div>
                    <div style="font-size:18px;font-weight:800;color:var(--black);">Rendre ce prospect client ?</div>
                </div>
                <p style="color:var(--muted);font-size:14px;line-height:1.7;margin-bottom:28px;">Cette action convertira <strong style="color:var(--black);">{{ $client->name }}</strong> en client. Le statut et l'identité visuelle seront mis à jour.</p>
                <div style="display:flex;gap:10px;justify-content:flex-end;">
                    <button type="button" id="confirmCancel" style="padding:11px 22px;border-radius:5px;border:1px solid var(--line);background:white;color:#666;font-size:14px;font-weight:700;cursor:pointer;transition:all .2s;">Annuler</button>
                    <button type="button" id="confirmOk" style="padding:11px 22px;border-radius:5px;border:none;background:#176139;color:#fff;font-size:14px;font-weight:700;cursor:pointer;transition:all .2s;">Confirmer</button>
                </div>
            </div>
            <div id="confirmSuccess" style="display:none;flex-direction:column;align-items:center;padding:40px 0 24px;">
                <svg id="checkSvg" width="120" height="120" viewBox="0 0 120 120" fill="none">
                    <path id="checkPath" d="M24 62L48 86L96 30" stroke="#176139" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <div id="successLabel" style="margin-top:24px;font-size:16px;font-weight:700;color:var(--black);opacity:0;transform:translateY(6px);">Prospect converti en client</div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var convertBtn = document.getElementById('convertBtn');
        var convertForm = document.getElementById('convertForm');
        var overlay = document.getElementById('confirmOverlay');
        var box = document.getElementById('confirmBox');
        var confirmOk = document.getElementById('confirmOk');
        var confirmCancel = document.getElementById('confirmCancel');
        var confirmContent = document.getElementById('confirmContent');
        var confirmSuccess = document.getElementById('confirmSuccess');
        var checkSvg = document.getElementById('checkSvg');
        var checkPath = document.getElementById('checkPath');
        var successLabel = document.getElementById('successLabel');

        if (!convertBtn) return;

        var isProcessing = false;

        function openModal() {
            overlay.style.opacity = '1';
            overlay.style.pointerEvents = 'auto';
            requestAnimationFrame(function() {
                box.style.opacity = '1';
                box.style.transform = 'scale(1)';
            });
        }

        function closeModal() {
            box.style.opacity = '0';
            box.style.transform = 'scale(.85)';
            overlay.style.opacity = '0';
            overlay.style.pointerEvents = 'none';
            setTimeout(function() {
                confirmContent.style.display = '';
                confirmSuccess.style.display = 'none';
                checkSvg.style.transition = 'none';
                checkSvg.style.transform = 'scale(0)';
                checkPath.style.transition = 'none';
                checkPath.style.strokeDashoffset = checkPath.getTotalLength();
                successLabel.style.transition = 'none';
                successLabel.style.opacity = '0';
                successLabel.style.transform = 'translateY(6px)';
            }, 400);
        }

        convertBtn.addEventListener('click', function() {
            openModal();
        });

        confirmCancel.addEventListener('click', function() {
            closeModal();
        });

        overlay.addEventListener('click', function(e) {
            if (e.target === overlay) closeModal();
        });

        confirmOk.addEventListener('click', function() {
            if (isProcessing) return;
            isProcessing = true;

            confirmOk.style.opacity = '.5';
            confirmOk.style.pointerEvents = 'none';
            confirmCancel.style.opacity = '.5';
            confirmCancel.style.pointerEvents = 'none';

            confirmContent.style.display = 'none';
            confirmSuccess.style.display = 'flex';

            checkSvg.style.transform = 'scale(0)';
            checkSvg.style.transition = 'none';

            var pathLen = checkPath.getTotalLength();
            checkPath.style.strokeDasharray = pathLen;
            checkPath.style.strokeDashoffset = pathLen;
            checkPath.style.transition = 'none';

            successLabel.style.opacity = '0';
            successLabel.style.transition = 'none';

            requestAnimationFrame(function() {
                requestAnimationFrame(function() {
                    checkSvg.style.transition = 'transform .4s cubic-bezier(.175,.885,.32,1.275)';
                    checkSvg.style.transform = 'scale(1)';

                    setTimeout(function() {
                        checkPath.style.transition = 'stroke-dashoffset .55s cubic-bezier(.4,0,.2,1)';
                        checkPath.style.strokeDashoffset = '0';
                    }, 120);

                    setTimeout(function() {
                        successLabel.style.transition = 'all .3s ease';
                        successLabel.style.opacity = '1';
                        successLabel.style.transform = 'translateY(0)';
                    }, 480);
                });
            });

            setTimeout(function() {
                convertForm.submit();

                var badge = document.querySelector('.badge-pending');
                if (badge) {
                    badge.className = 'admin-badge badge-client';
                    badge.textContent = 'CLIENT';
                    badge.style.cssText = 'background:#176139;color:#fff;font-size:12px;font-weight:700;';
                }
                convertBtn.style.display = 'none';
                convertForm.style.display = 'none';

                setTimeout(function() {
                    closeModal();
                    isProcessing = false;
                    confirmOk.style.opacity = '';
                    confirmOk.style.pointerEvents = '';
                    confirmCancel.style.opacity = '';
                    confirmCancel.style.pointerEvents = '';
                }, 800);
            }, 900);
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && overlay.style.pointerEvents === 'auto' && !isProcessing) {
                closeModal();
            }
        });
    });
    </script>

</x-layouts.admin>
