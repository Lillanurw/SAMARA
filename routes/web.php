<?php

use App\Http\Controllers\Admin\AppSettingController;
use App\Http\Controllers\Admin\AreaController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\ImportController;
use App\Http\Controllers\Admin\SegmentController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DirectorInputController;
use App\Http\Controllers\FollowUpController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MyWeekController;
use App\Http\Controllers\VisitPlanController;
use App\Http\Controllers\VisitReportController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Guest Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/auth/google', [AuthController::class, 'redirectToGoogle'])->name('auth.google');
    Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback']);
});

// Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/', function () {
        return redirect()->route('home');
    });

    Route::get('/home', [HomeController::class, 'index'])->name('home');

    // Profile
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile/update', [ProfileController::class, 'update'])->name('profile.update');

    // Notifications
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.readAll');

    // Monthly Plan
    Route::get('/plans', [VisitPlanController::class, 'index'])->name('plans.index');
    Route::post('/plans', [VisitPlanController::class, 'store'])->name('plans.store');
    Route::get('/plans/{visitPlan}', [VisitPlanController::class, 'show'])->name('plans.show');
    Route::put('/plans/{visitPlan}', [VisitPlanController::class, 'update'])->name('plans.update');
    Route::delete('/plans/{visitPlan}', [VisitPlanController::class, 'destroy'])->name('plans.destroy');
    Route::patch('/plans/{visitPlan}/cancel', [VisitPlanController::class, 'cancel'])->name('plans.cancel');
    Route::patch('/plans/{visitPlan}/reschedule', [VisitPlanController::class, 'reschedule'])->name('plans.reschedule');

    // My Week
    Route::get('/my-week', [MyWeekController::class, 'index'])->name('weekly.index');
    Route::patch('/my-week/{visitPlan}/start', [MyWeekController::class, 'startVisit'])->name('weekly.start');

    // Visit Report
    Route::get('/reports', [VisitReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export/excel', [VisitReportController::class, 'exportExcel'])->name('reports.exportExcel');
    Route::get('/reports/export/pdf', [VisitReportController::class, 'exportPdf'])->name('reports.exportPdf');
    Route::get('/reports/export/gsheets', [VisitReportController::class, 'exportGsheets'])->name('reports.exportGsheets');
    Route::get('/reports/export/gdocs', [VisitReportController::class, 'exportGdocs'])->name('reports.exportGdocs');
    Route::get('/reports/create', [VisitReportController::class, 'create'])->name('reports.create');
    Route::post('/reports', [VisitReportController::class, 'store'])->name('reports.store');
    Route::get('/reports/{visitReport}', [VisitReportController::class, 'show'])->name('reports.show');

    // Follow-ups
    Route::get('/followups', [FollowUpController::class, 'index'])->name('followups.index');
    Route::patch('/followups/{followUp}/status', [FollowUpController::class, 'updateStatus'])->name('followups.updateStatus');

    // Director Input (Arahan untuk Saya for Tim)
    Route::get('/my-directions', [DirectorInputController::class, 'myDirections'])->name('directions.myDirections');
    Route::patch('/my-directions/{directorInput}/acknowledge', [DirectorInputController::class, 'acknowledge'])->name('directions.acknowledge');
    Route::patch('/my-directions/{directorInput}/start', [DirectorInputController::class, 'start'])->name('directions.start');
    Route::patch('/my-directions/{directorInput}/complete', [DirectorInputController::class, 'complete'])->name('directions.complete');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

    // Director Review Routes (ADMIN & DIRECTOR)
    Route::middleware('role:ADMIN,DIRECTOR')->group(function () {
        Route::get('/director-review', [DirectorInputController::class, 'indexReview'])->name('director.review');
        Route::post('/director-review', [DirectorInputController::class, 'store'])->name('director.store');
    });

    // Admin Master Data Routes (ADMIN only)
    Route::middleware('role:ADMIN')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::patch('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::patch('/users/{user}/toggle', [UserController::class, 'toggleActive'])->name('users.toggle');

        Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
        Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
        Route::patch('/customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
        Route::patch('/customers/{customer}/toggle', [CustomerController::class, 'toggleActive'])->name('customers.toggle');

        Route::get('/areas', [AreaController::class, 'index'])->name('areas.index');
        Route::post('/areas', [AreaController::class, 'store'])->name('areas.store');
        Route::patch('/areas/{area}', [AreaController::class, 'update'])->name('areas.update');

        Route::get('/segments', [SegmentController::class, 'index'])->name('segments.index');
        Route::post('/segments', [SegmentController::class, 'store'])->name('segments.store');
        Route::patch('/segments/{segment}', [SegmentController::class, 'update'])->name('segments.update');

        Route::get('/settings', [AppSettingController::class, 'index'])->name('settings.index');
        Route::post('/settings', [AppSettingController::class, 'update'])->name('settings.update');

        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit_logs.index');

        Route::get('/import', [ImportController::class, 'index'])->name('import.index');
        Route::post('/import', [ImportController::class, 'process'])->name('import.process');
    });
});
