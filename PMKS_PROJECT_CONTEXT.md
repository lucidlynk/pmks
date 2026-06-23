# PMKS PROJECT CONTEXT
> Dokumen ini dibuat untuk memudahkan kolaborasi dengan AI manapun.
> Upload dokumen ini di awal sesi agar AI langsung paham konteks penuh proyek.
> Terakhir diperbarui: Juni 2026 (sesi 10 — Rekap Bansos PKH & Sembako per Desa)

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

### Sesi 10 — LIVE PRODUCTION

#### Rekap Bansos PKH & Sembako per Desa — commit 8c1fd94

**Fitur:** Sub-menu "Rekap Bansos" di grup navigasi "Data Bansos" — menampilkan tabel agregasi penerima PKH dan Sembako per kecamatan dan desa/kelurahan dengan filter periode.

| File | Perubahan |
|---|---|
| `app/Filament/Pages/RekapBansos.php` | Custom Filament Page dengan `InteractsWithTable` — filter periode (tombol triwulan+tahun), 4 stat card (PKH, Sembako, Total, Jumlah Desa), tabel native Filament: search, sort, filter kecamatan, pagination (25/50/100/all), Sum summarizer, navigasi grup "Data Bansos" sort 2, semua role dapat akses |
| `app/Exports/BansosRekapExport.php` | Export Excel 6 kolom (No, Kecamatan, Desa, PKH, Sembako, Total), header biru bold, filename `Rekap_Bansos_TW{n}_{tahun}.xlsx` |
| `resources/views/filament/pages/rekap-bansos.blade.php` | Blade view: filter periode + stat card + `{{ $this->table }}` |

**Catatan teknis:**
- Query pakai `selectRaw` + `groupBy` di `BansosMember` — override `getTableRecordKey()` diperlukan karena tidak ada kolom `id` pada hasil agregasi
- Key unik per baris: `kec_name||kel_name`
- Periode otomatis pilih yang paling baru saat pertama load
- Tombol Download Excel hanya aktif (`->visible()`) saat ada data

**Total test:** 262/262 pass (tidak ada perubahan test).

---

### Sesi 9 — LIVE PRODUCTION

#### Download Excel DTSEN Rekap — commit a837ac2

**Fitur:** Tombol "Download Excel" di halaman view Rekap DTSEN (`/admin/dtsen-rekaps/{id}`).

| File | Perubahan |
|---|---|
| `app/Exports/DtsenRekapExport.php` | Export baru: semua detail per desa/kelurahan, 21 kolom (kecamatan, kelurahan, KK, jiwa, desil 1–5, D6-10, belum peringkat, nonaktif), header biru bold |
| `app/Filament/Resources/DtsenRekaps/Pages/ViewDtsenRekap.php` | Tambah `getHeaderActions()` dengan `Action::make('download_excel')` warna hijau |
| `tests/Feature/Dtsen/DtsenRekapTest.php` | +7 test (headings, mapping, sheet title, scope per rekap, akses semua role) |

Filename: `DTSEN_[Periode].xlsx` (contoh: `DTSEN_Mei_2026.xlsx`)

#### Download Excel Permohonan DTSEN — commit 3df0b5e

**Fitur:** Tombol "Download Excel" di halaman view Permohonan SUKET DTSEN (`/admin/dtsen-requests/{id}`), berisi daftar warga yang diajukan.

| File | Perubahan |
|---|---|
| `app/Exports/DtsenRequestExport.php` | Export baru: info permohonan (no. referensi, desa, keperluan, status) di baris 1–4, tabel warga (No, NIK, Nama, Tempat Lahir, Tanggal Lahir, Jenis Kelamin) mulai baris 6 |
| `app/Filament/Resources/DtsenRequests/Pages/ViewDtsenRequest.php` | Tambah `downloadExcelAction()` private method, disisipkan ke `getHeaderActions()` |
| `tests/Feature/Dtsen/DtsenRequestTest.php` | +7 test (headings, info rows, sheet title, mapping data, scope per request, filename, akses semua role) |

Filename: `DTSEN_[No-Referensi].xlsx` (contoh: `DTSEN_DTSEN-2026-06-0001.xlsx`)

**Total test sesi 9:** 262/262 pass (naik dari 248).

---

### Sesi 8 — LIVE PRODUCTION

#### Import PMKS/PSKS Mode Seluruh Kabupaten — commit 08c7a40

**Masalah sebelumnya:** Import CSV PMKS/PSKS mengharuskan memilih satu batch desa — tidak bisa import sekaligus untuk seluruh kabupaten.

**Perubahan:**

| File | Perubahan |
|---|---|
| `database/migrations/2026_06_12_000001_...` | `submission_batch_id` nullable, tambah kolom `import_mode` (enum) + `period_year` |
| `app/Models/PmksImport.php` | Tambah `import_mode`, `period_year` ke fillable, method `isKabupatenMode()` |
| `app/Models/PsksImport.php` | Sama dengan PmksImport |
| `app/Filament/Resources/PmksImports/Schemas/PmksImportForm.php` | Radio mode (per_desa/kabupaten) hanya untuk admin; show/hide batch select / period_year |
| `app/Filament/Resources/PsksImports/Schemas/PsksImportForm.php` | Sama |
| `app/Jobs/Pmks/PmksImportChunkJob.php` | Mode kabupaten: resolve village+batch per baris via `kode_desa`, cache in-memory |
| `app/Jobs/Psks/PsksImportChunkJob.php` | Sama |
| `app/Filament/Resources/PmksImports/Pages/ViewPmksImport.php` | Infolist handle null batch, template download smart per mode |
| `app/Filament/Resources/PsksImports/Pages/ViewPsksImport.php` | Sama |
| `app/Filament/Resources/PmksImports/Tables/PmksImportsTable.php` | Kolom Desa/Tahun pakai `getStateUsing`, filter mode import |
| `app/Filament/Resources/PsksImports/Tables/PsksImportsTable.php` | Sama |
| `app/Filament/Resources/PmksImports/Pages/CreatePmksImport.php` | `mutateFormDataBeforeCreate` handle mode, null-kan submission_batch_id jika kabupaten |
| `app/Filament/Resources/PsksImports/Pages/CreatePsksImport.php` | Sama |

**Format CSV mode Kabupaten:**

PMKS: `kode_desa;nik;nama;tgl_lahir;jenis_kelamin;kode_kategori;catatan;jenis_disabilitas`

PSKS: `kode_desa;kode_kategori;nik;nama;tgl_lahir;jenis_kelamin;tipe_lembaga;nomor_registrasi;catatan`

`kode_desa` = nilai kolom `code` di tabel `villages` (bukan nama desa).

**Cara kerja:**
- Admin pilih mode "Seluruh Kabupaten" → pilih tahun periode → upload CSV
- Sistem per-baris: cari `Village` by `code` → cari batch `draft/revised` untuk `village_id + period_year`
- Cache hasil lookup per `kode_desa` agar tidak query berulang
- Error jelas: desa tidak ditemukan / batch belum dibuat / batch bukan Draft/Direvisi

**Backward-compatible:** Mode `per_desa` (existing) tidak terpengaruh.

---

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
| Import PMKS/PSKS mode Seluruh Kabupaten | Tinggi | SELESAI sesi 8 |
| Download Excel Rekap DTSEN | Sedang | SELESAI sesi 9 |
| Download Excel Permohonan DTSEN | Sedang | SELESAI sesi 9 |
| Rekap Bansos PKH & Sembako per desa | Sedang | SELESAI sesi 10 |

---

## 12. KANDIDAT FITUR BERIKUTNYA

1. Filter tahun di dashboard widget (hardcode now()->year)
2. Notifikasi email saat batch diapprove/request revisi
3. Export PDF ringkasan PMKS/PSKS per kecamatan
4. ~~Import massal PMKS/PSKS dari template Excel~~ → **SELESAI sesi 6 (CSV)**
5. ~~Import mode Seluruh Kabupaten~~ → **SELESAI sesi 8**
6. ~~Rekap Bansos PKH & Sembako per desa~~ → **SELESAI sesi 10**

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

--- REKAP BANSOS (sesi 10) ---
app/Filament/Pages/RekapBansos.php         (halaman rekap PKH & Sembako per desa — sesi 10)
app/Exports/BansosRekapExport.php          (export Excel rekap bansos — sesi 10)
resources/views/filament/pages/rekap-bansos.blade.php  (blade view — sesi 10)

--- DOWNLOAD EXCEL DTSEN (sesi 9) ---
app/Exports/DtsenRekapExport.php           (export rekap DTSEN per desa — sesi 9)
app/Exports/DtsenRequestExport.php         (export daftar warga permohonan DTSEN — sesi 9)
app/Filament/Resources/DtsenRekaps/Pages/ViewDtsenRekap.php    (tombol download Excel — sesi 9)
app/Filament/Resources/DtsenRequests/Pages/ViewDtsenRequest.php (tombol download Excel — sesi 9)

--- IMPORT MODE KABUPATEN (sesi 8) ---
database/migrations/2026_06_12_000001_make_submission_batch_nullable_in_imports_tables.php
app/Jobs/Pmks/PmksImportChunkJob.php               (resolve village+batch per baris, cache in-memory)
app/Jobs/Psks/PsksImportChunkJob.php               (sama)
app/Filament/Resources/PmksImports/Schemas/PmksImportForm.php  (radio mode per_desa/kabupaten)
app/Filament/Resources/PsksImports/Schemas/PsksImportForm.php  (sama)

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
php artisan test                          # full suite (262 test)
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

Status saat ini: 262/262 test pass. Commit terakhir sesi 10: 8c1fd94 — live production.
Sesi 6, 7 & 8 sudah di-deploy ke pmks-app.
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
