# Panduan Backup & Restore Database SAMARA

---

## 1. Cara Backup Database MySQL

Gunakan utilitas `mysqldump` pada command line server:

```bash
mysqldump -u root -p samara > backup_samara_$(date +%Y%m%d_%H%M%S).sql
```

Atau kompresi langsung ke file GZ:

```bash
mysqldump -u root -p samara | gzip > backup_samara_$(date +%Y%m%d).sql.gz
```

---

## 2. Cara Restore Database MySQL

1. Pastikan database `samara` sudah ada:
   ```sql
   CREATE DATABASE IF NOT EXISTS samara CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

2. Jalankan perintah restore:
   ```bash
   mysql -u root -p samara < backup_samara_20260924.sql
   ```

   Atau untuk file `.sql.gz`:
   ```bash
   gunzip < backup_samara_20260924.sql.gz | mysql -u root -p samara
   ```
