@extends('layouts.app')

@section('title', 'Laporan ' . $visitReport->report_number)
@section('page_title', 'Detail Laporan Kunjungan')
@section('page_subtitle', $visitReport->report_number . ' - ' . $visitReport->visitPlan->customer->customer_name)

@section('content')
<div style="margin-bottom: 20px;">
    <a href="{{ route('reports.index') }}" class="btn btn-secondary">← Kembali ke Daftar Laporan</a>
</div>

<div class="grid grid-3">
    <!-- Main Report Body -->
    <div class="card" style="grid-column: span 2;">
        <div class="card-title">
            <span>Ringkasan Pelaporan</span>
            <span class="badge badge-success">{{ $visitReport->outcome_type->label() ?? $visitReport->outcome_type }}</span>
        </div>

        <table class="table" style="margin-bottom: 20px;">
            <tr>
                <td style="width: 200px; font-weight: 600; color: var(--muted);">Customer</td>
                <td style="font-weight: 700; color: var(--navy);">{{ $visitReport->visitPlan->customer->customer_name }}</td>
            </tr>
            <tr>
                <td style="font-weight: 600; color: var(--muted);">Waktu Aktual Kunjungan</td>
                <td>{{ $visitReport->actual_start_at->format('d/m/Y H:i') }} - {{ $visitReport->actual_end_at->format('H:i') }}</td>
            </tr>
            <tr>
                <td style="font-weight: 600; color: var(--muted);">Engagement Score</td>
                <td style="font-weight: 700; color: var(--warning);">★ {{ $visitReport->engagement_score }} dari 5</td>
            </tr>
            <tr>
                <td style="font-weight: 600; color: var(--muted);">Daftar Kehadiran</td>
                <td>{{ $visitReport->attendance_summary }}</td>
            </tr>
        </table>

        <div style="margin-bottom: 16px;">
            <h4 style="font-size: 14px; font-weight: 700; color: var(--navy); margin-bottom: 4px;">Ringkasan Hasil (Outcome Summary):</h4>
            <p style="background: #F8FAFC; padding: 12px; border-radius: var(--radius-sm); border: 1px solid var(--border);">{{ $visitReport->outcome_summary }}</p>
        </div>

        <div style="margin-bottom: 16px;">
            <h4 style="font-size: 14px; font-weight: 700; color: var(--navy); margin-bottom: 4px;">Rencana Langkah Selanjutnya (Next Step):</h4>
            <p style="background: #F8FAFC; padding: 12px; border-radius: var(--radius-sm); border: 1px solid var(--border);">{{ $visitReport->next_step_summary }}</p>
        </div>

        @if($visitReport->barrier)
            <div style="margin-bottom: 16px;">
                <h4 style="font-size: 14px; font-weight: 700; color: var(--danger); margin-bottom: 4px;">Hambatan / Kendala (Barrier):</h4>
                <p style="background: #FEF3F2; padding: 12px; border-radius: var(--radius-sm); border: 1px solid #FECDCA; color: var(--danger);">{{ $visitReport->barrier }}</p>
            </div>
        @endif

        @if($visitReport->need_or_opportunity)
            <div style="margin-bottom: 16px;">
                <h4 style="font-size: 14px; font-weight: 700; color: var(--success); margin-bottom: 4px;">Peluang Baru (Opportunity):</h4>
                <p style="background: #ECFDF3; padding: 12px; border-radius: var(--radius-sm); border: 1px solid #A6F4C5; color: var(--success);">{{ $visitReport->need_or_opportunity }}</p>
            </div>
        @endif

        @if($visitReport->competitor_information)
            <div style="margin-bottom: 16px;">
                <h4 style="font-size: 14px; font-weight: 700; color: var(--warning); margin-bottom: 4px;">Informasi Kompetitor:</h4>
                <p style="background: #FFFAEB; padding: 12px; border-radius: var(--radius-sm); border: 1px solid #FEDF89; color: var(--warning);">{{ $visitReport->competitor_information }}</p>
            </div>
        @endif

        <!-- Director Review Section -->
        <div style="margin-top: 32px; padding-top: 20px; border-top: 2px solid var(--border);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <h3 style="font-size: 16px; font-weight: 800; color: var(--navy);">Review & Arahan Director</h3>
                @if(auth()->user()->isAdmin() || auth()->user()->isDirector())
                    <a href="{{ route('director.review') }}" class="btn btn-sm btn-primary">➕ Berikan Arahan</a>
                @endif
            </div>

            @forelse($visitReport->directorInputs as $dir)
                <div style="padding: 16px; border: 1px solid var(--border); border-left: 4px solid var(--cyan); border-radius: var(--radius-sm); background: #F4F7FB; margin-bottom: 12px;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 6px;">
                        <div>
                            <span class="badge badge-info">{{ $dir->input_type->label() ?? $dir->input_type }}</span>
                            <strong style="margin-left: 6px; color: var(--navy); font-size: 14px;">{{ $dir->topic }}</strong>
                        </div>
                        <span class="badge badge-primary">{{ $dir->status->label() ?? $dir->status }}</span>
                    </div>

                    <p style="font-size: 13px; color: var(--text); margin: 8px 0;">{{ $dir->direction_text }}</p>

                    <div style="display: flex; justify-content: space-between; align-items: center; font-size: 11px; color: var(--muted); margin-top: 8px; border-top: 1px solid rgba(0,0,0,0.05); padding-top: 6px;">
                        <div>Director: <strong>{{ $dir->creator->full_name ?? 'Director' }}</strong> | PIC: <strong>{{ $dir->assignedUser->full_name ?? '-' }}</strong></div>
                        <div>Tenggat: <strong>{{ $dir->due_date ? $dir->due_date->format('d/m/Y') : '-' }}</strong></div>
                    </div>

                    @if($dir->completion_note)
                        <div style="margin-top: 10px; padding: 8px 10px; background: var(--success-bg); border-radius: 4px; font-size: 12px; color: var(--success);">
                            <strong>Catatan Penyelesaian:</strong> {{ $dir->completion_note }}
                        </div>
                    @endif
                </div>
            @empty
                <div style="text-align: center; padding: 24px; background: #F8FAFC; border-radius: var(--radius-sm); color: var(--muted); font-size: 13px;">
                    Belum ada review atau arahan Director untuk laporan ini.
                </div>
            @endforelse
        </div>
    </div>

    <!-- Right Sidebar Information -->
    <div class="card">
        <div class="card-title">Informasi Pelapor</div>
        <div style="margin-bottom: 16px;">
            <div style="font-size: 12px; color: var(--muted);">Disubmit Oleh:</div>
            <div style="font-weight: 700;">{{ $visitReport->submitter->full_name ?? '-' }}</div>
            <div style="font-size: 12px; color: var(--muted);">{{ $visitReport->submitted_at ? $visitReport->submitted_at->format('d F Y H:i') : '-' }}</div>
        </div>

        <hr style="border: none; border-top: 1px solid var(--border); margin: 16px 0;">

        <div class="card-title">Tindak Lanjut (Follow-ups)</div>
        @forelse($visitReport->followUps as $fu)
            <div style="padding: 10px; border: 1px solid var(--border); border-radius: 6px; margin-bottom: 8px; background: #FAFBFD;">
                <div style="font-weight: 700; font-size: 13px; color: var(--navy);">{{ $fu->action_title }}</div>
                <div style="font-size: 11px; color: var(--muted); margin-top: 2px;">
                    Owner: {{ $fu->owner->full_name }} | Due: {{ $fu->due_date->format('d/m/Y') }}
                </div>
                <div style="margin-top: 4px;">
                    <span class="badge badge-secondary" style="font-size: 10px;">{{ $fu->status->label() ?? $fu->status }}</span>
                </div>
            </div>
        @empty
            <div style="font-size: 12px; color: var(--muted);">Tidak ada tindak lanjut khusus dari kunjungan ini.</div>
        @endforelse
    </div>
</div>
@endsection
