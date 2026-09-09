<x-layouts.admin :title="'Aperçu — Devis '.$quote->number">

    <section class="intro">
        <div>
            <div class="intro-label">
                <span></span>
                Aperçu
            </div>
            <h1>Devis {{ $quote->number }}</h1>
            <p>{{ $quote->client->name }} — {{ number_format($quote->total, 0, ',', ' ') }} {{ $company->currency }}</p>
        </div>
        <div style="display:flex;gap:8px;align-items:center;">
            <a href="{{ route('admin.quotes.pdf', $quote) }}" class="top-btn primary" target="_blank"><i class="fa-solid fa-file-pdf" style="margin-right:4px;"></i> Télécharger le PDF</a>
            <a href="{{ route('admin.quotes.edit', $quote) }}" class="top-btn"><i class="fa-solid fa-pen" style="margin-right:4px;"></i> Modifier</a>
            <a href="{{ route('admin.quotes.show', $quote) }}" class="top-btn"><i class="fa-solid fa-arrow-left" style="margin-right:4px;"></i> Retour</a>
        </div>
    </section>

    <section style="background:#f5f5f5;padding:10px 0 40px;">
        <div class="quote-page" style="width:210mm;min-height:297mm;padding:20mm;margin:0 auto;background:#ffffff;box-shadow:0 0 10px rgba(0,0,0,.1);box-sizing:border-box;position:relative;font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;color:#333333;line-height:1.5;">

            <style>
                {{-- CSS du document, identique à devis.html pour l'aperçu navigateur --}}
                .quote-page .header { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:30px; }
                .quote-page .logo-container { display:flex; align-items:center; }
                .quote-page .logo-icon { width:50px; height:50px; border:4px solid #e5b83a; border-radius:50%; display:flex; align-items:center; justify-content:center; margin-right:15px; position:relative; }
                .quote-page .logo-img { height:50px; width:auto; margin-right:15px; }
                .quote-page .logo-icon::after { content:''; width:4px; height:30px; background-color:#e5b83a; position:absolute; left:50%; transform:translateX(-50%); }
                .quote-page .company-name { font-family:'Montserrat',sans-serif; font-size:20px; font-weight:700; color:#1a1a1a; letter-spacing:.5px; }
                .quote-page .company-sub { font-size:11px; color:#666666; margin-top:2px; }
                .quote-page .doc-title-container { text-align:right; }
                .quote-page .doc-title { font-family:'Montserrat',sans-serif; font-size:28px; font-weight:700; letter-spacing:2px; margin:0; color:#000; }
                .quote-page .doc-subtitle { font-size:11px; color:#555; margin:3px 0 0 0; }
                .quote-page .doc-meta { font-size:12px; text-align:right; margin-top:15px; color:#333; }
                .quote-page .top-bar { height:4px; background-color:#e5b83a; margin-bottom:30px; }
                .quote-page .client-provider-section { display:flex; justify-content:space-between; margin-bottom:30px; font-size:13px; }
                .quote-page .info-block { width:48%; }
                .quote-page .block-title { font-size:11px; font-weight:bold; color:#888; letter-spacing:1px; margin-bottom:8px; text-transform:uppercase; }
                .quote-page .block-content strong { font-size:15px; color:#1a1a1a; }
                .quote-page .block-content p { margin:5px 0; color:#444; }
                .quote-page .section-title { font-family:'Montserrat',sans-serif; font-size:13px; font-weight:700; letter-spacing:1px; text-transform:uppercase; border-bottom:2px solid #1a1a1a; padding-bottom:5px; margin-top:30px; margin-bottom:15px; color:#1a1a1a; }
                .quote-page .section-p { font-size:13px; color:#333; margin:10px 0; line-height:1.6; }
                .quote-page table { width:100%; border-collapse:collapse; margin-top:15px; font-size:13px; }
                .quote-page th { background-color:#1a1a1a; color:#fff; font-weight:600; text-align:left; padding:10px 12px; font-size:12px; letter-spacing:.5px; }
                .quote-page td { padding:12px; border-bottom:1px solid #e0e0e0; vertical-align:top; color:#333; }
                .quote-page .bg-total { background-color:#e5b83a; font-weight:bold; color:#1a1a1a; }
                .quote-page .bg-total td { border-bottom:none; padding:10px 12px; }
                .quote-page .note-text { font-size:11px; font-style:italic; color:#666; margin-top:10px; line-height:1.4; }
                .quote-page .bullet-list { margin:10px 0; padding-left:20px; font-size:13px; color:#333; }
                .quote-page .bullet-list li { margin-bottom:7px; line-height:1.5; }
                .quote-page .table-prod th { background-color:#e5b83a; color:#1a1a1a; font-weight:700; }
                .quote-page .table-prod td { border:1px solid #ddd; }
                .quote-page .table-fields td { border:1px solid #ddd; }
                .quote-page .payment-text { font-size:13px; line-height:1.6; color:#333; margin-bottom:20px; }
                .quote-page .status-badge { color:#198754; font-weight:bold; font-size:14px; margin:25px 0; display:flex; align-items:center; }
                .quote-page .signatures { display:flex; justify-content:space-between; margin-top:30px; font-size:13px; }
                .quote-page .sig-block { width:45%; }
                .quote-page .sig-label { color:#666; margin-bottom:50px; }
                .quote-page .sig-name { font-weight:bold; color:#1a1a1a; }
                .quote-page .footer { position:absolute; bottom:20mm; left:20mm; right:20mm; text-align:center; font-size:10px; color:#888; border-top:1px solid #eee; padding-top:10px; }
            </style>

            @include('admin.quotes.partials.document', ['quote' => $quote, 'company' => $company])
        </div>
    </section>

</x-layouts.admin>
