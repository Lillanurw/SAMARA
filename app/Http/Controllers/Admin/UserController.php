<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->input('search');
        $role = $request->input('role');

        $users = User::with('areas')
            ->when($search, function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            })
            ->when($role, fn($q) => $q->where('role', $role))
            ->orderBy('full_name')
            ->paginate(15);

        $areas = Area::where('is_active', true)->get();

        return view('admin.users.index', compact('users', 'search', 'role', 'areas'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'full_name' => ['required', 'string', 'max:255'],
            'role' => ['required', 'string'],
            'password' => ['required', 'string', 'min:6'],
            'manager_email' => ['nullable', 'email', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
            'areas' => ['nullable', 'array'],
            'areas.*' => ['exists:areas,id'],
        ], [
            'email.unique' => 'Email ini sudah terdaftar dalam sistem.',
        ]);

        $user = User::create([
            'email' => strtolower(trim($validated['email'])),
            'full_name' => $validated['full_name'],
            'role' => $validated['role'],
            'password' => Hash::make($validated['password']),
            'manager_email' => $validated['manager_email'] ?? null,
            'is_active' => !empty($validated['is_active']),
        ]);

        if (!empty($validated['areas'])) {
            $user->areas()->sync($validated['areas']);
        }

        AuditLogger::log('CREATE_USER', 'USER', $user->id, null, $user->toArray());

        return back()->with('success', 'User ' . $user->full_name . ' berhasil ditambahkan.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'full_name' => ['required', 'string', 'max:255'],
            'role' => ['required', 'string'],
            'password' => ['nullable', 'string', 'min:6'],
            'manager_email' => ['nullable', 'email', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
            'areas' => ['nullable', 'array'],
            'areas.*' => ['exists:areas,id'],
        ]);

        $before = $user->toArray();

        $user->email = strtolower(trim($validated['email']));
        $user->full_name = $validated['full_name'];
        $user->role = $validated['role'];
        $user->manager_email = $validated['manager_email'] ?? null;
        $user->is_active = !empty($validated['is_active']);

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        if (isset($validated['areas'])) {
            $user->areas()->sync($validated['areas']);
        }

        AuditLogger::log('UPDATE_USER', 'USER', $user->id, $before, $user->toArray());

        return back()->with('success', 'Data user ' . $user->full_name . ' berhasil diperbarui.');
    }

    public function toggleActive(User $user): RedirectResponse
    {
        $before = $user->toArray();
        $user->is_active = !$user->is_active;
        $user->save();

        AuditLogger::log('TOGGLE_USER_ACTIVE', 'USER', $user->id, $before, $user->toArray());

        $statusText = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', 'User ' . $user->full_name . ' berhasil ' . $statusText . '.');
    }
}
