<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Export Google Sheets - SAMARA</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', Arial, sans-serif;
            background: #f0fdf4;
            color: #1a1a1a;
            min-height: 100vh;
        }

        /* Top Action Bar */
        .action-bar {
            position: sticky;
            top: 0;
            z-index: 999;
            background: linear-gradient(135deg, #166534 0%, #15803d 50%, #16a34a 100%);
            color: #ffffff;
            padding: 16px 28px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 20px rgba(22, 101, 52, 0.3);
        }
        .action-bar .info { display: flex; flex-direction: column; gap: 2px; }
        .action-bar .info .title { font-weight: 800; font-size: 16px; display: flex; align-items: center; gap: 8px; }
        .action-bar .info .subtitle { font-size: 12px; opacity: 0.85; }
        .action-bar .actions { display: flex; gap: 10px; align-items: center; }
        .btn-action {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 9px 18px; border-radius: 8px;
            font-size: 13px; font-weight: 700;
            cursor: pointer; border: none;
            transition: all 0.2s ease;
            text-decoration: none;
        }
        .btn-copy {
            background: #ffffff; color: #166534;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .btn-copy:hover { background: #dcfce7; transform: translateY(-1px); }
        .btn-copy.copied { background: #bbf7d0; color: #14532d; }
        .btn-back {
            background: rgba(255,255,255,0.15); color: #ffffff;
            border: 1px solid rgba(255,255,255,0.3);
        }
        .btn-back:hover { background: rgba(255,255,255,0.25); }

        /* Toast notification */
        .toast {
            position: fixed; top: 80px; right: 24px;
            background: #166534; color: #fff;
            padding: 12px 20px; border-radius: 10px;
            font-size: 13px; font-weight: 600;
            box-shadow: 0 8px 24px rgba(0,0,0,0.2);
            opacity: 0; transform: translateY(-10px);
            transition: all 0.3s ease;
            z-index: 1000;
            display: flex; align-items: center; gap: 8px;
        }
        .toast.show { opacity: 1; transform: translateY(0); }

        /* Instructions */
        .instructions {
            max-width: 960px; margin: 20px auto; padding: 0 20px;
        }
        .instructions-card {
            background: #ffffff;
            border: 1px solid #bbf7d0;
            border-radius: 12px;
            padding: 18px 22px;
            display: flex; gap: 16px; align-items: flex-start;
        }
        .instructions-card .icon {
            font-size: 28px; flex-shrink: 0;
        }
        .instructions-card .steps { font-size: 13px; line-height: 1.7; color: #374151; }
        .instructions-card .steps strong { color: #166534; }
        .step-num {
            display: inline-flex; width: 22px; height: 22px;
            background: #166534; color: #fff; border-radius: 50%;
            font-size: 11px; font-weight: 800;
            align-items: center; justify-content: center;
            margin-right: 6px;
        }

        /* Table Container */
        .table-container {
            max-width: 960px; margin: 16px auto 40px; padding: 0 20px;
        }
        .table-wrapper {
            background: #ffffff;
            border: 1px solid #d1d5db;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 16px rgba(0,0,0,0.06);
        }

        /* Spreadsheet-style table */
        #sheets-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }
        #sheets-table thead th {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            padding: 10px 8px;
            font-weight: 700;
            text-align: center;
            font-size: 11px;
            color: #166534;
            white-space: nowrap;
        }
        #sheets-table tbody td {
            border: 1px solid #e5e7eb;
            padding: 8px;
            vertical-align: top;
            line-height: 1.5;
        }
        #sheets-table tbody tr:hover {
            background: #f0fdf4;
        }
        #sheets-table tbody tr:nth-child(even) {
            background: #fafafa;
        }
        #sheets-table tbody tr:nth-child(even):hover {
            background: #f0fdf4;
        }
        .cell-date { font-weight: 600; white-space: nowrap; }
        .cell-muted { color: #6b7280; }
        .cell-empty { color: #9ca3af; font-style: italic; }

        @media (max-width: 768px) {
            .action-bar { flex-direction: column; gap: 12px; align-items: stretch; padding: 14px 16px; }
            .action-bar .actions { justify-content: flex-end; }
            .instructions-card { flex-direction: column; gap: 10px; }
        }
    </style>
</head>
<body>
    <!-- Action Bar -->
    <div class="action-bar">
        <div class="info">
            <div class="title">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 3v3h-7V6h7zm-8 3H4V6h7v3zM4 11h7v3H4v-3zm9 0h7v3h-7v-3zm7 7h-7v-3h7v3zm-8 0H4v-3h7v3z"/></svg>
                Export ke Google Sheets
            </div>
            <div class="subtitle">Data Rencana & Realisasi Kunjungan — Siap disalin ke Google Sheets</div>
        </div>
        <div class="actions">
            <a href="{{ route('reports.index', ['start_date' => $startDate, 'end_date' => $endDate]) }}" class="btn-action btn-back">
                ← Kembali
            </a>
            <button id="btnCopy" class="btn-action btn-copy" onclick="copyTableToClipboard()">
                📋 Salin Tabel ke Clipboard
            </button>
        </div>
    </div>

    <!-- Toast -->
    <div id="toast" class="toast">
        ✅ Tabel berhasil disalin! Paste di Google Sheets (Ctrl+V)
    </div>

    <!-- Instructions -->
    <div class="instructions">
        <div class="instructions-card">
            <div class="icon">💡</div>
            <div class="steps">
                <div><span class="step-num">1</span> Klik tombol <strong>"Salin Tabel ke Clipboard"</strong> di atas</div>
                <div><span class="step-num">2</span> Buka <strong><a href="https://sheets.google.com/create" target="_blank" style="color: #166534;">Google Sheets</a></strong> di tab baru</div>
                <div><span class="step-num">3</span> Klik sel A1, lalu tekan <strong>Ctrl + V</strong> untuk menempel data</div>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="table-container">
        <div class="table-wrapper">
            <table id="sheets-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Hari/Tanggal</th>
                        <th>Customer (Rencana)</th>
                        <th>Target</th>
                        <th>Keterangan</th>
                        <th>Customer (Realisasi)</th>
                        <th>Nama Pejabat</th>
                        <th>Jabatan</th>
                        <th>Hasil Kunjungan</th>
                        <th>Tindak Lanjut</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($plans as $index => $plan)
                        <tr>
                            <td style="text-align: center; font-weight: 600;">{{ $index + 1 }}</td>
                            <td class="cell-date">{{ $plan->planned_date->translatedFormat('l, d F Y') }}</td>
                            <td>{{ $plan->customer->customer_name ?? '-' }}</td>
                            <td>{{ $plan->specific_objective ?? $plan->monthly_objective ?? '-' }}</td>
                            <td>{{ $plan->resource_notes ?? $plan->location_text ?? '-' }}</td>
                            <td>{{ $plan->customer->customer_name ?? '-' }}</td>
                            <td>{{ $plan->visitReport->attendance_summary ?? $plan->customer->contact_name ?? '-' }}</td>
                            <td>{{ $plan->customer->contact_position ?? '-' }}</td>
                            <td>
                                @if($plan->visitReport)
                                    {{ $plan->visitReport->outcome_summary ?: ($plan->visitReport->outcome_type->label() ?? 'Selesai') }}
                                @elseif($plan->status->value === 'cancelled')
                                    Batal: {{ $plan->cancellation_reason ?: '-' }}
                                @else
                                    Belum Ada Laporan
                                @endif
                            </td>
                            <td>
                                @if($plan->visitReport)
                                    {{ $plan->visitReport->next_step_summary ?: ($plan->visitReport->followUps->pluck('action_plan')->implode(', ') ?: '-') }}
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" style="text-align: center; padding: 32px; color: #9ca3af; font-style: italic;">
                                Tidak ada data kunjungan pada rentang tanggal yang dipilih.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Metadata footer -->
    <div style="max-width: 960px; margin: 0 auto 40px; padding: 0 20px;">
        <div style="font-size: 11px; color: #9ca3af; display: flex; gap: 20px; flex-wrap: wrap;">
            <span>📋 Field Force: <strong>{{ strtoupper($fieldForce) }}</strong></span>
            <span>📍 Rayon: <strong>{{ strtoupper($rayon) }}</strong></span>
            <span>📅 Minggu ke-{{ $weekNo }} — {{ strtoupper($monthName) }}</span>
            <span>🕐 Diekspor: {{ now()->translatedFormat('d F Y, H:i') }}</span>
        </div>
    </div>

    <script>
        function copyTableToClipboard() {
            const table = document.getElementById('sheets-table');
            const range = document.createRange();
            range.selectNode(table);
            const selection = window.getSelection();
            selection.removeAllRanges();
            selection.addRange(range);

            try {
                document.execCommand('copy');
                // Show toast
                const toast = document.getElementById('toast');
                toast.classList.add('show');
                setTimeout(() => toast.classList.remove('show'), 3000);
                // Button feedback
                const btn = document.getElementById('btnCopy');
                btn.classList.add('copied');
                btn.innerHTML = '✅ Tersalin!';
                setTimeout(() => {
                    btn.classList.remove('copied');
                    btn.innerHTML = '📋 Salin Tabel ke Clipboard';
                }, 3000);
            } catch (err) {
                alert('Gagal menyalin. Silakan pilih tabel secara manual dan tekan Ctrl+C.');
            }
            selection.removeAllRanges();
        }
    </script>
</body>
</html>
