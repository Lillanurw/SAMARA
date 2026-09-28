@extends('layouts.app')

@section('title', 'Audit Log System')
@section('page_title', 'System Audit Logs')
@section('page_subtitle', 'Catatan Aktivitas Pengguna & Jejak Transaksi Sistem (Read-Only)')

@section('content')
<div class="card">
    <div style="display: flex; gap: 12px; margin-bottom: 16px; flex-wrap: wrap;">
        <form method="GET" action="{{ route('admin.audit_logs.index') }}" style="display: flex; gap: 10px; flex: 1;">
            <input type="text" name="search" class="form-control" placeholder="Cari action, entity, atau nama aktor..." value="{{ $search }}">
            <button type="submit" class="btn btn-secondary">Cari Audit Log</button>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table" style="font-size: 13px;">
            <thead>
                <tr>
                    <th>Waktu (Timestamp)</th>
                    <th>Aktor (User)</th>
                    <th>Action</th>
                    <th>Entity & ID</th>
                    <th>IP Address & Device</th>
                    <th>Metadata</th>
                </tr>
            </thead>
            <tbody>
                @foreach($logs as $log)
                    <tr>
                        <td style="white-space: nowrap; font-weight: 600;">{{ $log->created_at ? $log->created_at->format('Y-m-d H:i:s') : '-' }}</td>
                        <td>
                            <div style="font-weight: 700;">{{ $log->actor->full_name ?? 'System' }}</div>
                            <div style="font-size: 11px; color: var(--muted);">{{ $log->actor->email ?? '-' }}</div>
                        </td>
                        <td><span class="badge badge-primary" style="font-size: 11px;">{{ $log->action }}</span></td>
                        <td>{{ $log->entity_type }} #{{ $log->entity_id ?? '-' }}</td>
                        <td style="font-size: 11px; color: var(--muted);">
                            {{ $log->ip_address }}<br>
                            {{ Str::limit($log->user_agent, 40) }}
                        </td>
                        <td>
                            @if($log->metadata)
                                <pre style="font-size: 10px; background: #F8FAFC; padding: 4px; border-radius: 4px; max-width: 250px; overflow-x: auto;">{{ json_encode($log->metadata, JSON_PRETTY_PRINT) }}</pre>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div style="margin-top: 16px;">
        {{ $logs->links() }}
    </div>
</div>
@endsection
