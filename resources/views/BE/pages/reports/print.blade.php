<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laporan Hasil Seleksi Penerimaan Karyawan Baru</title>
    <style>
        * { box-sizing: border-box; }
        :root {
            --print-border: #1a1a1a;
            --print-muted: #555;
            --print-header-bg: #f4f4f5;
        }
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, BlinkMacSystemFont, 'Helvetica Neue', Arial, sans-serif;
            font-size: 10.5pt;
            color: #1a1a1a;
            margin: 0;
            padding: 28px 32px 40px;
            line-height: 1.5;
        }
        .doc-header {
            text-align: center;
            padding-bottom: 18px;
            margin-bottom: 22px;
            border-bottom: 2px solid var(--print-border);
            position: relative;
        }
        .doc-header::after {
            content: '';
            display: block;
            height: 1px;
            background: var(--print-border);
            margin-top: 6px;
            opacity: 0.35;
        }
        .institution {
            font-family: Georgia, 'Times New Roman', serif;
            font-weight: 700;
            font-size: 13pt;
            letter-spacing: 0.02em;
            margin: 0 0 6px;
            text-transform: uppercase;
        }
        .institution-sub {
            font-size: 9pt;
            color: var(--print-muted);
            font-weight: 400;
            margin: 0 0 14px;
        }
        h1 {
            font-family: Georgia, 'Times New Roman', serif;
            font-size: 12pt;
            font-weight: 700;
            text-align: center;
            margin: 0;
            line-height: 1.35;
        }
        .meta {
            text-align: center;
            font-size: 9.5pt;
            margin-top: 16px;
            color: var(--print-muted);
        }
        .meta div { margin: 3px 0; }
        .section-title {
            font-size: 10.5pt;
            font-weight: 700;
            margin: 22px 0 10px;
            padding-bottom: 4px;
            border-bottom: 1px solid #ccc;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
            font-size: 9.5pt;
        }
        th, td {
            border: 1px solid var(--print-border);
            padding: 7px 10px;
            vertical-align: middle;
        }
        th {
            background: var(--print-header-bg);
            font-weight: 600;
            text-align: left;
        }
        tbody tr:nth-child(even) { background: #fafafa; }
        td.text-end, th.text-end { text-align: right; }
        td.text-center, th.text-center { text-align: center; }
        .num { font-variant-numeric: tabular-nums; }
        .footer-signatures {
            margin-top: 56px;
            display: table;
            width: 100%;
            table-layout: fixed;
            border-collapse: separate;
            border-spacing: 48px 0;
        }
        .sig-cell {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            text-align: center;
            font-size: 9.5pt;
        }
        .sig-label {
            font-weight: 600;
            margin-bottom: 72px;
        }
        .sig-line {
            border-top: 1px solid var(--print-border);
            padding-top: 6px;
            margin: 0 auto;
            max-width: 220px;
        }
        .print-stamp {
            margin-top: 28px;
            font-size: 8.5pt;
            color: var(--print-muted);
        }
        @media print {
            body {
                padding: 0;
                font-size: 10pt;
            }
            .doc-header { padding-bottom: 14px; margin-bottom: 16px; }
            @page {
                margin: 14mm 16mm;
                size: A4;
            }
            table { page-break-inside: auto; }
            tr { page-break-inside: avoid; page-break-after: auto; }
            .section-title { page-break-after: avoid; }
            .footer-signatures { page-break-inside: avoid; }
        }
    </style>
    <script>
        window.addEventListener('load', function () { window.print(); });
    </script>
</head>
<body>
    <header class="doc-header">
        <p class="institution">{{ config('app.name', 'Perusahaan') }}</p>
        <p class="institution-sub">Laporan dokumentasi seleksi penerimaan karyawan</p>
        <h1>Laporan Hasil Seleksi<br>Penerimaan Karyawan Baru</h1>
        <div class="meta">
            <div><strong>Periode:</strong> {{ $period->name }}</div>
            <div><strong>Posisi:</strong> {{ $period->position }}</div>
            <div>
                <strong>Tanggal seleksi:</strong>
                {{ $period->start_date?->format('d/m/Y') }} — {{ $period->end_date?->format('d/m/Y') }}
            </div>
            <div><strong>Dibuat oleh:</strong> {{ $period->creator->name ?? '—' }}</div>
        </div>
    </header>

    <div class="section-title">Tabel 1 — Bobot kriteria</div>
    <table>
        <thead>
            <tr>
                <th class="text-center" style="width:3em;">No</th>
                <th style="width:5em;">Kode</th>
                <th>Nama kriteria</th>
                <th class="text-end">Bobot</th>
            </tr>
        </thead>
        <tbody>
            @foreach($criteria as $i => $criterion)
            @php $cw = $weights->get($criterion->id); @endphp
            <tr>
                <td class="text-center">{{ $i + 1 }}</td>
                <td class="num">{{ $criterion->code }}</td>
                <td>{{ $criterion->name }}</td>
                <td class="text-end num">{{ $cw ? number_format((float) $cw->weight, 6) : '—' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="section-title">Tabel 2 — Nilai pelamar per kriteria</div>
    <table>
        <thead>
            <tr>
                <th class="text-center" style="width:3em;">No</th>
                <th>Nama pelamar</th>
                @foreach($criteria as $criterion)
                <th class="text-end num">{{ $criterion->code }}</th>
                @endforeach
                <th class="text-end">Total terbobot</th>
            </tr>
        </thead>
        <tbody>
            @foreach($evaluations as $applicantId => $applicantEvals)
            @php
                $applicant = optional($applicantEvals->first())->applicant;
                $weighted = 0;
                foreach ($criteria as $criterion) {
                    $w = $weights->get($criterion->id);
                    $ev = $applicantEvals->firstWhere('criteria_id', $criterion->id);
                    if ($w && $ev) {
                        $weighted += (float) $ev->score * (float) $w->weight;
                    }
                }
            @endphp
            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td>{{ $applicant->name ?? '—' }}</td>
                @foreach($criteria as $criterion)
                @php $ev = $applicantEvals->firstWhere('criteria_id', $criterion->id); @endphp
                <td class="text-end num">{{ $ev ? number_format((float) $ev->score, 4) : '—' }}</td>
                @endforeach
                <td class="text-end num">{{ number_format($weighted, 6) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="section-title">Tabel 3 — Hasil perangkingan</div>
    <table>
        <thead>
            <tr>
                <th class="text-center" style="width:4em;">Rank</th>
                <th>Nama</th>
                <th class="text-end">Nilai preferensi</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($results as $result)
            <tr>
                <td class="text-center">{{ $result->rank }}</td>
                <td>{{ $result->applicant->name }}</td>
                <td class="text-end num">{{ number_format((float) $result->preference_value, 6) }}</td>
                <td>{{ $result->status === 'lulus' ? 'Lulus' : 'Tidak Lulus' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer-signatures">
        <div class="sig-cell">
            <div class="sig-label">Yang membuat laporan</div>
            <div class="sig-line"></div>
        </div>
        <div class="sig-cell">
            <div class="sig-label">Mengetahui<br>Direktur</div>
            <div class="sig-line"></div>
        </div>
    </div>
    <p class="print-stamp">Dicetak pada: {{ now()->format('d/m/Y H:i') }}</p>
</body>
</html>
