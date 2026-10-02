@extends('layouts.app')

@section('title', 'Kelola Customer')
@section('page_title', 'Master Data - Customers')
@section('page_subtitle', 'Pengelolaan Data Customer, Area, Segment, dan Owner PIC')

@section('content')
<div x-data="{ addModal: false }">
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 16px;">
            <form method="GET" action="{{ route('customers.index') }}" style="display: flex; gap: 10px; flex: 1; flex-wrap: wrap;">
                <input type="text" name="search" class="form-control" placeholder="Cari kode/nama customer..." value="{{ $search }}" style="max-width: 250px;">
                
                
                <button type="submit" class="btn btn-secondary">Filter</button>
            </form>

            <button type="button" @click="addModal = true" class="btn btn-primary">➕ Tambah Customer Baru</button>
        </div>

        <div class="table-responsive" style="max-height: calc(100vh - 220px); overflow-y: auto; border: 1px solid var(--border); border-radius: var(--radius-sm);">
            <table class="samara-table" style="border: 1px solid var(--border);">
                <th style="border: 1px solid var(--border);"ead>
                    <tr>
                        <th style="border: 1px solid var(--border); text-align: center; vertical-align: middle; position: sticky; top: 0; z-index: 20; background-color: var(--surface-soft); box-shadow: 0 2px 2px -1px rgba(0,0,0,0.1);">Kode & Customer</th>
                        <th style="border: 1px solid var(--border); text-align: center; vertical-align: middle; position: sticky; top: 0; z-index: 20; background-color: var(--surface-soft); box-shadow: 0 2px 2px -1px rgba(0,0,0,0.1);">Instansi & Satuan</th>
                        
                        <th style="border: 1px solid var(--border); text-align: center; vertical-align: middle; position: sticky; top: 0; z-index: 20; background-color: var(--surface-soft); box-shadow: 0 2px 2px -1px rgba(0,0,0,0.1);">Kota / Provinsi</th>
                        <th style="border: 1px solid var(--border); text-align: center; vertical-align: middle; position: sticky; top: 0; z-index: 20; background-color: var(--surface-soft); box-shadow: 0 2px 2px -1px rgba(0,0,0,0.1);">PIC (Owner)</th>
                        <th style="border: 1px solid var(--border); text-align: center; vertical-align: middle; position: sticky; top: 0; z-index: 20; background-color: var(--surface-soft); box-shadow: 0 2px 2px -1px rgba(0,0,0,0.1);">Tier</th>
                        <th style="border: 1px solid var(--border); text-align: center; vertical-align: middle; position: sticky; top: 0; z-index: 20; background-color: var(--surface-soft); box-shadow: 0 2px 2px -1px rgba(0,0,0,0.1);">Status</th>
                        <th style="border: 1px solid var(--border); text-align: center; vertical-align: middle; position: sticky; top: 0; z-index: 20; background-color: var(--surface-soft); box-shadow: 0 2px 2px -1px rgba(0,0,0,0.1);">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($customers as $c)
                        <tr>
                            <td style="border: 1px solid var(--border);">
                                <div style="font-weight: 700; color: var(--navy);">{{ $c->customer_name }}</div>
                                <div style="font-size: 11px; color: var(--muted);">{{ $c->customer_code }}</div>
                            </td>
                            
                            <td style="border: 1px solid var(--border);">{{ $c->city }}, {{ $c->province }}</td>
                            <td style="border: 1px solid var(--border);">{{ $c->owner->full_name ?? '-' }}</td>
                            <td style="border: 1px solid var(--border);"><span class="badge badge-warning">Tier {{ $c->priority_tier->value ?? $c->priority_tier }}</span></td>
                            <td style="border: 1px solid var(--border);">
                                @if($c->is_active)
                                    <span class="badge badge-success">Aktif</span>
                                @else
                                    <span class="badge badge-danger">Nonaktif</span>
                                @endif
                            </td>
                            <td style="border: 1px solid var(--border); vertical-align: middle;">
                                <div style="display: flex; gap: 8px; justify-content: center; align-items: center;">
                                <div x-data="{ editModal: false }">
                                    <button type="button" @click="editModal = true" class="btn btn-sm btn-secondary">Edit</button>

                                    <!-- Edit Modal -->
                                    <div x-show="editModal" style="position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 2000; display: flex; align-items: center; justify-content: center; padding: 20px; text-align: left;" x-cloak>
                                        <div style="background: white; width: 100%; max-width: 600px; border-radius: var(--radius); padding: 24px; max-height: 90vh; overflow-y: auto; text-align: left;" @click.away="editModal = false">
                                            <h3 style="font-weight: 700; margin-bottom: 16px; color: var(--navy);">Edit Customer</h3>
                                            <form action="{{ route('customers.update', $c->id) }}" method="POST" x-data="customerForm('{{ addslashes($c->instansi) }}', '{{ addslashes($c->satuan) }}')">
                                                @csrf
                                                @method('PUT')
                                                <div class="grid grid-2">
                                                    <div class="form-group">
                                                        <label class="form-label">Kode Customer *</label>
                                                        <input type="text" class="form-control" value="{{ $c->customer_code }}" disabled style="background-color: var(--surface-soft);">
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="form-label">Nama Customer *</label>
                                                        <input type="text" name="customer_name" class="form-control" required value="{{ $c->customer_name }}">
                                                    </div>
                                                </div>

                                                <div class="grid grid-2">
                                                    <div class="form-group">
                                                        <label class="form-label">Instansi *</label>
                                                        <select name="instansi" class="form-select" x-model="selectedInstansi" @change="updateSatuans()" required>
                                                            <option value="">Pilih Instansi</option>
                                                            <template x-for="inst in instansiList" :key="inst.name">
                                                                <option :value="inst.name" x-text="inst.name"></option>
                                                            </template>
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="form-label">Satuan *</label>
                                                        <select name="satuan" class="form-select" x-model="selectedSatuan" required>
                                                            <option value="">Pilih Satuan</option>
                                                            <template x-for="sat in availableSatuans" :key="sat">
                                                                <option :value="sat" x-text="sat"></option>
                                                            </template>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="grid grid-2">
                                                    
                                                    
                                                </div>

                                                <div class="grid grid-2">
                                                    <div class="form-group">
                                                        <label class="form-label">PIC (Owner) *</label>
                                                        <select name="owner_id" class="form-select" required>
                                                            @foreach($users as $u)
                                                                <option value="{{ $u->id }}" {{ $c->owner_id == $u->id ? 'selected' : '' }}>{{ $u->full_name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="form-label">Priority Tier *</label>
                                                        <select name="priority_tier" class="form-select" required>
                                                            <option value="A" {{ ($c->priority_tier->value ?? $c->priority_tier) == 'A' ? 'selected' : '' }}>Tier A (Prioritas Tinggi)</option>
                                                            <option value="B" {{ ($c->priority_tier->value ?? $c->priority_tier) == 'B' ? 'selected' : '' }}>Tier B (Sedang)</option>
                                                            <option value="C" {{ ($c->priority_tier->value ?? $c->priority_tier) == 'C' ? 'selected' : '' }}>Tier C (Rendah / Standar)</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <label class="form-label">Alamat Lengkap *</label>
                                                    <input type="text" name="address" class="form-control" required value="{{ $c->address }}">
                                                </div>

                                                <div class="grid grid-2">
                                                    <div class="form-group">
                                                        <label class="form-label">Kota *</label>
                                                        <input type="text" name="city" class="form-control" required value="{{ $c->city }}">
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="form-label">Provinsi *</label>
                                                        <input type="text" name="province" class="form-control" required value="{{ $c->province }}">
                                                    </div>
                                                </div>

                                                <h4 style="margin-top: 16px; margin-bottom: 12px; font-weight: 600;">Pihak yang Ditemui</h4>
                                                <div class="grid grid-2">
                                                    <div class="form-group">
                                                        <label class="form-label">Nama Kontak *</label>
                                                        <input type="text" name="contact_name" class="form-control" required placeholder="Kapten Suwandi" value="{{ $c->contact_name }}">
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="form-label">Pangkat *</label>
                                                        <input type="text" name="contact_pangkat" class="form-control" required placeholder="Kapten" value="{{ $c->contact_pangkat }}">
                                                    </div>
                                                </div>
                                                <div class="grid grid-2">
                                                    <div class="form-group">
                                                        <label class="form-label">Jabatan *</label>
                                                        <input type="text" name="contact_position" class="form-control" required placeholder="Kasi Logistik" value="{{ $c->contact_position }}">
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="form-label">Letting/Angkatan</label>
                                                        <input type="text" name="contact_letting" class="form-control" placeholder="Akmil 1999" value="{{ $c->contact_letting }}">
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label class="form-label">No. HP / Telepon Kontak *</label>
                                                    <input type="text" name="contact_phone" class="form-control" required placeholder="081299887766" value="{{ $c->contact_phone }}">
                                                </div>

                                                <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 20px;">
                                                    <button type="button" @click="editModal = false" class="btn btn-secondary">Batal</button>
                                                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <form action="{{ route('customers.toggle', $c->id) }}" method="POST" style="display: inline;">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm {{ $c->is_active ? 'btn-danger' : 'btn-success' }}">
                                        {{ $c->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
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
            {{ $customers->links() }}
        </div>
    </div>

    <!-- Modal Tambah Customer -->
    <div x-show="addModal" style="position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 2000; display: flex; align-items: center; justify-content: center; padding: 20px;" x-cloak>
        <div style="background: white; width: 100%; max-width: 600px; border-radius: var(--radius); padding: 24px; max-height: 90vh; overflow-y: auto;" @click.away="addModal = false">
            <h3 style="font-weight: 700; margin-bottom: 16px; color: var(--navy);">Tambah Customer Baru</h3>
            <form action="{{ route('customers.store') }}" method="POST" x-data="customerForm()">
                @csrf
                <div class="grid grid-2">
                    <div class="form-group">
                        <label class="form-label">Kode Customer *</label>
                        <input type="text" class="form-control" placeholder="Dibuat Otomatis" disabled style="background-color: var(--surface-soft);">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Nama Customer *</label>
                        <input type="text" name="customer_name" class="form-control" required placeholder="Koperasi Primkopad Mabes">
                    </div>
                </div>

                <div class="grid grid-2">
                    <div class="form-group">
                        <label class="form-label">Instansi *</label>
                        <select name="instansi" class="form-select" x-model="selectedInstansi" @change="updateSatuans()" required>
                            <option value="">Pilih Instansi</option>
                            <template x-for="inst in instansiList" :key="inst.name">
                                <option :value="inst.name" x-text="inst.name"></option>
                            </template>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Satuan *</label>
                        <select name="satuan" class="form-select" x-model="selectedSatuan" required>
                            <option value="">Pilih Satuan</option>
                            <template x-for="sat in availableSatuans" :key="sat">
                                <option :value="sat" x-text="sat"></option>
                            </template>
                        </select>
                    </div>
                </div>

                <div class="grid grid-2">
                    
                    
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

                <h4 style="margin-top: 16px; margin-bottom: 12px; font-weight: 600;">Pihak yang Ditemui</h4>
                <div class="grid grid-2">
                    <div class="form-group">
                        <label class="form-label">Nama Kontak *</label>
                        <input type="text" name="contact_name" class="form-control" required placeholder="Kapten Suwandi">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Pangkat *</label>
                        <input type="text" name="contact_pangkat" class="form-control" required placeholder="Kapten">
                    </div>
                </div>
                <div class="grid grid-2">
                    <div class="form-group">
                        <label class="form-label">Jabatan *</label>
                        <input type="text" name="contact_position" class="form-control" required placeholder="Kasi Logistik">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Letting/Angkatan</label>
                        <input type="text" name="contact_letting" class="form-control" placeholder="Akmil 1999">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">No. HP / Telepon Kontak *</label>
                    <input type="text" name="contact_phone" class="form-control" required placeholder="081299887766">
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

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('customerForm', (initialInstansi = '', initialSatuan = '') => ({
            instansiList: [
                { name: 'Mabes TNI', satuans: ['Akademi TNI', 'Paspamres', 'Koopssus', 'PMPP', 'Balog TNI', 'Pusada TNI', 'Pusjianstra Litbang TNI'] },
                { name: 'Mabes AD', satuans: ['Pusbekangad', 'Pusziad', 'Puspalad', 'Pussenif', 'Pussenkav', 'Puspenerbad', 'Kodam Jaya', 'Kostrad', 'Kopassus', 'Dislaikad', 'Dislitbangad'] },
                { name: 'Mabes AL', satuans: ['Kormar', 'Denjaka', 'Denipam', 'Disbekal', 'Dislaikmatal', 'Koarmada RI', 'Kopaska', 'Dislitbangal', 'Puspenerbal'] },
                { name: 'Mabes AU', satuans: ['Disaero', 'Skadron 8', 'Kopasgat', 'Sat 90 Bravo', 'Lanud ATS', 'Koharmatau', 'Dislaiklambangjaau', 'Dislitbangau', 'Pusbekmatau', 'Disbangops'] },
                { name: 'Kemhan', satuans: ['Baloghan', 'Pusalpalhan', 'Bidmatra Darat', 'Bidmatra Laut', 'Bidmatra Udara', 'TKDN', 'Bagmalur', 'Proglap', 'Pothan', 'Bacadnas', 'Puslaik', 'Puskod', 'Puslitbang', 'Renhan', 'Strahan'] },
                { name: 'POLRI', satuans: ['Brimob', 'Wanterror', 'Polairud'] },
                { name: 'Instansi Lain', satuans: ['BIN', 'Lemhannas', 'Basarnas', 'Kemlu', 'Bea Cukai', 'BNN', 'BNPT'] }
            ],
            selectedInstansi: initialInstansi,
            selectedSatuan: initialSatuan,
            availableSatuans: [],
            init() {
                this.updateSatuans();
            },
            updateSatuans() {
                let found = this.instansiList.find(i => i.name === this.selectedInstansi);
                this.availableSatuans = found ? found.satuans : [];
                if (!this.availableSatuans.includes(this.selectedSatuan)) {
                    this.selectedSatuan = '';
                }
            }
        }));
    });
</script>
@endpush















