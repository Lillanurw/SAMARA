<?php

namespace App\Http\Controllers;

use App\Enums\FollowUpStatus;
use App\Enums\OutcomeType;
use App\Enums\PlanStatus;
use App\Enums\SubmitStatus;
use App\Models\Customer;
use App\Models\FollowUp;
use App\Models\User;
use App\Models\VisitPlan;
use App\Models\VisitReport;
use App\Support\AuditLogger;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class VisitReportController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();

        // Date range filtering
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));

        // 1. Visits needing report (In Progress or past planned without report)
        $pendingPlans = VisitPlan::with(['customer', 'area', 'owner'])
            ->where(function ($q) use ($user) {
                $q->where('owner_id', $user->id)
                  ->orWhereHas('members', fn($m) => $m->where('users.id', $user->id));
            })
            ->whereIn('status', [PlanStatus::IN_PROGRESS, PlanStatus::PLANNED])
            ->doesntHave('visitReport')
            ->orderBy('planned_date', 'desc')
            ->get();

        // 2. Draft reports
        $draftReports = VisitReport::with(['visitPlan.customer', 'submitter'])
            ->where('submitted_by', $user->id)
            ->where('submit_status', SubmitStatus::DRAFT)
            ->orderBy('updated_at', 'desc')
            ->get();

        // 3. Submitted reports history
        $reportsQuery = VisitReport::with(['visitPlan.customer', 'visitPlan.area', 'submitter', 'directorInputs'])
            ->when($startDate, fn($q) => $q->whereDate('created_at', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('created_at', '<=', $endDate))
            ->when(!$user->isAdmin() && !$user->isDirector(), function ($q) use ($user) {
                $userAreaIds = $user->areas()->pluck('areas.id')->toArray();
                $q->where('submitted_by', $user->id)
                  ->orWhereHas('visitPlan', function ($vp) use ($user, $userAreaIds) {
                      $vp->where('owner_id', $user->id)
                        ->orWhereIn('area_id', $userAreaIds);
                  });
            });

        $reports = $reportsQuery->orderBy('submitted_at', 'desc')->paginate(15)->withQueryString();

        // 4. Export matrix data
        $exportData = $this->getReportExportData($request);

        return view('reports.index', array_merge(
            compact('pendingPlans', 'draftReports', 'reports', 'startDate', 'endDate'),
            $exportData
        ));
    }

    private function getReportExportData(Request $request): array
    {
        $user = Auth::user();
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));

        $carbonStart = Carbon::parse($startDate);
        $carbonEnd = Carbon::parse($endDate);

        $plans = VisitPlan::with(['customer.area', 'owner', 'visitReport.followUps', 'visitReport.submitter'])
            ->when(!$user->isAdmin() && !$user->isDirector(), function ($q) use ($user) {
                $userAreaIds = $user->areas()->pluck('areas.id')->toArray();
                $q->where('owner_id', $user->id)
                  ->orWhereIn('area_id', $userAreaIds);
            })
            ->whereBetween('planned_date', [$startDate, $endDate])
            ->orderBy('planned_date', 'asc')
            ->get();

        $fieldForce = $user->full_name;
        $rayon = $user->areas->pluck('area_name')->implode(', ') ?: ($plans->pluck('customer.area.area_name')->filter()->unique()->implode(', ') ?: 'GP');
        $weekNo = $carbonStart->weekOfMonth == $carbonEnd->weekOfMonth
            ? $carbonStart->weekOfMonth
            : $carbonStart->weekOfMonth . ' - ' . $carbonEnd->weekOfMonth;
        $monthName = $carbonStart->translatedFormat('F Y') == $carbonEnd->translatedFormat('F Y')
            ? $carbonStart->translatedFormat('F Y')
            : $carbonStart->translatedFormat('d M Y') . ' — ' . $carbonEnd->translatedFormat('d M Y');

        return compact('user', 'startDate', 'endDate', 'carbonStart', 'carbonEnd', 'plans', 'fieldForce', 'rayon', 'weekNo', 'monthName');
    }

    public function exportExcel(Request $request)
    {
        $data = $this->getReportExportData($request);
        $filename = 'Rencana_dan_Realisasi_Kunjungan_' . $data['startDate'] . '_sd_' . $data['endDate'] . '.xls';

        return response()->streamDownload(function () use ($data) {
            echo "\xEF\xBB\xBF";
            echo view('reports.export_table', $data)->render();
        }, $filename, [
            'Content-Type' => 'application/vnd.ms-excel; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function exportPdf(Request $request): View
    {
        $data = $this->getReportExportData($request);
        return view('reports.export_pdf', $data);
    }

    public function exportGsheets(Request $request)
    {
        $data = $this->getReportExportData($request);
        $filename = 'Rencana_dan_Realisasi_Kunjungan_GSheets_' . $data['startDate'] . '_sd_' . $data['endDate'] . '.csv';

        return response()->streamDownload(function () use ($data) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['FIELD FORCE', $data['fieldForce'], '', 'MINGGU KE', $data['weekNo']]);
            fputcsv($handle, ['RAYON', $data['rayon'], '', 'BULAN', $data['monthName']]);
            fputcsv($handle, []);

            fputcsv($handle, [
                'Hari/Tanggal',
                'Rencana Customer',
                'Rencana Target',
                'Keterangan',
                'Hasil Customer',
                'Pejabat Nama',
                'Pejabat Jabatan',
                'Hasil Kunjungan',
                'Tindak Lanjut'
            ]);

            foreach ($data['plans'] as $plan) {
                $report = $plan->visitReport;
                $dayDate = \Carbon\Carbon::parse($plan->planned_date)->translatedFormat('l, d F Y');

                fputcsv($handle, [
                    $dayDate,
                    $plan->customer->customer_name ?? '—',
                    $plan->specific_objective ?? '—',
                    $plan->resource_notes ?? '—',
                    $report ? ($plan->customer->customer_name ?? '—') : '—',
                    $report ? ($report->submitter->full_name ?? '—') : '—',
                    $report ? 'Submitter' : '—',
                    $report ? ($report->outcome_summary ?? 'Belum Ada Laporan') : 'Belum Ada Laporan',
                    $report ? ($report->next_step_summary ?? '—') : '—'
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function exportGdocs(Request $request)
    {
        $data = $this->getReportExportData($request);
        $filename = 'Rencana_dan_Realisasi_Kunjungan_GDocs_' . $data['startDate'] . '_sd_' . $data['endDate'] . '.doc';

        return response()->streamDownload(function () use ($data) {
            echo "<!DOCTYPE html><html><head><meta charset='utf-8'><title>Laporan Kunjungan</title></head><body>";
            echo view('reports.export_table', $data)->render();
            echo "</body></html>";
        }, $filename, [
            'Content-Type' => 'application/msword; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function create(Request $request): View|RedirectResponse
    {
        $planId = $request->input('visit_plan_id');

        $visitPlan = VisitPlan::with(['customer', 'area', 'members', 'owner'])
            ->findOrFail($planId);

        $this->authorize('view', $visitPlan);

        if ($visitPlan->visitReport) {
            return redirect()->route('reports.show', $visitPlan->visitReport->id)
                ->with('info', 'Laporan kunjungan ini sudah ada.');
        }

        $teamUsers = User::where('is_active', true)->orderBy('full_name')->get();

        return view('reports.create', compact('visitPlan', 'teamUsers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'visit_plan_id' => ['required', 'exists:visit_plans,id'],
            'actual_start_at' => ['required', 'date'],
            'actual_end_at' => ['required', 'date', 'after:actual_start_at'],
            'outcome_summary' => ['required', 'string', 'max:2000'],
            'outcome_type' => ['required', 'string'],
            'engagement_score' => ['required', 'integer', 'between:1,5'],
            'attendance_summary' => ['required', 'string', 'max:1000'],
            'barrier' => ['nullable', 'string', 'max:2000'],
            'need_or_opportunity' => ['nullable', 'string', 'max:2000'],
            'competitor_information' => ['nullable', 'string', 'max:2000'],
            'next_step_summary' => ['required', 'string', 'max:2000'],
            'follow_up_required' => ['nullable', 'boolean'],

            // Follow Up fields if checked
            'action_title' => ['required_if:follow_up_required,1', 'nullable', 'string', 'max:255'],
            'action_detail' => ['required_if:follow_up_required,1', 'nullable', 'string', 'max:2000'],
            'owner_id' => ['required_if:follow_up_required,1', 'nullable', 'exists:users,id'],
            'due_date' => ['required_if:follow_up_required,1', 'nullable', 'date'],
            'priority' => ['required_if:follow_up_required,1', 'nullable', 'string'],
        ], [
            'actual_end_at.after' => 'Waktu selesai kunjungan aktual tidak boleh sebelum waktu mulai.',
            'engagement_score.between' => 'Skor keterikatan (engagement) harus diisi antara 1 sampai 5.',
            'action_title.required_if' => 'Judul tindakan follow-up wajib diisi jika tindak lanjut diperlukan.',
            'owner_id.required_if' => 'Penanggung jawab follow-up wajib dipilih.',
            'due_date.required_if' => 'Tenggat waktu follow-up wajib diisi.',
        ]);

        $visitPlan = VisitPlan::findOrFail($validated['visit_plan_id']);

        if ($visitPlan->visitReport) {
            return back()->withInput()->withErrors(['visit_plan_id' => 'Laporan kunjungan ini sudah pernah dibuat.']);
        }

        $reportNumber = 'VR-' . Carbon::now()->format('Ym') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);

        DB::transaction(function () use ($validated, $visitPlan, $reportNumber, &$report) {
            $report = VisitReport::create([
                'report_number' => $reportNumber,
                'visit_plan_id' => $visitPlan->id,
                'actual_start_at' => $validated['actual_start_at'],
                'actual_end_at' => $validated['actual_end_at'],
                'outcome_summary' => $validated['outcome_summary'],
                'outcome_type' => $validated['outcome_type'],
                'engagement_score' => $validated['engagement_score'],
                'attendance_summary' => $validated['attendance_summary'],
                'barrier' => $validated['barrier'] ?? null,
                'need_or_opportunity' => $validated['need_or_opportunity'] ?? null,
                'competitor_information' => $validated['competitor_information'] ?? null,
                'next_step_summary' => $validated['next_step_summary'],
                'follow_up_required' => !empty($validated['follow_up_required']),
                'submitted_by' => Auth::id(),
                'submitted_at' => now(),
                'submit_status' => SubmitStatus::SUBMITTED->value,
                'created_by' => Auth::id(),
            ]);

            // Update visit plan status to COMPLETED
            $visitPlan->status = PlanStatus::COMPLETED->value;
            $visitPlan->actual_start_at = $validated['actual_start_at'];
            $visitPlan->actual_end_at = $validated['actual_end_at'];
            $visitPlan->completed_at = now();
            $visitPlan->updated_by = Auth::id();
            $visitPlan->save();

            // Create Follow-up if required
            if (!empty($validated['follow_up_required'])) {
                $actionNumber = 'ACT-' . Carbon::now()->format('Ym') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
                FollowUp::create([
                    'action_number' => $actionNumber,
                    'visit_plan_id' => $visitPlan->id,
                    'visit_report_id' => $report->id,
                    'customer_id' => $visitPlan->customer_id,
                    'action_title' => $validated['action_title'],
                    'action_detail' => $validated['action_detail'],
                    'owner_id' => $validated['owner_id'],
                    'due_date' => $validated['due_date'],
                    'priority' => $validated['priority'],
                    'status' => FollowUpStatus::OPEN->value,
                    'created_by' => Auth::id(),
                ]);
            }

            AuditLogger::log('SUBMIT_VISIT_REPORT', 'VISIT_REPORT', $report->id, null, $report->toArray());
        });

        return redirect()->route('reports.show', $report->id)
            ->with('success', 'Laporan kunjungan ' . $report->report_number . ' berhasil disimpan!');
    }

    public function show(VisitReport $visitReport): View
    {
        $this->authorize('view', $visitReport);

        $visitReport->load([
            'visitPlan.customer.area',
            'visitPlan.customer.segment',
            'visitPlan.owner',
            'visitPlan.members',
            'submitter',
            'followUps.owner',
            'directorInputs.creator',
            'directorInputs.assignedUser'
        ]);

        return view('reports.show', compact('visitReport'));
    }
}
