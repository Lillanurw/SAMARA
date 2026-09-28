<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rencana Kunjungan dan Realisasi Kunjungan - SAMARA</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 10mm;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            margin: 0;
            padding: 16px;
            background-color: #f8fafc;
            color: #0f172a;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background-color: #ffffff;
                padding: 0;
            }
        }
        .print-control-bar {
            position: sticky;
            top: 0;
            z-index: 999;
            background: #1e293b;
            color: #ffffff;
            padding: 14px 24px;
            border-radius: 10px;
            margin-bottom: 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.2);
        }
        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            border: none;
            transition: all 0.2s;
        }
        .btn-print {
            background-color: #10b981;
            color: #ffffff;
        }
        .btn-print:hover {
            background-color: #059669;
        }
        .btn-back {
            background-color: #475569;
            color: #ffffff;
        }
        .btn-back:hover {
            background-color: #334155;
        }
        .document-wrapper {
            background: #ffffff;
            padding: 24px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            max-width: 1200px;
            margin: 0 auto;
        }
    </style>
</head>
<body>
    <div class="print-control-bar no-print">
        <div>
            <div style="font-weight: 800; font-size: 15px;">📄 Pratinjau Cetak / PDF</div>
            <div style="font-size: 12px; opacity: 0.85;">Dokumen Rencana dan Realisasi Kunjungan Lapangan</div>
        </div>
        <div style="display: flex; gap: 12px;">
            <a href="{{ route('reports.index', ['start_date' => $startDate, 'end_date' => $endDate]) }}" class="btn-action btn-back">
                ← Kembali ke Laporan
            </a>
            <button onclick="window.print()" class="btn-action btn-print">
                🖨️ Cetak / Download PDF
            </button>
        </div>
    </div>

    <div class="document-wrapper">
        @include('reports.export_table')
    </div>
</body>
</html>
