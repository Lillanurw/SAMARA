@extends('layouts.app')

@section('title', 'Kelola Users')
@section('page_title', 'Master Data - User Management')
@section('page_subtitle', 'Pengelolaan Akun, Role (ADMIN, TIM, DIRECTOR), dan Area Penugasan')

@section('content')
<div x-data="{ addModal: false, editModal: false, selectedUser: {} }">
    <div class="samara-card">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 16px;">
            <form method="GET" action="{{ route('admin.users.index') }}" style="display: flex; gap: 10px; flex: 1; max-width: 500px;">
                <input type="text" name="search" class="form-control" placeholder="Cari nama atau email..." value="{{ $search }}">
                <select name="role" class="form-select" onchange="this.form.submit()" style="max-width: 150px;">
                    <option value="">Semua Role</option>
                    <option value="ADMIN" {{ $role == 'ADMIN' ? 'selected' : '' }}>ADMIN</option>
                    <option value="TIM" {{ $role == 'TIM' ? 'selected' : '' }}>TIM</option>
                    <option value="DIRECTOR" {{ $role == 'DIRECTOR' ? 'selected' : '' }}>DIRECTOR</option>
                </select>
                <button type="submit" class="btn btn-secondary">Cari</button>
            </form>

            <button type="button" @click="addModal = true" class="btn btn-primary">➕ Tambah User Baru</button>
        </div>

        <div class="table-responsive">
            <table class="samara-table">
                <thead>
                    <tr>
                        <th>Nama & Email</th>
                        <th>Role</th>
                        <th>Area Penugasan</th>
                        <th>Manager Email</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $u)
                        <tr>
                            <td>
                                <div style="font-weight: 700; color: var(--navy);">{{ $u->full_name }}</div>
                                <div style="font-size: 12px; color: var(--muted);">{{ $u->email }}</div>
                            </td>
                            <td>
                                <span class="badge badge-cyan">{{ $u->role?->value ?? $u->role }}</span>
                            </td>
                            <td>
                                @forelse($u->areas as $ar)
                                    <span class="badge badge-gray" style="font-size: 10px;">{{ $ar->area_code }}</span>
                                @empty
                                    <span style="font-size: 12px; color: var(--muted);">-</span>
                                @endforelse
                            </td>
                            <td>{{ $u->manager_email ?? '-' }}</td>
                            <td>
                                @if($u->is_active)
                                    <span class="badge badge-success">Aktif</span>
                                @else
                                    <span class="badge badge-danger">Nonaktif</span>
                                @endif
                            </td>
                            <td>
                                <form action="{{ route('admin.users.toggle', $u->id) }}" method="POST" style="display: inline;">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm {{ $u->is_active ? 'btn-danger' : 'btn-success' }}">
                                        {{ $u->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div style="margin-top: 16px;">
            {{ $users->links() }}
        </div>
    </div>

    <!-- Modal Tambah User -->
    <div x-show="addModal" style="position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 2000; display: flex; align-items: center; justify-content: center; padding: 20px;" x-cloak>
        <div style="background: white; width: 100%; max-width: 500px; border-radius: var(--radius); padding: 24px;" @click.away="addModal = false">
            <h3 style="font-weight: 700; margin-bottom: 16px; color: var(--navy);">Tambah User Baru</h3>
            <form action="{{ route('admin.users.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label class="form-label">Nama Lengkap *</label>
                    <input type="text" name="full_name" class="form-control" required placeholder="Contoh: Ahmad Subagyo">
                </div>

                <div class="form-group">
                    <label class="form-label">Alamat Email *</label>
                    <input type="email" name="email" class="form-control" required placeholder="nama@perusahaan.com">
                </div>

                <div class="grid grid-2">
                    <div class="form-group">
                        <label class="form-label">Role *</label>
                        <select name="role" class="form-select" required>
                            <option value="TIM">TIM (Sales / Field)</option>
                            <option value="DIRECTOR">DIRECTOR</option>
                            <option value="ADMIN">ADMIN</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Kata Sandi Initial *</label>
                        <input type="password" name="password" class="form-control" required value="password123">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Manager Email (Opsional)</label>
                    <input type="email" name="manager_email" class="form-control" placeholder="director@perusahaan.com">
                </div>

                <div class="form-group">
                    <label class="form-label">Area Penugasan</label>
                    <select name="areas[]" class="form-select" multiple style="height: 90px;">
                        @foreach($areas as $area)
                            <option value="{{ $area->id }}">{{ $area->area_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label style="display: flex; align-items: center; gap: 8px; font-weight: 600;">
                        <input type="checkbox" name="is_active" value="1" checked> User Langsung Aktif
                    </label>
                </div>

                <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 20px;">
                    <button type="button" @click="addModal = false" class="btn btn-secondary">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan User</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
