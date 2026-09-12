<x-layouts.admin :title="$quote->exists ? 'Modifier — Devis '.$quote->number : 'Nouveau devis'">

@php
    $selectedClientId = old('client_id', $client?->id ?? $quote->client_id);
    $selectedStatus = old('status', $quote->status ?? 'draft');
    // Priorité aux old() (erreurs de validation) ; sinon données existantes ;
    // sinon modèle par défaut (devis.html) pour un nouveau devis.
    $existingItems = old('items', $quote->exists ? $quote->items->map(fn ($i) => $i->only(['designation', 'description', 'quantity', 'unit_price']))->values()->all() : $templateItems);
    $existingSections = collect(old('sections', $quote->exists ? $quote->sections->map(fn ($s) => ['id' => $s->id, 'type' => $s->type, 'title' => $s->title, 'content' => $s->content])->values()->all() : $templateSections));
    $existingFields = old('custom_fields', $quote->exists ? $quote->customFields->map(fn ($f) => $f->only(['name', 'type', 'value']))->values()->all() : []);
    $existingPrestations = old('prestations', $quote->exists ? ($quote->prestations ?? []) : $templatePrestations);
    if (is_string($existingPrestations)) {
        $existingPrestations = json_decode($existingPrestations, true) ?: []; // old() renvoie la chaîne JSON de l'input caché
    }

    // Texte d'analyse : priorité à old() (erreur de validation), puis à la
    // valeur sauvegardée du devis, sinon texte par défaut du modèle.
    $analysisTitle = 'Analyse des produits & cible recommandée';
    $analysisDefault = (string) data_get(
        collect($templateSections ?? [])->first(fn ($s) => is_array($s) && ($s['type'] ?? null) === \App\Models\QuoteSection::TYPE_TEXT),
        'content.text',
        ''
    );
    $savedAnalysis = $existingSections->first(fn ($s) => is_array($s) && ($s['type'] ?? null) === \App\Models\QuoteSection::TYPE_TEXT && ($s['title'] ?? null) === $analysisTitle);
    $analysisValue = old('sections.0.content.text', $quote->exists && $savedAnalysis !== null ? (string) data_get($savedAnalysis, 'content.text', '') : $analysisDefault);
    $analysisSectionId = old('sections.0.id', $quote->exists && $savedAnalysis !== null ? ($savedAnalysis['id'] ?? null) : null);
@endphp

    <section class="intro">
        <div>
            <div class="intro-label">
                <span></span>
                {{ $quote->exists ? 'Modification' : 'Création' }}
            </div>
            <h1>{{ $quote->exists ? 'Devis '.$quote->number : 'Nouveau devis' }}</h1>
            <p>Le modèle DOMINIUM est préchargé : modifiez, ajoutez, supprimez ou déplacez librement les blocs.</p>
        </div>
        <div style="display:flex;gap:8px;align-items:center;">
            @if($quote->exists)
                <a href="{{ route('admin.quotes.preview', $quote) }}" class="top-btn" target="_blank"><i class="fa-solid fa-eye" style="margin-right:4px;"></i> Aperçu</a>
            @endif
            <a href="{{ $quote->exists ? route('admin.quotes.show', $quote) : route('admin.quotes.index') }}" class="top-btn"><i class="fa-solid fa-arrow-left" style="margin-right:4px;"></i> Retour</a>
        </div>
    </section>

    <form method="POST" action="{{ $quote->exists ? route('admin.quotes.update', $quote) : route('admin.quotes.store') }}" id="quoteForm">
        @csrf
        @if($quote->exists)
            @method('PATCH')
        @endif

        {{-- Champs principaux --}}
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:18px;" class="form-grid">
            <div>
                <label class="admin-label">Numéro de devis</label>
                <input type="text" name="number" class="admin-input" value="{{ old('number', $quote->number) }}" placeholder="DT-2026-0001 (auto-généré si vide)">
                @error('number')<div style="color:#dc2626;font-size:12px;margin-top:4px;">{{ $message }}</div>@enderror
            </div>
            <div>
                <label class="admin-label">Client / Prospect *</label>
                <select name="client_id" class="admin-input" required>
                    <option value="">— Sélectionner —</option>
                    @foreach(\App\Models\Client::orderBy('name')->get() as $c)
                        <option value="{{ $c->id }}" @selected($selectedClientId == $c->id)>
                            {{ $c->name }} ({{ $c->isClient() ? 'Client' : 'Prospect' }}){{ $c->company_name ? ' — '.$c->company_name : '' }}
                        </option>
                    @endforeach
                </select>
                @error('client_id')<div style="color:#dc2626;font-size:12px;margin-top:4px;">{{ $message }}</div>@enderror
            </div>
            <div style="grid-column:1/-1;">
                <label class="admin-label">Objet de la prestation</label>
                <input type="text" name="description" class="admin-input" value="{{ old('description', $quote->description) }}" placeholder="Objet de la prestation">
            </div>
        </div>

        {{-- Répartition du budget --}}
        <div class="admin-card">
            <div class="card-header">
                <div>
                    <div class="card-title"><i class="fa-solid fa-table-columns" style="color:var(--yellow);margin-right:6px;"></i> Répartition du budget</div>
                    <div class="card-subtitle">Prestations incluses — Grille éditable, le total est calculé automatiquement.</div>
                </div>
            </div>
            <div style="padding:22px;">
                <div style="overflow-x:auto;">
                    <table id="budgetTable" style="width:100%;border-collapse:collapse;font-size:14px;border:1px solid var(--line);">
                        <thead id="budgetHead"></thead>
                        <tbody id="itemsBody"></tbody>
                        <tfoot id="budgetFoot"></tfoot>
                    </table>
                </div>
                <div style="display:flex;gap:10px;margin-top:14px;">
                    <button type="button" id="addItemBtn" class="admin-btn btn-secondary"><i class="fa-solid fa-plus"></i> Ajouter une ligne</button>
                    <button type="button" id="addColBtn" class="admin-btn btn-secondary"><i class="fa-solid fa-table-columns"></i> Ajouter une colonne</button>
                </div>
                <input type="hidden" name="budget_extra_headers" id="budgetExtraHeaders" value="">
                @error('items.*.designation')<div style="color:#dc2626;font-size:12px;margin-top:8px;">{{ $message }}</div>@enderror
            </div>
        </div>

        <div style="margin-top:12px;margin-bottom:18px;padding:12px 16px;background:#fafaf8;border:1px solid var(--line);border-radius:5px;font-size:13px;color:#555;line-height:1.6;">
            <strong>Note :</strong> le taux de commission Dominium Tech appliqué est de 20 % du budget pour tout montant à partir de 100 000 FCFA, ou de 20 000 FCFA forfaitaires en dessous de ce seuil — ce qui correspond au présent budget de 50 000 FCFA.
        </div>

        {{-- PRESTATIONS INCLUSES --}}
        <div class="admin-card" style="margin-bottom:18px;">
            <div class="card-header">
                <div>
                    <div class="card-title"><i class="fa-solid fa-list-check" style="color:var(--yellow);margin-right:6px;"></i> PRESTATIONS INCLUSES</div>
                    <div class="card-subtitle">Une prestation par ligne — exactement 2 lignes, chaque prestation reste individuelle.</div>
                </div>
            </div>
            <div style="padding:22px;">
                <div id="prestationsList"></div>
                <button type="button" id="addPrestationBtn" class="admin-btn btn-secondary" style="margin-top:4px;"><i class="fa-solid fa-plus"></i> Ajouter une ligne</button>
                <input type="hidden" name="prestations" id="prestationsData" value="">
            </div>
        </div>

        {{-- ANALYSE DES PRODUITS & CIBLE RECOMMANDÉE --}}
        <div class="admin-card" style="margin-bottom:18px;">
            <div class="card-header">
                <div>
                    <div class="card-title"><i class="fa-solid fa-bullseye" style="color:var(--yellow);margin-right:6px;"></i> ANALYSE DES PRODUITS & CIBLE RECOMMANDÉE</div>
                    <div class="card-subtitle">Texte entièrement éditable — sauvegardé avec le devis.</div>
                </div>
            </div>
            <div style="padding:22px;">
                @if($analysisSectionId)
                    <input type="hidden" name="sections[0][id]" value="{{ $analysisSectionId }}">
                @endif
                <input type="hidden" name="sections[0][type]" value="{{ \App\Models\QuoteSection::TYPE_TEXT }}">
                <input type="hidden" name="sections[0][title]" value="{{ $analysisTitle }}">
                <textarea id="analysisText" name="sections[0][content][text]" class="admin-input" rows="5" placeholder="{{ $analysisTitle }}">{{ $analysisValue }}</textarea>
                @error('sections.0.content.text')<div style="color:#dc2626;font-size:12px;margin-top:8px;">{{ $message }}</div>@enderror
            </div>
        </div>

        {{-- Champs cachés (requis par le backend) --}}
        <input type="hidden" name="status" value="{{ old('status', $quote->status ?? 'draft') }}">
        <input type="hidden" name="issued_at" value="{{ old('issued_at', $quote->issued_at?->format('Y-m-d') ?? now()->format('Y-m-d')) }}">
        <input type="hidden" name="subject" value="{{ old('subject', $quote->subject ?? $quote->description) }}">
        <input type="hidden" name="discount" value="0">

        <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:22px;">
            <a href="{{ $quote->exists ? route('admin.quotes.show', $quote) : route('admin.quotes.index') }}" class="admin-btn btn-secondary">Annuler</a>
            <button type="submit" class="admin-btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Enregistrer le devis</button>
        </div>
    </form>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var itemsBody = document.getElementById('itemsBody');

        function esc(str) {
            var div = document.createElement('div');
            div.textContent = str == null ? '' : String(str);
            return div.innerHTML;
        }

        // ── Budget grid state ──
        var budgetHeaders = ['Désignation', 'Détail', 'Montant (FCFA)'];
        var budgetExtraHeaders = [];

        function getColCount() { return 3 + budgetExtraHeaders.length; }

        function renderBudgetHead() {
            var thead = document.getElementById('budgetHead');
            var html = '<tr style="background:#111;">';
            for (var i = 0; i < getColCount(); i++) {
                var txt = i < 3 ? budgetHeaders[i] : esc(budgetExtraHeaders[i - 3]);
                var style = 'padding:10px 14px;font-size:12px;font-weight:700;color:#fff;background:#111;text-transform:uppercase;letter-spacing:.7px;border:1px solid #333;';
                if (i === 2) style += 'text-align:right;';
                html += '<th contenteditable="true" data-col="' + i + '" style="' + style + '">' + txt + '</th>';
            }
            html += '<th style="width:44px;border:1px solid #333;background:#111;"></th></tr>';

            if (budgetExtraHeaders.length > 0) {
                html += '<tr style="background:#111;">';
                html += '<th colspan="3" style="border:1px solid #333;background:#111;"></th>';
                for (var j = 0; j < budgetExtraHeaders.length; j++) {
                    html += '<th style="border:1px solid #333;background:#111;text-align:center;padding:2px;"><button type="button" class="remove-col" data-col-index="' + j + '" title="Supprimer cette colonne" style="width:22px;height:22px;border:1px solid #555;background:#333;border-radius:3px;cursor:pointer;color:#fff;font-size:10px;"><i class="fa-solid fa-xmark"></i></button></th>';
                }
                html += '<th style="border:1px solid #333;background:#111;"></th></tr>';
            }

            thead.innerHTML = html;

            thead.querySelectorAll('th[data-col]').forEach(function(th) {
                th.addEventListener('blur', function() {
                    var idx = parseInt(this.dataset.col);
                    var val = this.textContent.trim();
                    if (idx < 3) { budgetHeaders[idx] = val || budgetHeaders[idx]; }
                    else { budgetExtraHeaders[idx - 3] = val || budgetExtraHeaders[idx - 3]; }
                    syncExtraHeaders();
                });
            });
        }

        function renderBudgetFoot() {
            var foot = document.getElementById('budgetFoot');
            var cols = getColCount();
            var html = '<tr style="background:var(--yellow);font-weight:800;">';
            html += '<td colspan="' + (cols - 1) + '" style="padding:10px 14px;font-size:14px;border:1px solid var(--line);">TOTAL</td>';
            html += '<td style="padding:10px 14px;text-align:right;font-size:14px;border:1px solid var(--line);" id="budgetTotal">0 FCFA</td>';
            html += '<td style="border:1px solid var(--line);background:var(--yellow);"></td></tr>';
            foot.innerHTML = html;
        }

        function syncExtraHeaders() {
            document.getElementById('budgetExtraHeaders').value = JSON.stringify(budgetExtraHeaders);
        }

        function budgetInput(type, name, value, opts) {
            var s = 'width:100%;height:36px;padding:0 10px;border:1px solid var(--line);border-radius:0;background:white;color:var(--text);font-size:14px;font-family:inherit;box-sizing:border-box;outline:none;';
            if (type === 'number') s += 'text-align:right;';
            return '<input type="' + type + '" name="' + name + '" class="budget-cell-input item-amount" value="' + esc(value) + '" style="' + s + '"' + (opts || '') + '>';
        }

        function addBudgetRow(data) {
            data = data || {};
            var tr = document.createElement('tr');
            tr.className = 'quote-item-row';
            var html = '';

            html += '<td style="padding:0;">' + budgetInput('text', 'items[0][designation]', data.designation, ' placeholder="Désignation"') + '</td>';
            html += '<td style="padding:0;">' + budgetInput('text', 'items[0][description]', data.description, ' placeholder="Détail"') + '</td>';
            html += '<td style="padding:0;">' + budgetInput('number', 'items[0][unit_price]', data.unit_price || 0, ' min="0" step="1"') + '<input type="hidden" name="items[0][quantity]" value="1"></td>';

            var extras = data.extra || [];
            for (var i = 0; i < budgetExtraHeaders.length; i++) {
                html += '<td style="padding:0;">' + budgetInput('text', 'items[0][extra][' + i + ']', extras[i] || '', '') + '</td>';
            }

            html += '<td style="padding:4px;text-align:center;width:44px;border-left:1px solid var(--line);"><button type="button" class="remove-row" title="Supprimer" style="height:28px;border:1px solid var(--line);background:white;border-radius:4px;cursor:pointer;color:#999;font-size:12px;"><i class="fa-solid fa-trash-can"></i></button></td>';

            tr.innerHTML = html;
            itemsBody.appendChild(tr);
            renumberAll();
            recalcTotals();
        }

        function addBudgetCol() {
            var name = prompt('Nom de la colonne :', 'Colonne ' + (budgetExtraHeaders.length + 1));
            if (!name || !name.trim()) return;
            budgetExtraHeaders.push(name.trim());
            syncExtraHeaders();

            itemsBody.querySelectorAll('tr').forEach(function(tr) {
                var td = document.createElement('td');
                td.style.cssText = 'padding:0;';
                td.innerHTML = budgetInput('text', 'items[0][extra][' + (budgetExtraHeaders.length - 1) + ']', '', '');
                tr.insertBefore(td, tr.lastElementChild);
            });

            renderBudgetHead();
            renderBudgetFoot();
            renumberAll();
        }

        function removeCol(colIndex) {
            if (colIndex < 0 || colIndex >= budgetExtraHeaders.length) return;
            budgetExtraHeaders.splice(colIndex, 1);
            syncExtraHeaders();

            itemsBody.querySelectorAll('tr').forEach(function(tr) {
                var tds = tr.querySelectorAll('td');
                var targetIdx = 3 + colIndex;
                if (tds[targetIdx]) tds[targetIdx].remove();
            });

            renderBudgetHead();
            renderBudgetFoot();
            renumberAll();
        }

        function fmt(n) {
            return Number(n || 0).toLocaleString('fr-FR').replace(/,/g, ' ');
        }

        function recalcTotals() {
            var subtotal = 0;
            itemsBody.querySelectorAll('.item-amount').forEach(function(inp) {
                subtotal += parseInt(inp.value) || 0;
            });
            var el = document.getElementById('budgetTotal');
            if (el) el.textContent = fmt(subtotal) + ' FCFA';
        }

        function renumberAll() {
            Array.from(itemsBody.children).forEach(function(row, index) {
                row.querySelectorAll('input, textarea, select').forEach(function(el) {
                    if (!el.name) return;
                    el.name = el.name.replace(new RegExp('^items\\[[^\\]]*\\]'), 'items[' + index + ']');
                });
            });
        }

        // ── Budget actions ──
        document.addEventListener('click', function(e) {
            var btn = e.target.closest('.remove-row');
            if (btn) {
                var tr = btn.closest('tr');
                if (tr) tr.remove();
                renumberAll();
                recalcTotals();
                return;
            }
            var colBtn = e.target.closest('.remove-col');
            if (colBtn) {
                removeCol(parseInt(colBtn.dataset.colIndex));
                return;
            }
        });

        itemsBody.addEventListener('input', function(e) {
            if (e.target.classList.contains('item-amount')) recalcTotals();
        });

        document.getElementById('addItemBtn').addEventListener('click', function() { addBudgetRow(); });
        document.getElementById('addColBtn').addEventListener('click', addBudgetCol);

        // ── Init budget ──
        renderBudgetHead();
        renderBudgetFoot();
        syncExtraHeaders();

        var existingItems = @json($existingItems);
        existingItems.forEach(function(item) { addBudgetRow(item); });

        recalcTotals();

        // ── Prestations (exactement 2, une par ligne) ──
        var prestationsList = document.getElementById('prestationsList');
        var prestationsData = document.getElementById('prestationsData');
        var prestations = @json($existingPrestations);

        // Compatibilité ancien format (chaînes ou objets designation/detail) : une ligne = une prestation.
        prestations = prestations.map(function(p) {
            if (typeof p === 'string') return p;
            return p.designation || '';
        }).slice(0, 2);
        while (prestations.length < 2) prestations.push('');

        function syncPrestations() {
            prestationsData.value = JSON.stringify(prestations.map(function(text) { return { designation: text }; }));
        }

        function presInput(value) {
            var s = 'width:100%;height:36px;padding:0 10px;border:1px solid var(--line);border-radius:0;background:white;color:var(--text);font-size:14px;font-family:inherit;box-sizing:border-box;outline:none;';
            return '<input type="text" class="pres-input" value="' + esc(value) + '" placeholder="Prestation" style="' + s + '">';
        }

        function presTool(icon, cls, title, disabled) {
            return '<button type="button" class="' + cls + '" title="' + title + '"' + (disabled ? ' disabled style="opacity:.35;cursor:not-allowed;"' : ' style="width:26px;height:26px;border:1px solid var(--line);background:white;border-radius:4px;cursor:pointer;color:#666;font-size:11px;"') + '><i class="fa-solid ' + icon + '"></i></button>';
        }

        function renderPrestations() {
            prestationsList.innerHTML = '';
            prestations.forEach(function(text, idx) {
                var row = document.createElement('div');
                row.dataset.index = idx;
                row.style.cssText = 'display:flex;align-items:center;gap:8px;margin-bottom:10px;';
                row.innerHTML =
                    '<span style="min-width:22px;font-size:12px;font-weight:700;color:#999;">' + (idx + 1) + '</span>' +
                    '<div style="flex:1;">' + presInput(text) + '</div>' +
                    '<div style="display:flex;gap:4px;">' +
                        presTool('fa-arrow-up', 'move-prestation-up', 'Monter', idx === 0) +
                        presTool('fa-arrow-down', 'move-prestation-down', 'Descendre', idx === prestations.length - 1) +
                        presTool('fa-trash-can', 'remove-prestation', 'Supprimer', false) +
                    '</div>';
                prestationsList.appendChild(row);
            });

            prestationsList.querySelectorAll('div[data-index]').forEach(function(row) {
                var idx = parseInt(row.dataset.index);
                row.querySelector('.pres-input').addEventListener('input', function() {
                    prestations[idx] = this.value;
                    syncPrestations();
                });
            });
        }

        function movePrestation(from, to) {
            if (to < 0 || to >= prestations.length) return;
            var tmp = prestations[from];
            prestations[from] = prestations[to];
            prestations[to] = tmp;
            syncPrestations();
            renderPrestations();
            var rows = prestationsList.querySelectorAll('div[data-index]');
            if (rows[to]) rows[to].querySelector('.pres-input').focus();
        }

        document.addEventListener('click', function(e) {
            var up = e.target.closest('.move-prestation-up');
            var down = e.target.closest('.move-prestation-down');
            var removeBtn = e.target.closest('.remove-prestation');
            if (up) {
                var idx = parseInt(up.closest('div[data-index]').dataset.index);
                movePrestation(idx, idx - 1);
            } else if (down) {
                var idx = parseInt(down.closest('div[data-index]').dataset.index);
                movePrestation(idx, idx + 1);
            } else if (removeBtn) {
                var idx = parseInt(removeBtn.closest('div[data-index]').dataset.index);
                prestations.splice(idx, 1);
                while (prestations.length < 2) prestations.push('');
                syncPrestations();
                renderPrestations();
            }
        });

        document.getElementById('addPrestationBtn').addEventListener('click', function() {
            prestations.push('');
            syncPrestations();
            renderPrestations();
            var rows = prestationsList.querySelectorAll('div[data-index]');
            var lastRow = rows[rows.length - 1];
            if (lastRow) lastRow.querySelector('.pres-input').focus();
        });

        // ── Init prestations ──
        syncPrestations();
        renderPrestations();

        // ── Analyse des produits & cible recommandée ──
        // Une analyse vidée n'enregistre pas de section vide dans le devis.
        document.getElementById('quoteForm').addEventListener('submit', function() {
            if (document.getElementById('analysisText').value.trim() === '') {
                this.querySelectorAll('[name^="sections[0]"]').forEach(function(el) { el.disabled = true; });
            }
        });
    });
    </script>

</x-layouts.admin>
