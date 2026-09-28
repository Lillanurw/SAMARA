@extends('layouts.app')

@section('title', 'Kelola Segment')
@section('page_title', 'Master Data - Segments')
@section('page_subtitle', 'Pengelolaan Segment Industri dan Jenis Perlengkapan')

@section('content')
<div x-data="{ addModal: false }">
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h3 style="font-size: 16px; font-weight: 700; color: var(--navy);">Daftar Segment</h3>
            <button type="button" @click="addModal = true" class="btn btn-primary">➕ Tambah Segment</button>
        </div>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Kode Segment</th>
                        <th>Nama Segment</th>
                        <th>Deskripsi</th>
                        <th>Jumlah Customer</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($segments as $sg)
                        <tr>
                            <td style="font-weight: 700; color: var(--navy);">{{ $sg->segment_code }}</td>
                            <td style="font-weight: 600;">{{ $sg->segment_name }}</td>
                            <td style="font-size: 13px; color: var(--muted);">{{ $sg->description ?? '-' }}</td>
                            <td><span class="badge badge-primary">{{ $sg->customers_count }} Customer</span></td>
                            <td>
                                @if($sg->is_active)
                                    <span class="badge badge-success">Aktif</span>
                                @else
                                    <span class="badge badge-danger">Nonaktif</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Tambah Segment -->
    <div x-show="addModal" style="position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 2000; display: flex; align-items: center; justify-content: center; padding: 20px;" x-cloak>
        <div style="background: white; width: 100%; max-width: 480px; border-radius: var(--radius); padding: 24px;" @click.away="addModal = false">
            <h3 style="font-weight: 700; margin-bottom: 16px; color: var(--navy);">Tambah Segment Baru</h3>
            <form action="{{ route('admin.segments.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label class="form-label">Kode Segment *</label>
                    <input type="text" name="segment_code" class="form-control" required placeholder="SEG-TI">
                </div>
                <div class="form-group">
                    <label class="form-label">Nama Segment *</label>
                    <input type="text" name="segment_name" class="form-control" required placeholder="Perangkat IT & Software">
                </div>
                <div class="form-group">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="description" class="form-control" rows="2" placeholder="Deskripsi jenis produk/layanan segment..."></textarea>
                </div>
                <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 20px;">
                    <button type="button" @click="addModal = false" class="btn btn-secondary">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Segment</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
