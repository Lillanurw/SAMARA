# Panduan Instalasi Aplikasi SAMARA

Dokumen ini menjelaskan langkah-langkah instalasi aplikasi SAMARA pada lingkungan lokal (Windows / Linux / macOS) tanpa menggunakan Docker.

---

## 1. Prasyarat Perangkat Lunak

Sebelum menginstal aplikasi, pastikan perangkat server / lokal Anda telah terpasang:

1. **PHP**: Versi 8.2 atau lebih baru.
   - Pastikan ekstensi berikut aktif: `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `fileinfo`, `gd`, `zip`.
2. **Database MySQL**: Versi 8.0.x atau MariaDB 10.5+
3. **Composer**: Versi 2.x
4. **Node.js & npm**: Versi 18+ atau 20+ (opsional jika mengkompilasi CSS/JS Vite)

---

## 2. Langkah Instalasi di Windows (Laragon / XAMPP)

1. **Unduh atau Masuk ke Direktori Proyek**:
   ```powershell
   cd e:\SMD
   ```

2. **Install Dependensi PHP via Composer**:
   ```powershell
   composer install
   ```

3. **Buat Database MySQL**:
   Buka MySQL CLI atau phpMyAdmin dan jalankan query:
   ```sql
   CREATE DATABASE samara CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

4. **Konfigurasi Environment (`.env`)**:
   Salin `.env.example` menjadi `.env`:
   ```powershell
   copy .env.example .env
   php artisan key:generate
   ```
   Sesuaikan parameter database pada `.env`:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=samara
   DB_USERNAME=root
   DB_PASSWORD=
   ```

5. **Jalankan Database Migration dan Seeder**:
   ```powershell
   php artisan migrate --seed
   ```

6. **Storage Link**:
   ```powershell
   php artisan storage:link
   ```

7. **Jalankan Local Development Server**:
   ```powershell
   php artisan serve
   ```
   Aplikasi dapat diakses di `http://localhost:8000`.

---

## 3. Konfigurasi Google OAuth Socialite (Opsional)

Untuk mengaktifkan login "Masuk dengan Google":

1. Buka [Google Cloud Console](https://console.cloud.google.com/).
2. Buat proyek baru dan buka menu **APIs & Services** → **Credentials**.
3. Buat **OAuth 2.0 Client ID** (Web application).
4. Tambahkan Authorized Redirect URI:
   `http://localhost:8000/auth/google/callback` (atau domain server Anda).
5. Salin Client ID dan Client Secret ke file `.env`:
   ```env
   GOOGLE_CLIENT_ID=your-client-id.apps.googleusercontent.com
   GOOGLE_CLIENT_SECRET=your-client-secret
   GOOGLE_REDIRECT_URI="${APP_URL}/auth/google/callback"
   ```
