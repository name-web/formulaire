<x-layouts.admin :title="'Commerciaux'">

    <section class="intro">
        <div>
            <div class="intro-label">
                <span></span>
                Super Admin
            </div>
            <h1>Commerciaux</h1>
            <p>Créez un compte commercial et générez son mot de passe d'accès.</p>
        </div>
        <a href="{{ route('super_admin.dashboard') }}" class="top-btn"><i class="fa-solid fa-arrow-left" style="margin-right:4px;"></i> Vue globale</a>
    </section>

    {{-- Generated password (shown once, right after creation) --}}
    @if(session('generated_password'))
        <div class="card" style="border:1px solid var(--yellow);margin-top:18px;">
            <div class="side-section">
                <div class="side-heading">
                    <i class="fa-solid fa-key" style="color:var(--yellow);margin-right:6px;"></i>
                    Mot de passe généré
                </div>
                <p style="color:var(--muted);font-size:14px;margin-bottom:14px;">
                    Compte de <strong style="color:var(--text);">{{ session('generated_password_for') }}</strong> —
                    ce mot de passe n'est affiché qu'une seule fois, communiquez-le maintenant au commercial.
                </p>
                <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
                    <code id="generatedPassword" style="background:var(--navy-2);color:var(--yellow);padding:12px 16px;border-radius:var(--radius);font-size:15px;font-weight:700;letter-spacing:.5px;">{{ session('generated_password') }}</code>
                    <button type="button" class="admin-btn btn-secondary btn-sm" id="copyPasswordBtn">
                        <i class="fa-regular fa-copy"></i> Copier
                    </button>
                </div>
                <p style="color:var(--muted);font-size:12px;margin-top:12px;">
                    Seule l'empreinte du mot de passe est stockée en base de données.
                </p>
            </div>
        </div>
    @endif

    {{-- Create commercial --}}
    <section class="stats" style="grid-template-columns:1fr;min-height:auto;margin-top:18px;">
        <div class="stat" style="border-right:0;min-height:auto;">
            <form method="POST" action="{{ route('super_admin.commercials.store') }}" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
                @csrf
                <div style="flex:1;min-width:240px;">
                    <label class="admin-label" for="commercial-name">Nom du commercial</label>
                    <input type="text" id="commercial-name" name="name" class="admin-input" placeholder="Nom du commercial" value="{{ old('name') }}" required>
                    @error('name')<div style="color:#dc2626;font-size:12px;margin-top:6px;">{{ $message }}</div>@enderror
                </div>
                <div>
                    <button type="submit" class="admin-btn btn-primary"><i class="fa-solid fa-user-plus"></i> Créer le commercial</button>
                </div>
            </form>
            <p style="color:var(--muted);font-size:12px;margin-top:12px;">
                Le nom suffit : un identifiant de connexion et un mot de passe sécurisé sont générés automatiquement.
            </p>
        </div>
    </section>

    {{-- Commercials table --}}
    <section class="content" style="grid-template-columns:1fr;">
        <div class="card">
            <div class="card-header">
                <div>
                    <div class="card-title">Tous les commerciaux</div>
                    <div class="card-subtitle">{{ $commercials->count() }} compte{{ $commercials->count() > 1 ? 's' : '' }}</div>
                </div>
            </div>

            @if($commercials->isEmpty())
                <div class="empty-state">
                    <i class="fa-regular fa-handshake"></i>
                    <p>Aucun commercial pour le moment.</p>
                </div>
            @else
                <div class="table-wrap">
                    <div class="table-head" style="grid-template-columns:1.4fr 1.3fr .7fr .7fr .8fr 52px;">
                        <div>Commercial</div>
                        <div>Identifiant technique</div>
                        <div>Prospects</div>
                        <div>Clients</div>
                        <div>En retard</div>
                        <div></div>
                    </div>

                    @foreach($commercials as $commercial)
                        <div class="table-row" style="grid-template-columns:1.4fr 1.3fr .7fr .7fr .8fr 52px;cursor:pointer;" onclick="window.location='{{ route('super_admin.commercials.show', $commercial) }}'">
                            <div class="client">
                                <div class="client-avatar">{{ strtoupper(substr($commercial->name, 0, 2)) }}</div>
                                <div>
                                    <div class="client-name">{{ $commercial->name }}</div>
                                </div>
                            </div>
                            <div class="col-meta" data-label="Identifiant">{{ $commercial->email }}</div>
                            <div class="col-info" data-label="Prospects">
                                <span class="admin-badge badge-in_progress">{{ $commercial->prospects_count }}</span>
                            </div>
                            <div class="col-info" data-label="Clients">
                                <span class="admin-badge badge-client">{{ $commercial->clients_count }}</span>
                            </div>
                            <div class="col-info" data-label="En retard">
                                <span class="admin-badge {{ $commercial->overdue_count > 0 ? 'badge-submitted' : 'badge-user' }}">{{ $commercial->overdue_count }}</span>
                            </div>
                            <a href="{{ route('super_admin.commercials.show', $commercial) }}" class="open" onclick="event.stopPropagation();">↗</a>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var copyBtn = document.getElementById('copyPasswordBtn');
            var password = document.getElementById('generatedPassword');

            if (!copyBtn || !password) return;

            copyBtn.addEventListener('click', function() {
                var text = password.textContent.trim();

                function done() {
                    copyBtn.innerHTML = '<i class="fa-solid fa-check"></i> Copié';
                    setTimeout(function() {
                        copyBtn.innerHTML = '<i class="fa-regular fa-copy"></i> Copier';
                    }, 2000);
                }

                if (navigator.clipboard) {
                    navigator.clipboard.writeText(text).then(done, done);
                } else {
                    var range = document.createRange();
                    range.selectNodeContents(password);
                    var selection = window.getSelection();
                    selection.removeAllRanges();
                    selection.addRange(range);
                    done();
                }
            });
        });
    </script>

</x-layouts.admin>
