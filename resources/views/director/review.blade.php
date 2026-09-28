@extends('layouts.app')

@section('title', 'Director Review')
@section('page_title', 'Director Review & Strategic Directions')
@section('page_subtitle', 'Review Laporan Kunjungan & Pemberian Arahan Strategis')

@section('content')
<div x-data="{ selectedReportId: null, selectedTopic: '', selectedCustomer: '' }">
    <div class="grid grid-2">

        {{-- =====================================================
             KOLOM KIRI – Laporan Perlu Perhatian
        ====================================================== --}}
        <div>
            <div class="samara-card">
                <div class="card-header-title">
                    <span style="display:flex; align-items:center; gap:8px;">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                        Laporan Perlu Perhatian
                    </span>
                    <span class="badge badge-gold">{{ $reportsNeedingAttention->count() }}</span>
                </div>
                <p style="font-size:12px; color:var(--text-secondary); margin-bottom:16px; margin-top:-10px;">
                    Laporan dengan hambatan, engagement rendah (≤3), atau customer prioritas A.
                </p>

                @forelse($reportsNeedingAttention as $rep)
                    <div class="agenda-item" style="margin-bottom:12px;">
                        <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:8px;">
                            <h4 style="font-weight:700; font-size:15px; color:var(--primary);">
                                {{ $rep->visitPlan->customer->customer_name ?? '—' }}
                            </h4>
                            <span class="badge badge-gold">★ {{ $rep->engagement_score }}/5</span>
                        </div>

                        <div style="font-size:12px; color:var(--text-secondary); margin-bottom:8px;">
                            Pelapor: <strong>{{ $rep->submitter->full_name ?? '—' }}</strong>
                            &nbsp;|&nbsp;
                            {{ $rep->submitted_at ? $rep->submitted_at->format('d/m/Y') : '—' }}
                        </div>

                        @if($rep->barrier)
                            <div style="font-size:12px; color:var(--danger); background:var(--danger-soft); padding:8px 12px; border-radius:var(--radius-sm); margin-bottom:8px;">
                                <strong>Hambatan:</strong> {{ Str::limit($rep->barrier, 120) }}
                            </div>
                        @endif

                        @if($rep->need_or_opportunity)
                            <div style="font-size:12px; color:var(--success); background:var(--success-soft); padding:8px 12px; border-radius:var(--radius-sm); margin-bottom:8px;">
                                <strong>Peluang:</strong> {{ Str::limit($rep->need_or_opportunity, 120) }}
                            </div>
                        @endif

                        <div style="display:flex; gap:8px; margin-top:10px; flex-wrap:wrap;">
                            <a href="{{ route('reports.show', $rep->id) }}"
                               target="_blank"
                               class="btn btn-sm btn-secondary">
                                Lihat Detail
                            </a>
                            <button type="button"
                                    @click="selectedReportId = {{ $rep->id }}; selectedTopic = 'Arahan — {{ $rep->report_number }}'; selectedCustomer = '{{ $rep->visitPlan->customer->customer_name ?? '' }}'"
                                    class="btn btn-sm btn-primary">
                                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 013 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                                Berikan Arahan
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="empty-state">
                        <div class="empty-state-icon">✅</div>
                        <div class="empty-state-title">Tidak ada laporan kritis</div>
                        <div class="empty-state-text">Semua laporan dalam kondisi baik saat ini.</div>
                    </div>
                @endforelse

                <div style="margin-top:16px;">
                    {{ $reportsNeedingAttention->links() }}
                </div>
            </div>
        </div>

        {{-- =====================================================
             KOLOM KANAN – Form Arahan + Tracking
        ====================================================== --}}
        <div style="display:flex; flex-direction:column; gap:20px;">

            {{-- Form Arahan Director --}}
            <div class="samara-card card-accent-cyan">
                <div class="card-header-title">
                    <span style="display:flex; align-items:center; gap:8px;">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 013 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                        Form Arahan Director
                    </span>
                </div>

                <form action="{{ route('director.store') }}" method="POST" id="form-director-arahan">
                    @csrf
                    <input type="hidden" name="visit_report_id" x-model="selectedReportId">

                    <div x-show="selectedCustomer"
                         style="padding:10px 14px; background:var(--accent-soft); border-radius:var(--radius-sm); margin-bottom:16px; font-size:13px; font-weight:600; color:var(--primary); border-left:3px solid var(--accent);"
                         x-cloak>
                        Arahan terkait: <span x-text="selectedCustomer"></span>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="topic">Topik Arahan *</label>
                        <input type="text"
                               name="topic"
                               id="topic"
                               class="form-control {{ $errors->has('topic') ? 'is-invalid' : '' }}"
                               x-model="selectedTopic"
                               required
                               placeholder="Contoh: Strategi penetrasi wilayah Jawa Timur Q4"
                               value="{{ old('topic') }}">
                        @error('topic') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="grid grid-2">
                        <div class="form-group">
                            <label class="form-label" for="input_type">Jenis Arahan *</label>
                            <select name="input_type" id="input_type" class="form-select" required>
                                <option value="STRATEGIC_DIRECTION" {{ old('input_type') == 'STRATEGIC_DIRECTION' ? 'selected' : '' }}>Strategic Direction</option>
                                <option value="SUGGESTION" {{ old('input_type') == 'SUGGESTION' ? 'selected' : '' }}>Suggestion / Saran</option>
                                <option value="SOLUTION" {{ old('input_type') == 'SOLUTION' ? 'selected' : '' }}>Solution / Solusi</option>
                                <option value="DECISION" {{ old('input_type') == 'DECISION' ? 'selected' : '' }}>Decision / Keputusan</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="priority">Prioritas *</label>
                            <select name="priority" id="priority" class="form-select" required>
                                <option value="LOW" {{ old('priority') == 'LOW' ? 'selected' : '' }}>Rendah</option>
                                <option value="MEDIUM" {{ old('priority') == 'MEDIUM' ? 'selected' : '' }}>Sedang</option>
                                <option value="HIGH" {{ old('priority', 'HIGH') == 'HIGH' ? 'selected' : '' }}>Tinggi</option>
                                <option value="CRITICAL" {{ old('priority') == 'CRITICAL' ? 'selected' : '' }}>Kritis</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="direction_text">Isi Arahan Director *</label>
                        <textarea name="direction_text"
                                  id="direction_text"
                                  class="form-control {{ $errors->has('direction_text') ? 'is-invalid' : '' }}"
                                  rows="4"
                                  required
                                  placeholder="Tuliskan petunjuk strategis, arahan, atau keputusan resmi...">{{ old('direction_text') }}</textarea>
                        @error('direction_text') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="grid grid-2">
                        <div class="form-group">
                            <label class="form-label" for="assigned_to">Tugaskan Kepada (PIC) *</label>
                            <select name="assigned_to" id="assigned_to" class="form-select" required>
                                <option value="">— Pilih PIC —</option>
                                @foreach($teamUsers as $tu)
                                    <option value="{{ $tu->id }}" {{ old('assigned_to') == $tu->id ? 'selected' : '' }}>
                                        {{ $tu->full_name }} ({{ $tu->role?->value ?? $tu->role }})
                                    </option>
                                @endforeach
                            </select>
                            @error('assigned_to') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="due_date">Tenggat Waktu *</label>
                            <input type="date"
                                   name="due_date"
                                   id="due_date"
                                   class="form-control {{ $errors->has('due_date') ? 'is-invalid' : '' }}"
                                   required
                                   value="{{ old('due_date', date('Y-m-d', strtotime('+3 days'))) }}">
                            @error('due_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width:100%; margin-top:6px;">
                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                        Kirim Arahan Director
                    </button>
                </form>
            </div>

            {{-- Tracking Arahan Terbaru --}}
            <div class="samara-card">
                <div class="card-header-title">Tracking Arahan Sebelumnya</div>

                @forelse($recentInputs as $input)
                    <div style="padding:12px 0; border-bottom:1px solid var(--border);">
                        <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:4px;">
                            <div style="font-weight:700; font-size:13px; color:var(--primary); flex:1; margin-right:8px;">
                                {{ $input->topic }}
                            </div>
                            @php
                                $statusBadge = match($input->status?->value ?? (string)$input->status) {
                                    'OPEN'         => 'badge-navy',
                                    'ACKNOWLEDGED' => 'badge-cyan',
                                    'IN_PROGRESS'  => 'badge-gold',
                                    'CLOSED'       => 'badge-green',
                                    'CANCELLED'    => 'badge-gray',
                                    default        => 'badge-gray',
                                };
                            @endphp
                            <span class="badge {{ $statusBadge }}">{{ $input->status?->label() ?? $input->status }}</span>
                        </div>
                        <div style="font-size:12px; color:var(--text-secondary);">
                            PIC: <strong>{{ $input->assignedUser->full_name ?? '—' }}</strong>
                            &nbsp;|&nbsp; Due: {{ $input->due_date ? $input->due_date->format('d/m/Y') : '—' }}
                        </div>
                    </div>
                @empty
                    <div style="text-align:center; padding:20px; color:var(--text-muted); font-size:13px;">
                        Belum ada arahan sebelumnya.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
