<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Export Google Docs - SAMARA</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', Arial, sans-serif;
            background: #eff6ff;
            color: #1a1a1a;
            min-height: 100vh;
        }

        /* Top Action Bar */
        .action-bar {
            position: sticky;
            top: 0;
            z-index: 999;
            background: linear-gradient(135deg, #1e3a5f 0%, #1d4ed8 50%, #2563eb 100%);
            color: #ffffff;
            padding: 16px 28px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 20px rgba(29, 78, 216, 0.3);
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
            background: #ffffff; color: #1d4ed8;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .btn-copy:hover { background: #dbeafe; transform: translateY(-1px); }
        .btn-copy.copied { background: #bfdbfe; color: #1e3a8a; }
        .btn-back {
            background: rgba(255,255,255,0.15); color: #ffffff;
            border: 1px solid rgba(255,255,255,0.3);
        }
        .btn-back:hover { background: rgba(255,255,255,0.25); }

        /* Toast notification */
        .toast {
            position: fixed; top: 80px; right: 24px;
            background: #1d4ed8; color: #fff;
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
            max-width: 800px; margin: 20px auto; padding: 0 20px;
        }
        .instructions-card {
            background: #ffffff;
            border: 1px solid #bfdbfe;
            border-radius: 12px;
            padding: 18px 22px;
            display: flex; gap: 16px; align-items: flex-start;
        }
        .instructions-card .icon { font-size: 28px; flex-shrink: 0; }
        .instructions-card .steps { font-size: 13px; line-height: 1.7; color: #374151; }
        .instructions-card .steps strong { color: #1d4ed8; }
        .step-num {
            display: inline-flex; width: 22px; height: 22px;
            background: #1d4ed8; color: #fff; border-radius: 50%;
            font-size: 11px; font-weight: 800;
            align-items: center; justify-content: center;
            margin-right: 6px;
        }

        /* Document Container */
        .doc-container {
            max-width: 800px; margin: 16px auto 40px; padding: 0 20px;
        }
        .doc-page {
            background: #ffffff;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            padding: 48px 56px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            line-height: 1.7;
            font-size: 13px;
        }

        /* Document Typography */
        .doc-page .doc-title {
            text-align: center;
            font-size: 18px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #111827;
            margin-bottom: 6px;
        }
        .doc-page .doc-subtitle {
            text-align: center;
            font-size: 12px;
            color: #6b7280;
            margin-bottom: 28px;
            padding-bottom: 16px;
            border-bottom: 2px solid #e5e7eb;
        }
        .doc-page .meta-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4px 32px;
            margin-bottom: 24px;
            font-size: 13px;
        }
        .doc-page .meta-item {
            display: flex; gap: 6px;
        }
        .doc-page .meta-label { font-weight: 700; color: #374151; min-width: 100px; }
        .doc-page .meta-value { color: #111827; }

        .doc-page .section-divider {
            height: 2px;
            background: linear-gradient(90deg, #1d4ed8, #93c5fd, transparent);
            margin: 24px 0;
            border: none;
        }

        /* Visit entries */
        .visit-entry {
            margin-bottom: 24px;
            padding: 18px;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            border-left: 4px solid #1d4ed8;
        }
        .visit-entry .entry-header {
            font-weight: 700;
            font-size: 14px;
            color: #1d4ed8;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .visit-entry .entry-num {
            display: inline-flex; width: 26px; height: 26px;
            background: #1d4ed8; color: #fff; border-radius: 50%;
            font-size: 12px; font-weight: 800;
            align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .visit-entry .entry-grid {
            display: grid;
            grid-template-columns: 130px 1fr;
            gap: 6px 0;
            font-size: 12.5px;
        }
        .visit-entry .field-label {
            font-weight: 600;
            color: #4b5563;
        }
        .visit-entry .field-value {
            color: #111827;
        }
        .visit-entry .field-value.empty {
            color: #9ca3af; font-style: italic;
        }

        .doc-footer {
            margin-top: 32px;
            padding-top: 16px;
            border-top: 1px solid #e5e7eb;
            font-size: 11px;
            color: #9ca3af;
            text-align: center;
        }

        .empty-state {
            text-align: center;
            padding: 48px 24px;
            color: #9ca3af;
        }
        .empty-state .icon { font-size: 40px; margin-bottom: 10px; }

        @media print {
            .action-bar, .instructions, .toast { display: none !important; }
            .doc-container { margin: 0; padding: 0; max-width: 100%; }
            .doc-page { border: none; box-shadow: none; padding: 20px; }
        }

        @media (max-width: 768px) {
            .action-bar { flex-direction: column; gap: 12px; align-items: stretch; padding: 14px 16px; }
            .action-bar .actions { justify-content: flex-end; }
            .instructions-card { flex-direction: column; gap: 10px; }
            .doc-page { padding: 24px 20px; }
            .doc-page .meta-grid { grid-template-columns: 1fr; }
            .visit-entry .entry-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <!-- Action Bar -->
    <div class="action-bar">
        <div class="info">
            <div class="title">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/></svg>
                Export ke Google Docs
            </div>
            <div class="subtitle">Dokumen Rencana & Realisasi Kunjungan — Format dokumen siap salin</div>
        </div>
        <div class="actions">
            <a href="{{ route('reports.index', ['start_date' => $startDate, 'end_date' => $endDate]) }}" class="btn-action btn-back">
                ← Kembali
            </a>
            <button id="btnCopy" class="btn-action btn-copy" onclick="copyDocToClipboard()">
                📋 Salin Dokumen ke Clipboard
            </button>
        </div>
    </div>

    <!-- Toast -->
    <div id="toast" class="toast">
        ✅ Dokumen berhasil disalin! Paste di Google Docs (Ctrl+V)
    </div>

    <!-- Instructions -->
    <div class="instructions">
        <div class="instructions-card">
            <div class="icon">💡</div>
            <div class="steps">
                <div><span class="step-num">1</span> Klik tombol <strong>"Salin Dokumen ke Clipboard"</strong> di atas</div>
                <div><span class="step-num">2</span> Buka <strong><a href="https://docs.google.com/document/create" target="_blank" style="color: #1d4ed8;">Google Docs</a></strong> di tab baru</div>
                <div><span class="step-num">3</span> Klik area dokumen, lalu tekan <strong>Ctrl + V</strong> untuk menempel</div>
            </div>
        </div>
    </div>

    <!-- Document -->
    <div class="doc-container">
        <div class="doc-page" id="doc-content">
            <div class="doc-title">Rencana Kunjungan dan Realisasi Kunjungan</div>
            <div class="doc-subtitle">Laporan Aktivitas Lapangan — SAMARA</div>

            <div class="meta-grid">
                <div class="meta-item">
                    <span class="meta-label">Field Force</span>
                    <span class="meta-value">: {{ strtoupper($fieldForce) }}</span>
                </div>
                <div class="meta-item">
                    <span class="meta-label">Minggu ke</span>
                    <span class="meta-value">: {{ $weekNo }}</span>
                </div>
                <div class="meta-item">
                    <span class="meta-label">Rayon</span>
                    <span class="meta-value">: {{ strtoupper($rayon) }}</span>
                </div>
                <div class="meta-item">
                    <span class="meta-label">Bulan</span>
                    <span class="meta-value">: {{ strtoupper($monthName) }}</span>
                </div>
            </div>

            <hr class="section-divider">

            @forelse($plans as $index => $plan)
                <div class="visit-entry">
                    <div class="entry-header">
                        <span class="entry-num">{{ $index + 1 }}</span>
                        {{ $plan->planned_date->translatedFormat('l, d F Y') }}
                    </div>
                    <div class="entry-grid">
                        <div class="field-label">Customer</div>
                        <div class="field-value">{{ $plan->customer->customer_name ?? '-' }}</div>

                        <div class="field-label">Target</div>
                        <div class="field-value {{ ($plan->specific_objective ?? $plan->monthly_objective) ? '' : 'empty' }}">
                            {{ $plan->specific_objective ?? $plan->monthly_objective ?? 'Belum ditentukan' }}
                        </div>

                        <div class="field-label">Keterangan</div>
                        <div class="field-value {{ ($plan->resource_notes ?? $plan->location_text) ? '' : 'empty' }}">
                            {{ $plan->resource_notes ?? $plan->location_text ?? '-' }}
                        </div>

                        <div class="field-label">Pejabat Ditemui</div>
                        <div class="field-value">
                            {{ $plan->visitReport->attendance_summary ?? $plan->customer->contact_name ?? '-' }}
                            @if($plan->customer->contact_position)
                                <span style="color: #6b7280;"> — {{ $plan->customer->contact_position }}</span>
                            @endif
                        </div>

                        <div class="field-label">Hasil Kunjungan</div>
                        <div class="field-value">
                            @if($plan->visitReport)
                                {{ $plan->visitReport->outcome_summary ?: ($plan->visitReport->outcome_type->label() ?? 'Selesai') }}
                            @elseif($plan->status->value === 'cancelled')
                                <span style="color: #dc2626;">Batal: {{ $plan->cancellation_reason ?: '-' }}</span>
                            @else
                                <span class="empty">Belum Ada Laporan</span>
                            @endif
                        </div>

                        <div class="field-label">Tindak Lanjut</div>
                        <div class="field-value">
                            @if($plan->visitReport)
                                {{ $plan->visitReport->next_step_summary ?: ($plan->visitReport->followUps->pluck('action_plan')->implode(', ') ?: '-') }}
                            @else
                                <span class="empty">-</span>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="empty-state">
                    <div class="icon">📝</div>
                    <div>Tidak ada data kunjungan pada rentang tanggal yang dipilih.</div>
                </div>
            @endforelse

            <div class="doc-footer">
                Dokumen ini diekspor dari sistem SAMARA pada {{ now()->translatedFormat('d F Y, H:i') }} WIB
            </div>
        </div>
    </div>

    <script>
        function copyDocToClipboard() {
            const doc = document.getElementById('doc-content');
            const range = document.createRange();
            range.selectNode(doc);
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
                    btn.innerHTML = '📋 Salin Dokumen ke Clipboard';
                }, 3000);
            } catch (err) {
                alert('Gagal menyalin. Silakan pilih konten secara manual dan tekan Ctrl+C.');
            }
            selection.removeAllRanges();
        }
    </script>
</body>
</html>
