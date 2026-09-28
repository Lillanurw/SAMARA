@extends('layouts.app')

@section('title', 'Kelola Area')
@section('page_title', 'Master Data - Wilayah / Areas')
@section('page_subtitle', 'Pengelolaan Area Operasional Penjualan')

@section('content')
<div x-data="{ addModal: false }">
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h3 style="font-size: 16px; font-weight: 700; color: var(--navy);">Daftar Wilayah Operasional</h3>
            <button type="button" @click="addModal = true" class="btn btn-primary">➕ Tambah Area</button>
        </div>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Kode Area</th>
                        <th>Nama Area</th>
                        <th>Region / Region Utama</th>
                        <th>Jumlah Customer</th>
                        <th>Jumlah User PIC</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($areas as $ar)
                        <tr>
                            <td style="font-weight: 700; color: var(--navy);">{{ $ar->area_code }}</td>
                            <td style="font-weight: 600;">{{ $ar->area_name }}</td>
                            <td>{{ $ar->region }}</td>
                            <td><span class="badge badge-primary">{{ $ar->customers_count }} Customer</span></td>
                            <td><span class="badge badge-secondary">{{ $ar->users_count }} User</span></td>
                            <td>
                                @if($ar->is_active)
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

    <!-- Modal Tambah Area -->
    <div x-show="addModal" style="position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 2000; display: flex; align-items: center; justify-content: center; padding: 20px;" x-cloak>
        <div style="background: white; width: 100%; max-width: 480px; border-radius: var(--radius); padding: 24px;" @click.away="addModal = false">
            <h3 style="font-weight: 700; margin-bottom: 16px; color: var(--navy);">Tambah Area Baru</h3>
            <form action="{{ route('admin.areas.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label class="form-label">Kode Area *</label>
                    <input type="text" name="area_code" class="form-control" required placeholder="AREA-BALI">
                </div>
                <div class="form-group">
                    <label class="form-label">Nama Area *</label>
                    <input type="text" name="area_name" class="form-control" required placeholder="Wilayah Bali & Nusa Tenggara">
                </div>
                <div class="form-group">
                    <label class="form-label">Region *</label>
                    <input type="text" name="region" class="form-control" required placeholder="Wilayah Timur">
                </div>
                <div class="form-group">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="description" class="form-control" rows="2" placeholder="Cakupan operasional area..."></textarea>
                </div>
                <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 20px;">
                    <button type="button" @click="addModal = false" class="btn btn-secondary">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Area</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
