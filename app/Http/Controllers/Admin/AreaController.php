<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AreaController extends Controller
{
    public function index(): View
    {
        $areas = Area::withCount(['customers', 'users'])->paginate(15);
        return view('admin.areas.index', compact('areas'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'area_code' => ['required', 'string', 'max:50', 'unique:areas,area_code'],
            'area_name' => ['required', 'string', 'max:255'],
            'region' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = !empty($validated['is_active']);
        $area = Area::create($validated);

        AuditLogger::log('CREATE_AREA', 'AREA', $area->id, null, $area->toArray());

        return back()->with('success', 'Area ' . $area->area_name . ' berhasil ditambahkan!');
    }

    public function update(Request $request, Area $area): RedirectResponse
    {
        $validated = $request->validate([
            'area_code' => ['required', 'string', 'max:50', Rule::unique('areas')->ignore($area->id)],
            'area_name' => ['required', 'string', 'max:255'],
            'region' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $before = $area->toArray();
        $validated['is_active'] = !empty($validated['is_active']);
        $area->update($validated);

        AuditLogger::log('UPDATE_AREA', 'AREA', $area->id, $before, $area->toArray());

        return back()->with('success', 'Data area ' . $area->area_name . ' berhasil diperbarui.');
    }
}
