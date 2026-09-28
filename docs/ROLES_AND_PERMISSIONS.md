# Hak Akses & Role Policy SAMARA

Sistem SAMARA menerapkan Role-Based Access Control (RBAC) ketat dengan tiga role utama:

1. **`ADMIN`**
2. **`TIM`**
3. **`DIRECTOR`**

---

## Matrix Menu & Hak Akses

| Menu / Fitur | ADMIN | TIM | DIRECTOR |
|---|:---:|:---:|:---:|
| **Home** | ✅ | ✅ | ✅ |
| **Monthly Plan** | ✅ | ✅ | ✅ |
| **My Week** | ✅ | ✅ | ✅ |
| **Report** | ✅ | ✅ | ✅ |
| **Follow-ups** | ✅ | ✅ | ✅ |
| **Dashboard** | ✅ (Semua Area) | ✅ (Terbatas Area PIC) | ✅ (Semua Area) |
| **Arahan untuk Saya** | ✅ | ✅ | ✅ |
| **Director Review** | ✅ | ❌ (HTTP 403) | ✅ |
| **Admin Menu** | ✅ | ❌ (HTTP 403) | ❌ (HTTP 403) |

---

## Aturan Authorization Backend

1. **Penguncian Middleware & Policy**:
   - Jika pengguna `TIM` mencoba membuka URL `/admin/*` atau `/director-review`, backend secara otomatis menggagalkan request dengan HTTP 403:
     `"Anda tidak memiliki izin untuk membuka halaman ini."`
   - Authorization tidak hanya menyembunyikan elemen tombol pada Blade HTML, tetapi selalu diverifikasi ulang pada level Middleware & Gate Policy.

2. **Aturan Khusus Director Inputs untuk Tim**:
   - Tim **dapat**:
     - Melihat arahan yang ditugaskan kepada mereka (`assigned_to === user.id`).
     - Mengubah status arahan milik mereka via endpoint khusus:
       - `PATCH /my-directions/{id}/acknowledge` (OPEN → ACKNOWLEDGED)
       - `PATCH /my-directions/{id}/start` (ACKNOWLEDGED → IN_PROGRESS)
       - `PATCH /my-directions/{id}/complete` (IN_PROGRESS → CLOSED + wajib completion note)
   - Tim **TIDAK dapat**:
     - Membuat arahan Director.
     - Mengubah teks/topik/prioritas/due date arahan.
     - Menghapus arahan.
     - Mengembalikan status `CLOSED` menjadi `OPEN`.
