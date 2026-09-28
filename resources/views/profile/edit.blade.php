@extends('layouts.app')

@section('title', 'Edit Profil')
@section('page_title', 'Edit Profil')
@section('page_subtitle', 'Perbarui nama dan foto profil Anda')

@push('styles')
<style>
.profile-card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    padding: 32px;
    max-width: 560px;
    margin: 0 auto;
    box-shadow: var(--shadow-md);
}

.profile-avatar-wrapper {
    display: flex;
    flex-direction: column;
    align-items: center;
    margin-bottom: 32px;
    gap: 12px;
}

.profile-avatar-preview {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid var(--primary);
    box-shadow: 0 0 0 4px rgba(79,70,229,0.12);
}

.profile-avatar-initials {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary), var(--primary-dark, #4338ca));
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 32px;
    font-weight: 700;
    color: #fff;
    border: 3px solid var(--primary);
    box-shadow: 0 0 0 4px rgba(79,70,229,0.12);
    letter-spacing: 1px;
}

.profile-upload-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: var(--surface);
    border: 1px dashed var(--border);
    color: var(--text-muted);
    padding: 7px 16px;
    border-radius: var(--radius-md);
    font-size: 13px;
    cursor: pointer;
    transition: border-color 0.2s, color 0.2s;
}
.profile-upload-btn:hover {
    border-color: var(--primary);
    color: var(--primary);
}

.form-group {
    margin-bottom: 20px;
}

.form-label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: var(--text-muted);
    margin-bottom: 6px;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.form-input {
    width: 100%;
    background: var(--surface);
    border: 1px solid var(--border);
    color: var(--text);
    padding: 10px 14px;
    border-radius: var(--radius-md);
    font-size: 14px;
    transition: border-color 0.2s, box-shadow 0.2s;
    outline: none;
    box-sizing: border-box;
}
.form-input:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(79,70,229,0.12);
}

.form-input[readonly] {
    opacity: 0.55;
    cursor: not-allowed;
}

.alert-success {
    background: rgba(16,185,129,0.1);
    border: 1px solid rgba(16,185,129,0.3);
    color: #059669;
    padding: 12px 16px;
    border-radius: var(--radius-md);
    font-size: 14px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.alert-danger {
    background: rgba(239,68,68,0.1);
    border: 1px solid rgba(239,68,68,0.3);
    color: var(--danger);
    padding: 12px 16px;
    border-radius: var(--radius-md);
    font-size: 14px;
    margin-bottom: 20px;
}
</style>
@endpush

@section('content')
<div class="profile-card">

    {{-- Success Alert --}}
    @if (session('status'))
        <div class="alert-success">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
            </svg>
            {{ session('status') }}
        </div>
    @endif

    {{-- Validation Errors --}}
    @if ($errors->any())
        <div class="alert-danger">
            <ul style="margin: 0; padding-left: 16px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data"
          x-data="profileForm()">

        @csrf

        {{-- Avatar Preview --}}
        <div class="profile-avatar-wrapper">
            @if ($user->profile_picture)
                <img id="avatar-img"
                     src="{{ asset('storage/' . $user->profile_picture) }}"
                     alt="Foto Profil"
                     class="profile-avatar-preview"
                     x-ref="avatarImg">
            @else
                <div class="profile-avatar-initials" id="avatar-initials" x-ref="avatarInitials">
                    {{ strtoupper(substr($user->full_name ?? 'U', 0, 2)) }}
                </div>
                <img id="avatar-img"
                     src=""
                     alt="Foto Profil"
                     class="profile-avatar-preview"
                     style="display:none;"
                     x-ref="avatarImg">
            @endif

            <label for="profile_picture" class="profile-upload-btn">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                    <polyline points="17 8 12 3 7 8"/>
                    <line x1="12" y1="3" x2="12" y2="15"/>
                </svg>
                Ganti Foto
            </label>

            <input type="file"
                   name="profile_picture"
                   id="profile_picture"
                   accept="image/*"
                   style="display:none;"
                   @change="previewImage($event)">

            <span style="font-size:11px; color:var(--text-muted);">JPG, PNG, GIF, WEBP — Maks. 2 MB</span>
        </div>

        {{-- Full Name --}}
        <div class="form-group">
            <label class="form-label" for="full_name">Nama Lengkap</label>
            <input type="text"
                   name="full_name"
                   id="full_name"
                   class="form-input"
                   value="{{ old('full_name', $user->full_name) }}"
                   required
                   placeholder="Masukkan nama lengkap Anda">
        </div>

        {{-- Email (read-only) --}}
        <div class="form-group">
            <label class="form-label">Email</label>
            <input type="email"
                   class="form-input"
                   value="{{ $user->email }}"
                   readonly>
        </div>

        {{-- Role (read-only) --}}
        <div class="form-group">
            <label class="form-label">Jabatan / Role</label>
            <input type="text"
                   class="form-input"
                   value="{{ $user->role?->value ?? $user->role }}"
                   readonly>
        </div>

        <div style="display:flex; justify-content:flex-end; margin-top:8px;">
            <button type="submit" class="btn btn-primary">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                    <polyline points="17 21 17 13 7 13 7 21"/>
                    <polyline points="7 3 7 8 15 8"/>
                </svg>
                Simpan Perubahan
            </button>
        </div>

    </form>
</div>

<script>
function profileForm() {
    return {
        previewImage(event) {
            const file = event.target.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = (e) => {
                const img = document.getElementById('avatar-img');
                const initials = document.getElementById('avatar-initials');

                img.src = e.target.result;
                img.style.display = 'block';
                if (initials) initials.style.display = 'none';
            };
            reader.readAsDataURL(file);
        }
    }
}
</script>
@endsection
