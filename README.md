# SAMARA - Sales and Marketing Activity Reporting and Analytics

**SAMARA** adalah sistem manajemen aktivitas Sales and Marketing / SMD (Sales Marketing Department) yang mengintegrasikan perencanaan kunjungan bulanan, agenda kerja mingguan, pelaksanaan kunjungan lapangan, pelaporan, tindak lanjut (follow-ups), arahan strategis Director, serta analitik performa.

---

## 🚀 Fitur Utama

1. **Perencanaan Kunjungan Bulanan (Monthly Plan)**: Penjadwalan aktivitas sales, account maintenance, demo produk, dan survei teknis.
2. **Agenda Mingguan (My Week)**: Tampilan agenda 7 hari yang intuitif, tombol "Mulai Kunjungan" cepat, dan status real-time.
3. **Pelaporan Kunjungan (Visit Report)**: Pengisian hasil pertemuan, pencatatan score engagement (1-5), hambatan, peluang, dan info kompetitor.
4. **Tindak Lanjut (Follow-ups)**: Pengelolaan action items (Open, Overdue, Blocked, Done) dengan penguncian syarat catatan penyelesaian.
5. **Review & Arahan Director (Director Directions)**: Pemberian petunjuk strategis oleh Director dan pelacakan alur status (`OPEN` → `ACKNOWLEDGED` → `IN_PROGRESS` → `CLOSED`).
6. **Dashboard Activity & Performance**: KPI agregat, completion rate, customer coverage, dan grafik Chart.js.
7. **Master Data & Audit Log**: Pengelolaan Users, Customers, Areas, Segments, App Settings, dan Audit Logs untuk keamanan.
8. **Impor Data CSV/XLSX**: Fitur migrasi data lama dari Google Sheets/Excel ke MySQL.

---

## ⚙️ Persyaratan Sistem

- PHP >= 8.2 (Extension: pdo_mysql, mbstring, openssl, ctype, json, xml, curl)
- MySQL >= 8.0 atau MariaDB compatible
- Composer >= 2.x
- Node.js & npm (opsional untuk build asset Vite)

---

## 🛠️ Cara Instalasi Cepat

```bash
# 1. Clone / Masuk ke folder proyek
cd /path/to/SMD

# 2. Install dependency Composer
composer install

# 3. Salin environment file & Generate key
cp .env.example .env
php artisan key:generate

# 4. Konfigurasi kredensial MySQL di .env
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=samara
# DB_USERNAME=root
# DB_PASSWORD=

# 5. Jalankan Migration dan Seeder Data Fiktif
php artisan migrate --seed

# 6. Buat symbolic link untuk storage (jika ada lampiran)
php artisan storage:link

# 7. Jalankan aplikasi secara lokal
php artisan serve
```

Buka peramban (browser) di `http://localhost:8000`.

---

## 🔑 Akun Default Seeder (Development / Demo)

| Nama Lengkap Di Sistem | Email | Password | Role System | Access Menu |
|---|---|---|---|---|
| `Admin - Lilla Nur (LN)` | `Admin@pastac.co.id` | `password123` | **ADMIN** | Semua Menu (Home, Plan, Week, Report, Followups, Dashboard, Director Review, Admin) |
| `Direktur SMD - Muhammad Ghaffari (MG)` | `Ghaffari@pastac.co.id` | `password123` | **DIRECTOR** | Menu Operasional + Director Review Page |
| `Direktur Proyek - Wandono Rino (WR)` | `Wandono@pastac.co.id` | `password123` | **DIRECTOR** | Menu Operasional + Director Review Page |
| `Sales Executive - Gunardo Probojakti (GP)` | `Gunardo@pastac.co.id` | `password123` | **TIM** | Menu Operasional (Home, Plan, Week, Report, Followups, Dashboard, Arahan untuk Saya) |
| `Sales Executive - Fikri Ardisa (FA)` | `Fikri@pastac.co.id` | `password123` | **TIM** | Menu Operasional (Home, Plan, Week, Report, Followups, Dashboard, Arahan untuk Saya) |
| `Sales Executive - Wera Sauma (WS)` | `Wera@pastac.co.id` | `password123` | **TIM** | Menu Operasional (Home, Plan, Week, Report, Followups, Dashboard, Arahan untuk Saya) |

---

## 📚 Dokumentasi Lengkap

Dokumentasi detail tersedia pada direktori `docs/`:

- [Dokumentasi Instalasi](docs/INSTALLATION.md)
- [Arsitektur Sistem](docs/ARCHITECTURE.md)
- [Struktur Database](docs/DATABASE.md)
- [Hak Akses & Role Policy](docs/ROLES_AND_PERMISSIONS.md)
- [Panduan Migrasi Data](docs/MIGRATION.md)
- [Panduan Server Deployment](docs/DEPLOYMENT.md)
- [Backup & Restore Database](docs/BACKUP_RESTORE.md)
- [Panduan Pengguna (User Guide)](docs/USER_GUIDE.md)

---

## 🧪 Menguji Aplikasi (Testing)

Jalankan pengujian otomatis:

```bash
php artisan test
```

---

*SAMARA - Developed with Laravel & MySQL.*
