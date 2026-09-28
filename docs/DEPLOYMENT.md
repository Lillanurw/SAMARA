# Panduan Deployment Server Production

Dokumen ini menjelaskan prosedur cara memasang aplikasi SAMARA pada Shared Hosting, VPS (Ubuntu/Nginx/Apache), atau Server Internal.

---

## 1. Shared Hosting (cPanel / DirectAdmin)

1. **Upload File**:
   Upload seluruh isi proyek (kecuali folder `node_modules` dan `.git`) ke direktori root hosting Anda (misal `/home/username/samara`).
2. **Set Web Root ke folder `public`**:
   - Jika cPanel mendukung Document Root modification, arahkan domain/subdomain Anda ke `/home/username/samara/public`.
   - Atau pindahkan seluruh file dalam folder `public/` ke `public_html/` dan ubah path pada `index.php`:
     ```php
     require __DIR__.'/../samara/vendor/autoload.php';
     $app = require_once __DIR__.'/../samara/bootstrap/app.php';
     ```
3. **Set File `.env` Production**:
   ```env
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://samara.perusahaan.com
   ```
4. **Optimasi Cache Laravel**:
   Jalankan command via Terminal / SSH / Cron:
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

---

## 2. VPS / Dedicated Server (Ubuntu + Nginx + PHP 8.2 + MySQL)

### Nginx Virtual Host Configuration Sample:
```nginx
server {
    listen 80;
    server_name samara.perusahaan.com;
    root /var/www/samara/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### Permission Setup:
```bash
sudo chown -R www-data:www-data /var/www/samara/storage /var/www/samara/bootstrap/cache
sudo chmod -R 775 /var/www/samara/storage /var/www/samara/bootstrap/cache
```
