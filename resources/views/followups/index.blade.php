@extends('layouts.app')

@section('title', 'Follow-ups')
@section('page_title', 'Tindak Lanjut Kunjungan')
@section('page_subtitle', 'Pengelolaan Action Items & Komitmen Customer')

@section('content')

{{-- =====================================================
     TAB NAVIGATION
====================================================== --}}
<div class="pill-tabs">
    <a href="{{ route('followups.index', ['tab' => 'open']) }}"
       class="pill-tab-item {{ $tab === 'open' ? 'active' : '' }}"
       id="tab-open">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/></svg>
        Open & In Progress
        @if($counts['open'] > 0)
            <span class="badge badge-navy" style="margin-left:4px;">{{ $counts['open'] }}</span>
        @endif
    </a>

    <a href="{{ route('followups.index', ['tab' => 'overdue']) }}"
       class="pill-tab-item {{ $tab === 'overdue' ? 'active' : '' }}"
       id="tab-overdue"
       style="{{ $counts['overdue'] > 0 && $tab !== 'overdue' ? 'color: var(--danger); border-color: var(--danger-soft);' : '' }}">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
        Overdue
        @if($counts['overdue'] > 0)
            <span class="badge badge-red" style="margin-left:4px;">{{ $counts['overdue'] }}</span>
        @endif
    </a>

    <a href="{{ route('followups.index', ['tab' => 'blocked']) }}"
       class="pill-tab-item {{ $tab === 'blocked' ? 'active' : '' }}"
       id="tab-blocked">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
        Blocked
        @if($counts['blocked'] > 0)
            <span class="badge badge-gold" style="margin-left:4px;">{{ $counts['blocked'] }}</span>
        @endif
    </a>

    <a href="{{ route('followups.index', ['tab' => 'done']) }}"
       class="pill-tab-item {{ $tab === 'done' ? 'active' : '' }}"
       id="tab-done">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
        Done
        @if($counts['done'] > 0)
            <span class="badge badge-green" style="margin-left:4px;">{{ $counts['done'] }}</span>
        @endif
    </a>
</div>

{{-- =====================================================
     FOLLOW-UP CARDS
====================================================== --}}
@if($followUps->isEmpty())
    <div class="samara-card">
        <div class="empty-state">
            <div class="empty-state-icon">{{ $tab === 'done' ? '🎉' : '✅' }}</div>
            <div class="empty-state-title">Tidak ada follow-up di sini</div>
            <div class="empty-state-text">Tidak ada data follow-up untuk tab "{{ $tab }}" saat ini.</div>
        </div>
    </div>
@else
    <div class="grid grid-2" x-data="{ activeModalId: null, selectedStatus: '' }">
        @foreach($followUps as $fu)
            @php
                $isOverdue = $fu->isOverdue();
                $statusVal = $fu->status?->value ?? (string)$fu->status;
                $priorityBadge = match($fu->priority?->value ?? (string)$fu->priority) {
                    'CRITICAL' => 'badge-red',
                    'HIGH'     => 'badge-gold',
                    'MEDIUM'   => 'badge-cyan',
                    'LOW'      => 'badge-gray',
                    default    => 'badge-gray',
                };
                $statusBadge = match($statusVal) {
                    'OPEN'        => 'badge-navy',
                    'IN_PROGRESS' => 'badge-gold',
                    'BLOCKED'     => 'badge-red',
                    'DONE'        => 'badge-green',
                    'CANCELLED'   => 'badge-gray',
                    default       => 'badge-gray',
                };
            @endphp

            <div class="samara-card"
                 style="margin-bottom:0; display:flex; flex-direction:column; justify-content:space-between;
                        {{ $isOverdue ? 'border-color: var(--danger-soft); background: var(--danger-soft);' : '' }}">
                <div>
                    {{-- Header --}}
                    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:10px;">
                        <span class="badge badge-navy" style="font-size:10px;">{{ $fu->action_number }}</span>
                        <div style="display:flex; gap:4px; flex-wrap:wrap; justify-content:flex-end;">
                            @if($isOverdue)
                                <span class="badge badge-red">OVERDUE</span>
                            @endif
                            <span class="badge {{ $priorityBadge }}">{{ $fu->priority?->label() ?? $fu->priority }}</span>
                            <span class="badge {{ $statusBadge }}">{{ $fu->status?->label() ?? $fu->status }}</span>
                        </div>
                    </div>

                    {{-- Title --}}
                    <h4 style="font-weight:700; font-size:15px; color:var(--primary); margin-bottom:5px;">
                        {{ $fu->action_title }}
                    </h4>

                    <div style="font-size:13px; font-weight:600; color:var(--text); margin-bottom:10px; display:flex; align-items:center; gap:6px;">
                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
                        {{ $fu->customer->customer_name }}
                    </div>

                    <p style="font-size:13px; color:var(--text); background:var(--surface-soft); padding:10px 12px; border-radius:var(--radius-sm); margin-bottom:12px; border:1px solid var(--border);">
                        {{ $fu->action_detail }}
                    </p>

                    @if($fu->blocked_reason)
                        <div style="font-size:12px; color:var(--danger); background:var(--danger-soft); padding:8px 12px; border-radius:var(--radius-sm); margin-bottom:10px;">
                            <strong>Alasan Terhambat:</strong> {{ $fu->blocked_reason }}
                        </div>
                    @endif

                    @if($fu->completion_note)
                        <div style="font-size:12px; color:var(--success); background:var(--success-soft); padding:8px 12px; border-radius:var(--radius-sm); margin-bottom:10px;">
                            <strong>Catatan Penyelesaian:</strong> {{ $fu->completion_note }}
                        </div>
                    @endif
                </div>

                {{-- Footer --}}
                <div style="border-top:1px solid var(--border); padding-top:12px; margin-top:12px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px;">
                    <div style="font-size:12px; color:var(--text-secondary);">
                        <div style="display:flex; align-items:center; gap:5px; margin-bottom:2px;">
                            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M20 21a8 8 0 10-16 0"/></svg>
                            <strong>{{ $fu->owner->full_name }}</strong>
                        </div>
                        <div style="display:flex; align-items:center; gap:5px;">
                            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                            Tenggat: <strong class="{{ $isOverdue ? 'text-danger' : '' }}">{{ $fu->due_date->format('d/m/Y') }}</strong>
                        </div>
                    </div>

                    @if($statusVal !== 'DONE' && $statusVal !== 'CANCELLED')
                    <button type="button"
                            id="btn-update-{{ $fu->id }}"
                            @click="activeModalId = {{ $fu->id }}; selectedStatus = '{{ $statusVal }}'"
                            class="btn btn-sm btn-primary">
                        Update Status
                    </button>
                    @endif
                </div>

                {{-- Update Status Modal --}}
                <div x-show="activeModalId === {{ $fu->id }}"
                     style="position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:2000; display:flex; align-items:center; justify-content:center; padding:20px;"
                     x-cloak
                     aria-modal="true"
                     role="dialog">
                    <div style="background:var(--surface); width:100%; max-width:480px; border-radius:var(--radius-lg); padding:28px; max-height:85vh; overflow-y:auto;"
                         @click.away="activeModalId = null">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; border-bottom:1px solid var(--border); padding-bottom:14px;">
                            <h3 style="font-weight:700; font-size:16px; color:var(--primary);">Update Status Follow-up</h3>
                            <button type="button" @click="activeModalId = null" style="background:none; border:none; font-size:20px; cursor:pointer; color:var(--text-secondary);">✕</button>
                        </div>

                        <div style="font-size:13px; font-weight:700; color:var(--text); margin-bottom:16px;">{{ $fu->action_title }}</div>

                        <form action="{{ route('followups.updateStatus', $fu->id) }}" method="POST">
                            @csrf @method('PATCH')

                            <div class="form-group">
                                <label class="form-label">Status Baru *</label>
                                <select name="status" class="form-select" x-model="selectedStatus" required>
                                    <option value="OPEN">OPEN — Belum Dimulai</option>
                                    <option value="IN_PROGRESS">IN_PROGRESS — Sedang Dikerjakan</option>
                                    <option value="BLOCKED">BLOCKED — Terhambat Kendala</option>
                                    <option value="DONE">DONE — Selesai Ditindaklanjuti</option>
                                    <option value="CANCELLED">CANCELLED — Dibatalkan</option>
                                </select>
                            </div>

                            <div class="form-group" x-show="selectedStatus === 'DONE'">
                                <label class="form-label">Catatan Penyelesaian *</label>
                                <textarea name="completion_note" class="form-control" rows="3"
                                          placeholder="Jelaskan hasil dan bukti penyelesaian..."></textarea>
                            </div>

                            <div class="form-group" x-show="selectedStatus === 'BLOCKED'">
                                <label class="form-label">Alasan Terhambat *</label>
                                <textarea name="blocked_reason" class="form-control" rows="3"
                                          placeholder="Tuliskan kendala spesifik yang menghambat..."></textarea>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Jadwal Ulang Tenggat (Opsional)</label>
                                <input type="date" name="rescheduled_to" class="form-control"
                                       value="{{ $fu->due_date->format('Y-m-d') }}">
                            </div>

                            <div style="display:flex; gap:10px; justify-content:flex-end; margin-top:20px;">
                                <button type="button" @click="activeModalId = null" class="btn btn-secondary">Batal</button>
                                <button type="submit" class="btn btn-primary">Simpan Status</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div style="margin-top:20px;">
        {{ $followUps->links() }}
    </div>
@endif

@endsection
