# PMKS PROJECT CONTEXT
> Dokumen ini dibuat untuk memudahkan kolaborasi dengan AI manapun.
> Upload dokumen ini di awal sesi agar AI langsung paham konteks penuh proyek.
> Terakhir diperbarui: Juni 2026 (sesi 7 — Perluas akses Import Bansos)

---

## 1. IDENTITAS PROYEK

| Atribut | Nilai |
|---|---|
| Nama Aplikasi | PMKS — Sistem Informasi Sosial Kabupaten Buleleng |
| Tujuan | Pendataan & pengajuan PMKS dan PSKS oleh Dinas Sosial |
| Stack | Laravel 13, Filament v4, Livewire v3, PHP 8.3 |
| Database | MySQL |
| Queue | QUEUE_CONNECTION=database |
| User server | www-data (PHP-FPM) |
| Testing | Pest PHP |

---

## 2. STRUKTUR FOLDER & GIT

| Folder | Branch | Tujuan | Path |
|---|---|---|---|
| pmks-dev | develop | Development & testing | /DATA/coding/laravel/projects/pmks-dev |
| pmks-app | main | Production | /DATA/coding/laravel/projects/pmks-app |

---

## 3. WORKER & QUEUE

### Production — FULLY AUTOMATIC via Supervisor

```
sudo supervisorctl status
# pmks-worker:pmks-worker_00                   RUNNING
# pmks-worker:pmks-worker_01                   RUNNING
# pmks-worker-imports:pmks-worker-imports_00   RUNNING
```

| Program | Queue | Jumlah | Timeout | Fungsi |
|---|---|---|---|---|
| pmks-worker | default | 2 | 3600s | Notifikasi, batch PMKS/PSKS |
| pmks-worker-imports | imports | 1 | 300s | Import CSV PBI APBD |

**Worker perlu direstart HANYA kalau ada perubahan:**
- app/Jobs/ — job baru atau berubah
- config/queue.php — konfigurasi queue berubah
- .env — QUEUE_CONNECTION berubah
- composer.json — package baru

Perubahan controller / form / export TIDAK perlu restart worker.

### Development — MANUAL

```bash
screen -dmS pmks-imports bash -c "cd /DATA/coding/laravel/projects/pmks-dev && php artisan queue:work database --queue=imports --timeout=600 --tries=3 -v >> /tmp/pmks-imports.log 2>&1"
tail -f /tmp/pmks-imports.log
screen -X -S pmks-imports quit
```

---

## 4. WORKFLOW DEPLOY KE PRODUCTION

```bash
# WAJIB: cek git status dulu!
cd /DATA/coding/laravel/projects/pmks-dev && git status

# Commit & push develop
git add <file> && git commit -m "..." && git push origin develop

# Merge ke main & deploy
git checkout main && git merge develop && git push origin main && git checkout develop

cd /DATA/coding/laravel/projects/pmks-app
git pull origin main && php artisan optimize:clear && php artisan view:cache

# Verifikasi sinkron
echo "=== DEV ===" && git -C /DATA/coding/laravel/projects/pmks-dev status
echo "=== PRODUCTION ===" && git -C /DATA/coding/laravel/projects/pmks-app status
```

---

## 5. KONVENSI TERMINAL

- File PHP -> SELALU pakai tee dengan delimiter ENDOFFILE atau PHPEOF
- JANGAN pakai python3 heredoc untuk file PHP (backslash namespace = unicode error, dan PYEOF di dalam konten memotong heredoc lebih awal)
- Edit sederhana (replace string) -> boleh python3
- Selalu bertahap: buat 1 file, verifikasi, baru lanjut

---

## 6. KONVENSI FILAMENT V4

```
Section di form        -> Filament\Schemas\Components\Section (BUKAN Forms)
infolist() signature   -> public function infolist(Schema $schema): Schema
TextEntry              -> Filament\Infolists\Components\TextEntry
Grid, Section infolist -> Filament\Schemas\Components\
Custom Page            -> implements HasSchemas, use InteractsWithSchemas
$view di Page          -> protected string $view (TIDAK static)
$title                 -> protected static ?string $title
getNavigationGroup()   -> public static function
Widget                 -> auto-discover, tidak perlu daftar di AdminPanelProvider

Shortcut createOption di Select:
  ->createOptionForm(function (callable $get) { return [...]; })
  ->createOptionUsing(function (array $data, callable $get) { return $record->id; })
  ->createOptionModalHeading('Judul Modal')
```

---

## 7. ROLE & AKSES PENGGUNA

| Role | Kode | Deskripsi |
|---|---|---|
| Admin Dinsos | admin_dinsos | Akses penuh semua fitur |
| Operator Bidang | operator_bidang | Kelola data & pengajuan |
| Verifikator | verifikator | Read-only, bisa verifikasi batch |
| Operator Desa | operator_desa | Hanya data desanya sendiri |
| Staf Dinsos | staf_dinsos | Role tambahan |

---

## 8. CATATAN INFRASTRUKTUR

- Server: Armbian STB HG680P, RAM 2GB (terbatas — hindari N+1 query)
- Online: Cloudflare Zero Trust, URL: https://pm.lucidlynk.my.id
- CSV PBI APBD: separator semicolon, bulan nama Indonesia, ~16MB/246rb baris
- MySQL timeout workaround: dispatch per 50 chunks + DB::reconnect() di KisPbiApbdParserJob

---

## 9. FITUR SELESAI & PRODUCTION

### Sebelum Sesi 5
- Master Data: Kecamatan, Desa, KK, Penduduk, Lembaga
- PMKS/PSKS: SubmissionBatch + submissions, status flow 7 tahap + revisi
- DTSEN: permohonan surat, upload PDF, rekap Excel desil kemiskinan
- KIS: Rekap Agregat, Upload CSV PBI APBD background, Cek Kepesertaan per NIK
- Bansos: Import CSV PKH & Sembako
- Surat Dinas, API Publik Sanctum (8 endpoint), Dashboard Publik

### Sesi 7 — LIVE PRODUCTION

#### Perluas Akses Import Bansos — commit 38fa1c7

**Perubahan akses `BansosImportResource`:**

| Aksi | Admin | Op. Bidang | Verifikator | Op. Desa | Staf Dinsos |
|---|:---:|:---:|:---:|:---:|:---:|
| Menu & lihat list | ✅ | ✅ | ✅ | ✅ | ✅ |
| Lihat detail | ✅ | ✅ | ✅ | ✅ | ✅ |
| Upload CSV | ✅ | ❌ | ❌ | ❌ | ❌ |
| Download CSV | ✅ | ✅ | ❌ | ✅ | ✅ |
| Hapus | ✅ | ❌ | ❌ | ❌ | ❌ |

- File: `app/Policies/BansosImportPolicy.php`
- File: `app/Filament/Resources/BansosImports/BansosImportResource.php`
- File: `app/Filament/Resources/BansosImports/Pages/ViewBansosImport.php`
- Tombol download pakai `auth()->user()?->can('download', $record)` (delegate ke policy)
- Tambah factory `stafDinsos()` di `database/factories/UserFactory.php`
- Update test di `tests/Feature/Bansos/BansosImportTest.php`

**Catatan infrastruktur ditemukan:** OPcache aktif di PHP-FPM dengan `validate_timestamps=Off`.
Setiap deploy kode ke dev maupun production **wajib** jalankan `sudo systemctl restart php8.3-fpm`
selain `php artisan optimize:clear`.

---

### Sesi 6 — LIVE PRODUCTION

#### Fitur 1 — Import CSV PMKS
- Migration: `database/migrations/2026_06_10_000001_create_pmks_imports_table.php`
- Model: `app/Models/PmksImport.php`
- Policy: `app/Policies/PmksImportPolicy.php`
- Jobs: `app/Jobs/Pmks/PmksImportParserJob.php` + `PmksImportChunkJob.php`
- Resource: `app/Filament/Resources/PmksImports/` (5 file)
- Format CSV (separator `;`): `nik;nama;tgl_lahir;jenis_kelamin;kode_kategori;catatan;jenis_disabilitas`
- Validasi: NIK 16 digit, format tanggal `dd-mm-yyyy`, batasan usia & gender per kategori, jenis disabilitas wajib untuk PMKS-05 & PMKS-09
- Auto-create Resident jika NIK belum ada di DB
- Unique constraint: 1 resident tidak boleh kategori yang sama 2x per batch
- Queue: `imports`, pattern sama dengan BansosParserJob (Bus::batch, chunk 100 baris)

#### Fitur 2 — Import CSV PSKS
- Migration: `database/migrations/2026_06_10_000002_create_psks_imports_table.php`
- Model: `app/Models/PsksImport.php`
- Policy: `app/Policies/PsksImportPolicy.php`
- Jobs: `app/Jobs/Psks/PsksImportParserJob.php` + `PsksImportChunkJob.php`
- Resource: `app/Filament/Resources/PsksImports/` (5 file)
- Format CSV (separator `;`): `kode_kategori;nik;nama;tgl_lahir;jenis_kelamin;tipe_lembaga;nomor_registrasi;catatan`
- Satu file CSV bisa memuat baris individu (PSKS-J-*) dan lembaga (PSKS-L-*) sekaligus
- Subject type ditentukan otomatis dari kode kategori (J = person, L = institution)
- Auto-create Resident atau Institution jika belum ada di DB
- Navigation group: `Pengajuan PMKS & PSKS`, sort 20 (PMKS) & 21 (PSKS)

#### Fix 3 — Filesystem Permissions
- File: `config/filesystems.php`
- Masalah: Flysystem membuat directory upload baru (`pmks-imports`, `psks-imports`) dengan permission `0700` karena `visibility('private')` di FileUpload — worker yang jalan sebagai `lucidlynk` tidak bisa baca file yang di-upload PHP-FPM (`www-data`)
- Solusi: tambah `permissions.dir.private = 0755` di disk `local`
- **PENTING:** Directory yang sudah terlanjur dibuat dengan `0700` perlu di-fix manual:
  **Catatan:** chmod manual tidak diperlukan di production karena direktori `pmks-imports` dan `psks-imports` belum pernah dibuat sebelum config fix ini masuk. Direktori baru akan otomatis dibuat dengan `0755` saat import pertama dijalankan.

#### Dokumen Tambahan
- `penggunaan_import_pmks_psks.md` — panduan lengkap cara membuat file CSV untuk import (format, validasi, contoh, kesalahan umum)
- `contoh_import_pmks.csv` — file CSV contoh PMKS (2 baris)
- `contoh_import_psks.csv` — file CSV contoh PSKS (2 individu + 2 lembaga)

---

### Sesi 5 — LIVE PRODUCTION

#### Fix 1 — Bug static $no di Export — commit 32a4208
- File: app/Exports/PmksSubmissionExport.php & PsksSubmissionExport.php
- Masalah: static $no di map() — nomor urut kacau kalau 2 user export bersamaan
- Solusi: private int $no = 0 (property instance, reset tiap objek baru)

#### Fix 2 — Shortcut Input di Form PSKS — commit 542594b
- File: app/Filament/Resources/PsksSubmissions/Schemas/PsksSubmissionForm.php
- subject_type person -> popup tambah Resident (NIK, nama, TTL, gender, KK, HP)
- subject_type institution -> popup tambah Institution (nama, tipe, no.reg, alamat, CP, HP)

#### Fix 3 — N+1 Query di StatistikController — commit 7cb3be2
- File: app/Http/Controllers/Api/StatistikController.php
- Sebelum: ~260 query untuk 130 desa (1 query per desa di dalam loop)
- Sesudah: ~5 query total (JOIN + groupBy + pluck, hitung di Collection PHP)
- Helper: getPmksCountByVillage() dan getPsksCountByVillage()
- Fungsi fix: pmks(), psks(), perKecamatan(), perDesa()

---

## 10. API PUBLIK

```
GET /api/v1/statistik/pmks?tahun=2026
GET /api/v1/statistik/psks?tahun=2026
GET /api/v1/statistik/ringkasan?tahun=2026
GET /api/v1/statistik/kecamatan?tahun=2026
GET /api/v1/statistik/desa/{kecamatan_id}?tahun=2026
GET /api/v1/statistik/dtsen
GET /api/v1/statistik/kis?tahun=2026&bulan=3
GET /api/v1/statistik/bansos?jenis=pkh&triwulan=1&tahun=2026

Header: Authorization: Bearer {token}
Rate limit: 60 request/menit per IP
```

---

## 11. TECHNICAL DEBT (SISA)

| Item | Prioritas | Status |
|---|---|---|
| static $no di Export PMKS & PSKS | Sedang | SELESAI sesi 5 |
| N+1 di StatistikController | Sedang | SELESAI sesi 5 |
| Import CSV PMKS & PSKS | Tinggi | SELESAI sesi 6 |
| Widget filter tahun hardcode now()->year | Rendah | Belum |
| HasRoleAccess trait dead code | Rendah | Belum |
| chmod 755 pmks-imports & psks-imports di production | Tinggi | TIDAK PERLU — direktori belum pernah dibuat, config 0755 sudah aktif |
| Akses Import Bansos per role | Sedang | SELESAI sesi 7 |

---

## 12. KANDIDAT FITUR BERIKUTNYA

1. Filter tahun di dashboard widget (hardcode now()->year)
2. Notifikasi email saat batch diapprove/request revisi
3. Export PDF ringkasan PMKS/PSKS per kecamatan
4. ~~Import massal PMKS/PSKS dari template Excel~~ → **SELESAI sesi 6 (CSV)**

---

## 13. FILE PENTING

```
app/Enums/UserRole.php
app/Providers/AuthServiceProvider.php              (daftar semua Policy termasuk PmksImport & PsksImport)
app/Providers/Filament/AdminPanelProvider.php
app/Services/AuditLogService.php
app/Console/Commands/DetectStuckImports.php
bootstrap/app.php
deployment/supervisor-pmks-worker.conf
config/livewire.php                                (max upload 100MB)
config/filesystems.php                             (permissions 0755 untuk private dirs — sesi 6)
routes/api.php
app/Http/Controllers/Api/StatistikController.php   (N+1 fix sesi 5)
app/Exports/PmksSubmissionExport.php               (static $no fix sesi 5)
app/Exports/PsksSubmissionExport.php               (static $no fix sesi 5)
app/Filament/Resources/PsksSubmissions/Schemas/PsksSubmissionForm.php  (shortcut sesi 5)

--- IMPORT PMKS/PSKS (sesi 6) ---
app/Models/PmksImport.php
app/Models/PsksImport.php
app/Policies/PmksImportPolicy.php
app/Policies/PsksImportPolicy.php
app/Jobs/Pmks/PmksImportParserJob.php
app/Jobs/Pmks/PmksImportChunkJob.php
app/Jobs/Psks/PsksImportParserJob.php
app/Jobs/Psks/PsksImportChunkJob.php
app/Filament/Resources/PmksImports/
app/Filament/Resources/PsksImports/
penggunaan_import_pmks_psks.md                     (panduan user)
```

---

## 14. PERINTAH BERGUNA

```bash
# DEVELOPMENT
cd /DATA/coding/laravel/projects/pmks-dev
php artisan test                          # full suite (248 test)
php artisan test tests/Feature/Api/       # test API saja
php artisan test tests/Feature/Exports/   # test export saja

# PRODUCTION
cd /DATA/coding/laravel/projects/pmks-app
sudo supervisorctl status
php artisan schedule:list

# Verifikasi sinkron dev & production
echo "=== DEV ===" && git -C /DATA/coding/laravel/projects/pmks-dev status && \
echo "=== PRODUCTION ===" && git -C /DATA/coding/laravel/projects/pmks-app status
```

---

## 15. CARA MELANJUTKAN DENGAN AI BARU

Upload file ini + repomix-output-dev.xml terbaru, lalu katakan:
"Ini konteks proyek PMKS. Sesi 5 sudah selesai (commit terakhir 7cb3be2). Lanjutkan ke [nama fitur]."

Generate repomix:
```bash
cd /DATA/coding/laravel/projects/pmks-dev && npx repomix --output repomix-output-dev.xml
```

Status saat ini: 248/248 test pass. Commit terakhir sesi 7: 38fa1c7 — live production.
Sesi 6 & 7 sudah di-deploy ke pmks-app.
**chmod pmks/psks-imports:** Tidak perlu dijalankan. Direktori belum pernah dibuat di production, dan `config/filesystems.php` sudah mengatur `dir.private = 0755` sehingga direktori baru otomatis dibuat dengan permission yang benar.
**WAJIB setiap deploy:** `sudo systemctl restart php8.3-fpm` karena OPcache `validate_timestamps=Off`.

---

*Di-generate dari sesi kolaborasi developer + Claude (Anthropic), Juni 2026.*

---

## 18. OPTIMASI PERFORMA SERVER

### OPcache PHP-FPM (dikerjakan sesi 5)

**Masalah sebelumnya:** OPcache tidak dikonfigurasi optimal → PHP compile ulang semua file Laravel setiap request → aplikasi lambat.

**Solusi:** Edit `/etc/php/8.3/fpm/conf.d/10-opcache.ini`:

```
zend_extension=opcache.so
opcache.enable=1
opcache.enable_cli=0
opcache.memory_consumption=128
opcache.max_accelerated_files=20000
opcache.validate_timestamps=0
opcache.interned_strings_buffer=16
opcache.jit=off
opcache.fast_shutdown=1
```

Setelah edit: `sudo systemctl restart php8.3-fpm`

**PENTING — karena `validate_timestamps=0`:**
Setiap deploy kode baru ke production WAJIB jalankan `php artisan optimize:clear`
Perintah ini sudah ada di workflow deploy, jadi aman.

**Verifikasi OPcache aktif:**
```bash
# Buat file test sementara
echo "<?php opcache_get_status() ? print('OPcache: AKTIF') : print('OPcache: MATI'); ?>" | \
sudo tee /DATA/coding/laravel/projects/pmks-app/public/opcache-check.php

# Buka di browser: https://pm.lucidlynk.my.id/opcache-check.php
# Setelah cek, HAPUS file test:
rm /DATA/coding/laravel/projects/pmks-app/public/opcache-check.php
```

### Pembersihan Log

Log Laravel yang besar (21MB+) memperlambat I/O disk. Bersihkan secara berkala:

```bash
# Backup & kosongkan log production
cp /DATA/coding/laravel/projects/pmks-app/storage/logs/laravel.log \
   /DATA/coding/laravel/projects/pmks-app/storage/logs/laravel.log.bak
echo "" > /DATA/coding/laravel/projects/pmks-app/storage/logs/laravel.log

# Kosongkan log dev
echo "" > /DATA/coding/laravel/projects/pmks-dev/storage/logs/laravel.log
```

### Hasil Optimasi Sesi 5

| Item | Sebelum | Sesudah |
|---|---|---|
| OPcache | Mati | Aktif via FPM |
| Log size | 21MB | 4KB |
| Query statistik | ~260 per request | ~5 per request |
| Kecepatan | Lambat | Terasa lebih cepat |
