<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('home');
        }

        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $email = strtolower(trim($credentials['email']));

        $user = User::where('email', $email)->first();

        if (!$user || !$user->is_active) {
            AuditLogger::log(
                action: 'LOGIN_FAILED',
                entityType: 'USER',
                entityId: $user?->id,
                metadata: ['reason' => 'User not found or inactive', 'email' => $email]
            );

            return back()->withErrors([
                'email' => 'Akun belum terdaftar atau tidak aktif. Hubungi Admin SAMARA.',
            ])->onlyInput('email');
        }

        if (Auth::attempt(['email' => $email, 'password' => $credentials['password']], $request->boolean('remember'))) {
            $request->session()->regenerate();
            $user->update(['last_login_at' => now()]);

            AuditLogger::log(
                action: 'LOGIN_SUCCESS',
                entityType: 'USER',
                entityId: $user->id,
                metadata: ['method' => 'PASSWORD']
            );

            return redirect()->intended(route('home'));
        }

        AuditLogger::log(
            action: 'LOGIN_FAILED',
            entityType: 'USER',
            entityId: $user->id,
            metadata: ['reason' => 'Invalid password', 'email' => $email]
        );

        return back()->withErrors([
            'email' => 'Kombinasi email dan kata sandi tidak valid.',
        ])->onlyInput('email');
    }

    public function redirectToGoogle(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback(): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Throwable $e) {
            return redirect()->route('login')->withErrors([
                'email' => 'Gagal menghubungkan dengan akun Google. Silakan coba lagi.',
            ]);
        }

        $email = strtolower(trim($googleUser->getEmail()));

        $user = User::where('email', $email)->first();

        if (!$user || !$user->is_active) {
            AuditLogger::log(
                action: 'LOGIN_FAILED_GOOGLE',
                entityType: 'USER',
                entityId: $user?->id,
                metadata: ['email' => $email, 'google_id' => $googleUser->getId()]
            );

            return redirect()->route('login')->withErrors([
                'email' => 'Akun belum terdaftar atau tidak aktif. Hubungi Admin SAMARA.',
            ]);
        }

        if ($googleUser->getAvatar() && !$user->avatar_url) {
            $user->avatar_url = $googleUser->getAvatar();
        }
        $user->last_login_at = now();
        $user->save();

        Auth::login($user, true);
        request()->session()->regenerate();

        AuditLogger::log(
            action: 'LOGIN_SUCCESS_GOOGLE',
            entityType: 'USER',
            entityId: $user->id,
            metadata: ['google_id' => $googleUser->getId()]
        );

        return redirect()->intended(route('home'));
    }

    public function logout(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if ($user) {
            AuditLogger::log(
                action: 'LOGOUT',
                entityType: 'USER',
                entityId: $user->id
            );
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
