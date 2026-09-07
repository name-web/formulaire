<x-layouts.app :title="__('Merci')">
    <style>
        @media (max-width: 600px) {
            #thankYouCard { padding: 32px 20px !important; }
            #thankYouCard h1 { font-size: 24px !important; }
        }
    </style>
    <main class="main-content" style="align-items:center;">
        <div class="content-card anim-scale" style="max-width:580px;text-align:center;padding:65px 60px;" id="thankYouCard">

            <div style="width:100px;height:100px;border-radius:50%;background:rgba(16,185,129,0.1);border:2px solid rgba(16,185,129,0.18);display:flex;align-items:center;justify-content:center;margin:0 auto 32px;position:relative;">
                <div style="font-size:44px;color:#ffb703;" class="anim-scale d1">
                    <i class="fa-solid fa-check"></i>
                </div>                    <div style="position:absolute;inset:-8px;border-radius:50%;border:2px solid rgba(255,183,3,0.1);animation:pulseRing 2s ease-out infinite;"></div>
                    <div style="position:absolute;inset:-16px;border-radius:50%;border:1px solid rgba(255,183,3,0.06);animation:pulseRing 2s ease-out infinite 0.5s;"></div>
            </div>

            <h1 style="font-size:34px;font-weight:800;color:white;margin:0 0 12px;letter-spacing:-0.03em;" class="anim-up d2">
                {{ __('Demande envoyée !') }}
            </h1>
            <p style="font-size:16px;color:var(--text-secondary);line-height:1.7;margin:0 0 44px;" class="anim-up d3">
                {{ __('Merci pour votre confiance. Notre équipe examinera votre demande et vous recontactera très prochainement.') }}
            </p>

            <div style="display:flex;flex-direction:column;gap:12px;margin-bottom:44px;" class="anim-up d4">
                <div style="padding:18px;border-radius:var(--radius-sm);background:rgba(255,255,255,0.035);border:1px solid rgba(255,255,255,0.08);display:flex;align-items:center;gap:16px;text-align:left;">                        <div style="width:44px;height:44px;border-radius:12px;background:rgba(255,183,3,0.12);display:flex;align-items:center;justify-content:center;font-size:17px;color:#ffb703;flex-shrink:0;">
                            <i class="fa-solid fa-clock"></i>
                    </div>
                    <div>
                        <div style="font-size:10px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.06em;margin-bottom:3px;">{{ __('Délai de réponse') }}</div>
                        <div style="font-size:13px;color:rgba(255,255,255,0.85);">{{ __('Nous vous répondrons sous 24 à 48 heures') }}</div>
                    </div>
                </div>
            </div>

            <div class="anim-up d5">
                <a href="{{ route('welcome') }}" class="btn-continue" style="text-decoration:none;">
                    <i class="fa-solid fa-arrow-left"></i> {{ __('Retour à l\'accueil') }}
                </a>
            </div>

        </div>
    </main>
</x-layouts.app>