# PUSKESOSGCT — Context untuk Claude

## Stack
- Laravel 13, Filament v4, PHP 8.3
- Server: Armbian STB HG680P, RAM 2GB
- Database: MariaDB
- Deployment: Cloudflare Zero Trust

## Struktur
- Production: /DATA/coding/laravel/projects/pmks-app (port 80, branch main)
- Development: /DATA/coding/laravel/projects/pmks-dev (port 8001, branch develop)
- GitHub: https://github.com/lucidlynk/pmks

## Database
- Production: pmks_app
- Development: pmks_dev
- MySQL user: root, password: pingwin119

## Role
- admin_dinsos: akses penuh
- verifikator: lihat & verifikasi batch
- operator_bidang: buat & edit data semua desa
- operator_desa: hanya desanya sendiri

## Alur batch
draft → submitted → verified → approved
rejected → (operator) perbaiki → draft
revision_requested → (operator) terima → revised → submitted

## Hal penting
- Section di Filament v4 dari Filament\Schemas\Components\Section
- Status PMKS/PSKS derive dari batch (tidak ada kolom status sendiri)
- PmksAgeRule baca dari kolom min_age/max_age di pmks_categories
- Notifikasi in-app via databaseNotifications Filament

## ⚠️ KONVENSI UI — WAJIB DIIKUTI
SEMUA tampilan UI HARUS menggunakan komponen Filament native. DILARANG membuat tampilan
di luar Filament (raw HTML table, custom Tailwind div independen, dll).

- Tabel data       → InteractsWithTable + Table Filament → render via `{{ $this->table }}`
- Statistik/info   → `<x-filament::section>` atau TextEntry di infolist
- Halaman baru     → extend Filament\Pages\Page, blade hanya wrapper minimal
- Form             → Filament form builder (Schema, TextInput, Select, dll)
- Export           → Maatwebsite Excel + Action di getHeaderActions()
- Query groupBy    → override getTableRecordKey() dengan key unik jika tidak ada id
- Navigasi         → getNavigationGroup(), $navigationIcon, $navigationSort

Alasan: konsistensi design system, CSS terjamin via Filament, tidak perlu build Vite terpisah.

## Workflow git
- Kerjakan di pmks-dev (develop branch)
- Test manual di browser port 8001
- Commit & push ke develop
- Merge ke main
- Pull di pmks-app & migrate
