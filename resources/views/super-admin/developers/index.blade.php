<x-layouts.admin :title="'Développeurs'">

    <section class="intro">
        <div>
            <div class="intro-label">
                <span></span>
                Super Admin
            </div>
            <h1>Développeurs</h1>
            <p>Créez un compte développeur et générez son mot de passe d'accès.</p>
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
                    Compte de <strong style="color:var(--black);">{{ session('generated_password_for') }}</strong> —
                    ce mot de passe n'est affiché qu'une seule fois, communiquez-le maintenant au développeur.
                </p>
                <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
                    <code id="generatedPassword" style="background:var(--black);color:var(--yellow);padding:12px 16px;border-radius:5px;font-size:15px;font-weight:700;letter-spacing:.5px;">{{ session('generated_password') }}</code>
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

    {{-- Create developer --}}
    <section class="stats" style="grid-template-columns:1fr;min-height:auto;margin-top:18px;">
        <div class="stat" style="border-right:0;min-height:auto;">
            <form method="POST" action="{{ route('super_admin.developers.store') }}" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
                @csrf
                <div style="flex:1;min-width:240px;">
                    <label class="admin-label" for="developer-name">Nom du développeur</label>
                    <input type="text" id="developer-name" name="name" class="admin-input" placeholder="Nom du développeur" value="{{ old('name') }}" required>
                    @error('name')<div style="color:#dc2626;font-size:12px;margin-top:6px;">{{ $message }}</div>@enderror
                </div>
                <div>
                    <button type="submit" class="admin-btn btn-primary"><i class="fa-solid fa-user-plus"></i> Créer le développeur</button>
                </div>
            </form>
            <p style="color:var(--muted);font-size:12px;margin-top:12px;">
                Le nom suffit : un identifiant de connexion et un mot de passe sécurisé sont générés automatiquement.
            </p>
        </div>
    </section>

    {{-- Developers table --}}
    <section class="content" style="grid-template-columns:1fr;">
        <div class="card">
            <div class="card-header">
                <div>
                    <div class="card-title">Tous les développeurs</div>
                    <div class="card-subtitle">{{ $developers->count() }} compte{{ $developers->count() > 1 ? 's' : '' }}</div>
                </div>
            </div>

            @if($developers->isEmpty())
                <div class="empty-state">
                    <i class="fa-regular fa-laptop-code"></i>
                    <p>Aucun développeur pour le moment.</p>
                </div>
            @else
                <div class="table-wrap">
                    <div class="table-head" style="grid-template-columns:1.4fr 1.3fr .7fr .7fr .8fr 52px;">
                        <div>Développeur</div>
                        <div>Identifiant technique</div>
                        <div>Prospects</div>
                        <div>Clients</div>
                        <div>En retard</div>
                        <div></div>
                    </div>

                    @foreach($developers as $developer)
                        <div class="table-row" style="grid-template-columns:1.4fr 1.3fr .7fr .7fr .8fr 52px;cursor:pointer;" onclick="window.location='{{ route('super_admin.developers.show', $developer) }}'">
                            <div class="client">
                                <div class="client-avatar">{{ strtoupper(substr($developer->name, 0, 2)) }}</div>
                                <div>
                                    <div class="client-name">{{ $developer->name }}</div>
                                </div>
                            </div>
                            <div class="col-meta" data-label="Identifiant">{{ $developer->email }}</div>
                            <div class="col-info" data-label="Prospects">
                                <span class="admin-badge badge-in_progress">{{ $developer->prospects_count }}</span>
                            </div>
                            <div class="col-info" data-label="Clients">
                                <span class="admin-badge badge-client">{{ $developer->clients_count }}</span>
                            </div>
                            <div class="col-info" data-label="En retard">
                                <span class="admin-badge {{ $developer->overdue_count > 0 ? 'badge-submitted' : 'badge-user' }}">{{ $developer->overdue_count }}</span>
                            </div>
                            <a href="{{ route('super_admin.developers.show', $developer) }}" class="open" onclick="event.stopPropagation();">↗</a>
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
