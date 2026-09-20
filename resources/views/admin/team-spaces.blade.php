<x-layouts.admin :title="'Espaces équipe'">

    <section class="intro">
        <div>
            <div class="intro-label">
                <span></span>
                Admin
            </div>
            <h1>Espaces équipe</h1>
            <p>Accédez aux espaces de connexion des développeurs et commerciaux.</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="top-btn"><i class="fa-solid fa-arrow-left" style="margin-right:4px;"></i> Dashboard</a>
    </section>

    <section class="stats" style="grid-template-columns:repeat(2,1fr);">
        <article class="stat main-stat" style="cursor:pointer;" onclick="window.location='{{ route('developer.login') }}'">
            <div class="stat-label">Espace développeur</div>
            <div class="stat-number" style="font-size:28px;">
                <i class="fa-solid fa-laptop-code" style="margin-right:8px;"></i> ESPACE DÉVELOPPEUR
            </div>
            <div class="stat-info">Connexion développeur</div>
        </article>
        <article class="stat" style="cursor:pointer;" onclick="window.location='{{ route('commercial.login') }}'">
            <div class="stat-label">Espace commercial</div>
            <div class="stat-number" style="font-size:28px;">
                <i class="fa-solid fa-handshake" style="margin-right:8px;"></i> ESPACE COMMERCIAL
            </div>
            <div class="stat-info">Connexion commercial</div>
        </article>
    </section>

</x-layouts.admin>
