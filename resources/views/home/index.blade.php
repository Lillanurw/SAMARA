@extends('layouts.app')

@section('title', 'Home')
@section('page_title', 'Halo, ' . (str_contains(auth()->user()->full_name ?? '', ' - ') ? explode(' ', trim(explode('-', auth()->user()->full_name)[1]))[0] : explode(' ', auth()->user()->full_name ?? '')[0]) . '!')
@section('page_subtitle', \Carbon\Carbon::now()->translatedFormat('l, d F Y'))

@section('header_actions')
<div class="page-actions">
    <a href="{{ route('plans.index') }}" class="btn btn-primary">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
        Tambah Rencana
    </a>
    <a href="{{ route('reports.index') }}" class="btn btn-secondary">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6M8 13h8M8 17h6"/></svg>
        Isi Laporan
    </a>
</div>
@endsection

@section('content')
<div class="home-dashboard">
    <aside class="home-kpi-rail" aria-label="Ringkasan aktivitas">
        <a href="{{ route('weekly.index') }}" class="home-kpi-card">
            <span class="home-kpi-icon tone-blue"><svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="17" rx="2"/><path d="M8 2v4M16 2v4M3 10h18"/></svg></span>
            <span><strong>{{ $todayAgenda->count() }}</strong><small>Agenda Hari Ini</small></span>
        </a>
        <a href="{{ route('reports.index') }}" class="home-kpi-card">
            <span class="home-kpi-icon tone-gold"><svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6M8 13h8M8 17h6"/></svg></span>
            <span><strong>{{ $pendingReportPlans->count() }}</strong><small>Laporan Tertunda</small></span>
        </a>
        <a href="{{ route('followups.index', ['tab' => 'overdue']) }}" class="home-kpi-card">
            <span class="home-kpi-icon tone-red"><svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M12 9v4M12 17h.01"/><path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"/></svg></span>
            <span><strong>{{ $overdueFollowUps->count() }}</strong><small>Follow-up Overdue</small></span>
        </a>
        <a href="{{ route('directions.myDirections') }}" class="home-kpi-card">
            <span class="home-kpi-icon tone-cyan"><svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/></svg></span>
            <span><strong>{{ $directionCounts['open'] + $directionCounts['in_progress'] }}</strong><small>Arahan Aktif</small></span>
        </a>
    </aside>

    <section class="home-main-panel">
        <div class="samara-card home-agenda-card">
            <div class="home-card-head">
                <div><h2>Agenda Hari Ini</h2><p>Prioritas kunjungan yang perlu Anda jalankan hari ini.</p></div>
                <a href="{{ route('weekly.index') }}" class="text-link">Lihat My Week</a>
            </div>
            @if($todayAgenda->isEmpty())
                <div class="home-empty"><span class="home-empty-icon"><svg viewBox="0 0 24 24"><path d="M8 2v4M16 2v4M3 10h18"/><rect x="3" y="4" width="18" height="17" rx="2"/></svg></span><strong>Tidak ada agenda hari ini</strong><p>Belum ada kunjungan yang perlu dijalankan.</p></div>
            @else
                <div class="home-table-wrap" style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
                    <table class="home-table" style="min-width: 560px;">
                        <thead><tr><th>Waktu</th><th>Customer</th><th>Aktivitas</th><th>Status</th><th style="text-align: right;">Aksi</th></tr></thead>
                        <tbody>
                        @foreach($todayAgenda as $item)
                            @php($status = $item->status?->value ?? (string) $item->status)
                            <tr>
                                <td class="nowrap">{{ substr($item->start_time, 0, 5) }}–{{ substr($item->end_time, 0, 5) }}</td>
                                <td><strong>{{ $item->customer->customer_name }}</strong><small>{{ $item->customer->city ?? $item->area->area_name ?? '-' }}</small></td>
                                <td>{{ $item->activity_type?->label() ?? $item->activity_type }}</td>
                                <td><span class="status-pill status-{{ strtolower($status) }}">{{ str_replace('_', ' ', $status) }}</span></td>
                                <td class="home-row-actions">
                                    @if($status === 'PLANNED')
                                        <form action="{{ route('weekly.start', $item->id) }}" method="POST">@csrf @method('PATCH')<button class="btn btn-sm btn-primary" type="submit">Mulai</button></form>
                                    @elseif($status === 'IN_PROGRESS')
                                        <a class="btn btn-sm btn-accent" href="{{ route('reports.create', ['visit_plan_id' => $item->id]) }}">Isi Laporan</a>
                                    @endif
                                    <a class="icon-action" href="{{ route('plans.show', $item->id) }}" aria-label="Lihat detail"><svg viewBox="0 0 24 24"><path d="m9 18 6-6-6-6"/></svg></a>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <div class="home-lower-grid">
            <div class="samara-card">
                <div class="home-card-head"><div><h2>Laporan Tertunda</h2><p>Kunjungan selesai yang belum dilaporkan.</p></div><span class="count-pill tone-gold">{{ $pendingReportPlans->count() }}</span></div>
                <div class="compact-list">
                    @forelse($pendingReportPlans->take(4) as $plan)
                        <div class="compact-row"><span><strong>{{ $plan->customer->customer_name }}</strong><small>{{ $plan->planned_date->translatedFormat('d M Y') }} · {{ $plan->activity_type?->label() ?? $plan->activity_type }}</small></span><a href="{{ route('reports.create', ['visit_plan_id' => $plan->id]) }}" class="btn btn-sm btn-secondary">Isi</a></div>
                    @empty
                        <div class="mini-empty">Semua kunjungan sudah dilaporkan.</div>
                    @endforelse
                </div>
            </div>

            <div class="samara-card">
                <div class="home-card-head"><div><h2>Arahan Director</h2><p>Arahan terbaru yang perlu ditindaklanjuti.</p></div><a href="{{ route('directions.myDirections') }}" class="text-link">Lihat Semua</a></div>
                <div class="compact-list">
                    @forelse($recentDirections->take(4) as $dir)
                        <a href="{{ route('directions.myDirections') }}" class="compact-row compact-row-link"><span><strong>{{ $dir->topic }}</strong><small>{{ $dir->creator->full_name }} · Tenggat {{ $dir->due_date ? $dir->due_date->format('d/m/Y') : '—' }}</small></span><span class="status-pill status-{{ strtolower($dir->status?->value ?? (string)$dir->status) }}">{{ str_replace('_', ' ', $dir->status?->value ?? (string)$dir->status) }}</span></a>
                    @empty
                        <div class="mini-empty">Belum ada arahan Director.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="home-lower-grid">
            <div class="samara-card">
                <div class="home-card-head"><div><h2>Follow-up Overdue</h2><p>Tindakan yang telah melewati tenggat.</p></div><a href="{{ route('followups.index', ['tab' => 'overdue']) }}" class="text-link">Lihat Semua</a></div>
                <div class="compact-list">
                    @forelse($overdueFollowUps->take(4) as $fu)
                        <div class="compact-row"><span><strong>{{ $fu->action_title }}</strong><small>{{ $fu->customer->customer_name }} · {{ $fu->owner->full_name }}</small></span><span class="status-pill status-overdue">{{ $fu->due_date->format('d/m/Y') }}</span></div>
                    @empty
                        <div class="mini-empty">Tidak ada follow-up overdue.</div>
                    @endforelse
                </div>
            </div>

            <div class="samara-card">
                <div class="home-card-head"><div><h2>Agenda Mendatang</h2><p>Rencana kunjungan dalam tujuh hari ke depan.</p></div><a href="{{ route('weekly.index') }}" class="text-link">My Week</a></div>
                <div class="compact-list">
                    @forelse($upcomingAgenda->take(4) as $up)
                        <a href="{{ route('plans.show', $up->id) }}" class="compact-row compact-row-link"><span><strong>{{ $up->customer->customer_name }}</strong><small>{{ $up->planned_date->translatedFormat('d M Y') }} · {{ substr($up->start_time, 0, 5) }}</small></span><span class="status-pill status-planned">{{ $up->activity_type?->label() ?? $up->activity_type }}</span></a>
                    @empty
                        <div class="mini-empty">Belum ada agenda mendatang.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
