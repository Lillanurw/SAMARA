@extends('layouts.app')

@section('title', 'Monthly Plan')
@section('page_title', 'Monthly Visit Plan')
@section('page_subtitle', 'Perencanaan Kunjungan Bulanan Sales & Marketing')

@push('styles')
<style>
.monthly-calendar-container {
    background: var(--surface, #ffffff);
    border-radius: var(--radius-lg, 16px);
    border: 1px solid var(--border, #dce3ea);
    box-shadow: var(--shadow, 0 6px 20px rgba(23,54,93,.07));
    overflow: hidden;
    margin-bottom: 24px;
}

.monthly-calendar-header {
    display: grid !important;
    grid-template-columns: repeat(7, 1fr) !important;
    background-color: #1b365d !important;
    color: #ffffff !important;
    font-weight: 700;
    font-size: 13px;
    text-align: center;
    border-bottom: 1px solid var(--border, #dce3ea);
}

.monthly-calendar-header .day-name {
    padding: 12px 4px !important;
    letter-spacing: 0.3px;
    text-align: center;
}

.monthly-calendar-grid {
    display: grid !important;
    grid-template-columns: repeat(7, 1fr) !important;
    grid-auto-rows: 120px !important;
    background-color: var(--border, #dce3ea) !important;
    gap: 1px !important;
}

html.dark .monthly-calendar-grid {
    background-color: #344054 !important;
}

.monthly-calendar-cell {
    background-color: var(--surface, #ffffff) !important;
    height: 120px !important;
    min-height: 120px !important;
    max-height: 120px !important;
    padding: 8px !important;
    display: flex !important;
    flex-direction: column !important;
    position: relative;
    box-sizing: border-box !important;
    overflow: hidden !important;
}

html.dark .monthly-calendar-cell {
    background-color: #1f2937 !important;
}

.monthly-calendar-cell.other-month {
    background-color: var(--surface-soft, #f8fafc) !important;
    opacity: 0.55;
}

html.dark .monthly-calendar-cell.other-month {
    background-color: #1a2435 !important;
}

.monthly-calendar-cell.is-today {
    background-color: #f0fdf4 !important;
}

html.dark .monthly-calendar-cell.is-today {
    background-color: rgba(16, 185, 129, 0.12) !important;
}

.monthly-calendar-cell .day-number {
    font-size: 13px;
    font-weight: 700;
    color: var(--text, #1f2937);
    margin-bottom: 6px;
    line-height: 1;
}

html.dark .monthly-calendar-cell .day-number {
    color: #f8fafc;
}

.monthly-calendar-cell.is-today .day-number {
    color: var(--success, #2e8b57);
}

.monthly-calendar-events {
    display: flex !important;
    flex-direction: column !important;
    gap: 4px !important;
    overflow-y: auto;
    max-height: 85px;
}

.calendar-event-pill {
    display: flex !important;
    align-items: center !important;
    gap: 5px !important;
    padding: 3px 6px !important;
    border-radius: 4px !important;
    font-size: 11px !important;
    font-weight: 600 !important;
    background-color: #e0f2fe !important;
    color: #0369a1 !important;
    border-left: 3px solid #0284c7 !important;
    text-decoration: none !important;
    white-space: nowrap !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
    line-height: 1.3 !important;
}

.calendar-event-pill:hover {
    background-color: #bae6fd !important;
    color: #075985 !important;
}

.calendar-event-pill.status-completed {
    background-color: #dcfce7 !important;
    color: #15803d !important;
    border-left-color: #16a34a !important;
}

.calendar-event-pill.status-in_progress {
    background-color: #fef3c7 !important;
    color: #b45309 !important;
    border-left-color: #d97706 !important;
}

.calendar-event-pill.status-cancelled {
    background-color: #f3f4f6 !important;
    color: #4b5563 !important;
    border-left-color: #9ca3af !important;
}

.calendar-event-pill .event-time {
    font-weight: 700;
    flex-shrink: 0;
}

.calendar-event-pill .event-title {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
</style>
@endpush

@section('content')
<div x-data="{ addModalOpen: false }">

    <!-- Header Title Bar (Picture 1 style header) -->
    <div style="margin-bottom: 16px;">
        <h2 style="font-size: 20px; font-weight: 800; color: var(--primary);">
            Perencanaan Bulanan: {{ $carbonMonth->translatedFormat('F Y') }}
        </h2>
    </div>

    <!-- Filter & Action Header (Retained from Picture 2) -->
    <div class="samara-filter-bar">
        <form method="GET" action="{{ route('plans.index') }}" style="display: flex; gap: 14px; flex-wrap: wrap; align-items: flex-end;">
            <div style="flex: 1; min-width: 140px;">
                <label class="form-label">Pilih Bulan</label>
                <input type="month" name="month" class="form-control" value="{{ $month }}" onchange="this.form.submit()">
            </div>

            <div style="flex: 1; min-width: 160px;">
                <label class="form-label">Area</label>
                <select name="area_id" class="form-select" onchange="this.form.submit()">
                    <option value="">Semua Area</option>
                    @foreach($areas as $area)
                        <option value="{{ $area->id }}" {{ $areaId == $area->id ? 'selected' : '' }}>{{ $area->area_name }}</option>
                    @endforeach
                </select>
            </div>

            <div style="flex: 1; min-width: 160px;">
                <label class="form-label">Segment</label>
                <select name="segment_id" class="form-select" onchange="this.form.submit()">
                    <option value="">Semua Segment</option>
                    @foreach($segments as $segment)
                        <option value="{{ $segment->id }}" {{ $segmentId == $segment->id ? 'selected' : '' }}>{{ $segment->segment_name }}</option>
                    @endforeach
                </select>
            </div>

            <div style="flex: 1; min-width: 140px;">
                <label class="form-label">Prioritas</label>
                <select name="priority" class="form-select" onchange="this.form.submit()">
                    <option value="">Semua Prioritas</option>
                    <option value="LOW" {{ $priority == 'LOW' ? 'selected' : '' }}>Rendah</option>
                    <option value="MEDIUM" {{ $priority == 'MEDIUM' ? 'selected' : '' }}>Sedang</option>
                    <option value="HIGH" {{ $priority == 'HIGH' ? 'selected' : '' }}>Tinggi</option>
                    <option value="CRITICAL" {{ $priority == 'CRITICAL' ? 'selected' : '' }}>Kritis</option>
                </select>
            </div>

            <div style="flex: 1; min-width: 160px;">
                <label class="form-label">PIC (Owner)</label>
                <select name="owner_id" class="form-select" onchange="this.form.submit()">
                    <option value="">Semua PIC</option>
                    @foreach($teamUsers as $tu)
                        <option value="{{ $tu->id }}" {{ $ownerId == $tu->id ? 'selected' : '' }}>{{ $tu->full_name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <button type="button" @click="addModalOpen = true" class="btn btn-primary">
                    + Tambah Rencana
                </button>
            </div>
        </form>
    </div>

    <!-- Monthly Calendar Grid View (Picture 1 style) -->
    <div class="monthly-calendar-container" style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
        <div style="min-width: 680px;">
            <!-- Days Header -->
            <div class="monthly-calendar-header">
                <div class="day-name">Sen</div>
                <div class="day-name">Sel</div>
                <div class="day-name">Rab</div>
                <div class="day-name">Kam</div>
                <div class="day-name">Jum</div>
                <div class="day-name">Sab</div>
                <div class="day-name">Min</div>
            </div>

            <!-- Grid Cells -->
            <div class="monthly-calendar-grid">
                @php
                    $totalCells = ceil(($startOfWeekOffset + $daysInMonth) / 7) * 7;
                    $todayDateStr = \Carbon\Carbon::now()->format('Y-m-d');
                @endphp

                @for($cell = 0; $cell < $totalCells; $cell++)
                    @if($cell < $startOfWeekOffset)
                        <div class="monthly-calendar-cell other-month"></div>
                    @elseif($cell < $startOfWeekOffset + $daysInMonth)
                        @php
                            $dayNum = $cell - $startOfWeekOffset + 1;
                            $dateStr = sprintf('%s-%02d', $month, $dayNum);
                            $dayPlans = $plansByDate->get($dateStr, collect());
                            $isToday = ($dateStr === $todayDateStr);
                        @endphp
                        <div class="monthly-calendar-cell {{ $isToday ? 'is-today' : '' }}">
                            <div class="day-number">{{ $dayNum }}</div>
                            <div class="monthly-calendar-events">
                                @foreach($dayPlans as $p)
                                    @php
                                        $stVal = $p->status?->value ?? (string)$p->status;
                                        $statusPillClass = match($stVal) {
                                            'COMPLETED'   => 'status-completed',
                                            'IN_PROGRESS' => 'status-in_progress',
                                            'CANCELLED'   => 'status-cancelled',
                                            default       => '',
                                        };
                                        $timeFormatted = substr($p->start_time, 0, 5);
                                    @endphp
                                    <a href="{{ route('plans.show', $p->id) }}"
                                       class="calendar-event-pill {{ $statusPillClass }}"
                                       title="{{ $timeFormatted }} - {{ $p->customer->customer_name }} ({{ $p->activity_type?->label() ?? $p->activity_type }})">
                                        <span class="event-time">{{ $timeFormatted }}</span>
                                        <span class="event-title">{{ $p->customer->customer_name }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="monthly-calendar-cell other-month"></div>
                    @endif
                @endfor
            </div>
        </div>
    </div>

    <!-- Monthly Plans List / Table (Daftar Rencana - Picture 1 style) -->
    <div class="samara-card" style="padding: 0; overflow: hidden; margin-top: 24px;">
        <div style="padding: 18px 24px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border);">
            <h3 style="font-size: 18px; font-weight: 800; color: var(--primary); margin: 0;">Daftar Rencana</h3>
            <span class="badge badge-navy" style="font-size: 12px; padding: 4px 12px;">{{ $plans->count() }} kunjungan</span>
        </div>

        @if($plans->isEmpty())
            <div style="text-align: center; padding: 48px; color: var(--samara-text-secondary);">
                <div style="font-size: 36px; margin-bottom: 8px;">📅</div>
                <div style="font-weight: 700; font-size: 16px; color: var(--samara-text);">Belum Ada Rencana Kunjungan</div>
                <p style="font-size: 13px; margin-top: 4px;">Klik tombol "Tambah Rencana" untuk membuat agenda kunjungan baru bulan ini.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="samara-table">
                    <thead style="background-color: #1b365d; color: white;">
                        <tr>
                            <th style="color: white; background: #1b365d;">Tanggal</th>
                            <th style="color: white; background: #1b365d;">Customer</th>
                            <th style="color: white; background: #1b365d;">Area</th>
                            <th style="color: white; background: #1b365d;">Owner</th>
                            <th style="color: white; background: #1b365d;">Priority</th>
                            <th style="color: white; background: #1b365d;">Status</th>
                            <th style="color: white; background: #1b365d; text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($plans as $p)
                            <tr>
                                <td>
                                    <div style="font-weight: 700; color: var(--text);">{{ $p->planned_date->translatedFormat('d M Y') }}</div>
                                    <div style="font-size: 12px; color: var(--text-secondary);">{{ substr($p->start_time, 0, 5) }} - {{ substr($p->end_time, 0, 5) }}</div>
                                </td>
                                <td>
                                    <div style="font-weight: 700; color: var(--primary);">{{ $p->customer->customer_name }}</div>
                                    <div style="font-size: 12px; color: var(--text-secondary);">{{ $p->activity_type?->label() ?? $p->activity_type }}</div>
                                </td>
                                <td>
                                    <div style="font-weight: 600;">📍 {{ $p->area->area_name }}</div>
                                </td>
                                <td>
                                    <div style="font-weight: 600;">{{ $p->owner->full_name }}</div>
                                    @if($p->members->isNotEmpty())
                                        <div style="font-size: 11px; color: var(--text-secondary);">+{{ $p->members->pluck('full_name')->join(', ') }}</div>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-gray" style="font-size: 11px;">{{ $p->priority?->label() ?? $p->priority }}</span>
                                </td>
                                <td>
                                    @php
                                        $statusClass = match($p->status?->value ?? (string)$p->status) {
                                            'COMPLETED'   => 'badge-green',
                                            'IN_PROGRESS' => 'badge-gold',
                                            'APPROVED'    => 'badge-cyan',
                                            'PLANNED'     => 'badge-navy',
                                            'CANCELLED'   => 'badge-gray',
                                            'RESCHEDULED' => 'badge-gold',
                                            'DRAFT'       => 'badge-gray',
                                            default       => 'badge-gray',
                                        };
                                    @endphp
                                    <span class="badge {{ $statusClass }}">{{ $p->status?->label() ?? $p->status }}</span>
                                </td>
                                <td style="text-align: center; white-space: nowrap;">
                                    <div style="display: flex; gap: 6px; justify-content: center;">
                                        <a href="{{ route('plans.show', $p->id) }}" class="btn btn-sm btn-secondary">Detail / Edit</a>
                                        @can('delete', $p)
                                            @if(!$p->visitReport)
                                                <form action="{{ route('plans.destroy', $p->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus rencana kunjungan ini?');" style="display: inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" style="padding: 4px 8px; font-size: 11px;">🗑️ Hapus</button>
                                                </form>
                                            @endif
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- Modal Tambah Rencana Kunjungan -->
    <div x-show="addModalOpen" style="position: fixed; inset: 0; background: rgba(0,0,0,0.4); z-index: 2000; display: flex; align-items: center; justify-content: center; padding: 20px;" x-cloak>
        <div style="background: white; width: 100%; max-width: 600px; border-radius: var(--samara-radius-large); padding: 28px; max-height: 90vh; overflow-y: auto; box-shadow: var(--samara-shadow-hover);" @click.away="addModalOpen = false">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid var(--samara-border); padding-bottom: 14px;">
                <h3 style="font-weight: 800; font-size: 18px; color: var(--samara-navy);">Tambah Rencana Kunjungan</h3>
                <button type="button" @click="addModalOpen = false" style="background: none; border: none; font-size: 22px; cursor: pointer; color: var(--samara-text-secondary);">&times;</button>
            </div>

            <form action="{{ route('plans.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label class="form-label">Pilih Customer *</label>
                    <select name="customer_id" class="form-select" required>
                        <option value="">-- Pilih Customer --</option>
                        @foreach($customers as $cust)
                            <option value="{{ $cust->id }}">
                                {{ $cust->customer_code }} - {{ $cust->customer_name }} ({{ $cust->area->area_name }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-3">
                    <div class="form-group">
                        <label class="form-label">Tanggal Kunjungan *</label>
                        <input type="date" name="planned_date" class="form-control" required value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Waktu Mulai *</label>
                        <input type="time" name="start_time" class="form-control" required value="09:00">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Waktu Selesai *</label>
                        <input type="time" name="end_time" class="form-control" required value="11:00">
                    </div>
                </div>

                <div class="grid grid-2">
                    <div class="form-group">
                        <label class="form-label">Tipe Aktivitas *</label>
                        <select name="activity_type" class="form-select" required>
                            <option value="SALES_VISIT">Sales Visit</option>
                            <option value="ACCOUNT_MAINTENANCE">Account Maintenance</option>
                            <option value="PRODUCT_DEMO">Product Demo</option>
                            <option value="TECHNICAL_SURVEY">Technical Survey</option>
                            <option value="MARKETING_ENGAGEMENT">Marketing Engagement</option>
                            <option value="EVENT">Event / Exhibition</option>
                            <option value="OTHER">Lainnya</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Prioritas *</label>
                        <select name="priority" class="form-select" required>
                            <option value="LOW">Rendah</option>
                            <option value="MEDIUM" selected>Sedang</option>
                            <option value="HIGH">Tinggi</option>
                            <option value="CRITICAL">Kritis</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Tujuan Bulanan (Monthly Objective) *</label>
                    <textarea name="monthly_objective" class="form-control" rows="2" required placeholder="Contoh: Penetrasi produk sepatu boots untuk kuartal III"></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Tujuan Spesifik Kunjungan (Specific Objective) *</label>
                    <textarea name="specific_objective" class="form-control" rows="2" required placeholder="Contoh: Demo sampel produk kepada Kapten Suwandi"></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Catatan Sumber Daya / Peralatan Pembawa</label>
                    <input type="text" name="resource_notes" class="form-control" placeholder="Contoh: Sample boots size 42, brosur fisik">
                </div>

                <div class="form-group">
                    <label class="form-label">Anggota Tim Pendamping (Opsional)</label>
                    <select name="members[]" class="form-select" multiple style="height: 80px;">
                        @foreach($teamUsers as $userItem)
                            @if($userItem->id !== auth()->id())
                                <option value="{{ $userItem->id }}">{{ $userItem->full_name }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>

                <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 24px;">
                    <button type="button" @click="addModalOpen = false" class="btn btn-secondary">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Rencana</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
