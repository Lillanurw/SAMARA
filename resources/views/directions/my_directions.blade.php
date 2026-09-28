@extends('layouts.app')

@section('title', 'Arahan Director untuk Saya')
@section('page_title', 'Arahan & Petunjuk Director')
@section('page_subtitle', 'Monitoring Strategic Directions, Suggestions, Solutions & Decisions')

@section('content')
<!-- Navigation Tabs -->
<div class="tabs-nav">
    <a href="{{ route('directions.myDirections', ['tab' => 'baru']) }}" class="tab-link {{ $tab === 'baru' ? 'active' : '' }}">
        ✨ Baru ({{ $counts['baru'] }})
    </a>
    <a href="{{ route('directions.myDirections', ['tab' => 'acknowledged']) }}" class="tab-link {{ $tab === 'acknowledged' ? 'active' : '' }}">
        👀 Dibaca ({{ $counts['acknowledged'] }})
    </a>
    <a href="{{ route('directions.myDirections', ['tab' => 'in_progress']) }}" class="tab-link {{ $tab === 'in_progress' ? 'active' : '' }}">
        🔄 Dalam Proses ({{ $counts['in_progress'] }})
    </a>
    <a href="{{ route('directions.myDirections', ['tab' => 'overdue']) }}" class="tab-link {{ $tab === 'overdue' ? 'active' : '' }}" style="{{ $counts['overdue'] > 0 ? 'color: var(--danger);' : '' }}">
        ⚠️ Overdue ({{ $counts['overdue'] }})
    </a>
    <a href="{{ route('directions.myDirections', ['tab' => 'closed']) }}" class="tab-link {{ $tab === 'closed' ? 'active' : '' }}">
        ✅ Selesai ({{ $counts['closed'] }})
    </a>
</div>

<!-- List of Directions -->
@if($directions->isEmpty())
    <div class="samara-card" style="text-align: center; padding: 40px; color: var(--text-muted);">
        <div style="font-size: 32px; margin-bottom: 8px;">📢</div>
        <div>Tidak ada arahan Director pada tab ini.</div>
    </div>
@else
    <div class="grid grid-2" x-data="{ completeModalId: null }">
        @foreach($directions as $dir)
            <div class="samara-card" style="display: flex; flex-direction: column; justify-content: space-between; border-left: 4px solid var(--primary);">
                <div>
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px;">
                        <span class="badge badge-cyan">{{ $dir->input_type?->label() ?? $dir->input_type }}</span>
                        <div style="display: flex; gap: 4px;">
                            @if($dir->isOverdue())
                                <span class="badge badge-red">OVERDUE</span>
                            @endif
                            <span class="badge badge-gold">{{ $dir->priority?->label() ?? $dir->priority }}</span>
                            <span class="badge badge-navy">{{ $dir->status?->label() ?? $dir->status }}</span>
                        </div>
                    </div>

                    <h4 style="font-size: 16px; font-weight: 700; color: var(--primary); margin-bottom: 6px;">
                        {{ $dir->topic }}
                    </h4>

                    @if($dir->customer)
                        <div style="font-size: 13px; font-weight: 600; color: var(--text); margin-bottom: 8px;">
                            🏢 {{ $dir->customer->customer_name }} ({{ $dir->customer->area->area_name ?? '-' }})
                        </div>
                    @endif

                    <div style="background: var(--surface-soft); padding: 12px; border-radius: var(--radius-sm); border: 1px solid var(--border); margin-bottom: 12px; font-size: 13px;">
                        <strong>Arahan Director:</strong><br>
                        {{ $dir->direction_text }}
                    </div>

                    @if($dir->completion_note)
                        <div style="background: #ECFDF3; padding: 10px; border-radius: var(--radius-sm); font-size: 12px; color: var(--success); margin-bottom: 12px;">
                            <strong>Catatan Penyelesaian:</strong><br>
                            {{ $dir->completion_note }}
                        </div>
                    @endif
                </div>

                <div>
                    <div style="font-size: 12px; color: var(--muted); border-top: 1px solid var(--border); padding-top: 10px; margin-top: 10px; display: flex; justify-content: space-between;">
                        <div>Dari: <strong>{{ $dir->creator->full_name ?? 'Director' }}</strong></div>
                        <div>Tenggat: <strong>{{ $dir->due_date ? $dir->due_date->format('d/m/Y') : '-' }}</strong></div>
                    </div>

                    <!-- Tim Actions Progression -->
                    <div style="display: flex; gap: 8px; margin-top: 12px; flex-wrap: wrap;">
                        @if($dir->status->value === 'OPEN')
                            <form action="{{ route('directions.acknowledge', $dir->id) }}" method="POST" style="width: 100%;">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-sm btn-primary" style="width: 100%;">
                                    👀 Saya Sudah Membaca (Acknowledge)
                                </button>
                            </form>
                        @elseif($dir->status->value === 'ACKNOWLEDGED')
                            <form action="{{ route('directions.start', $dir->id) }}" method="POST" style="width: 100%;">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-sm btn-secondary" style="width: 100%;">
                                    🚀 Mulai Tindak Lanjut (In Progress)
                                </button>
                            </form>
                        @elseif($dir->status->value === 'IN_PROGRESS')
                            <button type="button" @click="completeModalId = {{ $dir->id }}" class="btn btn-sm btn-success" style="width: 100%;">
                                ✅ Tandai Selesai (Close)
                            </button>
                        @elseif($dir->visitReport)
                            <a href="{{ route('reports.show', $dir->visitReport->id) }}" class="btn btn-sm btn-secondary" style="width: 100%;">
                                Lihat Laporan Terkait
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Complete Modal -->
                <div x-show="completeModalId === {{ $dir->id }}" style="position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 2000; display: flex; align-items: center; justify-content: center; padding: 20px;" x-cloak>
                    <div style="background: white; width: 100%; max-width: 480px; border-radius: var(--radius); padding: 24px;" @click.away="completeModalId = null">
                        <h3 style="font-weight: 700; margin-bottom: 12px; color: var(--navy);">Selesaikan Arahan Director</h3>
                        <form action="{{ route('directions.complete', $dir->id) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <div class="form-group">
                                <label class="form-label">Catatan Penyelesaian (Completion Note) *</label>
                                <textarea name="completion_note" class="form-control" rows="3" required placeholder="Tuliskan tindakan yang telah diambil dan hasil akhir..."></textarea>
                            </div>
                            <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 16px;">
                                <button type="button" @click="completeModalId = null" class="btn btn-secondary">Batal</button>
                                <button type="submit" class="btn btn-success">Selesaikan Arahan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div style="margin-top: 20px;">
        {{ $directions->links() }}
    </div>
@endif
@endsection
