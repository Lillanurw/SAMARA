@extends('layouts.app')

@section('title', 'My Week')
@section('page_title', 'Agenda Kerja Mingguan (My Week)')
@section('page_subtitle', 'Periode ' . $startDate->translatedFormat('d M Y') . ' — ' . $endDate->translatedFormat('d M Y'))

@section('content')
<div x-data="{ addModalOpen: false }">
    <!-- Week Navigation Filter Bar -->
    <div class="samara-filter-bar">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px;">
            <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                <a href="{{ route('weekly.index', ['start_date' => $startDate->copy()->subWeek()->format('Y-m-d')]) }}" class="btn btn-secondary">
                    ← Minggu Sebelumnya
                </a>
                <a href="{{ route('weekly.index', ['start_date' => \Carbon\Carbon::now()->startOfWeek()->format('Y-m-d')]) }}" class="btn btn-secondary">
                    Minggu Ini
                </a>
                <a href="{{ route('weekly.index', ['start_date' => $startDate->copy()->addWeek()->format('Y-m-d')]) }}" class="btn btn-secondary">
                    Minggu Depan →
                </a>
            </div>

            <div style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
                <button type="button" @click="addModalOpen = true" class="btn btn-primary">
                    ➕ Tambah Rencana Baru
                </button>
            </div>
        </div>
    </div>

    <!-- 7 Days Agenda Cards -->
    <div style="display: flex; flex-direction: column; gap: 20px;">
        @foreach($days as $dateStr => $dayInfo)
            <div class="samara-card" style="{{ $dayInfo['isToday'] ? 'border: 2px solid var(--accent); background: #f0fdf4;' : '' }}">
                <div class="card-header-title" style="border-bottom: 1px solid var(--border); padding-bottom: 12px; margin-bottom: 16px;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span style="font-size: 18px; font-weight: 800; color: {{ $dayInfo['isToday'] ? 'var(--success)' : 'var(--primary)' }};">
                            {{ $dayInfo['date']->translatedFormat('l, d F Y') }}
                        </span>
                        @if($dayInfo['isToday'])
                            <span class="badge badge-green">HARI INI</span>
                        @endif
                    </div>
                    <span class="badge badge-gray">{{ $dayInfo['plans']->count() }} Kunjungan</span>
                </div>

                @if($dayInfo['plans']->isEmpty())
                    <div style="padding: 16px; text-align: center; color: var(--text-secondary); font-size: 13px;">
                        Tidak ada agenda kunjungan untuk hari ini.
                    </div>
                @else
                    <div class="grid grid-2">
                        @foreach($dayInfo['plans'] as $plan)
                            <div style="border: 1.5px solid var(--border); border-radius: var(--radius-md); padding: 18px; background: var(--surface); box-shadow: var(--shadow); transition: var(--transition);">
                                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px;">
                                    <div style="display: flex; gap: 6px;">
                                        <span class="badge badge-cyan" style="font-size: 11px;">{{ substr($plan->start_time, 0, 5) }} - {{ substr($plan->end_time, 0, 5) }}</span>
                                        <span class="badge badge-navy" style="font-size: 11px;">{{ $plan->activity_type?->label() ?? $plan->activity_type }}</span>
                                    </div>
                                    @php
                                        $stClass = match($plan->status?->value ?? (string)$plan->status) {
                                            'COMPLETED'   => 'badge-green',
                                            'IN_PROGRESS' => 'badge-gold',
                                            'CANCELLED'   => 'badge-red',
                                            'RESCHEDULED' => 'badge-gold',
                                            'PLANNED'     => 'badge-navy',
                                            default       => 'badge-gray',
                                        };
                                    @endphp
                                    <span class="badge {{ $stClass }}">{{ $plan->status?->label() ?? $plan->status }}</span>
                                </div>

                                <h4 style="font-size: 16px; font-weight: 700; color: var(--primary); margin-bottom: 4px;">
                                    {{ $plan->customer->customer_name }}
                                </h4>
                                <div style="font-size: 12px; color: var(--text-secondary); margin-bottom: 12px;">
                                    📍 Area: {{ $plan->area->area_name }} | {{ $plan->location_text ?? $plan->customer->address }}
                                </div>

                                <div style="font-size: 13px; color: var(--text); background: var(--surface-soft); padding: 12px; border-radius: 8px; margin-bottom: 14px; border: 1px solid var(--border);">
                                    <strong>Tujuan:</strong> {{ $plan->specific_objective }}
                                </div>

                                @if($plan->members->isNotEmpty())
                                    <div style="font-size: 12px; color: var(--text-secondary); margin-bottom: 14px;">
                                        👥 Tim: {{ $plan->owner->full_name }}, {{ $plan->members->pluck('full_name')->join(', ') }}
                                    </div>
                                @endif

                                <!-- Action Buttons -->
                                <div style="display: flex; gap: 8px; flex-wrap: wrap; margin-top: 14px; padding-top: 12px; border-top: 1px solid var(--border);">
                                    @if($plan->customer->latitude && $plan->customer->longitude)
                                        <a href="https://maps.google.com/?q={{ $plan->customer->latitude }},{{ $plan->customer->longitude }}" target="_blank" class="btn btn-sm btn-secondary">
                                            📍 Buka Lokasi
                                        </a>
                                    @endif

                                    @if(($plan->status?->value ?? (string)$plan->status) === 'PLANNED')
                                        <form action="{{ route('weekly.start', $plan->id) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-primary">🚀 Mulai Kunjungan</button>
                                        </form>
                                    @elseif(($plan->status?->value ?? (string)$plan->status) === 'IN_PROGRESS')
                                        <a href="{{ route('reports.create', ['visit_plan_id' => $plan->id]) }}" class="btn btn-sm btn-success">📝 Isi Laporan</a>
                                    @elseif($plan->visitReport)
                                        <a href="{{ route('reports.show', $plan->visitReport->id) }}" class="btn btn-sm btn-secondary">Lihat Laporan</a>
                                    @endif

                                    <a href="{{ route('plans.show', $plan->id) }}" class="btn btn-sm btn-secondary">Detail / Edit</a>
                                    @can('delete', $plan)
                                        @if(!$plan->visitReport)
                                            <form action="{{ route('plans.destroy', $plan->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus rencana kunjungan ini?');" style="display: inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">🗑️ Hapus</button>
                                            </form>
                                        @endif
                                    @endcan
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    <!-- Modal Tambah Rencana Kunjungan Baru (Saves to Visit Plans) -->
    <div x-show="addModalOpen" style="position: fixed; inset: 0; background: rgba(0,0,0,0.4); z-index: 2000; display: flex; align-items: center; justify-content: center; padding: 20px;" x-cloak>
        <div style="background: white; width: 100%; max-width: 600px; border-radius: var(--radius-lg); padding: 28px; max-height: 90vh; overflow-y: auto; box-shadow: var(--shadow-hover);" @click.away="addModalOpen = false">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid var(--border); padding-bottom: 14px;">
                <h3 style="font-weight: 800; font-size: 18px; color: var(--primary);">Tambah Rencana Kunjungan Baru</h3>
                <button type="button" @click="addModalOpen = false" style="background: none; border: none; font-size: 22px; cursor: pointer; color: var(--text-secondary);">&times;</button>
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
