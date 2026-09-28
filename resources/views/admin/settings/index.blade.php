@extends('layouts.app')

@section('title', 'Konfigurasi Aplikasi')
@section('page_title', 'Master Data - Settings')
@section('page_subtitle', 'Pengaturan Konfigurasi Parameter Aplikasi SAMARA')

@section('content')
<div class="card">
    <form action="{{ route('admin.settings.update') }}" method="POST">
        @csrf
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Key Setting</th>
                        <th>Value / Pengaturan</th>
                        <th>Deskripsi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($settings as $st)
                        <tr>
                            <td style="font-weight: 700; color: var(--navy);">{{ $st->setting_key }}</td>
                            <td>
                                <input type="text" name="settings[{{ $st->id }}][value]" class="form-control" value="{{ $st->setting_value }}">
                            </td>
                            <td>
                                <input type="text" name="settings[{{ $st->id }}][description]" class="form-control" value="{{ $st->description }}">
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div style="display: flex; justify-content: flex-end; margin-top: 20px;">
            <button type="submit" class="btn btn-primary">Simpan Seluruh Pengaturan</button>
        </div>
    </form>
</div>
@endsection
