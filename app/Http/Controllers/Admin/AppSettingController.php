<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AppSettingController extends Controller
{
    public function index(): View
    {
        $settings = AppSetting::all();
        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $settings = $request->input('settings', []);

        foreach ($settings as $id => $data) {
            $setting = AppSetting::find($id);
            if ($setting) {
                $before = $setting->toArray();
                $setting->setting_value = $data['value'] ?? null;
                $setting->description = $data['description'] ?? $setting->description;
                $setting->save();

                AuditLogger::log('UPDATE_SETTING', 'APP_SETTING', $setting->id, $before, $setting->toArray());
            }
        }

        return back()->with('success', 'Konfigurasi aplikasi berhasil diperbarui.');
    }
}
