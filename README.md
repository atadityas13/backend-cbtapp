# CBTApp Backend - Laravel 11 Version

Repository backend modern berbasis **Laravel 11** untuk **CBTApp MTs Negeri 11 Majalengka**. Project ini menggantikan backend PHP native lama dengan sistem autentikasi **Multi-User (Admin & Proktor)** yang aman, handal, dan profesional.

Sistem API dirancang **100% Backward Compatible** dengan format respons JSON lama sehingga aplikasi Android versi 6 (`4.2.3`) tetap berfungsi normal tanpa perubahan di sisi client.

---

## 🚀 Panduan Deployment di cPanel (Hosting)

Untuk melakukan deployment ke subdomain baru (misalnya `panelcbt.mtsn11majalengka.sch.id`), Anda dapat menggunakan Git langsung dari cPanel:

### 1. Push Project ke GitHub Anda
Inisialisasi Git lokal sudah dilakukan di folder ini. Anda tinggal mengaitkannya ke repository GitHub baru Anda:
```bash
# Tambahkan origin repository GitHub Anda
git remote add origin https://github.com/USERNAME/REPO-NAME.git

# Push ke main/master branch
git branch -M main
git push -u origin main
```

### 2. Konfigurasi Subdomain di cPanel
1. Masuk ke **cPanel** Anda.
2. Buka menu **Subdomains** atau **Domains** -> **Create a New Domain**.
3. Buat subdomain baru (misal: `panelcbt.mtsn11majalengka.sch.id`).
4. **PENTING:** Atur **Document Root** subdomain tersebut ke `/public_html/panelcbt/public` (harus mengarah ke folder **public** di dalam project Laravel).

### 3. Clone / Pull Repository di cPanel
1. Masuk ke terminal cPanel atau gunakan menu **Git™ Version Control** di cPanel.
2. Clone repository Anda ke folder subdomain (misal: `/public_html/panelcbt`):
   ```bash
   git clone https://github.com/USERNAME/REPO-NAME.git /home/username/public_html/panelcbt
   ```
3. Jika sudah ada folder, masuk ke folder tersebut lalu lakukan pull:
   ```bash
   git pull origin main
   ```

### 4. Instalasi Dependensi & Setup Environment
Masuk ke terminal cPanel, navigasikan ke folder project, lalu jalankan perintah berikut:
```bash
# 1. Install dependensi composer (tanpa dev package untuk produksi)
composer install --no-dev --optimize-autoloader

# 2. Duplikat file .env.example menjadi .env
cp .env.example .env

# 3. Generate Application Key
php artisan key:generate
```

### 5. Konfigurasi Database & Firebase
1. Buka file `.env` di cPanel File Manager, lalu ubah konfigurasi database:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=mtsnmaja_cbt_notifications
   DB_USERNAME=mtsnmaja_user
   DB_PASSWORD=password_anda
   ```
2. Jalankan migrasi database dan seeding data awal:
   ```bash
   php artisan migrate --seed
   ```
   *Perintah ini akan otomatis membuat tabel-tabel baru (`users`, `settings`, `cbt_pelanggaran`, `fcm_registrations`) dan mengisi akun Super Admin awal serta konfigurasi default.*
   
   **Akun Super Admin Awal:**
   - **Username:** `admin`
   - **Password:** `Admin021398` (Disarankan langsung diubah di halaman Pengaturan setelah masuk).

3. **Unggah file `service-account.json`** (kredensial Firebase Admin SDK Anda) ke root folder project (`/home/username/public_html/panelcbt/service-account.json`) agar fitur Push Notification Firebase dapat berfungsi normal.

### 6. Optimasi Produksi
Jalankan perintah optimasi berikut untuk mempercepat performa Laravel Anda di server:
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link
```

---

## 🔒 Fitur Unggulan & Hak Akses
1. **Multi-User (Role-Based Access Control):**
   - **Super Admin (`admin`):** Akses penuh ke seluruh menu, termasuk menambah/menghapus akun proktor lain dan mengubah pengaturan kritis seperti rentang waktu operasional ujian, waktu sesi, jenis asesmen aktif, serta iklan.
   - **Proktor (`proktor`):** Akses ke halaman Ringkasan Dashboard, memantau daftar siswa terdaftar, melihat riwayat pelanggaran, melepaskan ban siswa (Pardon), serta mengirim notifikasi push manual ke siswa. Menu "Kelola Proktor" akan disembunyikan dan diproteksi otomatis di tingkat Controller & View.
2. **Dynamic Configurations (`settings` table):**
   Semua setelan dinamis (seperti status aktif Asesmen Sumatif/Madrasah, durasi tayang iklan, banner iklan) tersimpan di database dan dapat diubah secara real-time langsung melalui dashboard admin tanpa mengubah file konfigurasi JSON secara manual.
3. **Painless Integration:**
   Sistem rute API mempertahankan ekstensi `.php` (misal: `/api/notifikasi/simpan_token.php`) untuk memastikan aplikasi Android siswa tetap terhubung 100% dengan database baru.

---

*CBTApp Backend dikembangkan dengan dedikasi penuh untuk kemajuan digitalisasi pendidikan di MTs Negeri 11 Majalengka.*
