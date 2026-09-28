# Panduan Migrasi Data dari Google Sheets / Excel

Sistem SAMARA menyediakan fitur pengimporan data lama berbasis CSV untuk membantu migrasi dari Google Sheets atau Excel.

---

## 1. Langkah-langkah Migrasi Data

1. Buka Google Sheets / Excel data lama Anda.
2. Pilih menu **File** → **Download** → **Comma-separated values (.csv)**.
3. Masuk ke aplikasi SAMARA sebagai **ADMIN**.
4. Buka menu **Admin** → **Import Data**.
5. Pilih jenis data (USERS, AREAS, SEGMENTS, CUSTOMERS).
6. Pilih file CSV yang disiapkan dan klik **Jalankan Import Data**.

---

## 2. Format Header CSV yang Dibutuhkan

### A. Format Import Users (`USERS`)
```csv
email,full_name,role,password,manager_email
budi@perusahaan.com,Budi Pratama,PLANNER,password123,director@perusahaan.com
```
*Catatan: Role `PLANNER`, `FIELD`, `MANAGER`, `VIEWER` akan otomatis dinormalisasi menjadi `TIM`.*

### B. Format Import Customers (`CUSTOMERS`)
```csv
customer_code,customer_name,area_code,segment_code,owner_email,address,city,province,priority_tier
CUST-101,Koperasi Primkopad,AREA-WEST,SEG-PERMIL,budi@perusahaan.com,Jl. Veteran No. 5,Jakarta Pusat,DKI Jakarta,A
```

---

## 3. Penanganan Duplikasi & Error Report

- Sistem secara otomatis memeriksa duplikasi berdasarkan kolom unik (email untuk users, customer_code untuk customers, area_code untuk areas).
- Jika terdapat baris duplikat atau tidak valid, sistem akan mencatat baris tersebut pada daftar error tanpa menggagalkan baris data yang valid lainnya.
