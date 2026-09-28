<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Segment;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SegmentController extends Controller
{
    public function index(): View
    {
        $segments = Segment::withCount('customers')->paginate(15);
        return view('admin.segments.index', compact('segments'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'segment_code' => ['required', 'string', 'max:50', 'unique:segments,segment_code'],
            'segment_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = !empty($validated['is_active']);
        $segment = Segment::create($validated);

        AuditLogger::log('CREATE_SEGMENT', 'SEGMENT', $segment->id, null, $segment->toArray());

        return back()->with('success', 'Segment ' . $segment->segment_name . ' berhasil ditambahkan!');
    }

    public function update(Request $request, Segment $segment): RedirectResponse
    {
        $validated = $request->validate([
            'segment_code' => ['required', 'string', 'max:50', Rule::unique('segments')->ignore($segment->id)],
            'segment_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $before = $segment->toArray();
        $validated['is_active'] = !empty($validated['is_active']);
        $segment->update($validated);

        AuditLogger::log('UPDATE_SEGMENT', 'SEGMENT', $segment->id, $before, $segment->toArray());

        return back()->with('success', 'Data segment ' . $segment->segment_name . ' berhasil diperbarui.');
    }
}
