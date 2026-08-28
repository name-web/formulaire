<x-layouts.app :title="'Récapitulatif'">

    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo">
                <img src="{{ asset('IMAGES/Logo dominium.png') }}" alt="DOMINIUM">
                <span>DOMINIUM TECH</span>
            </div>
        </div>

        <div class="sidebar-label">VOTRE PROGRESSION</div>

        <nav class="step-list">
            {{-- Questions du questionnaire --}}
            @if(!empty($summary['questions']))
                @foreach($summary['questions'] as $item)
                    @php $q = $item['question']; @endphp
                    <div class="step-item done">
                        <div class="step-dot done"><i class="fa-solid fa-check" style="font-size:11px;"></i></div>
                        <span class="step-label">{{ $q->title }}</span>
                    </div>
                @endforeach
            @endif
            <div class="step-item active">
                <div class="step-dot active"><i class="fa-solid fa-clipboard-check" style="font-size:10px;"></i></div>
                <span class="step-label">Récapitulatif</span>
                <i class="fa-solid fa-chevron-right step-arrow"></i>
            </div>
        </nav>

        <div class="progress-section">
            <div class="progress-header">
                <span class="progress-label">Progression</span>
                <span class="progress-value">{{ $progress['percentage'] }}%</span>
            </div>
            <div class="progress-bar-track">
                <div class="progress-bar-fill" style="width: {{ $progress['percentage'] }}%"></div>
            </div>
            <div class="progress-step-text">Récapitulatif</div>
        </div>
    </aside>


    <main class="main-content">
        <div class="content-card anim-scale">

            <div class="card-progress-top">
                <div class="card-progress-top-fill" style="width: {{ $progress['percentage'] }}%"></div>
            </div>

            <div class="top">
                <div class="eyebrow">PARLONS DE VOTRE PROJET</div>
                <div class="counter">{{ str_pad(count($summary['questions']), 2, '0', STR_PAD_LEFT) }} / {{ str_pad(count($summary['questions']), 2, '0', STR_PAD_LEFT) }}</div>
            </div>

            <h1 class="question-title anim-up d1">
                Récapitulatif
            </h1>
            <p class="question-desc anim-up d2">Vérifiez vos réponses avant de soumettre votre demande.</p>

            @if(!empty($summary['project']))
                <div style="padding:24px;border-radius:var(--radius);background:rgba(255,255,255,0.035);border:1px solid rgba(255,255,255,0.08);margin-top:12px;" class="anim-up d3">
                    <div style="display:flex;align-items:center;gap:12px;margin-bottom:12px;">
                        <div style="width:36px;height:36px;border-radius:10px;background:rgba(59,130,246,0.12);display:flex;align-items:center;justify-content:center;font-size:14px;color:#3b82f6;">
                            <i class="fa-solid fa-building"></i>
                        </div>
                        <span style="font-size:11px;font-weight:700;color:rgba(255,255,255,0.65);text-transform:uppercase;letter-spacing:0.06em;">Type de projet</span>
                    </div>
                    @php
                        $projType = $summary['project']->project_type ?? null;
                        $projTypes = is_array($projType) ? $projType : (isset($projType) ? [$projType] : []);
                        $projTypeLabels = collect($projTypes)->map(fn($t) => config("project_types.types.{$t}.label", $t))->implode(', ');
                    @endphp
                    <div style="font-size:15px;color:rgba(255,255,255,0.85);">{{ $projTypeLabels ?: '—' }}</div>
                </div>
            @endif

            @if(!empty($summary['client']))
                <div style="padding:24px;border-radius:var(--radius);background:rgba(255,255,255,0.035);border:1px solid rgba(255,255,255,0.08);margin-top:12px;" class="anim-up">
                    <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;">
                        <div style="width:36px;height:36px;border-radius:10px;background:var(--accent-surface);display:flex;align-items:center;justify-content:center;font-size:14px;color:var(--accent-light);">
                            <i class="fa-solid fa-user"></i>
                        </div>
                        <span style="font-size:11px;font-weight:700;color:rgba(255,255,255,0.65);text-transform:uppercase;letter-spacing:0.06em;">Vos informations</span>
                    </div>
                    <div style="font-size:14px;color:var(--text-secondary);line-height:1.9;">
                        <p><strong style="color:rgba(255,255,255,0.85);">Nom :</strong> {{ $summary['client']->name ?? '—' }}</p>
                        <p><strong style="color:rgba(255,255,255,0.85);">Email :</strong> {{ $summary['client']->email ?? '—' }}</p>
                        @if(!empty($summary['client']->phone))
                            <p><strong style="color:rgba(255,255,255,0.85);">Téléphone :</strong> {{ $summary['client']->phone }}</p>
                        @endif
                        @if(!empty($summary['client']->company_name))
                            <p><strong style="color:rgba(255,255,255,0.85);">Société :</strong> {{ $summary['client']->company_name }}</p>
                        @endif
                        @if(!empty($summary['client']->whatsapp))
                            <p><strong style="color:rgba(255,255,255,0.85);">WhatsApp :</strong> {{ $summary['client']->whatsapp }}</p>
                        @endif
                        @if(!empty($summary['client']->city))
                            <p><strong style="color:rgba(255,255,255,0.85);">Ville :</strong> {{ $summary['client']->city }}</p>
                        @endif
                        @if(!empty($summary['client']->country))
                            <p><strong style="color:rgba(255,255,255,0.85);">Pays :</strong> {{ $summary['client']->country }}</p>
                        @endif
                    </div>
                </div>
            @endif

            @if(!empty($summary['questions']) && count($summary['questions']) > 0)
                @foreach($summary['questions'] as $item)
                    @php
                        $question = $item['question'];
                        $answer = $item['answer'];
                        $display = $item['display_value'];
                    @endphp
                    <div style="padding:18px;border-radius:var(--radius-sm);background:rgba(255,255,255,0.035);border:1px solid rgba(255,255,255,0.08);margin-top:12px;{{ $loop->iteration <= 5 ? 'animation-delay:' . min($loop->iteration * 60, 300) . 'ms;opacity:0;animation-fill-mode:forwards;' : '' }}" class="anim-up">
                        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
                            <div style="font-size:11px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.06em;">{{ $question->title }}</div>
                            <a href="{{ route('questionnaire.step', $loop->iteration) }}?edit=1" style="font-size:11px;color:var(--accent-light);text-decoration:none;display:inline-flex;align-items:center;gap:4px;">
                                <i class="fa-solid fa-pen" style="font-size:9px;"></i> Modifier
                            </a>
                        </div>
                        <div style="font-size:15px;color:rgba(255,255,255,0.85);font-weight:500;line-height:1.6;">
                            @if(!empty($display))
                                {{ $display }}
                            @elseif(!empty($answer) && $question->type === 'file' && !empty($answer->file_path))
                                <span style="display:flex;align-items:center;gap:6px;"><i class="fa-solid fa-paperclip" style="color:var(--accent-light);"></i> Fichier joint</span>
                            @else
                                <span style="color:var(--text-muted);font-style:italic;">Non répondu</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            @endif

            @if(!empty($files) && count($files) > 0)
                <div style="padding:24px;border-radius:var(--radius);background:rgba(255,255,255,0.035);border:1px solid rgba(255,255,255,0.08);margin-top:12px;" class="anim-up">
                    <div style="display:flex;align-items:center;gap:12px;margin-bottom:12px;">
                        <div style="width:36px;height:36px;border-radius:10px;background:rgba(59,130,246,0.12);display:flex;align-items:center;justify-content:center;font-size:14px;color:#3b82f6;">
                            <i class="fa-solid fa-paperclip"></i>
                        </div>
                        <span style="font-size:11px;font-weight:700;color:rgba(255,255,255,0.65);text-transform:uppercase;letter-spacing:0.06em;">Fichiers joints</span>
                    </div>
                    <div style="display:flex;flex-direction:column;gap:8px;">
                        @foreach($files as $file)
                            <div style="display:flex;align-items:center;gap:10px;padding:10px 14px;border-radius:12px;background:rgba(255,255,255,0.05);font-size:13px;">
                                <i class="fa-solid fa-file" style="color:var(--accent-light);"></i>
                                <span style="color:white;">{{ $file->original_name }}</span>
                                <span style="margin-left:auto;color:var(--text-muted);font-size:11px;">{{ round($file->size / 1024) }} KB</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div style="display:flex;align-items:center;gap:14px;padding:18px;border-radius:var(--radius);margin-top:24px;background:rgba(16,185,129,0.06);border:1px solid rgba(16,185,129,0.12);" class="anim-up">
                <i class="fa-solid fa-shield-halved" style="color:#3b82f6;font-size:18px;"></i>
                <div style="font-size:12px;color:var(--text-secondary);line-height:1.6;">Vos données sont transmises de manière sécurisée et ne sont jamais partagées avec des tiers.</div>
            </div>

            <div class="card-bottom anim-up d5">
                <a href="{{ route('questionnaire.step', 1) }}" class="btn-back" style="text-decoration:none;">
                    <i class="fa-solid fa-arrow-left"></i> Modifier
                </a>
                <form action="{{ route('questionnaire.submit') }}" method="POST" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn-continue">
                        Envoyer ma demande <i class="fa-solid fa-paper-plane"></i>
                    </button>
                </form>
            </div>
        </div>
    </main>

</x-layouts.app>
