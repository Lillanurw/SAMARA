<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PriorityTier;
use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Customer;
use App\Models\Segment;
use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->input('search');
        $areaId = $request->input('area_id');
        $segmentId = $request->input('segment_id');
        $tier = $request->input('priority_tier');

        $customers = Customer::with(['area', 'segment', 'owner'])
            ->when($search, function ($q) use ($search) {
                $q->where('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_code', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%");
            })
            ->when($areaId, fn($q) => $q->where('area_id', $areaId))
            ->when($segmentId, fn($q) => $q->where('segment_id', $segmentId))
            ->when($tier, fn($q) => $q->where('priority_tier', $tier))
            ->orderBy('customer_name')
            ->paginate(15);

        $areas = Area::where('is_active', true)->get();
        $segments = Segment::where('is_active', true)->get();
        $users = User::where('is_active', true)->orderBy('full_name')->get();

        return view('admin.customers.index', compact('customers', 'search', 'areaId', 'segmentId', 'tier', 'areas', 'segments', 'users'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_code' => ['required', 'string', 'max:50', 'unique:customers,customer_code'],
            'customer_name' => ['required', 'string', 'max:255'],
            'area_id' => ['required', 'exists:areas,id'],
            'segment_id' => ['required', 'exists:segments,id'],
            'owner_id' => ['required', 'exists:users,id'],
            'address' => ['required', 'string', 'max:500'],
            'city' => ['required', 'string', 'max:100'],
            'province' => ['required', 'string', 'max:100'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'contact_position' => ['nullable', 'string', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'priority_tier' => ['required', 'string'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'customer_code.unique' => 'Kode customer ini sudah digunakan.',
            'customer_name.required' => 'Nama customer wajib diisi.',
            'area_id.required' => 'Area wajib dipilih.',
            'segment_id.required' => 'Segment wajib dipilih.',
        ]);

        $validated['is_active'] = !empty($validated['is_active']);
        $validated['created_by'] = Auth::id();

        $customer = Customer::create($validated);

        AuditLogger::log('CREATE_CUSTOMER', 'CUSTOMER', $customer->id, null, $customer->toArray());

        return back()->with('success', 'Customer ' . $customer->customer_name . ' berhasil ditambahkan!');
    }

    public function update(Request $request, Customer $customer): RedirectResponse
    {
        $validated = $request->validate([
            'customer_code' => ['required', 'string', 'max:50', Rule::unique('customers')->ignore($customer->id)],
            'customer_name' => ['required', 'string', 'max:255'],
            'area_id' => ['required', 'exists:areas,id'],
            'segment_id' => ['required', 'exists:segments,id'],
            'owner_id' => ['required', 'exists:users,id'],
            'address' => ['required', 'string', 'max:500'],
            'city' => ['required', 'string', 'max:100'],
            'province' => ['required', 'string', 'max:100'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'contact_position' => ['nullable', 'string', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'priority_tier' => ['required', 'string'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $before = $customer->toArray();

        $validated['is_active'] = !empty($validated['is_active']);
        $validated['updated_by'] = Auth::id();

        $customer->update($validated);

        AuditLogger::log('UPDATE_CUSTOMER', 'CUSTOMER', $customer->id, $before, $customer->toArray());

        return back()->with('success', 'Data customer ' . $customer->customer_name . ' berhasil diperbarui.');
    }

    public function toggleActive(Customer $customer): RedirectResponse
    {
        $before = $customer->toArray();
        $customer->is_active = !$customer->is_active;
        $customer->updated_by = Auth::id();
        $customer->save();

        AuditLogger::log('TOGGLE_CUSTOMER_ACTIVE', 'CUSTOMER', $customer->id, $before, $customer->toArray());

        $statusText = $customer->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', 'Customer ' . $customer->customer_name . ' berhasil ' . $statusText . '.');
    }
}
