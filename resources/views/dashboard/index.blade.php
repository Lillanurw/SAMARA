@extends('layouts.app')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard Aktivitas & Performa')
@section('page_subtitle', 'Sales and Marketing Activity Reporting and Analytics')

@push('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js" defer></script>
@endpush

@section('content')

{{-- =====================================================
     MODE TABS + FILTER BAR
====================================================== --}}
<div class="dashboard-header-bar">
    <div class="dashboard-mode-btns">
        <a href="{{ route('dashboard.index', ['type' => 'personal', 'month' => $month]) }}"
           class="btn {{ $type === 'personal' ? 'btn-primary' : 'btn-secondary' }}"
           id="btn-dashboard-personal">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M20 21a8 8 0 10-16 0"/></svg>
            Dashboard Saya
        </a>
        <a href="{{ route('dashboard.index', ['type' => 'overall', 'month' => $month]) }}"
           class="btn {{ $type === 'overall' ? 'btn-primary' : 'btn-secondary' }}"
           id="btn-dashboard-overall">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 010 20M12 2a15.3 15.3 0 000 20"/></svg>
            Dashboard Keseluruhan
        </a>
    </div>

    <form method="GET" action="{{ route('dashboard.index') }}" class="dashboard-filter-form">
        <input type="hidden" name="type" value="{{ $type }}">
        <input type="month" name="month" class="form-control dashboard-filter-input" value="{{ $month }}" onchange="this.form.submit()">
        @if($areas->count() > 1)
            <select name="area_id" class="form-select dashboard-filter-select" onchange="this.form.submit()">
                <option value="">Semua Area</option>
                @foreach($areas as $area)
                    <option value="{{ $area->id }}" {{ $areaId == $area->id ? 'selected' : '' }}>{{ $area->area_name }}</option>
                @endforeach
            </select>
        @endif
    </form>
</div>

{{-- =====================================================
     KPI CARDS – ROW 1 (4 columns)
====================================================== --}}
<div class="grid grid-4" style="margin-bottom: 20px;">

    <div class="samara-kpi-card samara-card-hover">
        <div class="kpi-icon-box icon-box-navy" aria-hidden="true">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        </div>
        <div>
            <div class="kpi-num" style="color:var(--primary);">{{ $plannedVisitsCount }}</div>
            <div class="kpi-desc">Planned Visits</div>
        </div>
    </div>

    <div class="samara-kpi-card samara-card-hover">
        <div class="kpi-icon-box icon-box-green" aria-hidden="true">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
        </div>
        <div>
            <div class="kpi-num" style="color:var(--success);">{{ $completedVisitsCount }}</div>
            <div class="kpi-desc">Completed Visits</div>
        </div>
    </div>

    <div class="samara-kpi-card samara-card-hover">
        <div class="kpi-icon-box icon-box-cyan" aria-hidden="true">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        </div>
        <div>
            <div class="kpi-num" style="color:var(--accent);">{{ $completionRate }}%</div>
            <div class="kpi-desc">Completion Rate</div>
        </div>
    </div>

    <div class="samara-kpi-card samara-card-hover">
        <div class="kpi-icon-box icon-box-gold" aria-hidden="true">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
        </div>
        <div>
            <div class="kpi-num" style="color:var(--warning);">{{ $avgEngagement }}</div>
            <div class="kpi-desc">Avg Engagement (1–5)</div>
        </div>
    </div>
</div>

{{-- KPI ROW 2 (3 columns) --}}
<div class="grid grid-3" style="margin-bottom: 24px;">

    <div class="samara-kpi-card samara-card-hover">
        <div class="kpi-icon-box icon-box-navy" aria-hidden="true">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
        </div>
        <div>
            <div class="kpi-num" style="color:var(--primary);">{{ $customerCoveragePct }}%</div>
            <div class="kpi-desc">Customer Coverage ({{ $totalCustomersCount }} Aktif)</div>
        </div>
    </div>

    <div class="samara-kpi-card samara-card-hover">
        <div class="kpi-icon-box icon-box-red" aria-hidden="true">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/></svg>
        </div>
        <div>
            <div class="kpi-num" style="color:var(--danger);">{{ $overdueFollowUpsCount }}</div>
            <div class="kpi-desc">Overdue Follow-ups</div>
        </div>
    </div>

    <div class="samara-kpi-card samara-card-hover">
        <div class="kpi-icon-box icon-box-cyan" aria-hidden="true">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 2L11 13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
        </div>
        <div>
            <div class="kpi-num" style="color:var(--accent);">{{ $directionStats['open'] + $directionStats['in_progress'] }}</div>
            <div class="kpi-desc">Active Directions</div>
        </div>
    </div>
</div>

{{-- =====================================================
     CHARTS – ROW 1
====================================================== --}}
<div class="grid grid-2" style="margin-bottom: 24px;">
    <div class="samara-card" style="min-width:0; max-width:100%; overflow:hidden;">
        <div class="card-header-title">Status Kunjungan (Planned vs Completed)</div>
        <div style="height: 250px; position: relative; width: 100%; max-width: 100%; min-width: 0;">
            <canvas id="statusChart" aria-label="Grafik status kunjungan" role="img"></canvas>
        </div>
    </div>

    <div class="samara-card" style="min-width:0; max-width:100%; overflow:hidden;">
        <div class="card-header-title">Hasil Kunjungan (Outcome)</div>
        <div style="height: 250px; position: relative; width: 100%; max-width: 100%; min-width: 0;">
            <canvas id="outcomeChart" aria-label="Grafik hasil kunjungan" role="img"></canvas>
        </div>
    </div>
</div>

{{-- CHARTS – ROW 2 --}}
<div class="grid grid-2">
    <div class="samara-card" style="min-width:0; max-width:100%; overflow:hidden;">
        <div class="card-header-title">Aktivitas per Wilayah / Area</div>
        <div style="height: 250px; position: relative; width: 100%; max-width: 100%; min-width: 0;">
            <canvas id="areaChart" aria-label="Grafik aktivitas per area" role="img"></canvas>
        </div>
    </div>

    {{-- Direction Status Tracker --}}
    <div class="samara-card" style="min-width:0; max-width:100%; overflow:hidden;">
        <div class="card-header-title">Tracking Status Arahan Director</div>
        <div style="display:flex; flex-direction:column; gap:10px; margin-top:8px;">
            @php
                $dirItems = [
                    ['label' => 'Arahan Baru (Open)',           'count' => $directionStats['open'],        'badge' => 'badge-navy',  'bg' => 'var(--accent-soft)'],
                    ['label' => 'Sudah Dibaca (Acknowledged)',  'count' => $directionStats['acknowledged'], 'badge' => 'badge-cyan',  'bg' => 'var(--accent-soft)'],
                    ['label' => 'Dalam Proses (In Progress)',   'count' => $directionStats['in_progress'], 'badge' => 'badge-gold',  'bg' => 'var(--warning-soft)'],
                    ['label' => 'Terlambat (Overdue)',          'count' => $directionStats['overdue'],     'badge' => 'badge-red',   'bg' => 'var(--danger-soft)'],
                    ['label' => 'Selesai (Closed)',             'count' => $directionStats['closed'],      'badge' => 'badge-green', 'bg' => 'var(--success-soft)'],
                ];
            @endphp
            @foreach($dirItems as $item)
            <div style="display:flex; justify-space-between; align-items:center; padding:11px 14px; background:{{ $item['bg'] }}; border-radius:var(--radius-sm);">
                <span style="font-size:13px; font-weight:600; color:var(--text);">{{ $item['label'] }}</span>
                <span class="badge {{ $item['badge'] }}" style="font-size:13px; min-width:32px; justify-content:center;">{{ $item['count'] }}</span>
            </div>
            @endforeach
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const isDark = document.documentElement.classList.contains('dark');
    const isMobile = window.innerWidth < 768;
    const gridColor = isDark ? 'rgba(255,255,255,0.08)' : 'rgba(0,0,0,0.06)';
    const labelColor = isDark ? '#CBD5E1' : '#667085';

    Chart.defaults.font.family = "'Plus Jakarta Sans', 'Inter', sans-serif";
    Chart.defaults.color = labelColor;

    // Label formatting map for clean UI
    const labelMap = {
        'COMPLETED': 'Selesai',
        'IN_PROGRESS': 'Berjalan',
        'PLANNED': 'Rencana',
        'CANCELLED': 'Batal',
        'RESCHEDULED': 'Dijadwal Ulang',
        'PROPOSAL': 'Proposal',
        'DEAL': 'Deal',
        'NO_CHANGE': 'Tanpa Perubahan',
        'FOLLOW_UP_REQUIRED': 'Perlu Follow-up',
        'OTHER': 'Lainnya'
    };

    function formatLabel(str) {
        return labelMap[str] || str.replace(/_/g, ' ');
    }

    // Navy-blue corporate palette
    const palette = ['#17365D', '#1F4E78', '#00A6B2', '#2E8B57', '#C69214', '#C33C3C', '#344054'];

    // 1. Status Doughnut
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    const statusData = @json($statusCounts);
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: Object.keys(statusData).map(formatLabel),
            datasets: [{
                data: Object.values(statusData),
                backgroundColor: palette,
                borderWidth: 2,
                borderColor: isDark ? '#1F2937' : '#fff',
                hoverOffset: 6,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '62%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: isMobile ? 8 : 14,
                        boxWidth: 10,
                        font: { size: isMobile ? 11 : 12 },
                        usePointStyle: true,
                        pointStyle: 'circle'
                    }
                }
            }
        }
    });

    // 2. Outcome Bar
    const outcomeCtx = document.getElementById('outcomeChart').getContext('2d');
    const outcomeData = @json($outcomeData);
    new Chart(outcomeCtx, {
        type: 'bar',
        data: {
            labels: Object.keys(outcomeData).map(formatLabel),
            datasets: [{
                label: 'Jumlah Kunjungan',
                data: Object.values(outcomeData),
                backgroundColor: '#1F4E78',
                borderRadius: 6,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { color: gridColor }, ticks: { color: labelColor, precision: 0 } },
                x: {
                    grid: { display: false },
                    ticks: {
                        color: labelColor,
                        font: { size: isMobile ? 10 : 12 },
                        maxRotation: isMobile ? 20 : 0,
                        autoSkip: false
                    }
                }
            }
        }
    });

    // 3. Area Pie
    const areaCtx = document.getElementById('areaChart').getContext('2d');
    const areaData = @json($areaActivityData);
    new Chart(areaCtx, {
        type: 'pie',
        data: {
            labels: Object.keys(areaData).map(formatLabel),
            datasets: [{
                data: Object.values(areaData),
                backgroundColor: palette,
                borderWidth: 2,
                borderColor: isDark ? '#1F2937' : '#fff',
                hoverOffset: 6,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: isMobile ? 8 : 14,
                        boxWidth: 10,
                        font: { size: isMobile ? 11 : 12 },
                        usePointStyle: true,
                        pointStyle: 'circle'
                    }
                }
            }
        }
    });
});
</script>
@endpush
