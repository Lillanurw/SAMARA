<div style="font-family: Arial, Helvetica, sans-serif; font-size: 11px; color: #000; background: #fff; padding: 10px;">
    <!-- Centered Header Title -->
    <div style="text-align: center; font-weight: bold; font-size: 14px; text-transform: uppercase; margin-bottom: 20px; letter-spacing: 0.5px;">
        Rencana Kunjungan dan Realisasi Kunjungan
    </div>

    <!-- Metadata Top Header (Field Force, Rayon, Minggu ke, Bulan) -->
    <table style="width: 100%; font-weight: bold; font-size: 11px; margin-bottom: 12px; border-collapse: collapse; border: none;">
        <tr>
            <td style="vertical-align: top; border: none; padding: 0;">
                <table style="border-collapse: collapse; border: none;">
                    <tr>
                        <td style="width: 90px; text-align: left; vertical-align: top; border: none; padding: 2px 0;">FIELD FORCE</td>
                        <td style="width: 15px; text-align: center; vertical-align: top; border: none; padding: 2px 0;">:</td>
                        <td style="text-align: left; vertical-align: top; border: none; padding: 2px 0;">{{ strtoupper($fieldForce) }}</td>
                    </tr>
                    <tr>
                        <td style="width: 90px; text-align: left; vertical-align: top; border: none; padding: 2px 0;">RAYON</td>
                        <td style="width: 15px; text-align: center; vertical-align: top; border: none; padding: 2px 0;">:</td>
                        <td style="text-align: left; vertical-align: top; border: none; padding: 2px 0;">{{ strtoupper($rayon) }}</td>
                    </tr>
                </table>
            </td>
            <td style="width: 200px; vertical-align: top; border: none; padding: 0;">
                <table style="width: 100%; border-collapse: collapse; border: none;">
                    <tr>
                        <td style="width: 75px; text-align: left; vertical-align: top; border: none; padding: 2px 0;">MINGGU KE</td>
                        <td style="width: 15px; text-align: center; vertical-align: top; border: none; padding: 2px 0;">:</td>
                        <td style="text-align: left; vertical-align: top; border: none; padding: 2px 0;">{{ $weekNo }}</td>
                    </tr>
                    <tr>
                        <td style="width: 75px; text-align: left; vertical-align: top; border: none; padding: 2px 0;">BULAN</td>
                        <td style="width: 15px; text-align: center; vertical-align: top; border: none; padding: 2px 0;">:</td>
                        <td style="text-align: left; vertical-align: top; border: none; padding: 2px 0;">{{ strtoupper($monthName) }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Main Grid Matrix Table -->
    <table border="1" cellspacing="0" cellpadding="6" style="width: 100%; border-collapse: collapse; font-size: 10.5px; text-align: left; border: 1px solid #000;">
        <thead>
            <tr style="background-color: #f2f2f2; text-align: center;">
                <th rowspan="3" style="width: 12%; text-align: center; vertical-align: middle; border: 1px solid #000; font-weight: bold; padding: 8px 4px;">Hari/Tanggal</th>
                <th colspan="2" style="text-align: center; border: 1px solid #000; font-weight: bold; padding: 6px 4px;">Rencana Kunjungan</th>
                <th rowspan="3" style="width: 8%; text-align: center; vertical-align: middle; border: 1px solid #000; font-weight: bold; padding: 8px 4px;">Keterangan</th>
                <th colspan="5" style="text-align: center; border: 1px solid #000; font-weight: bold; padding: 6px 4px;">Hasil Kunjungan</th>
            </tr>
            <tr style="background-color: #f2f2f2; text-align: center;">
                <th rowspan="2" style="width: 13%; text-align: center; vertical-align: middle; border: 1px solid #000; font-weight: bold; font-style: italic; padding: 6px 4px;">Customer</th>
                <th rowspan="2" style="width: 10%; text-align: center; vertical-align: middle; border: 1px solid #000; font-weight: bold; font-style: italic; padding: 6px 4px;">Target</th>
                <th rowspan="2" style="width: 13%; text-align: center; vertical-align: middle; border: 1px solid #000; font-weight: bold; font-style: italic; padding: 6px 4px;">Customer</th>
                <th colspan="2" style="text-align: center; border: 1px solid #000; font-weight: bold; padding: 4px;">Pejabat yang Ditemui</th>
                <th rowspan="2" style="width: 16%; text-align: center; vertical-align: middle; border: 1px solid #000; font-weight: bold; font-style: italic; padding: 6px 4px;">Hasil Kunjungan</th>
                <th rowspan="2" style="width: 14%; text-align: center; vertical-align: middle; border: 1px solid #000; font-weight: bold; font-style: italic; padding: 6px 4px;">Tindak Lanjut</th>
            </tr>
            <tr style="background-color: #f2f2f2; text-align: center;">
                <th style="width: 10%; text-align: center; border: 1px solid #000; font-weight: bold; padding: 4px;">Nama</th>
                <th style="width: 10%; text-align: center; border: 1px solid #000; font-weight: bold; padding: 4px;">Jabatan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($plans as $plan)
                <tr>
                    <td style="border: 1px solid #000; vertical-align: top; padding: 6px 4px; font-weight: 600;">
                        {{ $plan->planned_date->translatedFormat('l, d F Y') }}
                    </td>
                    <td style="border: 1px solid #000; vertical-align: top; padding: 6px 4px;">
                        {{ $plan->customer->customer_name ?? '-' }}
                    </td>
                    <td style="border: 1px solid #000; vertical-align: top; padding: 6px 4px;">
                        {{ $plan->specific_objective ?? $plan->monthly_objective ?? '-' }}
                    </td>
                    <td style="border: 1px solid #000; vertical-align: top; padding: 6px 4px;">
                        {{ $plan->resource_notes ?? $plan->location_text ?? '-' }}
                    </td>
                    <td style="border: 1px solid #000; vertical-align: top; padding: 6px 4px;">
                        {{ $plan->customer->customer_name ?? '-' }}
                    </td>
                    <td style="border: 1px solid #000; vertical-align: top; padding: 6px 4px;">
                        {{ $plan->visitReport->attendance_summary ?? $plan->customer->contact_name ?? '-' }}
                    </td>
                    <td style="border: 1px solid #000; vertical-align: top; padding: 6px 4px;">
                        {{ $plan->customer->contact_position ?? '-' }}
                    </td>
                    <td style="border: 1px solid #000; vertical-align: top; padding: 6px 4px;">
                        @if($plan->visitReport)
                            {{ $plan->visitReport->outcome_summary ?: ($plan->visitReport->outcome_type->label() ?? 'Selesai') }}
                        @elseif($plan->status->value === 'cancelled')
                            Batal: {{ $plan->cancellation_reason ?: '-' }}
                        @else
                            Belum Ada Laporan
                        @endif
                    </td>
                    <td style="border: 1px solid #000; vertical-align: top; padding: 6px 4px;">
                        @if($plan->visitReport)
                            {{ $plan->visitReport->next_step_summary ?: ($plan->visitReport->followUps->pluck('action_plan')->implode(', ') ?: '-') }}
                        @else
                            -
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" style="text-align: center; padding: 24px; border: 1px solid #000; color: #666; font-style: italic;">
                        Tidak ada data rencana dan realisasi kunjungan pada rentang tanggal yang dipilih.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
