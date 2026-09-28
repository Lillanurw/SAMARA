@extends('layouts.app')

@section('title', 'Kelola Customer')
@section('page_title', 'Master Data - Customers')
@section('page_subtitle', 'Pengelolaan Data Customer, Area, Segment, dan Owner PIC')

@section('content')
<div x-data="{ addModal: false }">
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 16px;">
            <form method="GET" action="{{ route('admin.customers.index') }}" style="display: flex; gap: 10px; flex: 1; flex-wrap: wrap;">
                <input type="text" name="search" class="form-control" placeholder="Cari kode/nama customer..." value="{{ $search }}" style="max-width: 250px;">
                <select name="area_id" class="form-select" onchange="this.form.submit()" style="max-width: 160px;">
                    <option value="">Semua Area</option>
                    @foreach($areas as $area)
                        <option value="{{ $area->id }}" {{ $areaId == $area->id ? 'selected' : '' }}>{{ $area->area_name }}</option>
                    @endforeach
                </select>
                <select name="segment_id" class="form-select" onchange="this.form.submit()" style="max-width: 160px;">
                    <option value="">Semua Segment</option>
                    @foreach($segments as $segment)
                        <option value="{{ $segment->id }}" {{ $segmentId == $segment->id ? 'selected' : '' }}>{{ $segment->segment_name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-secondary">Filter</button>
            </form>

            <button type="button" @click="addModal = true" class="btn btn-primary">➕ Tambah Customer Baru</button>
        </div>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Kode & Customer</th>
                        <th>Area & Segment</th>
                        <th>Kota / Provinsi</th>
                        <th>PIC (Owner)</th>
                        <th>Tier</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($customers as $c)
                        <tr>
                            <td>
                                <div style="font-weight: 700; color: var(--navy);">{{ $c->customer_name }}</div>
                                <div style="font-size: 11px; color: var(--muted);">{{ $c->customer_code }}</div>
                            </td>
                            <td>
                                <div style="font-weight: 600;">{{ $c->area->area_name ?? '-' }}</div>
                                <div style="font-size: 11px; color: var(--muted);">{{ $c->segment->segment_name ?? '-' }}</div>
                            </td>
                            <td>{{ $c->city }}, {{ $c->province }}</td>
                            <td>{{ $c->owner->full_name ?? '-' }}</td>
                            <td><span class="badge badge-warning">Tier {{ $c->priority_tier->value ?? $c->priority_tier }}</span></td>
                            <td>
                                @if($c->is_active)
                                    <span class="badge badge-success">Aktif</span>
                                @else
                                    <span class="badge badge-danger">Nonaktif</span>
                                @endif
                            </td>
                            <td>
                                <form action="{{ route('admin.customers.toggle', $c->id) }}" method="POST" style="display: inline;">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm {{ $c->is_active ? 'btn-danger' : 'btn-success' }}">
                                        {{ $c->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div style="margin-top: 16px;">
            {{ $customers->links() }}
        </div>
    </div>

    <!-- Modal Tambah Customer -->
    <div x-show="addModal" style="position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 2000; display: flex; align-items: center; justify-content: center; padding: 20px;" x-cloak>
        <div style="background: white; width: 100%; max-width: 600px; border-radius: var(--radius); padding: 24px; max-height: 90vh; overflow-y: auto;" @click.away="addModal = false">
            <h3 style="font-weight: 700; margin-bottom: 16px; color: var(--navy);">Tambah Customer Baru</h3>
            <form action="{{ route('admin.customers.store') }}" method="POST">
                @csrf
                <div class="grid grid-2">
                    <div class="form-group">
                        <label class="form-label">Kode Customer *</label>
                        <input type="text" name="customer_code" class="form-control" required placeholder="CUST-011">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Nama Customer *</label>
                        <input type="text" name="customer_name" class="form-control" required placeholder="Koperasi Primkopad Mabes">
                    </div>
                </div>

                <div class="grid grid-2">
                    <div class="form-group">
                        <label class="form-label">Area *</label>
                        <select name="area_id" class="form-select" required>
                            @foreach($areas as $area)
                                <option value="{{ $area->id }}">{{ $area->area_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Segment *</label>
                        <select name="segment_id" class="form-select" required>
                            @foreach($segments as $seg)
                                <option value="{{ $seg->id }}">{{ $seg->segment_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-2">
                    <div class="form-group">
                        <label class="form-label">PIC (Owner) *</label>
                        <select name="owner_id" class="form-select" required>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}">{{ $u->full_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Priority Tier *</label>
                        <select name="priority_tier" class="form-select" required>
                            <option value="A">Tier A (Prioritas Tinggi)</option>
                            <option value="B" selected>Tier B (Sedang)</option>
                            <option value="C">Tier C (Rendah / Standar)</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Alamat Lengkap *</label>
                    <input type="text" name="address" class="form-control" required placeholder="Jl. Veteran No. 5">
                </div>

                <div class="grid grid-2">
                    <div class="form-group">
                        <label class="form-label">Kota *</label>
                        <input type="text" name="city" class="form-control" required placeholder="Jakarta Pusat">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Provinsi *</label>
                        <input type="text" name="province" class="form-control" required placeholder="DKI Jakarta">
                    </div>
                </div>

                <div class="grid grid-2">
                    <div class="form-group">
                        <label class="form-label">Nama Kontak Person</label>
                        <input type="text" name="contact_name" class="form-control" placeholder="Kapten Suwandi">
                    </div>
                    <div class="form-group">
                        <label class="form-label">No. HP / Telepon Kontak</label>
                        <input type="text" name="contact_phone" class="form-control" placeholder="081299887766">
                    </div>
                </div>

                <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 20px;">
                    <button type="button" @click="addModal = false" class="btn btn-secondary">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Customer</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
