@extends('layouts.app')

@section('title', 'Import Data Lama')
@section('page_title', 'Import Data (CSV / XLSX)')
@section('page_subtitle', 'Migrasi Data Google Sheets / Excel ke Database MySQL SAMARA')

@section('content')
<div class="grid grid-2">
    <!-- Upload Form Card -->
    <div class="card">
        <div class="card-title">Unggah File Migrasi</div>

        @if(session('import_result'))
            @php $res = session('import_result'); @endphp
            <div class="alert alert-success">
                <div>
                    <strong>Proses Impor Selesai!</strong><br>
                    - Berhasil diimpor: <strong>{{ $res['success_count'] }}</strong> baris.<br>
                    - Gagal diimpor: <strong>{{ $res['failed_count'] }}</strong> baris.
                </div>
            </div>

            @if(!empty($res['errors']))
                <div class="alert alert-warning" style="max-height: 200px; overflow-y: auto;">
                    <strong>Daftar Baris Error:</strong>
                    <ul style="margin-left: 18px; margin-top: 6px; font-size: 12px;">
                        @foreach($res['errors'] as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        @endif

        <form action="{{ route('admin.import.process') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label class="form-label">Jenis Data yang Diimpor *</label>
                <select name="data_type" class="form-select" required>
                    <option value="USERS">1. Master Users (USERS)</option>
                    <option value="AREAS">2. Master Wilayah (AREAS)</option>
                    <option value="SEGMENTS">3. Master Segment (SEGMENTS)</option>
                    <option value="CUSTOMERS">4. Master Customers (CUSTOMERS)</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Pilih File CSV / XLSX *</label>
                <input type="file" name="file" class="form-control" accept=".csv, .txt, .xlsx" required style="padding: 8px;">
            </div>

            <div style="font-size: 12px; color: var(--muted); margin-bottom: 20px;">
                * File CSV harus memiliki baris pertama sebagai Header Nama Kolom (misal: <code>email, full_name, role, password</code>).
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%;">
                📥 Jalankan Import Data
            </button>
        </form>
    </div>

    <!-- Instructions & Rules -->
    <div class="card">
        <div class="card-title">Ketentuan & Normalisasi Impor</div>
        <ul style="margin-left: 20px; font-size: 13px; line-height: 1.6; color: var(--text);">
            <li><strong>Role Auto-Mapping:</strong> Role lama <code>PLANNER</code>, <code>FIELD</code>, <code>MANAGER</code>, dan <code>VIEWER</code> akan otomatis dinormalisasi menjadi <code>TIM</code>.</li>
            <li><strong>Deteksi Duplikasi:</strong> Email user, Kode Area, Kode Segment, dan Kode Customer yang sudah ada di database akan dilewati/diberikan keterangan gagal untuk mencegah korupsi data.</li>
            <li><strong>Database Transaction:</strong> Proses impor dieksekusi secara atomic dalam MySQL Transaction.</li>
            <li><strong>Format File:</strong> Disarankan menggunakan format CSV UTF-8.</li>
        </ul>
    </div>
</div>
@endsection
