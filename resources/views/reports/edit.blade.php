@extends('layouts.app')

@section('title', 'Edit Laporan Kunjungan')
@section('page_title', 'Edit Laporan Hasil Kunjungan')
@section('page_subtitle', 'Kunjungan ke ' . $visitPlan->customer->customer_name . ' (' . $visitPlan->plan_number . ')')

@section('content')
<div x-data="{ followUpRequired: false }">
    <div style="margin-bottom: 20px;">
        <a href="{{ route('reports.show', $visitReport->id) }}" class="btn btn-secondary">← Kembali ke Daftar Laporan</a>
    </div>

    <!-- Customer & Plan Summary Card -->
    <div class="card" style="background: #F8FAFC; margin-bottom: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px;">
            <div>
                <h3 style="font-size: 18px; font-weight: 800; color: var(--navy);">{{ $visitPlan->customer->customer_name }}</h3>
                <div style="font-size: 13px; color: var(--muted); margin-top: 2px;">
                    📍 {{ $visitPlan->area->area_name }} | {{ $visitPlan->customer->address }}
                </div>
                <div style="font-size: 13px; color: var(--text); margin-top: 6px;">
                    <strong>Tujuan:</strong> {{ $visitPlan->specific_objective }}
                </div>
            </div>
            <div>
                <span class="badge badge-primary">{{ $visitPlan->activity_type->label() ?? $visitPlan->activity_type }}</span>
                <div style="font-size: 12px; color: var(--muted); margin-top: 4px; text-align: right;">
                    Target: {{ $visitPlan->planned_date->format('d/m/Y') }} ({{ substr($visitPlan->start_time, 0, 5) }} - {{ substr($visitPlan->end_time, 0, 5) }})
                </div>
            </div>
        </div>
    </div>

    <!-- Report Entry Form -->
    <div class="card">
        <form action="{{ route('reports.update', $visitReport->id) }}" method="POST">
            @csrf
            @method('PUT')
            <input type="hidden" name="visit_plan_id" value="{{ $visitPlan->id }}">

            <h4 style="font-weight: 700; color: var(--navy); margin-bottom: 16px; border-bottom: 1px solid var(--border); padding-bottom: 8px;">
                1. Pelaksanaan & Waktu Aktual
            </h4>

            <div class="grid grid-2">
                <div class="form-group">
                    <label class="form-label">Waktu Mulai Aktual *</label>
                    <input type="datetime-local" name="actual_start_at" class="form-control" required value="{{ old('actual_start_at', $visitPlan->actual_start_at ? $visitPlan->actual_start_at->format('Y-m-d\TH:i') : $visitReport->actual_start_at->format('Y-m-d\TH:i')) }}">
                </div>

                <div class="form-group">
                    <label class="form-label">Waktu Selesai Aktual *</label>
                    <input type="datetime-local" name="actual_end_at" class="form-control" required value="{{ old('actual_end_at', $visitReport->actual_end_at->format('Y-m-d\TH:i')) }}">
                </div>
            </div>

            <h4 style="font-weight: 700; color: var(--navy); margin-top: 24px; margin-bottom: 16px; border-bottom: 1px solid var(--border); padding-bottom: 8px;">
                2. Hasil & Ringkasan Pertemuan
            </h4>

            <div class="grid grid-2">
                <div class="form-group">
                    <label class="form-label">Hasil Utama (Outcome Type) *</label>
                    <select name="outcome_type" class="form-select" required>
                        <option value="NO_CHANGE" {{ $visitReport->outcome_type->value == "NO_CHANGE" ? "selected" : "" }}>Tidak Ada Perubahan Status</option>
                        <option value="NEED_IDENTIFIED" {{ $visitReport->outcome_type->value == "NEED_IDENTIFIED" ? "selected" : "" }}>Kebutuhan Teridentifikasi</option>
                        <option value="FOLLOW_UP" {{ $visitReport->outcome_type->value == "FOLLOW_UP" ? "selected" : "" }}>Tindak Lanjut Diperlukan</option>
                        <option value="PROPOSAL" {{ $visitReport->outcome_type->value == "PROPOSAL" ? "selected" : "" }}>Tahap Proposal</option>
                        <option value="NEGOTIATION" {{ $visitReport->outcome_type->value == "NEGOTIATION" ? "selected" : "" }}>Tahap Negosiasi</option>
                        <option value="WON" {{ $visitReport->outcome_type->value == "WON" ? "selected" : "" }}>Deal / Menang (WON)</option>
                        <option value="LOST" {{ $visitReport->outcome_type->value == "LOST" ? "selected" : "" }}>Batal / Kalah (LOST)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Skor Keterikatan Pelanggan (Engagement Score 1 - 5) *</label>
                    <select name="engagement_score" class="form-select" required>
                        <option value="1" {{ $visitReport->engagement_score == 1 ? "selected" : "" }}>1 - Sangat Rendah (Dingin / Menolak)</option>
                        <option value="2" {{ $visitReport->engagement_score == 2 ? "selected" : "" }}>2 - Rendah (Pasif)</option>
                        <option value="3" {{ $visitReport->engagement_score == 3 ? "selected" : "" }}>3 - Sedang (Cukup Responsif)</option>
                        <option value="4" {{ $visitReport->engagement_score == 4 ? "selected" : "" }}>4 - Tinggi (Antusias & Positif)</option>
                        <option value="5" {{ $visitReport->engagement_score == 5 ? "selected" : "" }}>5 - Sangat Tinggi (Komitmen Kuat / Deal)</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Peserta & Kehadiran (Attendance Summary) *</label>
                <input type="text" name="attendance_summary" class="form-control" required placeholder="Contoh: Kapten Suwandi (Pengadaan), Budi Pratama (SAMARA)" value="{{ old('attendance_summary', $visitReport->attendance_summary) }}">
            </div>

            <div class="form-group">
                <label class="form-label">Ringkasan Hasil Pertemuan (Outcome Summary) *</label>
                <textarea name="outcome_summary" class="form-control" rows="3" required placeholder="Tuliskan poin-poin utama diskusi dan kesepakatan...">{{ old('outcome_summary', $visitReport->outcome_summary) }}</textarea>
            </div>

            <div class="form-group">
                <label class="form-label">Rencana Langkah Selanjutnya (Next Step) *</label>
                <textarea name="next_step_summary" class="form-control" rows="2" required placeholder="Tuliskan tindakan yang akan dilakukan berikutnya...">{{ old('next_step_summary', $visitReport->next_step_summary) }}</textarea>
            </div>

            <h4 style="font-weight: 700; color: var(--navy); margin-top: 24px; margin-bottom: 16px; border-bottom: 1px solid var(--border); padding-bottom: 8px;">
                3. Insight Tambahan & Kendala (Opsional)
            </h4>

            <div class="form-group">
                <label class="form-label">Hambatan / Kendala Utama (Barrier)</label>
                <textarea name="barrier" class="form-control" rows="2" placeholder="Contoh: Kendala anggaran, persetujuan atasan, atau isu legalitas...">{{ old('barrier', $visitReport->barrier) }}</textarea>
            </div>

            <div class="form-group">
                <label class="form-label">Peluang & Kebutuhan Baru (Need / Opportunity)</label>
                <textarea name="need_or_opportunity" class="form-control" rows="2" placeholder="Contoh: Teridentifikasi kebutuhan pengadaan rompi tambahan kuartal depan...">{{ old('need_or_opportunity', $visitReport->need_or_opportunity) }}</textarea>
            </div>

            <div class="form-group">
                <label class="form-label">Informasi Kompetitor (Competitor Info)</label>
                <textarea name="competitor_information" class="form-control" rows="2" placeholder="Contoh: Merek pesaing yang ikut menawarkan dan perbandingan harga...">{{ old('competitor_information', $visitReport->competitor_information) }}</textarea>
            </div>

            <!-- Checkbox Follow Up Required -->
            <div style="margin-top: 24px; padding: 16px; background: #FAFBFD; border-radius: var(--radius-sm); border: 1px solid var(--border);">
                <label style="display: flex; align-items: center; gap: 10px; font-weight: 700; color: var(--navy); cursor: pointer;">
                    <input type="checkbox" name="follow_up_required" value="1" {{ $visitReport->engagement_score == 1 ? "selected" : "" }} x-model="followUpRequired" style="width: 18px; height: 18px; accent-color: var(--navy);">
                    <span>Perlu Buat Tindak Lanjut (Follow-up Action Item) Sekarang?</span>
                </label>

                <div x-show="followUpRequired" style="margin-top: 16px;" x-cloak>
                    <div class="form-group">
                        <label class="form-label">Judul Tindakan Follow-up *</label>
                        <input type="text" name="action_title" class="form-control" placeholder="Contoh: Kirimkan Surat Penawaran Resmi">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Rincian Tindakan *</label>
                        <textarea name="action_detail" class="form-control" rows="2" placeholder="Jelaskan instruksi detail pekerjaan follow-up..."></textarea>
                    </div>

                    <div class="grid grid-3">
                        <div class="form-group">
                            <label class="form-label">Penanggung Jawab (Owner) *</label>
                            <select name="owner_id" class="form-select">
                                @foreach($teamUsers as $tu)
                                    <option value="{{ $tu->id }}" {{ $tu->id == auth()->id() ? 'selected' : '' }}>{{ $tu->full_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Tenggat Waktu (Due Date) *</label>
                            <input type="date" name="due_date" class="form-control" value="{{ date('Y-m-d', strtotime('+3 days')) }}">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Prioritas *</label>
                            <select name="priority" class="form-select">
                                <option value="LOW">Rendah</option>
                                <option value="MEDIUM" selected>Sedang</option>
                                <option value="HIGH">Tinggi</option>
                                <option value="CRITICAL">Kritis</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 24px;">
                <a href="{{ route('reports.show', $visitReport->id) }}" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan Perubahan Laporan</button>
            </div>
        </form>
    </div>
</div>
@endsection

