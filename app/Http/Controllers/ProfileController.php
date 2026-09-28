<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    /**
     * Show the profile edit page.
     */
    public function edit()
    {
        $user = Auth::user();
        return view('profile.edit', compact('user'));
    }

    /**
     * Update the user's full name and/or profile picture.
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'full_name'       => 'required|string|max:255',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $user->full_name = $request->input('full_name');

        if ($request->hasFile('profile_picture')) {
            // Store in storage/app/public/avatars — accessible via storage/avatars/...
            $path = $request->file('profile_picture')->store('avatars', 'public');
            $user->profile_picture = $path;
        }

        $user->save();

        return redirect()->route('profile.edit')
            ->with('status', 'Profil berhasil diperbarui.');
    }
}
