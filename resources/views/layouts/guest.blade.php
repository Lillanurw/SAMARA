<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="SAMARA — Login Aplikasi Sales and Marketing Activity Reporting and Analytics">
    <title>Masuk — SAMARA</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            background: var(--bg);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 24px;
        }
        .login-wrapper {
            width: 100%;
            max-width: 420px;
        }
        .login-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-hover);
            padding: 40px 36px;
        }
        @media (max-width: 480px) {
            .login-card { padding: 28px 20px; }
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        {{-- Brand Header --}}
        <div style="text-align: center; margin-bottom: 28px;">
            <div style="background: #ffffff; padding: 12px 18px; border-radius: var(--radius-lg); box-shadow: 0 4px 16px rgba(0,0,0,0.08); display: inline-block;">
                <img src="{{ asset('images/logo.png') }}" alt="SAMARA - Sales and Marketing Activity Reporting and Analytics" style="max-width: 280px; width: 100%; height: auto; display: block; margin: 0 auto;">
            </div>
        </div>

        <div class="login-card">
            @yield('content')
        </div>

        <p style="text-align: center; font-size: 11px; color: var(--text-muted); margin-top: 20px;">
            Akses terbatas untuk pengguna terdaftar. Hubungi Administrator jika mengalami kendala.
        </p>
    </div>
</body>
</html>
