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
                                <div style="font-weight: 700; color: var(--heading);">{{ $u->display_name }}</div>
                                <div style="font-size: 12px; color: var(--text-muted);">{{ $u->email }}</div>
                            </td>
                            <td>
                                <span class="badge badge-cyan">{{ $u->role?->value ?? $u->role }}</span>
                            </td>
                            <td>
                                @forelse($u->areas as $ar)
                                    <span class="badge badge-gray" style="font-size: 10px;">{{ $ar->area_code }}</span>
                                @empty
                                    <span style="font-size: 12px; color: var(--text-muted);">-</span>
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
                                <div style="display: flex; gap: 6px; align-items: center;">
                                    <button type="button"
                                            @click="selectedUser = {{ json_encode([
                                                'id' => $u->id,
                                                'honorific' => $u->honorific,
                                                'full_name' => $u->full_name,
                                                'email' => $u->email,
                                                'role' => $u->role?->value ?? $u->role,
                                                'manager_email' => $u->manager_email,
                                                'is_active' => (bool)$u->is_active,
                                                'areas' => $u->areas->pluck('id')->toArray(),
                                            ]) }}; editModal = true"
                                            class="btn btn-sm btn-secondary">
                                        Edit
                                    </button>
                                    <form action="{{ route('admin.users.toggle', $u->id) }}" method="POST" style="display: inline; margin: 0;">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm {{ $u->is_active ? 'btn-danger' : 'btn-success' }}">
                                            {{ $u->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </button>
                                    </form>
                                </div>
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
    <div x-show="addModal" style="position: fixed; inset: 0; background: rgba(0,0,0,0.6); z-index: 2000; display: flex; align-items: center; justify-content: center; padding: 20px;" x-cloak>
        <div style="background: var(--surface); color: var(--text); border: 1px solid var(--border); width: 100%; max-width: 500px; border-radius: var(--radius-lg); padding: 24px; box-shadow: var(--shadow-lg);" @click.away="addModal = false">
            <h3 style="font-weight: 700; margin-bottom: 16px; color: var(--heading);">Tambah User Baru</h3>
            <form action="{{ route('admin.users.store') }}" method="POST">
                @csrf
                <div class="grid grid-2">
                    <div class="form-group">
                        <label class="form-label">Sapaan (Opsional)</label>
                        <input type="text" name="honorific" class="form-control" placeholder="Contoh: Bpk, Ibu" maxlength="20">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Nama Lengkap *</label>
                        <input type="text" name="full_name" class="form-control" required placeholder="Contoh: Ahmad Subagyo">
                    </div>
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
                            <option value="DIREKTUR_UTAMA">DIREKTUR UTAMA</option>
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

    <!-- Modal Edit User -->
    <div x-show="editModal" style="position: fixed; inset: 0; background: rgba(0,0,0,0.6); z-index: 2000; display: flex; align-items: center; justify-content: center; padding: 20px;" x-cloak>
        <div style="background: var(--surface); color: var(--text); border: 1px solid var(--border); width: 100%; max-width: 500px; border-radius: var(--radius-lg); padding: 24px; box-shadow: var(--shadow-lg);" @click.away="editModal = false">
            <h3 style="font-weight: 700; margin-bottom: 16px; color: var(--heading);">Edit User</h3>
            <form :action="'{{ url('admin/users') }}/' + selectedUser.id" method="POST">
                @csrf
                @method('PATCH')
                <div class="grid grid-2">
                    <div class="form-group">
                        <label class="form-label">Sapaan (Opsional)</label>
                        <input type="text" name="honorific" class="form-control" :value="selectedUser.honorific" placeholder="Contoh: Bpk, Ibu" maxlength="20">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Nama Lengkap *</label>
                        <input type="text" name="full_name" class="form-control" required :value="selectedUser.full_name" placeholder="Contoh: Ahmad Subagyo">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Alamat Email *</label>
                    <input type="email" name="email" class="form-control" required :value="selectedUser.email" placeholder="nama@perusahaan.com">
                </div>

                <div class="grid grid-2">
                    <div class="form-group">
                        <label class="form-label">Role *</label>
                        <select name="role" class="form-select" required x-model="selectedUser.role">
                            <option value="TIM">TIM (Sales / Field)</option>
                            <option value="DIRECTOR">DIRECTOR</option>
                            <option value="DIREKTUR_UTAMA">DIREKTUR UTAMA</option>
                            <option value="ADMIN">ADMIN</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Kata Sandi Baru (Opsional)</label>
                        <input type="password" name="password" class="form-control" placeholder="Biarkan kosong jika tidak diubah">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Manager Email (Opsional)</label>
                    <input type="email" name="manager_email" class="form-control" :value="selectedUser.manager_email" placeholder="director@perusahaan.com">
                </div>

                <div class="form-group">
                    <label class="form-label">Area Penugasan</label>
                    <select name="areas[]" class="form-select" multiple style="height: 90px;">
                        @foreach($areas as $area)
                            <option value="{{ $area->id }}" :selected="selectedUser.areas && selectedUser.areas.includes({{ $area->id }})">{{ $area->area_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label style="display: flex; align-items: center; gap: 8px; font-weight: 600;">
                        <input type="checkbox" name="is_active" value="1" :checked="selectedUser.is_active"> User Aktif
                    </label>
                </div>

                <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 20px;">
                    <button type="button" @click="editModal = false" class="btn btn-secondary">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
