@extends('layouts.app')

@section('title', 'Detail Rencana ' . $visitPlan->plan_number)
@section('page_title', 'Detail Rencana Kunjungan')
@section('page_subtitle', $visitPlan->plan_number . ' — ' . $visitPlan->customer->customer_name)

@section('content')
<div x-data="{ cancelModal: false, rescheduleModal: false, editModal: false }">
    <div style="margin-bottom: 20px;">
        <a href="{{ route('plans.index') }}" class="btn btn-secondary">← Kembali ke Monthly Plan</a>
    </div>

    <div class="grid grid-3">
        <!-- Main Details Card -->
        <div class="samara-card" style="grid-column: span 2;">
            <div class="card-header-title">
                <span>Informasi Kunjungan</span>
                <span class="badge badge-navy">{{ $visitPlan->status?->label() ?? $visitPlan->status }}</span>
            </div>

            <table class="samara-table" style="margin-bottom: 24px;">
                <tr>
                    <td style="width: 200px; font-weight: 600; color: var(--text-secondary);">Customer</td>
                    <td style="font-weight: 700; color: var(--primary);">{{ $visitPlan->customer->customer_name }} ({{ $visitPlan->customer->customer_code }})</td>
                </tr>
                <tr>
                    <td style="font-weight: 600; color: var(--text-secondary);">Area & Segment</td>
                    <td>{{ $visitPlan->area->area_name }} | {{ $visitPlan->customer->segment->segment_name ?? '-' }}</td>
                </tr>
                <tr>
                    <td style="font-weight: 600; color: var(--text-secondary);">Tanggal & Jam Rencana</td>
                    <td>{{ $visitPlan->planned_date->translatedFormat('l, d F Y') }} ({{ substr($visitPlan->start_time, 0, 5) }} - {{ substr($visitPlan->end_time, 0, 5) }})</td>
                </tr>
                <tr>
                    <td style="font-weight: 600; color: var(--text-secondary);">Tipe Aktivitas</td>
                    <td><span class="badge badge-cyan">{{ $visitPlan->activity_type?->label() ?? $visitPlan->activity_type }}</span></td>
                </tr>
                <tr>
                    <td style="font-weight: 600; color: var(--text-secondary);">Prioritas</td>
                    <td><span class="badge badge-gray">{{ $visitPlan->priority?->label() ?? $visitPlan->priority }}</span></td>
                </tr>
                <tr>
                    <td style="font-weight: 600; color: var(--text-secondary);">Lokasi</td>
                    <td>{{ $visitPlan->location_text ?? $visitPlan->customer->address }}</td>
                </tr>
            </table>

            <div style="margin-bottom: 20px;">
                <h4 style="font-size: 14px; font-weight: 700; color: var(--primary); margin-bottom: 6px;">Tujuan Bulanan (Monthly Objective):</h4>
                <p style="background: var(--surface-soft); padding: 14px; border-radius: var(--radius-sm); border: 1px solid var(--border);">{{ $visitPlan->monthly_objective }}</p>
            </div>

            <div style="margin-bottom: 20px;">
                <h4 style="font-size: 14px; font-weight: 700; color: var(--primary); margin-bottom: 6px;">Tujuan Spesifik Kunjungan (Specific Objective):</h4>
                <p style="background: var(--surface-soft); padding: 14px; border-radius: var(--radius-sm); border: 1px solid var(--border);">{{ $visitPlan->specific_objective }}</p>
            </div>

            @if($visitPlan->resource_notes)
                <div style="margin-bottom: 20px;">
                    <h4 style="font-size: 14px; font-weight: 700; color: var(--primary); margin-bottom: 6px;">Peralatan / Resources:</h4>
                    <p style="background: var(--surface-soft); padding: 14px; border-radius: var(--radius-sm); border: 1px solid var(--border);">{{ $visitPlan->resource_notes }}</p>
                </div>
            @endif

            <!-- Action Buttons -->
            <div style="display: flex; gap: 12px; flex-wrap: wrap; margin-top: 24px; padding-top: 18px; border-top: 1px solid var(--border);">
                @if(($visitPlan->status?->value ?? (string)$visitPlan->status) === 'PLANNED')
                    <form action="{{ route('weekly.start', $visitPlan->id) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-primary">🚀 Mulai Kunjungan</button>
                    </form>
                    <button type="button" @click="rescheduleModal = true" class="btn btn-secondary">🔄 Reschedule</button>
                    <button type="button" @click="cancelModal = true" class="btn btn-secondary">❌ Batalkan</button>
                @elseif(($visitPlan->status?->value ?? (string)$visitPlan->status) === 'IN_PROGRESS')
                    <a href="{{ route('reports.create', ['visit_plan_id' => $visitPlan->id]) }}" class="btn btn-success">📝 Isi Laporan Kunjungan</a>
                @elseif($visitPlan->visitReport)
                    <a href="{{ route('reports.show', $visitPlan->visitReport->id) }}" class="btn btn-secondary">Lihat Laporan Kunjungan</a>
                @endif

                @can('update', $visitPlan)
                    <button type="button" @click="editModal = true" class="btn btn-secondary">✏️ Edit Rencana</button>
                @endcan

                @can('delete', $visitPlan)
                    @if(!$visitPlan->visitReport)
                        <form action="{{ route('plans.destroy', $visitPlan->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus rencana kunjungan ini?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">🗑️ Hapus</button>
                        </form>
                    @endif
                @endcan
            </div>
        </div>

        <!-- Sidebar Details Card -->
        <div class="samara-card">
            <div class="card-header-title">Tim & Kontak</div>
            <div style="margin-bottom: 16px;">
                <div style="font-size: 12px; color: var(--text-secondary); font-weight: 600;">PIC (Owner):</div>
                <div style="font-weight: 700; color: var(--text);">{{ $visitPlan->owner->full_name }}</div>
                <div style="font-size: 12px; color: var(--text-secondary);">{{ $visitPlan->owner->email }}</div>
            </div>

            @if($visitPlan->members->isNotEmpty())
                <div style="margin-bottom: 16px;">
                    <div style="font-size: 12px; color: var(--text-secondary); font-weight: 600;">Anggota Pendamping:</div>
                    @foreach($visitPlan->members as $m)
                        <div style="font-size: 13px; font-weight: 600;">• {{ $m->full_name }}</div>
                    @endforeach
                </div>
            @endif

            <hr style="border: none; border-top: 1px solid var(--border); margin: 16px 0;">

            <div style="margin-bottom: 12px;">
                <div style="font-size: 12px; color: var(--text-secondary); font-weight: 600;">Kontak Person Customer:</div>
                <div style="font-weight: 700; color: var(--primary);">{{ $visitPlan->customer->contact_name ?? '-' }}</div>
                <div style="font-size: 12px; color: var(--text-secondary);">{{ $visitPlan->customer->contact_position ?? '-' }}</div>
                <div style="font-size: 12px; color: var(--text-secondary);">📞 {{ $visitPlan->customer->contact_phone ?? '-' }}</div>
            </div>
        </div>
    </div>

    <!-- Modal Cancel -->
    <div x-show="cancelModal" style="position: fixed; inset: 0; background: rgba(0,0,0,0.4); z-index: 2000; display: flex; align-items: center; justify-content: center; padding: 20px;" x-cloak>
        <div style="background: white; width: 100%; max-width: 480px; border-radius: var(--radius-lg); padding: 24px;" @click.away="cancelModal = false">
            <h3 style="font-weight: 800; margin-bottom: 14px; color: var(--danger);">Batalkan Rencana Kunjungan</h3>
            <form action="{{ route('plans.cancel', $visitPlan->id) }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="form-group">
                    <label class="form-label">Alasan Pembatalan *</label>
                    <textarea name="cancellation_reason" class="form-control" rows="3" required placeholder="Tuliskan alasan pembatalan agenda kunjungan..."></textarea>
                </div>
                <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 20px;">
                    <button type="button" @click="cancelModal = false" class="btn btn-secondary">Batal</button>
                    <button type="submit" class="btn btn-danger">Konfirmasi Pembatalan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Reschedule -->
    <div x-show="rescheduleModal" style="position: fixed; inset: 0; background: rgba(0,0,0,0.4); z-index: 2000; display: flex; align-items: center; justify-content: center; padding: 20px;" x-cloak>
        <div style="background: white; width: 100%; max-width: 480px; border-radius: var(--radius-lg); padding: 24px;" @click.away="rescheduleModal = false">
            <h3 style="font-weight: 800; margin-bottom: 14px; color: var(--primary);">Jadwalkan Ulang Kunjungan</h3>
            <form action="{{ route('plans.reschedule', $visitPlan->id) }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="form-group">
                    <label class="form-label">Tanggal Baru *</label>
                    <input type="date" name="planned_date" class="form-control" required value="{{ date('Y-m-d') }}">
                </div>
                <div class="grid grid-2">
                    <div class="form-group">
                        <label class="form-label">Waktu Mulai *</label>
                        <input type="time" name="start_time" class="form-control" required value="09:00">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Waktu Selesai *</label>
                        <input type="time" name="end_time" class="form-control" required value="11:00">
                    </div>
                </div>
                <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 20px;">
                    <button type="button" @click="rescheduleModal = false" class="btn btn-secondary">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Reschedule</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit Rencana Kunjungan -->
    <div x-show="editModal" style="position: fixed; inset: 0; background: rgba(0,0,0,0.4); z-index: 2000; display: flex; align-items: center; justify-content: center; padding: 20px;" x-cloak>
        <div style="background: white; width: 100%; max-width: 600px; border-radius: var(--radius-lg); padding: 28px; max-height: 90vh; overflow-y: auto; box-shadow: var(--shadow-hover);" @click.away="editModal = false">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid var(--border); padding-bottom: 14px;">
                <h3 style="font-weight: 800; font-size: 18px; color: var(--primary);">Edit Rencana Kunjungan</h3>
                <button type="button" @click="editModal = false" style="background: none; border: none; font-size: 22px; cursor: pointer; color: var(--text-secondary);">&times;</button>
            </div>

            <form action="{{ route('plans.update', $visitPlan->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label class="form-label">Pilih Customer *</label>
                    <select name="customer_id" class="form-select" required>
                        @foreach($customers as $cust)
                            <option value="{{ $cust->id }}" {{ $visitPlan->customer_id == $cust->id ? 'selected' : '' }}>
                                {{ $cust->customer_code }} - {{ $cust->customer_name }} ({{ $cust->area->area_name }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-3">
                    <div class="form-group">
                        <label class="form-label">Tanggal Kunjungan *</label>
                        <input type="date" name="planned_date" class="form-control" required value="{{ $visitPlan->planned_date->format('Y-m-d') }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Waktu Mulai *</label>
                        <input type="time" name="start_time" class="form-control" required value="{{ substr($visitPlan->start_time, 0, 5) }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Waktu Selesai *</label>
                        <input type="time" name="end_time" class="form-control" required value="{{ substr($visitPlan->end_time, 0, 5) }}">
                    </div>
                </div>

                <div class="grid grid-2">
                    <div class="form-group">
                        <label class="form-label">Tipe Aktivitas *</label>
                        <select name="activity_type" class="form-select" required>
                            @php $currAct = $visitPlan->activity_type?->value ?? (string)$visitPlan->activity_type; @endphp
                            <option value="SALES_VISIT" {{ $currAct === 'SALES_VISIT' ? 'selected' : '' }}>Sales Visit</option>
                            <option value="ACCOUNT_MAINTENANCE" {{ $currAct === 'ACCOUNT_MAINTENANCE' ? 'selected' : '' }}>Account Maintenance</option>
                            <option value="PRODUCT_DEMO" {{ $currAct === 'PRODUCT_DEMO' ? 'selected' : '' }}>Product Demo</option>
                            <option value="TECHNICAL_SURVEY" {{ $currAct === 'TECHNICAL_SURVEY' ? 'selected' : '' }}>Technical Survey</option>
                            <option value="MARKETING_ENGAGEMENT" {{ $currAct === 'MARKETING_ENGAGEMENT' ? 'selected' : '' }}>Marketing Engagement</option>
                            <option value="EVENT" {{ $currAct === 'EVENT' ? 'selected' : '' }}>Event / Exhibition</option>
                            <option value="OTHER" {{ $currAct === 'OTHER' ? 'selected' : '' }}>Lainnya</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Prioritas *</label>
                        <select name="priority" class="form-select" required>
                            @php $currPrio = $visitPlan->priority?->value ?? (string)$visitPlan->priority; @endphp
                            <option value="LOW" {{ $currPrio === 'LOW' ? 'selected' : '' }}>Rendah</option>
                            <option value="MEDIUM" {{ $currPrio === 'MEDIUM' ? 'selected' : '' }}>Sedang</option>
                            <option value="HIGH" {{ $currPrio === 'HIGH' ? 'selected' : '' }}>Tinggi</option>
                            <option value="CRITICAL" {{ $currPrio === 'CRITICAL' ? 'selected' : '' }}>Kritis</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Tujuan Bulanan (Monthly Objective) *</label>
                    <textarea name="monthly_objective" class="form-control" rows="2" required>{{ $visitPlan->monthly_objective }}</textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Tujuan Spesifik Kunjungan (Specific Objective) *</label>
                    <textarea name="specific_objective" class="form-control" rows="2" required>{{ $visitPlan->specific_objective }}</textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Catatan Sumber Daya / Peralatan Pembawa</label>
                    <input type="text" name="resource_notes" class="form-control" value="{{ $visitPlan->resource_notes }}">
                </div>

                <div class="form-group">
                    <label class="form-label">Anggota Tim Pendamping (Opsional)</label>
                    <select name="members[]" class="form-select" multiple style="height: 80px;">
                        @php $memberIds = $visitPlan->members->pluck('id')->toArray(); @endphp
                        @foreach($teamUsers as $userItem)
                            @if($userItem->id !== auth()->id())
                                <option value="{{ $userItem->id }}" {{ in_array($userItem->id, $memberIds) ? 'selected' : '' }}>{{ $userItem->full_name }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>

                <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 24px;">
                    <button type="button" @click="editModal = false" class="btn btn-secondary">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
