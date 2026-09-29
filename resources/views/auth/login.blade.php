@extends('layouts.guest')

@section('content')
<div>
    <h2 style="font-size: 18px; font-weight: 700; color: var(--text); margin-bottom: 6px;">Selamat Datang</h2>
    <p style="font-size: 13px; color: var(--text-secondary); margin-bottom: 24px;">
        Masuk untuk melanjutkan ke aplikasi SAMARA.
    </p>

    {{-- Error messages --}}
    @if($errors->any())
        <div class="alert alert-danger" style="margin-bottom: 20px;">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <div>
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        </div>
    @endif



    {{-- Email & Password Form --}}
    <form action="{{ route('login') }}" method="POST" id="login-form">
        @csrf

        <div class="form-group">
            <label class="form-label" for="email">Alamat Email</label>
            <input type="email"
                   name="email"
                   id="email"
                   class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                   value="{{ old('email') }}"
                   required
                   autofocus
                   autocomplete="email"
                   placeholder="nama@perusahaan.com">
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label class="form-label" for="password">Kata Sandi</label>
            <input type="password"
                   name="password"
                   id="password"
                   class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}"
                   required
                   autocomplete="current-password"
                   placeholder="••••••••">
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 22px;">
            <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: var(--text-secondary); cursor: pointer; user-select: none;">
                <input type="checkbox" name="remember" id="remember" style="accent-color: var(--accent); width: 15px; height: 15px;">
                Ingat saya
            </label>
        </div>

        <button type="submit"
                id="btn-login-submit"
                class="btn btn-primary"
                style="width: 100%; font-weight: 700; font-size: 14px;">
            Masuk ke Aplikasi
        </button>
    </form>
</div>
@endsection
