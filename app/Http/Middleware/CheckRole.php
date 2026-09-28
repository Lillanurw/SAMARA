<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user || !$user->is_active) {
            auth()->logout();
            return redirect()->route('login')->withErrors([
                'email' => 'Akun belum terdaftar atau tidak aktif. Hubungi Admin SAMARA.',
            ]);
        }

        $userRole = strtoupper(trim($user->role->value ?? (string)$user->role));

        $allowedRoles = array_map(fn($r) => strtoupper(trim($r)), $roles);

        if (!in_array($userRole, $allowedRoles)) {
            abort(403, 'Anda tidak memiliki izin untuk membuka halaman ini.');
        }

        return $next($request);
    }
}
