<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->input('search');
        $action = $request->input('action');
        $entityType = $request->input('entity_type');

        $logs = AuditLog::with('actor')
            ->when($search, function ($q) use ($search) {
                $q->where('action', 'like', "%{$search}%")
                  ->orWhere('entity_type', 'like', "%{$search}%")
                  ->orWhereHas('actor', fn($a) => $a->where('full_name', 'like', "%{$search}%"));
            })
            ->when($action, fn($q) => $q->where('action', $action))
            ->when($entityType, fn($q) => $q->where('entity_type', $entityType))
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.audit_logs.index', compact('logs', 'search', 'action', 'entityType'));
    }
}
