<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Customer;
use App\Models\Segment;
use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ImportController extends Controller
{
    public function index(): View
    {
        return view('admin.import.index');
    }

    public function process(Request $request): RedirectResponse
    {
        $request->validate([
            'data_type' => ['required', 'string'],
            'file' => ['required', 'file', 'mimes:csv,txt,xlsx', 'max:10240'],
        ], [
            'file.required' => 'File CSV/XLSX wajib diunggah.',
            'file.mimes' => 'Format file harus CSV atau XLSX.',
            'file.max' => 'Ukuran file maksimal 10 MB.',
        ]);

        $dataType = $request->input('data_type');
        $file = $request->file('file');
        $path = $file->getRealPath();

        $rows = [];
        $extension = strtolower($file->getClientOriginalExtension());

        if (in_array($extension, ['csv', 'txt'])) {
            $handle = fopen($path, 'r');
            if ($handle !== false) {
                $header = fgetcsv($handle, 1000, ',');
                if ($header) {
                    $header = array_map('trim', $header);
                    while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                        if (count($data) === count($header)) {
                            $rows[] = array_combine($header, array_map('trim', $data));
                        }
                    }
                }
                fclose($handle);
            }
        } else {
            // For simple XLSX XML parsing if phpspreadsheet is not installed or as fallback
            return back()->withErrors(['file' => 'Untuk mengimpor XLSX, mohon simpan sebagai format CSV terlebih dahulu.']);
        }

        if (empty($rows)) {
            return back()->withErrors(['file' => 'File tidak mempunyai data yang dapat dibaca atau header tidak sesuai.']);
        }

        $successCount = 0;
        $failedCount = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            foreach ($rows as $index => $row) {
                $rowNum = $index + 2; // account for header
                try {
                    match ($dataType) {
                        'USERS' => $this->importUser($row),
                        'AREAS' => $this->importArea($row),
                        'SEGMENTS' => $this->importSegment($row),
                        'CUSTOMERS' => $this->importCustomer($row),
                        default => throw new \Exception("Tipe data {$dataType} belum didukung untuk impor otomatis."),
                    };
                    $successCount++;
                } catch (\Throwable $e) {
                    $failedCount++;
                    $errors[] = "Baris {$rowNum}: " . $e->getMessage();
                }
            }

            DB::commit();

            AuditLogger::log('IMPORT_DATA', 'SYSTEM', null, null, [
                'data_type' => $dataType,
                'success_count' => $successCount,
                'failed_count' => $failedCount,
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['file' => 'Terjadi kesalahan sistem saat proses impor: ' . $e->getMessage()]);
        }

        return back()->with('import_result', [
            'success_count' => $successCount,
            'failed_count' => $failedCount,
            'errors' => $errors,
        ]);
    }

    private function importUser(array $row): void
    {
        $email = strtolower(trim($row['email'] ?? ''));
        if (empty($email)) {
            throw new \Exception("Email tidak boleh kosong.");
        }

        if (User::where('email', $email)->exists()) {
            throw new \Exception("User dengan email {$email} sudah terdaftar (duplikasi).");
        }

        $roleRaw = strtoupper(trim($row['role'] ?? 'TIM'));
        $role = match ($roleRaw) {
            'PLANNER', 'FIELD', 'MANAGER', 'VIEWER', 'TIM' => UserRole::TIM->value,
            'ADMIN' => UserRole::ADMIN->value,
            'DIRECTOR' => UserRole::DIRECTOR->value,
            default => UserRole::TIM->value,
        };

        User::create([
            'email' => $email,
            'full_name' => $row['full_name'] ?? $row['name'] ?? 'User Import',
            'role' => $role,
            'password' => Hash::make($row['password'] ?? 'password123'),
            'manager_email' => $row['manager_email'] ?? null,
            'is_active' => true,
        ]);
    }

    private function importArea(array $row): void
    {
        $code = trim($row['area_code'] ?? '');
        if (empty($code)) throw new \Exception("Kode area tidak boleh kosong.");

        if (Area::where('area_code', $code)->exists()) {
            throw new \Exception("Area dengan kode {$code} sudah ada.");
        }

        Area::create([
            'area_code' => $code,
            'area_name' => $row['area_name'] ?? $code,
            'region' => $row['region'] ?? 'Umum',
            'description' => $row['description'] ?? null,
            'is_active' => true,
        ]);
    }

    private function importSegment(array $row): void
    {
        $code = trim($row['segment_code'] ?? '');
        if (empty($code)) throw new \Exception("Kode segment tidak boleh kosong.");

        if (Segment::where('segment_code', $code)->exists()) {
            throw new \Exception("Segment dengan kode {$code} sudah ada.");
        }

        Segment::create([
            'segment_code' => $code,
            'segment_name' => $row['segment_name'] ?? $code,
            'description' => $row['description'] ?? null,
            'is_active' => true,
        ]);
    }

    private function importCustomer(array $row): void
    {
        $code = trim($row['customer_code'] ?? '');
        if (empty($code)) throw new \Exception("Kode customer tidak boleh kosong.");

        if (Customer::where('customer_code', $code)->exists()) {
            throw new \Exception("Customer dengan kode {$code} sudah ada.");
        }

        $area = Area::where('area_code', $row['area_code'] ?? '')->first() ?? Area::first();
        $segment = Segment::where('segment_code', $row['segment_code'] ?? '')->first() ?? Segment::first();
        $owner = User::where('email', $row['owner_email'] ?? '')->first() ?? User::where('role', UserRole::TIM->value)->first();

        Customer::create([
            'customer_code' => $code,
            'customer_name' => $row['customer_name'] ?? $code,
            'area_id' => $area->id,
            'segment_id' => $segment->id,
            'owner_id' => $owner->id,
            'address' => $row['address'] ?? 'Alamat',
            'city' => $row['city'] ?? 'Kota',
            'province' => $row['province'] ?? 'Provinsi',
            'contact_name' => $row['contact_name'] ?? null,
            'contact_position' => $row['contact_position'] ?? null,
            'contact_phone' => $row['contact_phone'] ?? null,
            'contact_email' => $row['contact_email'] ?? null,
            'priority_tier' => strtoupper($row['priority_tier'] ?? 'B'),
            'notes' => $row['notes'] ?? null,
            'is_active' => true,
        ]);
    }
}
