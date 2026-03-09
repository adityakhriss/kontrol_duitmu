@php
    $payload = $report->payload ?? [];
    $analysisItems = preg_split('/\r\n|\r|\n/', (string) data_get($payload, 'ai_analysis.analysis', ''));
    $recommendationItems = preg_split('/\r\n|\r|\n/', (string) data_get($payload, 'ai_analysis.recommendations', ''));
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $report->title }}</title>
    <style>
        @page {
            margin: 90px 34px 70px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            color: #0f172a;
            font-size: 11px;
            line-height: 1.5;
        }

        header {
            position: fixed;
            top: -62px;
            left: 0;
            right: 0;
            height: 54px;
            border-bottom: 1px solid #dbe3ea;
            padding-bottom: 8px;
        }

        footer {
            position: fixed;
            bottom: -42px;
            left: 0;
            right: 0;
            height: 30px;
            border-top: 1px solid #dbe3ea;
            color: #64748b;
            font-size: 10px;
            padding-top: 8px;
        }

        .page-number:after {
            content: counter(page);
        }

        .brand-row {
            width: 100%;
        }

        .brand-left {
            float: left;
            width: 68%;
        }

        .brand-right {
            float: right;
            width: 30%;
            text-align: right;
        }

        .eyebrow {
            color: #0f766e;
            font-size: 10px;
            font-weight: bold;
            letter-spacing: 0.18em;
            text-transform: uppercase;
        }

        h1 {
            font-size: 22px;
            margin: 6px 0 4px;
        }

        h2 {
            font-size: 14px;
            margin: 0 0 12px;
        }

        .muted {
            color: #64748b;
        }

        .summary-grid {
            width: 100%;
            border-collapse: separate;
            border-spacing: 10px 0;
            margin: 0 -10px 20px;
        }

        .summary-grid td {
            width: 33.333%;
            vertical-align: top;
        }

        .stat-card {
            border: 1px solid #dbe3ea;
            border-radius: 14px;
            background: #f8fafc;
            padding: 14px;
        }

        .stat-label {
            color: #64748b;
            font-size: 10px;
            font-weight: bold;
            letter-spacing: 0.14em;
            text-transform: uppercase;
        }

        .stat-value {
            font-size: 18px;
            font-weight: bold;
            margin-top: 8px;
        }

        .section {
            margin-bottom: 20px;
            page-break-inside: avoid;
        }

        .section-box {
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 16px;
            background: #ffffff;
        }

        .two-col {
            width: 100%;
            border-collapse: separate;
            border-spacing: 14px 0;
        }

        .two-col td {
            width: 50%;
            vertical-align: top;
        }

        .mini-table {
            width: 100%;
            border-collapse: collapse;
        }

        .mini-table th,
        .mini-table td {
            border-bottom: 1px solid #e2e8f0;
            padding: 8px 0;
            text-align: left;
        }

        .mini-table th {
            color: #64748b;
            font-size: 10px;
            font-weight: bold;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }

        ul {
            margin: 0;
            padding-left: 18px;
        }

        li {
            margin-bottom: 8px;
        }

        .pill {
            display: inline-block;
            border-radius: 999px;
            padding: 4px 9px;
            font-size: 10px;
            font-weight: bold;
            background: #ecfdf5;
            color: #047857;
        }

        .recommendation-box {
            border: 1px solid #d1fae5;
            background: #f0fdf4;
            border-radius: 14px;
            padding: 14px;
        }

        .spacer:after,
        .brand-row:after {
            content: '';
            display: block;
            clear: both;
        }
    </style>
</head>
<body>
    <header>
        <div class="brand-row">
            <div class="brand-left">
                <div class="eyebrow">Kontrol Duitmu</div>
                <div style="font-size: 18px; font-weight: bold; margin-top: 4px;">Laporan Keuangan</div>
                <div class="muted" style="margin-top: 2px;">{{ $report->title }}</div>
            </div>
            <div class="brand-right muted">
                <div>Periode {{ \App\Support\FinancePresenter::shortDate($report->period_start) }} - {{ \App\Support\FinancePresenter::shortDate($report->period_end) }}</div>
                <div style="margin-top: 4px;">Dibuat {{ \App\Support\FinancePresenter::shortDate($report->generated_at) }}</div>
            </div>
        </div>
    </header>

    <footer>
        <div style="float: left;">Kontrol Duitmu - Laporan Keuangan</div>
        <div style="float: right;">Halaman <span class="page-number"></span></div>
    </footer>

    <main>
        <table class="summary-grid">
            <tr>
                <td>
                    <div class="stat-card">
                        <div class="stat-label">Pemasukan</div>
                        <div class="stat-value">{{ \App\Support\FinancePresenter::money((float) data_get($payload, 'summary.income', 0)) }}</div>
                    </div>
                </td>
                <td>
                    <div class="stat-card">
                        <div class="stat-label">Pengeluaran</div>
                        <div class="stat-value">{{ \App\Support\FinancePresenter::money((float) data_get($payload, 'summary.expense', 0)) }}</div>
                    </div>
                </td>
                <td>
                    <div class="stat-card">
                        <div class="stat-label">Selisih</div>
                        <div class="stat-value">{{ \App\Support\FinancePresenter::signedMoney((float) data_get($payload, 'summary.net_cashflow', 0)) }}</div>
                    </div>
                </td>
            </tr>
        </table>

        <div class="section section-box">
            <h2>Ringkasan Eksekutif</h2>
            <table class="mini-table">
                <tr><th>Total transaksi</th><td>{{ (int) data_get($payload, 'summary.transaction_count', 0) }}</td></tr>
                <tr><th>Saldo likuid</th><td>{{ \App\Support\FinancePresenter::money((float) data_get($payload, 'summary.liquid_balance_total', 0)) }}</td></tr>
                <tr><th>Tagihan aktif</th><td>{{ \App\Support\FinancePresenter::money((float) data_get($payload, 'summary.upcoming_bills_total', 0)) }}</td></tr>
                <tr><th>Nilai investasi</th><td>{{ \App\Support\FinancePresenter::money((float) data_get($payload, 'summary.investment_value_total', 0)) }}</td></tr>
            </table>
        </div>

        <table class="two-col section">
            <tr>
                <td>
                    <div class="section-box">
                        <h2>Analisis AI</h2>
                        <div class="pill">{{ data_get($payload, 'ai_analysis.provider', 'AI Provider') }}</div>
                        <div class="muted" style="margin: 8px 0 12px;">Snapshot {{ data_get($payload, 'ai_analysis.snapshot_month') ? \App\Support\FinancePresenter::shortDate(data_get($payload, 'ai_analysis.snapshot_month')) : 'belum tersedia' }}</div>
                        @if (array_filter($analysisItems))
                            <ul>
                                @foreach (array_filter($analysisItems) as $item)
                                    <li>{{ ltrim($item, "- \t") }}</li>
                                @endforeach
                            </ul>
                        @else
                            <div class="muted">Belum ada analisis AI.</div>
                        @endif
                    </div>
                </td>
                <td>
                    <div class="section-box">
                        <h2>Saran AI</h2>
                        <div class="recommendation-box">
                            @if (array_filter($recommendationItems))
                                <ul>
                                    @foreach (array_filter($recommendationItems) as $item)
                                        <li>{{ ltrim($item, "- \t") }}</li>
                                    @endforeach
                                </ul>
                            @else
                                <div class="muted">Belum ada saran AI.</div>
                            @endif
                        </div>
                    </div>
                </td>
            </tr>
        </table>

        <div class="section section-box">
            <h2>Kategori Pengeluaran</h2>
            <table class="mini-table">
                <thead>
                    <tr>
                        <th>Kategori</th>
                        <th>Jumlah</th>
                        <th>Transaksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse (data_get($payload, 'expense_by_category', []) as $item)
                        <tr>
                            <td>{{ $item['category'] }}</td>
                            <td>{{ \App\Support\FinancePresenter::money((float) $item['amount']) }}</td>
                            <td>{{ $item['count'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="muted">Belum ada data kategori pengeluaran.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="section section-box">
            <h2>Transaksi Terpilih</h2>
            <table class="mini-table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Tipe</th>
                        <th>Kategori</th>
                        <th>Akun</th>
                        <th>Nominal</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse (data_get($payload, 'transactions', []) as $transaction)
                        <tr>
                            <td>{{ \App\Support\FinancePresenter::shortDate($transaction['date'] ?? null) }}</td>
                            <td>{{ $transaction['type'] ?? '-' }}</td>
                            <td>{{ $transaction['category'] ?? '-' }}</td>
                            <td>{{ $transaction['account'] ?? '-' }}</td>
                            <td>{{ \App\Support\FinancePresenter::money((float) ($transaction['amount'] ?? 0)) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="muted">Belum ada transaksi terpilih.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>
