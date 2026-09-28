# Arsitektur Aplikasi SAMARA

Aplikasi **SAMARA** dibangun dengan mengikuti arsitektur Laravel Server-Side Rendered (SSR) Blade standar untuk performa cepat, keamanan tinggi, serta kemudahan pemeliharaan.

---

## 1. Alur Kontrol & Request Workflow

```text
Browser Client
   ↓ (HTTP Request)
Laravel Route (routes/web.php)
   ↓
Middleware Authorization & Role Validation (CheckRole, Auth)
   ↓
Controller (Form Request & Policy Check)
   ↓
Service / Database Transaction (DB::transaction)
   ↓
Eloquent Model & MySQL 8
   ↓
Blade Template Response (HTML + Vanilla CSS + Alpine.js)
```

---

## 2. Struktur Proyek

```text
app/
├── Enums/
│   ├── ActivityType.php
│   ├── DirectorInputStatus.php
│   ├── DirectorInputType.php
│   ├── FollowUpStatus.php
│   ├── OutcomeType.php
│   ├── PlanStatus.php
│   ├── PriorityLevel.php
│   ├── PriorityTier.php
│   ├── SubmitStatus.php
│   └── UserRole.php
├── Http/
│   ├── Controllers/
│   │   ├── Admin/ (UserController, CustomerController, AreaController, SegmentController, AppSettingController, AuditLogController, ImportController)
│   │   ├── AuthController.php
│   │   ├── DashboardController.php
│   │   ├── DirectorInputController.php
│   │   ├── FollowUpController.php
│   │   ├── HomeController.php
│   │   ├── MyWeekController.php
│   │   ├── VisitPlanController.php
│   │   └── VisitReportController.php
│   └── Middleware/
│       └── CheckRole.php
├── Models/
│   ├── AppSetting.php
│   ├── Area.php
│   ├── Attachment.php
│   ├── AuditLog.php
│   ├── Customer.php
│   ├── DirectorInput.php
│   ├── FollowUp.php
│   ├── Segment.php
│   ├── User.php
│   ├── VisitPlan.php
│   └── VisitReport.php
├── Policies/
│   ├── AreaPolicy.php
│   ├── CustomerPolicy.php
│   ├── DashboardPolicy.php
│   ├── DirectorInputPolicy.php
│   ├── FollowUpPolicy.php
│   ├── SegmentPolicy.php
│   ├── UserPolicy.php
│   ├── VisitPlanPolicy.php
│   └── VisitReportPolicy.php
└── Support/
    └── AuditLogger.php
```

---

## 3. Komponen Utama

1. **Role Normalization & PHP Enums**:
   Role pengguna (`ADMIN`, `TIM`, `DIRECTOR`) dikelola melalui PHP Enum `UserRole` dan dinormalisasi secara otomatis.

2. **Atomic Database Transaction**:
   Proses multi-tabel (seperti submit laporan kunjungan + pembuatan follow-up otomatis + pembaruan status visit plan ke `COMPLETED`) dibungkus dalam `DB::transaction()` untuk menjamin integritas data (ACID).

3. **Audit Log System**:
   Setiap perubahan data (create, update, status change, login) dicatat oleh helper `AuditLogger` ke dalam tabel `audit_logs`.

4. **UI Design System**:
   Menggunakan CSS kustom dengan variabel warna baku (`--navy`, `--blue`, `--cyan`, `--card`, `--radius: 14px`) dan Alpine.js ringan untuk interaksi modal/tab.
