# Panduan Import CSV — PMKS & PSKS

Dokumen ini menjelaskan cara membuat file CSV yang benar untuk fitur **Import PMKS** dan **Import PSKS** di aplikasi Sistem Informasi Sosial Kabupaten Buleleng.

---

## Daftar Isi

1. [Prasyarat Sebelum Import](#1-prasyarat-sebelum-import)
2. [Import PMKS](#2-import-pmks)
   - [Format Kolom](#21-format-kolom-pmks)
   - [Daftar Kode Kategori PMKS](#22-daftar-kode-kategori-pmks)
   - [Aturan Validasi PMKS](#23-aturan-validasi-pmks)
   - [Contoh File CSV PMKS](#24-contoh-file-csv-pmks)
   - [Kesalahan Umum PMKS](#25-kesalahan-umum-pmks)
3. [Import PSKS](#3-import-psks)
   - [Format Kolom](#31-format-kolom-psks)
   - [Daftar Kode Kategori PSKS](#32-daftar-kode-kategori-psks)
   - [Aturan Validasi PSKS](#33-aturan-validasi-psks)
   - [Contoh File CSV PSKS](#34-contoh-file-csv-psks)
   - [Kesalahan Umum PSKS](#35-kesalahan-umum-psks)
4. [Cara Membuat File CSV yang Benar](#4-cara-membuat-file-csv-yang-benar)
5. [Membaca Laporan Error](#5-membaca-laporan-error)
6. [Proses Ulang Import yang Gagal](#6-proses-ulang-import-yang-gagal)

---

## 1. Prasyarat Sebelum Import

Sebelum melakukan import, pastikan hal-hal berikut sudah terpenuhi:

1. **Batch Pengajuan sudah dibuat** — Import hanya bisa dilakukan ke batch dengan status **Draft** atau **Sudah Direvisi**. Buat batch terlebih dahulu melalui menu *Pengajuan PMKS & PSKS → Batch Pengajuan*.
2. **Akses pengguna** — Hanya Admin Dinsos dan Operator Bidang yang dapat melakukan import.
3. **Format file** — File harus berformat `.csv` dengan ukuran maksimal **10 MB**.
4. **Separator kolom** — Gunakan **titik koma (`;`)**, bukan koma.
5. **Encoding** — Simpan file dalam encoding **UTF-8**. Di Microsoft Excel: *File → Save As → pilih CSV UTF-8 (Comma delimited)*.

> **Catatan:** Satu file CSV digunakan untuk satu batch. Jika data berasal dari desa yang berbeda, buat file terpisah untuk masing-masing batch.

---

## 2. Import PMKS

### 2.1 Format Kolom PMKS

File CSV PMKS memiliki **7 kolom** dengan urutan sebagai berikut:

| No | Nama Kolom | Wajib | Keterangan |
|----|------------|-------|------------|
| 1 | `nik` | **Ya** | NIK 16 digit angka |
| 2 | `nama` | **Ya** | Nama lengkap penduduk |
| 3 | `tgl_lahir` | **Ya** | Tanggal lahir format `dd-mm-yyyy` |
| 4 | `jenis_kelamin` | **Ya** | `L` untuk Laki-laki, `P` untuk Perempuan |
| 5 | `kode_kategori` | **Ya** | Kode PMKS (lihat tabel di bawah) |
| 6 | `catatan` | Tidak | Catatan tambahan (boleh kosong) |
| 7 | `jenis_disabilitas` | Kondisional | Wajib hanya untuk **PMKS-05** dan **PMKS-09** |

**Baris pertama adalah header dan akan dilewati secara otomatis.**

Format header (harus persis seperti ini atau bisa diganti teks apapun, yang penting urutannya benar):
```
nik;nama;tgl_lahir;jenis_kelamin;kode_kategori;catatan;jenis_disabilitas
```

---

### 2.2 Daftar Kode Kategori PMKS

| Kode | Nama Kategori | Batasan Usia | Batasan Gender |
|------|--------------|--------------|----------------|
| PMKS-01 | ANAK BALITA TERLANTAR | 0–5 tahun | — |
| PMKS-02 | ANAK TERLANTAR | 6–18 tahun | — |
| PMKS-03 | ANAK YG BERHADAPAN DENGAN HUKUM | 6–18 tahun | — |
| PMKS-04 | ANAK JALANAN | 6–18 tahun | — |
| PMKS-05 | ANAK DENGAN KEDISABILITASAN ⚠️ | 6–18 tahun | — |
| PMKS-06 | ANAK YG MENJADI KORBAN TINDAK KEKERASAN ATAU DIPERLAKUKAN SALAH | 6–18 tahun | — |
| PMKS-07 | ANAK YG MEMERLUKAN PERLINDUNGAN KHUSUS | 6–18 tahun | — |
| PMKS-08 | LANJUT USIA TERLANTAR | 60 tahun ke atas | — |
| PMKS-09 | PENYANDANG DISABILITAS ⚠️ | — | — |
| PMKS-10 | TUNA SUSILA | — | — |
| PMKS-11 | GELANDANGAN | — | — |
| PMKS-12 | PENGEMIS | — | — |
| PMKS-13 | PEMULUNG | — | — |
| PMKS-14 | KELOMPOK MINORITAS | — | — |
| PMKS-15 | BEKAS WARGA BINAAN LEMBAGA PEMASYARAKATAN (BWBLP) | — | — |
| PMKS-16 | ORANG DENGAN HIV/AIDS | — | — |
| PMKS-17 | KORBAN PENYALAHGUNAAN NAPZA | — | — |
| PMKS-18 | KORBAN TRAFFICKING | — | — |
| PMKS-19 | KORBAN TINDAK KEKERASAN ATAU YANG DIPERLAKUKAN SALAH | — | — |
| PMKS-20 | PEKERJA MIGRAN BERMASALAH SOSIAL | — | — |
| PMKS-21 | KORBAN BENCANA ALAM | — | — |
| PMKS-22 | KORBAN BENCANA SOSIAL | — | — |
| PMKS-23 | PEREMPUAN RAWAN SOSIAL EKONOMI | — | **Perempuan saja** |
| PMKS-24 | FAKIR MISKIN | — | — |
| PMKS-25 | KELUARGA BERMASALAH SOSIAL PSIKOLOGIS | — | — |
| PMKS-26 | KOMUNITAS ADAT TERPENCIL | — | — |

> ⚠️ **PMKS-05 dan PMKS-09** wajib mengisi kolom `jenis_disabilitas`.

---

### 2.3 Aturan Validasi PMKS

#### NIK
- Harus tepat **16 digit angka**.
- Tidak boleh mengandung huruf, spasi, atau tanda baca.
- ✅ Benar: `5171234567890001`
- ❌ Salah: `517-1234-5678-9001`, `51712345678900`, `517123456789000A`

#### Tanggal Lahir
- Format wajib: **`dd-mm-yyyy`** (hari-bulan-tahun, masing-masing 2 digit, tahun 4 digit).
- Gunakan tanda minus (`-`) sebagai pemisah, bukan garis miring (`/`).
- ✅ Benar: `05-08-1985`, `31-12-2000`
- ❌ Salah: `5-8-1985`, `08/05/1985`, `1985-08-05`, `05-Agustus-1985`

#### Jenis Kelamin
- Isi dengan huruf kapital `L` (Laki-laki) atau `P` (Perempuan).
- ✅ Benar: `L`, `P`
- ❌ Salah: `l`, `p`, `Laki-laki`, `Perempuan`, `1`, `2`

#### Kode Kategori
- Harus sesuai dengan daftar di atas, huruf kapital dengan tanda minus.
- ✅ Benar: `PMKS-24`, `PMKS-08`
- ❌ Salah: `pmks-24`, `PMKS24`, `24`, `Fakir Miskin`

#### Batasan Usia
- Sistem menghitung usia dari `tgl_lahir` secara otomatis.
- Jika usia tidak sesuai kategori, baris tersebut **akan gagal** dan dicatat di laporan error.
- Contoh: NIK dengan usia 70 tahun tidak bisa didaftarkan ke PMKS-02 (anak 6–18 tahun).

#### Batasan Gender
- Kategori **PMKS-23** hanya untuk **Perempuan** (`P`).
- Jika `jenis_kelamin` tidak sesuai, baris gagal.

#### Kolom `jenis_disabilitas` (Khusus PMKS-05 dan PMKS-09)
- **Wajib diisi** untuk PMKS-05 dan PMKS-09.
- Pilihan nilai: `fisik`, `intelektual`, `mental`, `sensorik`.
- Boleh lebih dari satu, pisahkan dengan **tanda pipa** (`|`).
- ✅ Benar: `fisik`, `fisik|mental`, `fisik|intelektual|sensorik`
- ❌ Salah: `fisik,mental` (gunakan `|` bukan `,`), `Fisik` (gunakan huruf kecil), `cacat` (bukan nilai yang valid)

#### Penduduk Tidak Ditemukan di Sistem
- Jika NIK **belum terdaftar**, sistem akan **otomatis membuat data penduduk baru** menggunakan informasi dari CSV (NIK, nama, tgl_lahir, jenis_kelamin, dan desa dari batch).
- Pastikan `nama`, `tgl_lahir`, dan `jenis_kelamin` diisi dengan benar agar data penduduk yang dibuat akurat.

#### Duplikat dalam Batch
- Satu penduduk **tidak boleh** memiliki kategori yang sama dua kali dalam satu batch.
- Satu penduduk **boleh** memiliki beberapa kategori berbeda dalam satu batch (misalnya PMKS-09 dan PMKS-24).
- Baris duplikat akan **dilewati** (tercatat di laporan error, tidak menggagalkan baris lain).

---

### 2.4 Contoh File CSV PMKS

```
nik;nama;tgl_lahir;jenis_kelamin;kode_kategori;catatan;jenis_disabilitas
5171234567890001;I WAYAN SUKA;15-08-1985;L;PMKS-24;Kepala keluarga tidak bekerja;
5171234567890002;NI MADE SARI;20-03-1990;P;PMKS-23;;
5171234567890003;I KOMANG BUDI;10-05-2008;L;PMKS-05;Disabilitas sejak lahir;fisik|mental
5171234567890004;NI KETUT LANJUT;05-01-1958;P;PMKS-08;Tinggal sendiri;
5171234567890005;I MADE CACAT;12-07-1975;L;PMKS-09;Kecelakaan kerja 2010;sensorik
5171234567890006;NI WAYAN MISKIN;30-11-2005;P;PMKS-02;;
5171234567890001;I WAYAN SUKA;15-08-1985;L;PMKS-11;Sering terlihat di jalanan;
```

> Baris terakhir adalah penduduk yang sama (NIK `5171234567890001`) dengan **kategori berbeda** (PMKS-11) — ini **diizinkan**.

---

### 2.5 Kesalahan Umum PMKS

| Kesalahan | Pesan Error yang Muncul | Solusi |
|-----------|------------------------|--------|
| NIK kurang dari 16 digit | `NIK tidak valid — '...' (harus 16 digit angka)` | Pastikan NIK tepat 16 digit |
| Format tanggal salah | `Format tanggal lahir salah '...' — gunakan dd-mm-yyyy` | Ubah format ke dd-mm-yyyy |
| Kode kategori salah ketik | `Kode kategori '...' tidak ditemukan atau tidak aktif` | Cek ejaan kode dari tabel di atas |
| Usia tidak sesuai kategori | `Usia tidak sesuai — kategori ... untuk ..., penduduk ini berusia ... tahun` | Gunakan kode kategori yang sesuai usia |
| Gender tidak sesuai kategori | `Jenis kelamin tidak sesuai — kategori PMKS-23 hanya untuk Perempuan` | Periksa jenis kelamin atau kategori |
| Lupa isi jenis_disabilitas | `Jenis disabilitas wajib diisi untuk kategori PMKS-05/PMKS-09` | Isi kolom ke-7 dengan nilai yang valid |
| Nilai disabilitas salah | `Jenis disabilitas tidak valid: ... (pilihan: fisik, intelektual, mental, sensorik)` | Gunakan nilai yang tersedia, huruf kecil |
| Data sudah ada di batch | `Sudah terdaftar dengan kategori ... di batch ini — dilewati` | Hapus baris duplikat dari CSV |

---

## 3. Import PSKS

### 3.1 Format Kolom PSKS

File CSV PSKS memiliki **8 kolom**. Kolom yang diisi bergantung pada jenis subjek (individu atau lembaga) yang ditentukan otomatis dari kode kategori:

| No | Nama Kolom | Untuk Individu (PSKS-J-*) | Untuk Lembaga (PSKS-L-*) |
|----|------------|--------------------------|--------------------------|
| 1 | `kode_kategori` | **Wajib** | **Wajib** |
| 2 | `nik` | **Wajib** | Kosongkan |
| 3 | `nama` | **Wajib** (nama orang) | **Wajib** (nama lembaga) |
| 4 | `tgl_lahir` | Dianjurkan (dd-mm-yyyy) | Kosongkan |
| 5 | `jenis_kelamin` | Dianjurkan (L/P) | Kosongkan |
| 6 | `tipe_lembaga` | Kosongkan | **Wajib** |
| 7 | `nomor_registrasi` | Kosongkan | Tidak wajib |
| 8 | `catatan` | Tidak wajib | Tidak wajib |

Format header:
```
kode_kategori;nik;nama;tgl_lahir;jenis_kelamin;tipe_lembaga;nomor_registrasi;catatan
```

> **Satu file CSV dapat memuat baris individu dan lembaga sekaligus.** Sistem mendeteksi jenis subjek secara otomatis dari kode kategori.

---

### 3.2 Daftar Kode Kategori PSKS

#### Kategori Individu (PSKS-J-*) — subject_type: person

| Kode | Nama Kategori |
|------|--------------|
| PSKS-J-01 | Pekerja Sosial Masyarakat (PSM) |
| PSKS-J-02 | Tenaga Kesejahteraan Sosial Kecamatan (TKSK) |
| PSKS-J-03 | Relawan Sosial |
| PSKS-J-04 | Penyuluh Sosial |
| PSKS-J-05 | Taruna Siaga Bencana (TAGANA) |
| PSKS-J-06 | Wanita Pemimpin Kesejahteraan Sosial |

> Kategori PSKS-J-* **tidak memiliki** batasan usia atau gender.

#### Kategori Lembaga (PSKS-L-*) — subject_type: institution

| Kode | Nama Kategori |
|------|--------------|
| PSKS-L-01 | Karang Taruna |
| PSKS-L-02 | Lembaga Kesejahteraan Sosial (LKS) |
| PSKS-L-03 | Lembaga Konsultasi Kesejahteraan Keluarga (LK3) |
| PSKS-L-04 | Lembaga Kesejahteraan Sosial Anak (LKSA) |
| PSKS-L-05 | PKK |
| PSKS-L-06 | Organisasi Sosial (Orsos) |

---

### 3.3 Aturan Validasi PSKS

#### Untuk Baris Individu (PSKS-J-*)

- **NIK** wajib dan harus 16 digit angka (aturan sama seperti PMKS).
- **Nama** wajib diisi, digunakan untuk membuat penduduk baru jika NIK belum ada di sistem.
- **Tanggal lahir** dan **jenis kelamin** dianjurkan diisi agar data penduduk baru akurat. Jika penduduk sudah ada di sistem, kolom ini diabaikan.
- **Tidak ada validasi usia atau gender** untuk PSKS.

#### Untuk Baris Lembaga (PSKS-L-*)

- **Nama lembaga** wajib diisi. Sistem mencari lembaga berdasarkan nama (tidak membedakan huruf besar/kecil) dan desa dari batch. Jika tidak ditemukan, lembaga baru dibuat otomatis.
- **Tipe lembaga** wajib diisi. Pilihan nilai:

  | Nilai | Keterangan |
  |-------|------------|
  | `karang_taruna` | Karang Taruna |
  | `pkk` | PKK |
  | `lks` | Lembaga Kesejahteraan Sosial |
  | `lainnya` | Tipe lembaga lainnya |

  > Gunakan huruf kecil dan garis bawah, bukan spasi.

- **Nomor registrasi** opsional, diisi jika lembaga baru akan dibuat.

#### Duplikat dalam Batch
- Satu individu/lembaga tidak boleh mendaftar dengan kategori yang sama dua kali dalam satu batch.
- Individu boleh didaftarkan ke beberapa kategori berbeda dalam satu batch.

---

### 3.4 Contoh File CSV PSKS

```
kode_kategori;nik;nama;tgl_lahir;jenis_kelamin;tipe_lembaga;nomor_registrasi;catatan
PSKS-J-01;5171234567890010;I WAYAN RELAWAN;10-05-1990;L;;;PSM aktif sejak 2020
PSKS-J-02;5171234567890011;NI MADE TENAGA;20-03-1988;P;;;TKSK wilayah utara
PSKS-J-03;5171234567890012;I KOMANG SOSIAL;15-07-1995;L;;;
PSKS-J-06;5171234567890013;NI KETUT PIMPIN;08-11-1982;P;;;Ketua PKK desa
PSKS-L-01;;Karang Taruna Bhuana Utama;;;karang_taruna;KT-001/2020;Aktif sejak 2015
PSKS-L-05;;PKK Desa Banyuning;;;pkk;;Periode 2024-2029
PSKS-L-02;;LKS Cahaya Bali;;;lks;LKS-012/2019;
```

> Perhatikan baris lembaga: kolom `nik`, `tgl_lahir`, `jenis_kelamin` **dikosongkan** (tetap ada titik koma pemisahnya).

---

### 3.5 Kesalahan Umum PSKS

| Kesalahan | Pesan Error yang Muncul | Solusi |
|-----------|------------------------|--------|
| NIK tidak valid (baris individu) | `NIK tidak valid — '...' (harus 16 digit angka)` | Perbaiki NIK menjadi 16 digit |
| Nama kosong | `Nama tidak boleh kosong` | Isi kolom nama |
| Format tanggal salah | `Format tanggal lahir salah '...' — gunakan dd-mm-yyyy` | Ubah format ke dd-mm-yyyy |
| Kode kategori tidak dikenal | `Kode kategori '...' tidak ditemukan atau tidak aktif` | Cek tabel kode di atas |
| Tipe lembaga salah | `Tipe lembaga tidak valid '...' (pilihan: karang_taruna, pkk, lks, lainnya)` | Gunakan salah satu nilai yang tersedia |
| Nama lembaga kosong (baris PSKS-L-*) | `Nama lembaga tidak boleh kosong` | Isi kolom nama dengan nama lembaga |
| Duplikat dalam batch | `Sudah terdaftar dengan kategori ... di batch ini — dilewati` | Hapus baris duplikat |

---

## 4. Cara Membuat File CSV yang Benar

### Menggunakan Microsoft Excel

1. Buka Excel, buat spreadsheet baru.
2. Baris pertama: ketik header kolom sesuai format (nik, nama, dst).
3. Mulai baris kedua: isi data per baris.
4. Pastikan kolom NIK diformat sebagai **Teks** agar angka nol di depan tidak hilang:
   - Klik kanan kolom NIK → *Format Cells* → pilih **Text** → OK
   - Kemudian isi NIK.
5. Simpan: **File → Save As → pilih format `CSV UTF-8 (Comma delimited) (*.csv)`**.
6. Jika Excel bertanya apakah ingin menyimpan dalam format ini, pilih **Keep Current Format**.

> ⚠️ **Perhatian:** Excel default menggunakan koma sebagai separator. Aplikasi ini menggunakan **titik koma (`;`)**. Setelah menyimpan, buka file dengan Notepad dan periksa apakah separator sudah benar, atau gunakan cara di bawah.

### Menggunakan Google Sheets (Disarankan)

1. Buka [sheets.google.com](https://sheets.google.com), buat spreadsheet baru.
2. Isi data sesuai format.
3. Untuk kolom NIK: klik kanan header kolom → *Format cells* → pilih **Plain text**.
4. Download: **File → Download → Comma Separated Values (.csv)**.
5. Buka file hasil download dengan **Notepad** (Windows) atau **TextEdit** (Mac), lalu:
   - Tekan `Ctrl+H` (Find & Replace)
   - Find: `,` (koma)
   - Replace: `;` (titik koma)
   - Replace All
6. Simpan file.

### Menggunakan Notepad / Text Editor

Cara paling langsung dan tidak berisiko format:

```
nik;nama;tgl_lahir;jenis_kelamin;kode_kategori;catatan;jenis_disabilitas
5171234567890001;I WAYAN SUKA;15-08-1985;L;PMKS-24;;
5171234567890002;NI MADE SARI;20-03-1990;P;PMKS-23;;
```

Simpan sebagai file `.csv` dengan encoding **UTF-8**.

---

## 5. Membaca Laporan Error

Setelah proses selesai, buka halaman detail import. Jika ada baris yang gagal, akan muncul bagian **Detail Error** berisi daftar pesan seperti:

```
Baris 3 (NIK: 5171234567890003): Usia tidak sesuai — kategori PMKS-02 untuk 6-18 tahun, penduduk ini berusia 25 tahun
Baris 7 (NIK: 5171234567890007): Jenis disabilitas wajib diisi untuk kategori PMKS-09
Baris 12 (Lembaga: PKK Desa X): Tipe lembaga tidak valid 'PKK' (pilihan: karang_taruna, pkk, lks, lainnya)
```

**Cara membaca:**
- `Baris N` — nomor baris di file CSV (baris 1 adalah header, data dimulai dari baris 2).
- Identifier `(NIK: ...)` atau `(Lembaga: ...)` — untuk memudahkan pencarian di file CSV.
- Pesan setelah titik dua — penjelasan penyebab gagal dan cara memperbaiki.

**Baris yang berhasil tetap tersimpan.** Hanya baris yang gagal yang perlu diperbaiki.

---

## 6. Proses Ulang Import yang Gagal

Jika sebagian baris gagal:

1. Buka halaman detail import.
2. Catat semua baris yang error dari **Detail Error**.
3. Buka file CSV asli, perbaiki baris yang bermasalah.
4. **Hapus baris yang sudah berhasil** dari file CSV agar tidak terjadi duplikat.
5. Buat import baru dengan file CSV yang sudah diperbaiki ke batch yang sama.

Alternatif jika **seluruh import gagal** (misalnya file tidak terbaca):

1. Buka halaman detail import.
2. Klik tombol **Proses Ulang** — sistem akan memproses ulang dari file yang sama.
3. Catatan: tombol Proses Ulang hanya muncul jika status import **Gagal** atau jika sudah lebih dari 30 menit dalam status **Sedang Diproses**.

---

## Ringkasan Perbedaan PMKS vs PSKS

| Aspek | Import PMKS | Import PSKS |
|-------|-------------|-------------|
| Jumlah kolom | 7 | 8 |
| Jenis subjek | Penduduk saja | Penduduk (PSKS-J-*) atau Lembaga (PSKS-L-*) |
| Validasi usia | Ya (tergantung kategori) | Tidak |
| Validasi gender | Ya (PMKS-23 perempuan saja) | Tidak |
| Kolom disabilitas | Wajib untuk PMKS-05 & PMKS-09 | Tidak ada |
| Jumlah kategori | 26 kategori (PMKS-01 s.d. PMKS-26) | 12 kategori (6 individu + 6 lembaga) |
| Auto-create data baru | Buat Resident baru jika NIK belum ada | Buat Resident atau Institution baru jika belum ada |

---

*Dokumen ini berlaku untuk versi aplikasi sesi 6 (Juni 2026).*
