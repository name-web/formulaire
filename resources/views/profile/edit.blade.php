<x-layouts.app :title="'Profil'">

    <main class="main-content">
        <div class="content-card anim-scale">

            <div class="top">
                <div class="eyebrow">VOTRE PROFIL</div>
            </div>

            <h1 class="question-title anim-up d1">
                Profil
            </h1>
            <p class="question-desc anim-up d2">Gérez vos informations personnelles.</p>

            <div class="anim-up d3" style="padding:24px;border-radius:var(--radius);background:rgba(255,255,255,0.035);border:1px solid rgba(255,255,255,0.08);margin-top:12px;">
                @include('profile.partials.update-profile-information-form')
            </div>

            <div class="anim-up d4" style="padding:24px;border-radius:var(--radius);background:rgba(255,255,255,0.035);border:1px solid rgba(255,255,255,0.08);margin-top:12px;">
                @include('profile.partials.update-password-form')
            </div>

            <div class="anim-up d5" style="padding:24px;border-radius:var(--radius);background:rgba(255,255,255,0.035);border:1px solid rgba(255,255,255,0.08);margin-top:12px;">
                @include('profile.partials.delete-user-form')
            </div>

        </div>
    </main>

</x-layouts.app>
