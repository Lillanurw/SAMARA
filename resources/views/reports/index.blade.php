@extends('layouts.app')

@section('title', 'Laporan Kunjungan')
@section('page_title', 'Laporan Hasil Kunjungan (Visit Reports)')
@section('page_subtitle', 'Pengisian dan Pelaporan Aktivitas Lapangan')

@section('content')
<!-- Filter & Export Action Bar -->
<div class="samara-card" style="margin-bottom: 20px;">
    <form method="GET" action="{{ route('reports.index') }}" style="display: flex; gap: 14px; flex-wrap: wrap; align-items: flex-end; justify-content: space-between;">
        <div style="display: flex; gap: 12px; flex-wrap: wrap; align-items: flex-end; flex: 1;">
            <div style="min-width: 160px;">
                <label class="form-label" style="font-weight: 700; font-size: 12px;">Tanggal Mulai</label>
                <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
            </div>
            <div style="min-width: 160px;">
                <label class="form-label" style="font-weight: 700; font-size: 12px;">Tanggal Selesai</label>
                <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
            </div>
            <div style="display: flex; gap: 8px;">
                <button type="submit" class="btn btn-primary" style="min-height: 38px;">
                    🔍 Filter Tanggal
                </button>
                <a href="{{ route('reports.index') }}" class="btn btn-secondary" style="min-height: 38px; text-decoration: none; display: inline-flex; align-items: center;">
                    ↺ Reset
                </a>
            </div>
        </div>

        <div style="position: relative;" x-data="{ exportOpen: false }">
            <button type="button"
                    class="btn btn-primary"
                    @click="exportOpen = !exportOpen"
                    style="min-height: 38px; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; padding: 0 16px; border-radius: var(--radius-sm); cursor: pointer;">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                <span>Export Laporan</span>
                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="transition: transform 0.2s;" :style="exportOpen ? 'transform: rotate(180deg);' : ''"><path d="M6 9l6 6 6-6"/></svg>
            </button>

            <div x-show="exportOpen"
                 @click.away="exportOpen = false"
                 class="dropdown-menu"
                 style="position: absolute; right: 0; top: 100%; margin-top: 6px; min-width: 230px; z-index: 100; padding: 6px; background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); box-shadow: var(--shadow-lg);"
                 x-cloak>
                
                <div style="font-size: 10px; font-weight: 800; color: var(--text-muted); padding: 6px 12px 4px; text-transform: uppercase; letter-spacing: 0.5px;">
                    Format Dokumen
                </div>

                <a href="{{ route('reports.exportPdf', ['start_date' => $startDate, 'end_date' => $endDate]) }}"
                   target="_blank"
                   class="dropdown-item"
                   style="display: flex; align-items: center; gap: 10px; padding: 8px 12px; border-radius: var(--radius-sm); font-size: 13px; font-weight: 600; color: var(--text); text-decoration: none;">
                    <span style="font-size: 15px;">📄</span>
                    <span>Export PDF</span>
                </a>

                <a href="{{ route('reports.exportExcel', ['start_date' => $startDate, 'end_date' => $endDate]) }}"
                   class="dropdown-item"
                   style="display: flex; align-items: center; gap: 10px; padding: 8px 12px; border-radius: var(--radius-sm); font-size: 13px; font-weight: 600; color: var(--text); text-decoration: none;">
                    <span style="font-size: 15px;">📊</span>
                    <span>Export Excel (.xlsx)</span>
                </a>

                <div style="height: 1px; background: var(--border); margin: 4px 0;"></div>

                <div style="font-size: 10px; font-weight: 800; color: var(--text-muted); padding: 6px 12px 4px; text-transform: uppercase; letter-spacing: 0.5px;">
                    Google Workspace
                </div>

                <a href="{{ route('reports.exportGsheets', ['start_date' => $startDate, 'end_date' => $endDate]) }}"
                   class="dropdown-item"
                   style="display: flex; align-items: center; gap: 10px; padding: 8px 12px; border-radius: var(--radius-sm); font-size: 13px; font-weight: 600; color: #15803d; text-decoration: none;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 3v3h-7V6h7zm-8 3H4V6h7v3zM4 11h7v3H4v-3zm9 0h7v3h-7v-3zm7 7h-7v-3h7v3zm-8 0H4v-3h7v3z"/></svg>
                    <span>Export ke Google Sheets</span>
                </a>

                <a href="{{ route('reports.exportGdocs', ['start_date' => $startDate, 'end_date' => $endDate]) }}"
                   class="dropdown-item"
                   style="display: flex; align-items: center; gap: 10px; padding: 8px 12px; border-radius: var(--radius-sm); font-size: 13px; font-weight: 600; color: #1d4ed8; text-decoration: none;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/></svg>
                    <span>Export ke Google Docs</span>
                </a>
            </div>
        </div>
    </form>
</div>

<!-- Matrix Preview Container -->
<div class="samara-card" style="margin-bottom: 24px; overflow-x: auto;">
    <div class="card-header-title" style="margin-bottom: 16px;">
        <span>Pratinjau Format Matrix: Rencana & Realisasi Kunjungan</span>
    </div>
    <div style="background: #ffffff; border: 1px solid var(--border); border-radius: var(--radius-md); padding: 12px; overflow-x: auto;">
        @include('reports.export_table')
    </div>
</div>

<!-- Pending Visit Reports Box -->
@if($pendingPlans->isNotEmpty())
    <div class="samara-card" style="border-left: 4px solid var(--warning); background: #fffdf5; margin-bottom: 24px;">
        <div class="card-header-title" style="color: var(--warning);">
            <span>⚠️ Kunjungan Membutuhkan Laporan ({{ $pendingPlans->count() }})</span>
        </div>

        <div class="grid grid-3">
            @foreach($pendingPlans as $plan)
                <div style="background: white; padding: 16px; border-radius: var(--radius-sm); border: 1px solid var(--border);">
                    <div style="font-weight: 700; color: var(--primary); font-size: 14px;">{{ $plan->customer->customer_name }}</div>
                    <div style="font-size: 12px; color: var(--text-secondary); margin: 4px 0;">Tanggal: {{ $plan->planned_date->format('d/m/Y') }}</div>
                    <a href="{{ route('reports.create', ['visit_plan_id' => $plan->id]) }}" class="btn btn-sm btn-primary" style="width: 100%; margin-top: 10px;">
                        Isi Laporan Sekarang
                    </a>
                </div>
            @endforeach
        </div>
    </div>
@endif

<!-- Submitted Reports Table -->
<div class="samara-card">
    <div class="card-header-title">
        <span>Riwayat Laporan Kunjungan</span>
    </div>

    @if($reports->isEmpty())
        <div style="text-align: center; padding: 48px; color: var(--text-secondary);">
            <div style="font-size: 36px; margin-bottom: 8px;">📝</div>
            <div>Belum ada laporan kunjungan yang tersimpan.</div>
        </div>
    @else
        <div class="table-responsive">
            <table class="samara-table">
                <thead>
                    <tr>
                        <th>No. Laporan</th>
                        <th>Tanggal & Pelapor</th>
                        <th>Customer</th>
                        <th>Hasil & Score</th>
                        <th>Status Arahan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reports as $rep)
                        <tr>
                            <td>
                                <div style="font-weight: 700; color: var(--primary);">{{ $rep->report_number }}</div>
                                <div style="font-size: 11px; color: var(--text-secondary);">Plan: {{ $rep->visitPlan->plan_number ?? '-' }}</div>
                            </td>
                            <td>
                                <div style="font-weight: 600;">{{ $rep->submitted_at ? $rep->submitted_at->format('d/m/Y H:i') : '-' }}</div>
                                <div style="font-size: 12px; color: var(--text-secondary);">{{ $rep->submitter->full_name ?? '-' }}</div>
                            </td>
                            <td>
                                <div style="font-weight: 700;">{{ $rep->visitPlan->customer->customer_name ?? '-' }}</div>
                                <div style="font-size: 12px; color: var(--text-secondary);">📍 {{ $rep->visitPlan->area->area_name ?? '-' }}</div>
                            </td>
                            <td>
                                <span class="badge badge-blue">{{ $rep->outcome_type->label() ?? $rep->outcome_type }}</span>
                                <div style="font-size: 12px; font-weight: 700; margin-top: 4px; color: var(--warning);">
                                    ★ {{ $rep->engagement_score }} / 5 Engagement
                                </div>
                            </td>
                            <td>
                                @if($rep->directorInputs->isNotEmpty())
                                    <span class="badge badge-lime">📢 {{ $rep->directorInputs->count() }} Arahan Director</span>
                                @else
                                    <span class="badge badge-gray">Belum ada arahan</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('reports.show', $rep->id) }}" class="btn btn-sm btn-secondary">Lihat Detail</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div style="margin-top: 20px;">
            {{ $reports->links() }}
        </div>
    @endif
</div>
@endsection
