<?php

namespace App\Http\Controllers;

use App\Enums\AgendaStatus;
use App\Enums\AgendaType;
use App\Models\Agenda;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AgendaController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'view' => 'nullable|in:kalender,daftar',
            'mode' => 'nullable|in:bulan,minggu',
            'date' => 'nullable|date',
            'range' => 'nullable|in:hari_ini,besok,minggu_ini,minggu_depan,bulan_ini,mendatang,kustom',
            'from' => 'nullable|date',
            'to' => 'nullable|date|after_or_equal:from',
            'type' => ['nullable', Rule::enum(AgendaType::class)],
            'status' => ['nullable', Rule::enum(AgendaStatus::class)],
        ]);

        $user = auth()->user();
        $view = $request->input('view', 'kalender');
        $mode = $request->input('mode', 'bulan');
        $anchorDate = $request->filled('date') ? Carbon::parse($request->date)->startOfDay() : now()->startOfDay();

        $agendaTypes = AgendaType::cases();
        $agendaStatuses = AgendaStatus::cases();

        // ----------------------------------------------------
        // 1. DATA UNTUK TAB KALENDER (BULAN & MINGGU)
        // ----------------------------------------------------
        $todayDate = now()->toDateString();
        $calendarDays = [];
        $periodLabel = '';
        $prevDate = '';
        $nextDate = '';
        $eventsJson = collect();
        $eventsByDate = [];

        // ----------------------------------------------------
        // 2. DATA UNTUK TAB DAFTAR (LIST VIEW BERDASARKAN RENTANG)
        // ----------------------------------------------------
        $range = $request->input('range');
        $from = $request->input('from');
        $to = $request->input('to');
        $agendas = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20);
        $totalAgendasInRange = 0;

        if ($view === 'kalender') {
            if ($mode === 'minggu') {
                $gridStart = $anchorDate->copy()->startOfWeek(Carbon::MONDAY)->startOfDay();
                $gridEnd = $anchorDate->copy()->endOfWeek(Carbon::SUNDAY)->endOfDay();
                $periodLabel = $gridStart->translatedFormat('d M Y') . ' – ' . $gridEnd->translatedFormat('d M Y');
                $prevDate = $anchorDate->copy()->subWeek()->toDateString();
                $nextDate = $anchorDate->copy()->addWeek()->toDateString();

                $currDay = $gridStart->copy();
                while ($currDay->lessThanOrEqualTo($gridEnd)) {
                    $calendarDays[] = [
                        'date' => $currDay->toDateString(),
                        'day_number' => $currDay->day,
                        'is_today' => $currDay->isToday(),
                        'is_current_month' => true,
                        'day_name' => $currDay->translatedFormat('l'),
                        'day_short' => $currDay->translatedFormat('D'),
                        'formatted_date' => $currDay->translatedFormat('d M Y'),
                    ];
                    $currDay->addDay();
                }
            } else {
                // Mode Bulan
                $monthStart = $anchorDate->copy()->startOfMonth();
                $monthEnd = $anchorDate->copy()->endOfMonth();
                $gridStart = $monthStart->copy()->startOfWeek(Carbon::MONDAY)->startOfDay();
                $gridEnd = $monthEnd->copy()->endOfWeek(Carbon::SUNDAY)->endOfDay();

                $periodLabel = $monthStart->translatedFormat('F Y');
                $prevDate = $anchorDate->copy()->subMonth()->startOfMonth()->toDateString();
                $nextDate = $anchorDate->copy()->addMonth()->startOfMonth()->toDateString();

                $currDay = $gridStart->copy();
                while ($currDay->lessThanOrEqualTo($gridEnd)) {
                    $calendarDays[] = [
                        'date' => $currDay->toDateString(),
                        'day_number' => $currDay->day,
                        'is_today' => $currDay->isToday(),
                        'is_current_month' => $currDay->month === $anchorDate->month,
                        'day_name' => $currDay->translatedFormat('l'),
                        'day_short' => $currDay->translatedFormat('D'),
                        'formatted_date' => $currDay->translatedFormat('d M Y'),
                    ];
                    $currDay->addDay();
                }
            }

            // Query agenda untuk kalender (irisan rentang grid)
            $calendarQuery = Agenda::where('user_id', $user->id)
                ->where('start_time', '<=', $gridEnd)
                ->where('end_time', '>=', $gridStart);

            if ($request->filled('type')) {
                $calendarQuery->where('type', $request->type);
            }
            if ($request->filled('status')) {
                $calendarQuery->where('status', $request->status);
            }

            $calendarAgendas = $calendarQuery->orderBy('start_time')->get();

            // Data JSON bersih untuk Alpine
            $eventsJson = $calendarAgendas->map(function ($agenda) {
                return [
                    'id' => $agenda->id,
                    'title' => $agenda->title,
                    'type' => $agenda->type->value,
                    'type_label' => $agenda->type->label(),
                    'css_class' => $agenda->type->cssClass(),
                    'color' => $agenda->type->color(),
                    'text_color' => $agenda->type->textColor(),
                    'start' => $agenda->start_time->format('Y-m-d H:i'),
                    'start_date' => $agenda->start_time->format('Y-m-d'),
                    'start_time' => $agenda->start_time->format('H:i'),
                    'end' => $agenda->end_time->format('Y-m-d H:i'),
                    'end_date' => $agenda->end_time->format('Y-m-d'),
                    'end_time' => $agenda->end_time->format('H:i'),
                    'location' => $agenda->location ?? '',
                    'description' => $agenda->description ?? '',
                    'status' => $agenda->status->value,
                    'status_label' => $agenda->status->label(),
                    'is_cancelled' => $agenda->status === AgendaStatus::DIBATALKAN,
                    'is_completed' => $agenda->status === AgendaStatus::SELESAI,
                ];
            })->values();

            // Pemetaan event per tanggal (event yang melewati tengah malam tampil di setiap hari yang dilalui)
            foreach ($calendarAgendas as $agenda) {
                $dayIterator = $agenda->start_time->copy()->startOfDay();
                $lastDay = $agenda->end_time->copy()->startOfDay();

                $item = [
                    'id' => $agenda->id,
                    'title' => $agenda->title,
                    'type' => $agenda->type->value,
                    'type_label' => $agenda->type->label(),
                    'css_class' => $agenda->type->cssClass(),
                    'color' => $agenda->type->color(),
                    'text_color' => $agenda->type->textColor(),
                    'start' => $agenda->start_time->format('Y-m-d H:i'),
                    'start_date' => $agenda->start_time->format('Y-m-d'),
                    'start_time' => $agenda->start_time->format('H:i'),
                    'end' => $agenda->end_time->format('Y-m-d H:i'),
                    'end_date' => $agenda->end_time->format('Y-m-d'),
                    'end_time' => $agenda->end_time->format('H:i'),
                    'location' => $agenda->location ?? '',
                    'description' => $agenda->description ?? '',
                    'status' => $agenda->status->value,
                    'status_label' => $agenda->status->label(),
                    'is_cancelled' => $agenda->status === AgendaStatus::DIBATALKAN,
                    'is_completed' => $agenda->status === AgendaStatus::SELESAI,
                ];

                while ($dayIterator->lessThanOrEqualTo($lastDay)) {
                    $dKey = $dayIterator->toDateString();
                    $eventsByDate[$dKey][] = $item;
                    $dayIterator->addDay();
                }
            }
        } else {
            // Tab DAFTAR
            if (!$range) {
                if ($request->filled('from') || $request->filled('to')) {
                    $range = 'kustom';
                } else {
                    $range = 'bulan_ini';
                }
            }

            $rangeStart = null;
            $rangeEnd = null;

            switch ($range) {
                case 'hari_ini':
                    $rangeStart = now()->startOfDay();
                    $rangeEnd = now()->endOfDay();
                    break;
                case 'besok':
                    $rangeStart = now()->addDay()->startOfDay();
                    $rangeEnd = now()->addDay()->endOfDay();
                    break;
                case 'minggu_ini':
                    $rangeStart = now()->startOfWeek(Carbon::MONDAY)->startOfDay();
                    $rangeEnd = now()->endOfWeek(Carbon::SUNDAY)->endOfDay();
                    break;
                case 'minggu_depan':
                    $rangeStart = now()->addWeek()->startOfWeek(Carbon::MONDAY)->startOfDay();
                    $rangeEnd = now()->addWeek()->endOfWeek(Carbon::SUNDAY)->endOfDay();
                    break;
                case 'bulan_ini':
                    $rangeStart = now()->startOfMonth()->startOfDay();
                    $rangeEnd = now()->endOfMonth()->endOfDay();
                    break;
                case 'mendatang':
                    $rangeStart = now()->startOfDay();
                    $rangeEnd = null;
                    break;
                case 'kustom':
                    $rangeStart = $request->filled('from') ? Carbon::parse($request->from)->startOfDay() : null;
                    $rangeEnd = $request->filled('to') ? Carbon::parse($request->to)->endOfDay() : null;
                    break;
            }

            $listQuery = Agenda::where('user_id', $user->id);

            if ($rangeStart) {
                $listQuery->where('end_time', '>=', $rangeStart);
            }
            if ($rangeEnd) {
                $listQuery->where('start_time', '<=', $rangeEnd);
            }
            if ($request->filled('type')) {
                $listQuery->where('type', $request->type);
            }
            if ($request->filled('status')) {
                $listQuery->where('status', $request->status);
            }

            $totalAgendasInRange = (clone $listQuery)->count();
            $agendas = $listQuery->orderBy('start_time', 'asc')->paginate(20)->withQueryString();
        }

        return view('agenda.calendar', compact(
            'view',
            'mode',
            'anchorDate',
            'periodLabel',
            'prevDate',
            'nextDate',
            'todayDate',
            'calendarDays',
            'eventsByDate',
            'eventsJson',
            'range',
            'from',
            'to',
            'agendas',
            'totalAgendasInRange',
            'agendaTypes',
            'agendaStatuses'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => ['required', Rule::enum(AgendaType::class)],
            'start_time' => 'required|date',
            'end_time' => 'required|date|after_or_equal:start_time',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:2000',
        ]);

        $validated['user_id'] = auth()->id();
        $validated['status'] = AgendaStatus::TERJADWAL->value;

        Agenda::create($validated);

        $redirectParams = array_filter([
            'view' => $request->input('view'),
            'mode' => $request->input('mode'),
            'date' => $request->input('date'),
        ]);

        return redirect()->route('agenda.index', $redirectParams)
            ->with('success', 'Agenda berhasil ditambahkan.');
    }

    public function update(Request $request, Agenda $agenda)
    {
        if ($agenda->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => ['required', Rule::enum(AgendaType::class)],
            'start_time' => 'required|date',
            'end_time' => 'required|date|after_or_equal:start_time',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:2000',
            'status' => ['required', Rule::enum(AgendaStatus::class)],
        ]);

        // Jika start_time berubah, reset reminder_sent_at agar reminder terkirim ulang
        if ($agenda->start_time && !Carbon::parse($validated['start_time'])->equalTo($agenda->start_time)) {
            $validated['reminder_sent_at'] = null;
        }

        $agenda->update($validated);

        $redirectParams = array_filter([
            'view' => $request->input('view'),
            'mode' => $request->input('mode'),
            'date' => $request->input('date'),
        ]);

        return redirect()->route('agenda.index', $redirectParams)
            ->with('success', 'Agenda berhasil diperbarui.');
    }

    public function destroy(Request $request, Agenda $agenda)
    {
        if ($agenda->user_id !== auth()->id()) {
            abort(403);
        }

        $agenda->delete();

        $redirectParams = array_filter([
            'view' => $request->input('view'),
            'mode' => $request->input('mode'),
            'date' => $request->input('date'),
        ]);

        return redirect()->route('agenda.index', $redirectParams)
            ->with('success', 'Agenda berhasil dihapus.');
    }
}
