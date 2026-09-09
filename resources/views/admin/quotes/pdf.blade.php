<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Devis {{ $quote->number }}</title>
    <style>
        @page {
            margin: 0;
        }

        * { box-sizing: border-box; }

        body {
            font-family: Helvetica, Arial, sans-serif;
            color: #1A1A1A;
            margin: 0;
            padding: 0;
            line-height: 1.5;
        }

        .page {
            width: 794px;
            min-height: 1123px;
            padding: 0;
            position: relative;
            page-break-after: always;
        }
        .page:last-child {
            page-break-after: avoid;
        }

        .bar {
            height: 10px;
            background: {{ $company->primary_color ?? '#F5A623' }};
        }

        .content {
            padding: 36px 48px;
        }

        /* ── Header ── */
        .header {
            width: 100%;
            margin-bottom: 22px;
        }

        .brand {
            display: block;
            float: left;
            width: 60%;
        }

        .logo {
            display: inline-block;
            vertical-align: middle;
            margin-right: 12px;
        }

        .logo-circle {
            width: 56px;
            height: 56px;
            border: 3px solid {{ $company->primary_color ?? '#F5A623' }};
            border-radius: 50%;
            text-align: center;
            line-height: 50px;
            font-weight: bold;
            font-size: 22px;
        }

        .logo-img {
            height: 56px;
            width: auto;
            display: inline-block;
            vertical-align: middle;
            margin-right: 12px;
        }

        .brand-text {
            display: inline-block;
            vertical-align: middle;
        }

        .brand-sub {
            font-size: 15px;
            font-weight: 700;
        }

        .brand-sub .accent {
            color: {{ $company->primary_color ?? '#F5A623' }};
        }

        .brand-meta {
            font-size: 11px;
            color: #555555;
            margin-top: 2px;
            line-height: 1.5;
        }

        .doc-info {
            display: block;
            float: right;
            text-align: right;
            width: 35%;
        }

        .doc-title {
            font-size: 26px;
            font-weight: 800;
            letter-spacing: 1px;
        }

        .doc-sub {
            font-size: 11px;
            color: #555555;
            margin-top: 2px;
        }

        .doc-meta {
            font-size: 12px;
            margin-top: 8px;
        }

        .doc-meta strong {
            font-weight: 700;
        }

        hr.rule {
            border: none;
            border-top: 2px solid {{ $company->primary_color ?? '#F5A623' }};
            margin: 0 0 18px;
            clear: both;
        }

        /* ── Client / Prestataire ── */
        .parties {
            background: #F2F2F2;
            padding: 16px 20px;
            width: 100%;
            margin-bottom: 22px;
            font-size: 12px;
        }

        .parties .col {
            display: inline-block;
            width: 48%;
            vertical-align: top;
        }

        .label-sm {
            font-size: 10px;
            letter-spacing: 0.5px;
            color: #555555;
            margin-bottom: 6px;
        }

        .party-name {
            font-weight: 700;
            font-size: 14px;
            margin-bottom: 4px;
        }

        .party-line {
            font-size: 12px;
            margin-bottom: 4px;
            color: #333333;
        }

        /* ── Sections ── */
        h2.section {
            font-size: 12.5px;
            font-weight: 800;
            letter-spacing: 0.3px;
            margin: 22px 0 8px;
            text-transform: uppercase;
            color: {{ $company->secondary_color ?? '#1a1a1a' }};
        }

        p.body-text {
            font-size: 12.5px;
            line-height: 1.6;
            margin: 0 0 4px;
            color: #1A1A1A;
        }

        p.body-text strong {
            font-weight: 700;
        }

        /* ── Budget table ── */
        table.budget {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            margin-top: 6px;
        }

        table.budget th {
            background: {{ $company->secondary_color ?? '#111111' }};
            color: #fff;
            text-align: left;
            padding: 10px 12px;
            font-weight: 700;
        }

        table.budget th:last-child,
        table.budget td:last-child {
            text-align: right;
        }

        table.budget td {
            padding: 10px 12px;
            border-bottom: 1px solid #DDDDDD;
            vertical-align: top;
            color: #1A1A1A;
        }

        table.budget tr.total td {
            background: {{ $company->primary_color ?? '#F5A623' }};
            font-weight: 800;
            border-bottom: none;
            padding: 10px 12px;
        }

        p.note {
            font-size: 10.5px;
            font-style: italic;
            color: #555555;
            margin: 8px 0 0;
            line-height: 1.5;
        }

        /* ── Prestations list ── */
        ul.prestations {
            margin: 6px 0 0;
            padding-left: 18px;
            font-size: 12.5px;
            line-height: 1.7;
        }

        /* ── Products table ── */
        table.products {
            width: 100%;
            border-collapse: collapse;
            font-size: 11.5px;
            margin-top: 6px;
        }

        table.products th {
            background: #F2F2F2;
            text-align: left;
            padding: 8px 10px;
            font-weight: 700;
            border-bottom: 2px solid {{ $company->primary_color ?? '#F5A623' }};
            color: {{ $company->secondary_color ?? '#1a1a1a' }};
        }

        table.products td {
            padding: 8px 10px;
            border-bottom: 1px solid #DDDDDD;
            vertical-align: top;
        }

        table.products td.product-name {
            font-weight: 600;
        }

        /* ── Tableau custom fields ── */
        table.table-fields td {
            border: 1px solid #DDDDDD;
            padding: 8px 12px;
        }

        /* ── Modalités ── */
        .modalites {
            margin-top: 22px;
        }

        .stamp {
            display: inline-block;
            margin-top: 10px;
            padding: 6px 14px;
            background: {{ $company->primary_color ?? '#F5A623' }};
            color: #fff;
            font-weight: 800;
            font-size: 12px;
            letter-spacing: 0.3px;
        }

        /* ── Signatures ── */
        .signatures {
            width: 100%;
            margin-top: 36px;
            font-size: 12px;
        }

        .signatures .sig {
            display: inline-block;
            width: 45%;
            text-align: center;
            vertical-align: top;
        }

        .sig-line {
            border-top: 1px solid #555555;
            margin-top: 36px;
            padding-top: 6px;
        }

        /* ── Footer ── */
        .footer-content {
            text-align: center;
            font-size: 9.5px;
            color: #888888;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    @php $inPdf = true; @endphp
@include('admin.quotes.partials.document', ['quote' => $quote, 'company' => $company])
</body>
</html>
