<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DirutDashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        $todayAgendas = \App\Models\Agenda::where('user_id', $user->id)
            ->whereDate('start_time', now()->toDateString())
            ->orderBy('start_time')
            ->get();
            
        $upcomingAgendas = \App\Models\Agenda::where('user_id', $user->id)
            ->where('start_time', '>', now())
            ->whereDate('start_time', '!=', now()->toDateString())
            ->orderBy('start_time')
            ->take(5)
            ->get();
            
        $stats = [
            'terjadwal' => \App\Models\Agenda::where('user_id', $user->id)->where('status', \App\Enums\AgendaStatus::TERJADWAL->value)->count(),
            'selesai' => \App\Models\Agenda::where('user_id', $user->id)->where('status', \App\Enums\AgendaStatus::SELESAI->value)->count(),
            'dibatalkan' => \App\Models\Agenda::where('user_id', $user->id)->where('status', \App\Enums\AgendaStatus::DIBATALKAN->value)->count(),
        ];

        $agendaTypes = \App\Enums\AgendaType::cases();

        return view('agenda.dashboard', compact('todayAgendas', 'upcomingAgendas', 'stats', 'agendaTypes'));
    }
}
