@extends('layouts.app')

@php
    $hour = now('Asia/Jakarta')->hour;
    if ($hour < 11) {
        $greeting = 'Selamat pagi';
    } elseif ($hour < 15) {
        $greeting = 'Selamat siang';
    } elseif ($hour < 19) {
        $greeting = 'Selamat sore';
    } else {
        $greeting = 'Selamat malam';
    }
    $greetingText = $greeting . ', ' . auth()->user()->display_name;
@endphp

@section('title', 'Dashboard Direktur Utama — ' . auth()->user()->display_name)
@section('page_title', 'Dashboard Direktur Utama')
@section('page_subtitle', $greetingText)

@section('header_actions')
    <div style="display: flex; gap: 8px;">
        <button type="button" class="btn btn-primary" onclick="openAgendaModal()">+ Tambah Agenda</button>
        <a href="{{ route('agenda.index') }}" class="btn btn-outline-primary">Lihat Kalender</a>
    </div>
@endsection

@section('content')
<div class="samara-card" style="margin-bottom: 24px;">
    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border); padding-bottom: 12px; margin-bottom: 16px;">
        <div>
            <h2 style="font-size: 18px; font-weight: 800; color: var(--heading); margin: 0 0 4px 0;">{{ $greetingText }}</h2>
            <p style="font-size: 13px; color: var(--text-secondary); margin: 0;">Ringkasan Agenda & Jadwal ({{ auth()->user()->display_name }})</p>
        </div>
        <button onclick="requestNotificationPermission()" class="btn btn-sm btn-outline-primary" id="btn-subscribe">Aktifkan Notifikasi HP</button>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 16px;">
        <div style="background: var(--surface-soft); padding: 16px; border-radius: var(--radius); text-align: center;">
            <div style="font-size: 24px; font-weight: 800; color: var(--heading);">{{ $stats['terjadwal'] }}</div>
            <div style="font-size: 13px; color: var(--text-secondary);">Terjadwal</div>
        </div>
        <div style="background: var(--surface-soft); padding: 16px; border-radius: var(--radius); text-align: center;">
            <div style="font-size: 24px; font-weight: 800; color: var(--success);">{{ $stats['selesai'] }}</div>
            <div style="font-size: 13px; color: var(--text-secondary);">Selesai</div>
        </div>
        <div style="background: var(--surface-soft); padding: 16px; border-radius: var(--radius); text-align: center;">
            <div style="font-size: 24px; font-weight: 800; color: var(--danger);">{{ $stats['dibatalkan'] }}</div>
            <div style="font-size: 13px; color: var(--text-secondary);">Dibatalkan</div>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 24px;">
    <!-- Agenda Hari Ini -->
    <div class="samara-card">
        <h3 style="font-size: 16px; font-weight: 800; color: var(--heading); margin-bottom: 16px;">Agenda Hari Ini</h3>
        @if($todayAgendas->isEmpty())
            <div style="text-align: center; padding: 24px; color: var(--text-secondary);">Tidak ada agenda hari ini.</div>
        @else
            <div style="display: flex; flex-direction: column; gap: 12px;">
                @foreach($todayAgendas as $agenda)
                    <div style="padding: 12px; border: 1px solid var(--border); border-radius: var(--radius); border-left: 4px solid var(--accent); background: var(--surface-soft);">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                            <div style="font-weight: 700; color: var(--text);">{{ $agenda->title }}</div>
                            <span class="agenda-chip {{ $agenda->type->cssClass() }}">{{ $agenda->type->label() }}</span>
                        </div>
                        <div style="font-size: 13px; color: var(--text-secondary);">
                            🕒 {{ $agenda->start_time->format('H:i') }} - {{ $agenda->end_time->format('H:i') }} <br>
                            📍 {{ $agenda->location ?? 'Tidak ada lokasi' }}
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Agenda Mendatang -->
    <div class="samara-card">
        <h3 style="font-size: 16px; font-weight: 800; color: var(--heading); margin-bottom: 16px;">Agenda Mendatang</h3>
        @if($upcomingAgendas->isEmpty())
            <div style="text-align: center; padding: 24px; color: var(--text-secondary);">Tidak ada agenda mendatang.</div>
        @else
            <div style="display: flex; flex-direction: column; gap: 12px;">
                @foreach($upcomingAgendas as $agenda)
                    <div style="padding: 12px; border: 1px solid var(--border); border-radius: var(--radius); border-left: 4px solid var(--accent); background: var(--surface-soft);">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                            <div style="font-weight: 700; color: var(--text);">{{ $agenda->title }}</div>
                            <span class="agenda-chip {{ $agenda->type->cssClass() }}">{{ $agenda->type->label() }}</span>
                        </div>
                        <div style="font-size: 13px; color: var(--text-secondary);">
                            📅 {{ $agenda->start_time->format('d M Y') }} • 🕒 {{ $agenda->start_time->format('H:i') }} <br>
                            📍 {{ $agenda->location ?? 'Tidak ada lokasi' }}
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

<!-- Modal Form Tambah Agenda -->
<div id="agenda-modal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.6); z-index: 1000; align-items: center; justify-content: center; padding: 20px;">
    <div class="samara-card" style="width: 100%; max-width: 500px; max-height: 90vh; overflow-y: auto; box-shadow: var(--shadow-lg);">
        <h3 id="modal-title" style="margin-bottom: 16px; font-weight: 800; color: var(--heading);">Tambah Agenda</h3>
        
        <form id="agenda-form" method="POST" action="{{ route('agenda.store') }}">
            @csrf
            
            <div class="form-group" style="margin-bottom: 12px;">
                <label>Judul Agenda</label>
                <input type="text" name="title" id="agenda-title" class="form-control" placeholder="Contoh: Rapat Direksi Evaluasi Q3" required>
            </div>
            
            <div class="form-group" style="margin-bottom: 12px;">
                <label>Tipe Agenda</label>
                <select name="type" id="agenda-type" class="form-control" required>
                    @foreach($agendaTypes ?? \App\Enums\AgendaType::cases() as $type)
                        <option value="{{ $type->value }}">{{ $type->label() }}</option>
                    @endforeach
                </select>
            </div>
            
            <div style="display: flex; gap: 12px; margin-bottom: 12px;">
                <div class="form-group" style="flex: 1;">
                    <label>Waktu Mulai</label>
                    <input type="datetime-local" name="start_time" id="agenda-start" class="form-control" required>
                </div>
                <div class="form-group" style="flex: 1;">
                    <label>Waktu Selesai</label>
                    <input type="datetime-local" name="end_time" id="agenda-end" class="form-control" required>
                </div>
            </div>
            
            <div class="form-group" style="margin-bottom: 12px;">
                <label>Lokasi / Media</label>
                <input type="text" name="location" id="agenda-location" class="form-control" placeholder="Ruang Rapat Utama / Zoom Link">
            </div>
            
            <div class="form-group" style="margin-bottom: 12px;">
                <label>Deskripsi</label>
                <textarea name="description" id="agenda-desc" class="form-control" rows="3" placeholder="Catatan atau agenda rapat..."></textarea>
            </div>
            
            <div style="display: flex; justify-content: flex-end; gap: 8px; margin-top: 24px;">
                <button type="button" class="btn btn-outline-secondary" onclick="closeAgendaModal()">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Agenda</button>
            </div>
        </form>
    </div>
</div>

<script>
function openAgendaModal() {
    document.getElementById('agenda-modal').style.display = 'flex';
    document.getElementById('agenda-form').reset();
    
    // Set default times to now and +1 hour
    const now = new Date();
    now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
    document.getElementById('agenda-start').value = now.toISOString().slice(0,16);
    
    now.setHours(now.getHours() + 1);
    document.getElementById('agenda-end').value = now.toISOString().slice(0,16);
}

function closeAgendaModal() {
    document.getElementById('agenda-modal').style.display = 'none';
}

async function requestNotificationPermission() {
    if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
        alert('Push notifications tidak didukung di browser ini.');
        return;
    }

    try {
        const permission = await Notification.requestPermission();
        if (permission !== 'granted') {
            alert('Izin notifikasi ditolak.');
            return;
        }

        const registration = await navigator.serviceWorker.ready;
        let subscription = await registration.pushManager.getSubscription();
        
        if (!subscription) {
            const vapidPublicKey = '{{ config('services.vapid.public_key') }}';
            const convertedVapidKey = urlBase64ToUint8Array(vapidPublicKey);

            subscription = await registration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: convertedVapidKey
            });
        }

        // Send subscription to backend
        await fetch('{{ route('push.subscribe') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(subscription)
        });

        document.getElementById('btn-subscribe').textContent = 'Notifikasi Aktif ✔️';
        document.getElementById('btn-subscribe').classList.replace('btn-outline-primary', 'btn-success');
    } catch (error) {
        console.error('Error subscribing to push:', error);
        alert('Terjadi kesalahan saat mengaktifkan notifikasi.');
    }
}

function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - base64String.length % 4) % 4);
    const base64 = (base64String + padding)
        .replace(/\-/g, '+')
        .replace(/_/g, '/');

    const rawData = window.atob(base64);
    const outputArray = new Uint8Array(rawData.length);

    for (let i = 0; i < rawData.length; ++i) {
        outputArray[i] = rawData.charCodeAt(i);
    }
    return outputArray;
}
</script>
@endsection
