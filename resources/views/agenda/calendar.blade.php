@extends('layouts.app')

@section('title', 'Kalender Agenda — ' . auth()->user()->display_name)
@section('page_title', 'Kalender Agenda')
@section('page_subtitle', 'Kelola jadwal dan agenda ' . auth()->user()->display_name)

@push('styles')
<style>
/* =====================================================
   AGENDA CALENDAR & LIST COMPONENT STYLES
===================================================== */
@media print {
    .agenda-view-toggle, .btn, .agenda-legend-bar, .agenda-cal-toolbar, .agenda-list-filters, .pagination {
        display: none !important;
    }
    .agenda-print-header {
        display: block !important;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #333;
    }
}

.agenda-page-container {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

/* Header & Toggle */
.agenda-view-toggle {
    display: inline-flex;
    background: var(--surface-soft);
    padding: 4px;
    border-radius: var(--radius-md, 10px);
    border: 1px solid var(--border);
}
html.dark .agenda-view-toggle {
    background: var(--surface-soft);
    border-color: var(--border);
}
.agenda-view-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    font-size: 13px;
    font-weight: 700;
    border-radius: var(--radius-sm, 6px);
    text-decoration: none;
    color: var(--text-secondary);
    transition: all 0.2s ease;
}
.agenda-view-btn.active {
    background: var(--primary);
    color: var(--on-primary) !important;
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
}

/* Legend */
.agenda-legend-bar {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 8px 12px;
    background: var(--surface);
    padding: 12px 18px;
    border-radius: var(--radius-md, 10px);
    border: 1px solid var(--border);
    box-shadow: 0 1px 3px rgba(0,0,0,0.03);
}
html.dark .agenda-legend-bar {
    background: var(--surface);
    border-color: var(--border);
}
.agenda-legend-item {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 11px;
    font-weight: 600;
    padding: 4px 10px;
    border-radius: 99px;
}

/* Calendar Navigation Toolbar */
.agenda-cal-toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
    background: var(--surface);
    padding: 14px 18px;
    border-radius: var(--radius-md, 10px);
    border: 1px solid var(--border);
}
html.dark .agenda-cal-toolbar {
    background: var(--surface);
    border-color: var(--border);
}

/* Calendar Container */
.agenda-calendar-container {
    background: var(--surface);
    border-radius: var(--radius-lg, 14px);
    border: 1px solid var(--border);
    box-shadow: 0 4px 14px rgba(0,0,0,0.04);
    overflow: hidden;
}
html.dark .agenda-calendar-container {
    background: var(--surface);
    border-color: var(--border);
}

.agenda-calendar-header {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    background-color: var(--calendar-head-bg) !important;
    color: var(--calendar-head-text) !important;
    font-weight: 700;
    font-size: 13px;
    text-align: center;
    border-bottom: 1px solid var(--border);
    opacity: 1 !important;
}
html.dark .agenda-calendar-header {
    background-color: var(--calendar-head-bg) !important;
    color: var(--calendar-head-text) !important;
    border-bottom: 1px solid var(--border);
}
.agenda-calendar-header .day-name {
    padding: 10px 4px;
    letter-spacing: 0.3px;
    color: var(--calendar-head-text) !important;
    opacity: 1 !important;
}
html.dark .agenda-calendar-header .day-name {
    color: var(--calendar-head-text) !important;
    opacity: 1 !important;
}

/* Month Grid */
.agenda-calendar-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    grid-auto-rows: 125px;
    background-color: var(--border);
    gap: 1px;
}
html.dark .agenda-calendar-grid {
    background-color: var(--border);
}

.agenda-calendar-cell {
    background-color: var(--surface);
    height: 125px;
    padding: 6px 8px;
    display: flex;
    flex-direction: column;
    position: relative;
    box-sizing: border-box;
    overflow: hidden;
    cursor: pointer;
    transition: background 0.15s ease;
}
.agenda-calendar-cell:hover {
    background-color: rgba(27, 54, 93, 0.03);
}
html.dark .agenda-calendar-cell {
    background-color: var(--surface);
}
html.dark .agenda-calendar-cell:hover {
    background-color: var(--surface-soft);
}

.agenda-calendar-cell.other-month {
    background-color: var(--surface-soft);
    opacity: 0.55;
}
html.dark .agenda-calendar-cell.other-month {
    background-color: var(--surface-soft);
    opacity: 0.55;
}

.agenda-calendar-cell.is-today {
    background-color: #f0fdf4 !important;
}
html.dark .agenda-calendar-cell.is-today {
    background-color: rgba(16, 185, 129, 0.12) !important;
}

.agenda-calendar-cell .cell-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 4px;
}

.agenda-calendar-cell .day-number {
    font-size: 12px;
    font-weight: 700;
    color: var(--text, #1f2937);
    width: 22px;
    height: 22px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
}
html.dark .agenda-calendar-cell .day-number {
    color: #f1f5f9;
}
.agenda-calendar-cell.is-today .day-number {
    background: #16a34a;
    color: #ffffff !important;
}

.agenda-cell-events {
    display: flex;
    flex-direction: column;
    gap: 3px;
    overflow-y: auto;
    max-height: 92px;
}

.agenda-event-pill {
    display: flex;
    align-items: center;
    gap: 4px;
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 600;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    line-height: 1.3;
    border: none;
    text-align: left;
    width: 100%;
    cursor: pointer;
    transition: transform 0.1s ease, filter 0.15s ease;
}
.agenda-event-pill:hover {
    transform: translateY(-1px);
    filter: brightness(0.95);
}
.agenda-event-pill.status-cancelled {
    text-decoration: line-through;
    opacity: 0.6;
}
.agenda-event-pill.status-completed .status-check {
    font-weight: 800;
}

.agenda-more-pill {
    font-size: 10px;
    font-weight: 700;
    color: var(--primary, #1b365d);
    background: var(--surface-soft, #f1f5f9);
    padding: 1px 6px;
    border-radius: 4px;
    text-align: center;
    cursor: pointer;
}
html.dark .agenda-more-pill {
    background: var(--surface-soft);
    color: var(--link);
}

/* Week Grid */
.agenda-week-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 12px;
    padding: 16px;
}
.agenda-week-col {
    background: var(--surface-soft);
    border: 1px solid var(--border);
    border-radius: var(--radius-md, 10px);
    display: flex;
    flex-direction: column;
    min-height: 480px;
}
html.dark .agenda-week-col {
    background: var(--surface);
    border-color: var(--border);
}
.agenda-week-col-header {
    padding: 10px 8px;
    text-align: center;
    border-bottom: 1px solid var(--border);
    background: var(--surface);
    border-radius: var(--radius-md, 10px) var(--radius-md, 10px) 0 0;
}
html.dark .agenda-week-col-header {
    background: var(--surface-soft);
    border-color: var(--border);
}
.agenda-week-col.is-today .agenda-week-col-header {
    background: #dcfce7;
    border-bottom-color: #86efac;
}
html.dark .agenda-week-col.is-today .agenda-week-col-header {
    background: rgba(16, 185, 129, 0.2);
    border-bottom-color: #059669;
}
.agenda-week-events {
    padding: 10px 8px;
    display: flex;
    flex-direction: column;
    gap: 8px;
    flex: 1;
    overflow-y: auto;
}
.agenda-week-card {
    padding: 8px 10px;
    border-radius: 6px;
    font-size: 11px;
    cursor: pointer;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
    transition: transform 0.15s ease;
}
.agenda-week-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

/* Mobile responsive dots */
.calendar-mobile-dots {
    display: none;
    gap: 3px;
    flex-wrap: wrap;
    margin-top: 2px;
}
.calendar-event-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
}

@media (max-width: 768px) {
    .agenda-calendar-grid {
        grid-auto-rows: 56px;
    }
    .agenda-calendar-cell {
        height: 56px;
        padding: 4px;
        align-items: center;
    }
    .agenda-cell-events {
        display: none !important;
    }
    .calendar-mobile-dots {
        display: flex;
    }
    .agenda-week-grid {
        grid-template-columns: 1fr;
    }
}

/* Quick Filter Pills */
.agenda-quick-pills {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    align-items: center;
}
.agenda-quick-pill {
    padding: 6px 14px;
    border-radius: 99px;
    font-size: 12px;
    font-weight: 700;
    text-decoration: none;
    border: 1px solid var(--border, #e2e8f0);
    background: var(--surface, #ffffff);
    color: var(--text-secondary, #64748b);
    transition: all 0.15s ease;
}
.agenda-quick-pill:hover {
    background: var(--surface-soft, #f8fafc);
    color: var(--primary, #1b365d);
}
.agenda-quick-pill.active {
    background: var(--primary, #1b365d);
    color: #ffffff !important;
    border-color: var(--primary, #1b365d);
}
html.dark .agenda-quick-pill {
    background: var(--surface-soft);
    border-color: var(--border);
    color: var(--text-secondary);
}
html.dark .agenda-quick-pill.active {
    background: var(--primary);
    color: var(--on-primary) !important;
    border-color: var(--primary);
}

/* Date Group Header in Table */
.agenda-date-group-header {
    background: var(--surface-soft);
    padding: 10px 16px;
    font-weight: 800;
    font-size: 13px;
    color: var(--heading);
    border-top: 1px solid var(--border);
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    gap: 8px;
}
html.dark .agenda-date-group-header {
    background: var(--surface-soft);
    border-color: var(--border);
    color: var(--heading);
}
</style>
@endpush

@section('header_actions')
<div style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
    <!-- View Switcher Tabs -->
    <div class="agenda-view-toggle">
        <a href="{{ route('agenda.index', array_merge(request()->query(), ['view' => 'kalender'])) }}"
           class="agenda-view-btn {{ $view === 'kalender' ? 'active' : '' }}"
           id="tab-view-kalender">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            <span>Kalender</span>
        </a>
        <a href="{{ route('agenda.index', array_merge(request()->query(), ['view' => 'daftar'])) }}"
           class="agenda-view-btn {{ $view === 'daftar' ? 'active' : '' }}"
           id="tab-view-daftar">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
            <span>Daftar</span>
        </a>
    </div>

    <!-- Tambah Agenda Button -->
    <button type="button" class="btn btn-primary" onclick="openCreateAgendaModal()" id="btn-tambah-agenda">
        + Tambah Agenda
    </button>
</div>
@endsection

@section('content')
<div class="agenda-page-container" x-data="agendaApp()">

    {{-- Kop Cetak / Print Header --}}
    <div class="agenda-print-header" style="display: none;">
        <h2 style="font-size: 18px; font-weight: 800; margin: 0 0 4px 0;">SAMARA — Agenda Kegiatan</h2>
        <div style="font-size: 14px; font-weight: 700;">{{ auth()->user()->display_name }} (Direktur Utama)</div>
        <div style="font-size: 12px; color: var(--text-secondary); margin-top: 2px;">Dicetak pada: {{ now('Asia/Jakarta')->translatedFormat('l, d F Y H:i') }} WIB</div>
    </div>

    <!-- ----------------------------------------------------
         1. LEGENDA WARNA TIPE AGENDA
    ----------------------------------------------------- -->
    <div class="agenda-legend-bar" aria-label="Legenda Tipe Agenda">
        <span style="font-size: 11px; font-weight: 800; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.5px;">Tipe Agenda:</span>
        @foreach($agendaTypes as $type)
            <div class="agenda-legend-item agenda-chip {{ $type->cssClass() }}" style="background: {{ $type->color() }}; color: {{ $type->textColor() }};">
                <span style="width: 8px; height: 8px; border-radius: 50%; background: currentColor; display: inline-block;"></span>
                <span>{{ $type->label() }}</span>
            </div>
        @endforeach
    </div>

    @if($view === 'kalender')
    <!-- ====================================================
         2. TAB KALENDER (BULAN & MINGGU)
    ===================================================== -->
    <div class="agenda-cal-toolbar">
        <!-- Navigation Buttons & Title -->
        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
            <a href="{{ route('agenda.index', array_merge(request()->query(), ['date' => $prevDate, 'view' => 'kalender'])) }}"
               class="btn btn-sm btn-outline-secondary"
               title="Sebelumnya">
                &larr; Prev
            </a>
            <a href="{{ route('agenda.index', array_merge(request()->query(), ['date' => $todayDate, 'view' => 'kalender'])) }}"
               class="btn btn-sm btn-outline-primary">
                Hari Ini
            </a>
            <a href="{{ route('agenda.index', array_merge(request()->query(), ['date' => $nextDate, 'view' => 'kalender'])) }}"
               class="btn btn-sm btn-outline-secondary"
               title="Berikutnya">
                Next &rarr;
            </a>
            <h2 style="font-size: 18px; font-weight: 800; color: var(--heading); margin: 0 0 0 8px;">
                {{ $periodLabel }}
            </h2>
        </div>

        <!-- Mode Toggle & Filter -->
        <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
            <!-- Filter Form -->
            <form method="GET" action="{{ route('agenda.index') }}" style="display: flex; gap: 8px; align-items: center;">
                <input type="hidden" name="view" value="kalender">
                <input type="hidden" name="mode" value="{{ $mode }}">
                <input type="hidden" name="date" value="{{ $anchorDate->toDateString() }}">

                <select name="type" class="form-select form-select-sm" onchange="this.form.submit()" style="font-size: 12px;">
                    <option value="">Semua Tipe</option>
                    @foreach($agendaTypes as $type)
                        <option value="{{ $type->value }}" {{ request('type') == $type->value ? 'selected' : '' }}>{{ $type->label() }}</option>
                    @endforeach
                </select>

                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()" style="font-size: 12px;">
                    <option value="">Semua Status</option>
                    @foreach($agendaStatuses as $st)
                        <option value="{{ $st->value }}" {{ request('status') == $st->value ? 'selected' : '' }}>{{ $st->label() }}</option>
                    @endforeach
                </select>

                @if(request('type') || request('status'))
                    <a href="{{ route('agenda.index', ['view' => 'kalender', 'mode' => $mode, 'date' => $anchorDate->toDateString()]) }}"
                       class="btn btn-sm btn-outline-secondary"
                       style="font-size: 11px;">Reset</a>
                @endif
            </form>

            <!-- Mode Bulan / Minggu Button Group -->
            <div class="agenda-view-toggle" style="margin: 0;">
                <a href="{{ route('agenda.index', array_merge(request()->query(), ['mode' => 'bulan', 'view' => 'kalender'])) }}"
                   class="agenda-view-btn {{ $mode === 'bulan' ? 'active' : '' }}"
                   style="padding: 4px 12px; font-size: 12px;">
                    Bulan
                </a>
                <a href="{{ route('agenda.index', array_merge(request()->query(), ['mode' => 'minggu', 'view' => 'kalender'])) }}"
                   class="agenda-view-btn {{ $mode === 'minggu' ? 'active' : '' }}"
                   style="padding: 4px 12px; font-size: 12px;">
                    Minggu
                </a>
            </div>
        </div>
    </div>

    <!-- Calendar View Container -->
    <div class="agenda-calendar-container">
        @if($mode === 'bulan')
            <!-- Days of Week Header -->
            <div class="agenda-calendar-header">
                <div class="day-name">Sen</div>
                <div class="day-name">Sel</div>
                <div class="day-name">Rab</div>
                <div class="day-name">Kam</div>
                <div class="day-name">Jum</div>
                <div class="day-name">Sab</div>
                <div class="day-name">Min</div>
            </div>

            <!-- Month Grid -->
            <div class="agenda-calendar-grid">
                @foreach($calendarDays as $day)
                    @php
                        $dKey = $day['date'];
                        $dayEvents = $eventsByDate[$dKey] ?? [];
                        $eventCount = count($dayEvents);
                    @endphp
                    <div class="agenda-calendar-cell {{ !$day['is_current_month'] ? 'other-month' : '' }} {{ $day['is_today'] ? 'is-today' : '' }}"
                         @click="selectDay('{{ $day['date'] }}', '{{ $day['formatted_date'] }}')"
                         data-date="{{ $day['date'] }}">
                        <div class="cell-header">
                            <span class="day-number">{{ $day['day_number'] }}</span>
                            @if($eventCount > 0)
                                <span style="font-size: 10px; color: var(--text-secondary); font-weight: 700;" class="d-none d-md-inline">
                                    {{ $eventCount }}
                                </span>
                            @endif
                        </div>

                        <!-- Event Pills (Desktop / Tablet) -->
                        <div class="agenda-cell-events">
                            @foreach(array_slice($dayEvents, 0, 3) as $ev)
                                <button type="button"
                                        class="agenda-event-pill agenda-chip {{ $ev['css_class'] ?? '' }} {{ $ev['is_cancelled'] ? 'status-cancelled' : '' }} {{ $ev['is_completed'] ? 'status-completed' : '' }}"
                                        style="background-color: {{ $ev['color'] }}; color: {{ $ev['text_color'] }}; border-left: 3px solid {{ $ev['text_color'] }};"
                                        @click.stop="openEditAgendaModal({{ json_encode($ev) }})"
                                        title="{{ $ev['start_time'] }} - {{ $ev['end_time'] }} : {{ $ev['title'] }} ({{ $ev['type_label'] }})">
                                    <span style="font-weight: 700; flex-shrink: 0;">{{ $ev['start_time'] }}</span>
                                    <span style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                        @if($ev['is_completed']) <span class="status-check">✓</span> @endif
                                        {{ $ev['title'] }}
                                    </span>
                                </button>
                            @endforeach

                            @if($eventCount > 3)
                                <div class="agenda-more-pill" @click.stop="showDayEventsModal('{{ $day['date'] }}', '{{ $day['formatted_date'] }}')">
                                    +{{ $eventCount - 3 }} lagi
                                </div>
                            @endif
                        </div>

                        <!-- Mobile Event Dots -->
                        <div class="calendar-mobile-dots">
                            @foreach(array_slice($dayEvents, 0, 5) as $ev)
                                <span class="calendar-event-dot" style="background-color: {{ $ev['color'] }};" title="{{ $ev['title'] }}"></span>
                            @endforeach
                            @if($eventCount > 5)
                                <span style="font-size: 9px; line-height: 1; font-weight: 700; color: var(--text-secondary);">+</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Mobile Day Event List (Visible on click on mobile screens) -->
            <div class="samara-card d-block d-md-none" style="margin-top: 16px;" x-show="selectedDate">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                    <h3 style="font-size: 15px; font-weight: 800; color: var(--heading);" x-text="'Agenda: ' + selectedDateFormatted"></h3>
                    <button type="button" class="btn btn-sm btn-outline-primary" @click="openCreateModalForDate(selectedDate)">+ Tambah</button>
                </div>
                <template x-if="getSelectedDateEvents().length === 0">
                    <p style="font-size: 12px; color: var(--text-secondary); text-align: center; padding: 12px 0;">Tidak ada agenda pada tanggal ini.</p>
                </template>
                <div style="display: flex; flex-direction: column; gap: 8px;">
                    <template x-for="ev in getSelectedDateEvents()" :key="ev.id">
                        <div class="agenda-week-card"
                             :style="{ backgroundColor: ev.color, color: ev.text_color, borderLeft: '4px solid ' + ev.text_color }"
                             @click="openEditAgendaModal(ev)">
                            <div style="display: flex; justify-content: space-between; font-weight: 700;">
                                <span x-text="ev.start_time + ' - ' + ev.end_time"></span>
                                <span x-text="ev.type_label" style="font-size: 10px;"></span>
                            </div>
                            <div style="font-weight: 800; font-size: 13px; margin: 2px 0;" x-text="(ev.is_completed ? '✓ ' : '') + ev.title" :style="{ textDecoration: ev.is_cancelled ? 'line-through' : 'none' }"></div>
                            <div style="font-size: 11px; opacity: 0.9;" x-show="ev.location" x-text="'📍 ' + ev.location"></div>
                        </div>
                    </template>
                </div>
            </div>

        @else
            <!-- Mode Minggu (Week Grid) -->
            <div class="agenda-week-grid">
                @foreach($calendarDays as $day)
                    @php
                        $dKey = $day['date'];
                        $dayEvents = $eventsByDate[$dKey] ?? [];
                    @endphp
                    <div class="agenda-week-col {{ $day['is_today'] ? 'is-today' : '' }}">
                        <div class="agenda-week-col-header">
                            <div style="font-size: 11px; font-weight: 700; color: var(--text-secondary); text-transform: uppercase;">{{ $day['day_name'] }}</div>
                            <div style="font-size: 16px; font-weight: 800; color: var(--heading);">{{ $day['day_number'] }}</div>
                        </div>

                        <div class="agenda-week-events">
                            @forelse($dayEvents as $ev)
                                <div class="agenda-week-card agenda-chip {{ $ev['css_class'] ?? '' }}"
                                     style="background-color: {{ $ev['color'] }}; color: {{ $ev['text_color'] }}; border-left: 4px solid {{ $ev['text_color'] }};"
                                     @click="openEditAgendaModal({{ json_encode($ev) }})"
                                     title="Klik untuk edit agenda">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2px;">
                                        <span style="font-weight: 800; font-size: 11px;">{{ $ev['start_time'] }} - {{ $ev['end_time'] }}</span>
                                        <span class="badge" style="background: rgba(0,0,0,0.08); font-size: 9px; padding: 2px 6px;">{{ $ev['status_label'] }}</span>
                                    </div>
                                    <div style="font-weight: 800; font-size: 12px; margin-bottom: 3px; {{ $ev['is_cancelled'] ? 'text-decoration: line-through; opacity: 0.6;' : '' }}">
                                        @if($ev['is_completed']) ✓ @endif {{ $ev['title'] }}
                                    </div>
                                    @if(!empty($ev['location']))
                                        <div style="font-size: 10px; opacity: 0.85;">📍 {{ $ev['location'] }}</div>
                                    @endif
                                </div>
                            @empty
                                <div style="text-align: center; color: var(--text-secondary); font-size: 11px; padding: 20px 0;">
                                    Kosong
                                </div>
                            @endforelse
                        </div>

                        <div style="padding: 8px; border-top: 1px solid var(--border, #e2e8f0); text-align: center;">
                            <button type="button"
                                    class="btn btn-sm btn-outline-primary"
                                    style="width: 100%; font-size: 11px; padding: 4px 6px;"
                                    @click="openCreateModalForDate('{{ $day['date'] }}')">
                                + Tambah
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    @else
    <!-- ====================================================
         3. TAB DAFTAR (JADWAL / TABEL BERKELOMPOK)
    ===================================================== -->
    <div class="samara-card" style="padding: 20px;">
        <!-- Quick Select Range Buttons & Custom Filter Form -->
        <form method="GET" action="{{ route('agenda.index') }}" id="filter-daftar-form">
            <input type="hidden" name="view" value="daftar">

            <div style="display: flex; flex-direction: column; gap: 16px;">
                <!-- Quick Filter Range Pills -->
                <div>
                    <label class="form-label" style="margin-bottom: 8px;">Pilihan Rentang Waktu Cepat:</label>
                    <div class="agenda-quick-pills">
                        <a href="{{ route('agenda.index', ['view' => 'daftar', 'range' => 'hari_ini', 'type' => request('type'), 'status' => request('status')]) }}"
                           class="agenda-quick-pill {{ $range === 'hari_ini' ? 'active' : '' }}">Hari Ini</a>
                        <a href="{{ route('agenda.index', ['view' => 'daftar', 'range' => 'besok', 'type' => request('type'), 'status' => request('status')]) }}"
                           class="agenda-quick-pill {{ $range === 'besok' ? 'active' : '' }}">Besok</a>
                        <a href="{{ route('agenda.index', ['view' => 'daftar', 'range' => 'minggu_ini', 'type' => request('type'), 'status' => request('status')]) }}"
                           class="agenda-quick-pill {{ $range === 'minggu_ini' ? 'active' : '' }}">Minggu Ini (Sen-Min)</a>
                        <a href="{{ route('agenda.index', ['view' => 'daftar', 'range' => 'minggu_depan', 'type' => request('type'), 'status' => request('status')]) }}"
                           class="agenda-quick-pill {{ $range === 'minggu_depan' ? 'active' : '' }}">Minggu Depan</a>
                        <a href="{{ route('agenda.index', ['view' => 'daftar', 'range' => 'bulan_ini', 'type' => request('type'), 'status' => request('status')]) }}"
                           class="agenda-quick-pill {{ $range === 'bulan_ini' ? 'active' : '' }}">Bulan Ini</a>
                        <a href="{{ route('agenda.index', ['view' => 'daftar', 'range' => 'mendatang', 'type' => request('type'), 'status' => request('status')]) }}"
                           class="agenda-quick-pill {{ $range === 'mendatang' ? 'active' : '' }}">Semua Mendatang</a>
                    </div>
                </div>

                <!-- Custom Range Inputs & Dropdowns -->
                <div style="display: flex; gap: 12px; flex-wrap: wrap; align-items: flex-end; padding-top: 8px; border-top: 1px dashed var(--border);">
                    <div style="flex: 1; min-width: 130px;">
                        <label class="form-label">Dari Tanggal</label>
                        <input type="date" name="from" value="{{ $from }}" class="form-control" style="font-size: 13px;">
                    </div>
                    <div style="flex: 1; min-width: 130px;">
                        <label class="form-label">Sampai Tanggal</label>
                        <input type="date" name="to" value="{{ $to }}" class="form-control" style="font-size: 13px;">
                    </div>
                    <div style="flex: 1; min-width: 160px;">
                        <label class="form-label">Tipe Agenda</label>
                        <select name="type" class="form-select" style="font-size: 13px;">
                            <option value="">Semua Tipe</option>
                            @foreach($agendaTypes as $type)
                                <option value="{{ $type->value }}" {{ request('type') == $type->value ? 'selected' : '' }}>{{ $type->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div style="flex: 1; min-width: 140px;">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select" style="font-size: 13px;">
                            <option value="">Semua Status</option>
                            @foreach($agendaStatuses as $st)
                                <option value="{{ $st->value }}" {{ request('status') == $st->value ? 'selected' : '' }}>{{ $st->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div style="display: flex; gap: 8px;">
                        <button type="submit" class="btn btn-primary" style="font-size: 13px;">Filter</button>
                        <a href="{{ route('agenda.index', ['view' => 'daftar']) }}" class="btn btn-outline-secondary" style="font-size: 13px;">Reset</a>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Summary Count Badge -->
    <div style="display: flex; justify-content: space-between; align-items: center; padding: 4px 2px;">
        <div style="font-size: 13px; font-weight: 700; color: var(--text-secondary);">
            Menampilkan <span style="color: var(--heading); font-weight: 800;">{{ $totalAgendasInRange }}</span> agenda dalam rentang terpilih
        </div>
    </div>

    <!-- Grouped Agenda Table -->
    <div class="samara-card" style="padding: 0; overflow: hidden;">
        @if($agendas->isEmpty())
            <div style="text-align: center; padding: 48px; color: var(--text-secondary);">
                <div style="font-size: 36px; margin-bottom: 8px;">📅</div>
                <div style="font-weight: 700; font-size: 16px; color: var(--text);">Tidak ada agenda ditemukan</div>
                <p style="font-size: 13px; margin-top: 4px;">Ubah filter rentang atau klik tombol "+ Tambah Agenda" untuk membuat jadwal baru.</p>
            </div>
        @else
            @php
                $groupedAgendas = $agendas->getCollection()->groupBy(fn($a) => $a->start_time->format('Y-m-d'));
            @endphp

            @foreach($groupedAgendas as $dateStr => $dayItems)
                @php
                    $carbonDate = \Carbon\Carbon::parse($dateStr);
                    $isDateToday = $carbonDate->isToday();
                @endphp
                <div class="agenda-date-group-header">
                    <span>📅 {{ $carbonDate->translatedFormat('l, d F Y') }}</span>
                    @if($isDateToday)
                        <span class="badge badge-success" style="font-size: 11px;">Hari ini</span>
                    @endif
                    <span style="font-size: 11px; opacity: 0.7; font-weight: 600; margin-left: auto;">
                        {{ $dayItems->count() }} agenda
                    </span>
                </div>

                <div class="table-responsive">
                    <table class="samara-table" style="margin-bottom: 0;">
                        <thead>
                            <tr>
                                <th style="width: 140px;">Waktu</th>
                                <th>Judul Agenda</th>
                                <th style="width: 200px;">Tipe & Warna</th>
                                <th style="width: 180px;">Lokasi</th>
                                <th style="width: 130px;">Status</th>
                                <th style="width: 130px; text-align: center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($dayItems as $agenda)
                                <tr>
                                    <td>
                                        <div style="font-weight: 700; color: var(--text);">
                                            {{ $agenda->start_time->format('H:i') }} - {{ $agenda->end_time->format('H:i') }}
                                        </div>
                                        @if(!$agenda->start_time->isSameDay($agenda->end_time))
                                            <div style="font-size: 10px; color: var(--text-secondary);">
                                                s/d {{ $agenda->end_time->translatedFormat('d M H:i') }}
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <div style="font-weight: 800; color: var(--text); {{ $agenda->status === \App\Enums\AgendaStatus::DIBATALKAN ? 'text-decoration: line-through; opacity: 0.6;' : '' }}">
                                            @if($agenda->status === \App\Enums\AgendaStatus::SELESAI)
                                                <span style="color: var(--success); font-weight: 800;">✓</span>
                                            @endif
                                            {{ $agenda->title }}
                                        </div>
                                        @if($agenda->description)
                                            <div style="font-size: 12px; color: var(--text-secondary); margin-top: 2px;">
                                                {{ Str::limit($agenda->description, 80) }}
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge agenda-chip {{ $agenda->type->cssClass() }}" style="background: {{ $agenda->type->color() }}; color: {{ $agenda->type->textColor() }}; font-weight: 700;">
                                            {{ $agenda->type->label() }}
                                        </span>
                                    </td>
                                    <td>
                                        {{ $agenda->location ?: '-' }}
                                    </td>
                                    <td>
                                        @php
                                            $stClass = match($agenda->status) {
                                                \App\Enums\AgendaStatus::TERJADWAL => 'badge-navy',
                                                \App\Enums\AgendaStatus::SELESAI   => 'badge-success',
                                                \App\Enums\AgendaStatus::DIBATALKAN => 'badge-gray',
                                            };
                                        @endphp
                                        <span class="badge {{ $stClass }}">
                                            {{ $agenda->status->label() }}
                                        </span>
                                    </td>
                                    <td style="text-align: center;">
                                        <button type="button"
                                                class="btn btn-sm btn-outline-secondary"
                                                @click="openEditAgendaModal({{ json_encode([
                                                    'id' => $agenda->id,
                                                    'title' => $agenda->title,
                                                    'type' => $agenda->type->value,
                                                    'start' => $agenda->start_time->format('Y-m-d H:i'),
                                                    'end' => $agenda->end_time->format('Y-m-d H:i'),
                                                    'location' => $agenda->location ?? '',
                                                    'description' => $agenda->description ?? '',
                                                    'status' => $agenda->status->value
                                                ]) }})"
                                                style="padding: 2px 8px; font-size: 11px;">
                                            Edit
                                        </button>
                                        <form action="{{ route('agenda.destroy', $agenda->id) }}"
                                              method="POST"
                                              style="display: inline;"
                                              onsubmit="return confirm('Apakah Anda yakin ingin menghapus agenda ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="view" value="daftar">
                                            <button type="submit"
                                                    class="btn btn-sm btn-outline-danger"
                                                    style="padding: 2px 8px; font-size: 11px;">
                                                Hapus
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endforeach

            <!-- Pagination -->
            <div style="padding: 16px 20px; border-top: 1px solid var(--border);">
                {{ $agendas->links() }}
            </div>
        @endif
    </div>
    @endif

    <!-- ----------------------------------------------------
         4. MODAL FORM: TAMBAH AGENDA
    ----------------------------------------------------- -->
    <div id="agenda-create-modal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.6); z-index: 2000; align-items: center; justify-content: center; padding: 20px;">
        <div class="samara-card" style="width: 100%; max-width: 520px; max-height: 90vh; overflow-y: auto; box-shadow: var(--shadow-lg);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <h3 style="font-size: 18px; font-weight: 800; color: var(--heading); margin: 0;">Tambah Agenda Baru</h3>
                <button type="button" @click="closeCreateAgendaModal()" style="background: none; border: none; font-size: 20px; cursor: pointer; color: var(--text-secondary);">&times;</button>
            </div>

            <form method="POST" action="{{ route('agenda.store') }}" id="form-create-agenda">
                @csrf
                <input type="hidden" name="view" value="{{ $view }}">
                <input type="hidden" name="mode" value="{{ $mode }}">
                <input type="hidden" name="date" value="{{ $anchorDate->toDateString() }}">

                <div class="form-group" style="margin-bottom: 12px;">
                    <label class="form-label">Judul Agenda <span style="color: var(--danger);">*</span></label>
                    <input type="text" name="title" id="create-title" class="form-control" placeholder="Contoh: Rapat Direksi Evaluasi Kinerja" required>
                </div>

                <div class="form-group" style="margin-bottom: 12px;">
                    <label class="form-label">Tipe Agenda <span style="color: var(--danger);">*</span></label>
                    <select name="type" id="create-type" class="form-select" required>
                        @foreach($agendaTypes as $type)
                            <option value="{{ $type->value }}">{{ $type->label() }}</option>
                        @endforeach
                    </select>
                </div>

                <div style="display: flex; gap: 12px; margin-bottom: 12px;">
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label">Waktu Mulai <span style="color: var(--danger);">*</span></label>
                        <input type="datetime-local" name="start_time" id="create-start" class="form-control" required>
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label">Waktu Selesai <span style="color: var(--danger);">*</span></label>
                        <input type="datetime-local" name="end_time" id="create-end" class="form-control" required>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 12px;">
                    <label class="form-label">Lokasi / Media Pertemuan</label>
                    <input type="text" name="location" id="create-location" class="form-control" placeholder="Ruang Rapat Utama / Zoom Link">
                </div>

                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label">Deskripsi / Catatan Agenda</label>
                    <textarea name="description" id="create-desc" class="form-control" rows="3" placeholder="Tambahkan rincian atau catatan agenda..."></textarea>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 8px;">
                    <button type="button" class="btn btn-outline-secondary" @click="closeCreateAgendaModal()">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Agenda</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ----------------------------------------------------
         5. MODAL FORM: EDIT AGENDA
    ----------------------------------------------------- -->
    <div id="agenda-edit-modal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.6); z-index: 2000; align-items: center; justify-content: center; padding: 20px;">
        <div class="samara-card" style="width: 100%; max-width: 520px; max-height: 90vh; overflow-y: auto; box-shadow: var(--shadow-lg);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <h3 style="font-size: 18px; font-weight: 800; color: var(--heading); margin: 0;">Edit Agenda</h3>
                <button type="button" @click="closeEditAgendaModal()" style="background: none; border: none; font-size: 20px; cursor: pointer; color: var(--text-secondary);">&times;</button>
            </div>

            <form method="POST" id="form-edit-agenda" action="">
                @csrf
                @method('PUT')
                <input type="hidden" name="view" value="{{ $view }}">
                <input type="hidden" name="mode" value="{{ $mode }}">
                <input type="hidden" name="date" value="{{ $anchorDate->toDateString() }}">

                <div class="form-group" style="margin-bottom: 12px;">
                    <label class="form-label">Judul Agenda <span style="color: var(--danger);">*</span></label>
                    <input type="text" name="title" id="edit-title" class="form-control" required>
                </div>

                <div class="form-group" style="margin-bottom: 12px;">
                    <label class="form-label">Tipe Agenda <span style="color: var(--danger);">*</span></label>
                    <select name="type" id="edit-type" class="form-select" required>
                        @foreach($agendaTypes as $type)
                            <option value="{{ $type->value }}">{{ $type->label() }}</option>
                        @endforeach
                    </select>
                </div>

                <div style="display: flex; gap: 12px; margin-bottom: 12px;">
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label">Waktu Mulai <span style="color: var(--danger);">*</span></label>
                        <input type="datetime-local" name="start_time" id="edit-start" class="form-control" required>
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label">Waktu Selesai <span style="color: var(--danger);">*</span></label>
                        <input type="datetime-local" name="end_time" id="edit-end" class="form-control" required>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 12px;">
                    <label class="form-label">Lokasi / Media Pertemuan</label>
                    <input type="text" name="location" id="edit-location" class="form-control">
                </div>

                <div class="form-group" style="margin-bottom: 12px;">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="description" id="edit-desc" class="form-control" rows="3"></textarea>
                </div>

                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label">Status Agenda <span style="color: var(--danger);">*</span></label>
                    <select name="status" id="edit-status" class="form-select" required>
                        @foreach($agendaStatuses as $st)
                            <option value="{{ $st->value }}">{{ $st->label() }}</option>
                        @endforeach
                    </select>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 24px;">
                    <button type="button" class="btn btn-outline-danger" @click="deleteCurrentAgenda()">Hapus Agenda</button>
                    <div style="display: flex; gap: 8px;">
                        <button type="button" class="btn btn-outline-secondary" @click="closeEditAgendaModal()">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </div>
            </form>

            <form id="form-delete-agenda" method="POST" action="" style="display:none;">
                @csrf
                @method('DELETE')
                <input type="hidden" name="view" value="{{ $view }}">
                <input type="hidden" name="mode" value="{{ $mode }}">
                <input type="hidden" name="date" value="{{ $anchorDate->toDateString() }}">
            </form>
        </div>
    </div>

    <!-- ----------------------------------------------------
         6. MODAL: LIHAT SEMUA AGENDA HARI INI (+N LAGI)
    ----------------------------------------------------- -->
    <div id="agenda-day-events-modal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.6); z-index: 2000; align-items: center; justify-content: center; padding: 20px;">
        <div class="samara-card" style="width: 100%; max-width: 480px; max-height: 85vh; overflow-y: auto; box-shadow: var(--shadow-lg);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; border-bottom: 1px solid var(--border); padding-bottom: 8px;">
                <h3 style="font-size: 16px; font-weight: 800; color: var(--heading); margin: 0;" x-text="'Agenda Tanggal: ' + modalDayFormatted"></h3>
                <button type="button" @click="closeDayEventsModal()" style="background: none; border: none; font-size: 20px; cursor: pointer; color: var(--text-secondary);">&times;</button>
            </div>

            <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 16px;">
                <template x-for="ev in modalDayEvents" :key="ev.id">
                    <div class="agenda-week-card"
                         :style="{ backgroundColor: ev.color, color: ev.text_color, borderLeft: '4px solid ' + ev.text_color }"
                         @click="closeDayEventsModal(); openEditAgendaModal(ev)">
                        <div style="display: flex; justify-content: space-between; font-weight: 700;">
                            <span x-text="ev.start_time + ' - ' + ev.end_time"></span>
                            <span x-text="ev.type_label" style="font-size: 10px;"></span>
                        </div>
                        <div style="font-weight: 800; font-size: 13px; margin: 3px 0;"
                             :style="{ textDecoration: ev.is_cancelled ? 'line-through' : 'none' }"
                             x-text="(ev.is_completed ? '✓ ' : '') + ev.title"></div>
                        <div style="font-size: 11px; opacity: 0.9;" x-show="ev.location" x-text="'📍 ' + ev.location"></div>
                    </div>
                </template>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 10px; border-top: 1px solid var(--border);">
                <button type="button" class="btn btn-sm btn-primary" @click="closeDayEventsModal(); openCreateModalForDate(modalDayDate)">+ Tambah Agenda</button>
                <button type="button" class="btn btn-sm btn-outline-secondary" @click="closeDayEventsModal()">Tutup</button>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
function agendaApp() {
    return {
        eventsByDate: @js($eventsByDate ?? []),
        eventsList: @js($eventsJson ?? []),
        selectedDate: '{{ $todayDate }}',
        selectedDateFormatted: 'Hari Ini',
        modalDayDate: '',
        modalDayFormatted: '',
        modalDayEvents: [],

        init() {
            // Default selected date for mobile view
            this.selectedDate = '{{ $todayDate }}';
            this.selectedDateFormatted = '{{ now()->translatedFormat('d F Y') }}';
        },

        selectDay(dateStr, formattedDate) {
            this.selectedDate = dateStr;
            this.selectedDateFormatted = formattedDate;
        },

        getSelectedDateEvents() {
            return this.eventsByDate[this.selectedDate] || [];
        },

        showDayEventsModal(dateStr, formattedDate) {
            this.modalDayDate = dateStr;
            this.modalDayFormatted = formattedDate;
            this.modalDayEvents = this.eventsByDate[dateStr] || [];
            document.getElementById('agenda-day-events-modal').style.display = 'flex';
        },

        closeDayEventsModal() {
            document.getElementById('agenda-day-events-modal').style.display = 'none';
        },

        openCreateModalForDate(dateStr) {
            openCreateAgendaModal(dateStr);
        },

        openEditAgendaModal(ev) {
            openEditAgendaModal(ev);
        },

        deleteCurrentAgenda() {
            if (confirm('Apakah Anda yakin ingin menghapus agenda ini?')) {
                document.getElementById('form-delete-agenda').submit();
            }
        }
    };
}

function openCreateAgendaModal(presetDate = null) {
    const modal = document.getElementById('agenda-create-modal');
    modal.style.display = 'flex';
    document.getElementById('form-create-agenda').reset();

    const startInput = document.getElementById('create-start');
    const endInput = document.getElementById('create-end');

    if (presetDate) {
        startInput.value = presetDate + 'T09:00';
        endInput.value = presetDate + 'T10:00';
    } else {
        const now = new Date();
        now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
        startInput.value = now.toISOString().slice(0, 16);

        now.setHours(now.getHours() + 1);
        endInput.value = now.toISOString().slice(0, 16);
    }
}

function closeCreateAgendaModal() {
    document.getElementById('agenda-create-modal').style.display = 'none';
}

function openEditAgendaModal(agenda) {
    const modal = document.getElementById('agenda-edit-modal');
    modal.style.display = 'flex';

    const form = document.getElementById('form-edit-agenda');
    form.action = '/dirut/agenda/' + agenda.id;

    const deleteForm = document.getElementById('form-delete-agenda');
    deleteForm.action = '/dirut/agenda/' + agenda.id;

    document.getElementById('edit-title').value = agenda.title || '';
    document.getElementById('edit-type').value = agenda.type || '';
    document.getElementById('edit-location').value = agenda.location || '';
    document.getElementById('edit-desc').value = agenda.description || '';
    document.getElementById('edit-status').value = agenda.status || 'TERJADWAL';

    // Format start and end datetime-local
    if (agenda.start) {
        document.getElementById('edit-start').value = agenda.start.replace(' ', 'T').slice(0, 16);
    }
    if (agenda.end) {
        document.getElementById('edit-end').value = agenda.end.replace(' ', 'T').slice(0, 16);
    }
}

function closeEditAgendaModal() {
    document.getElementById('agenda-edit-modal').style.display = 'none';
}
</script>
@endpush
@endsection
