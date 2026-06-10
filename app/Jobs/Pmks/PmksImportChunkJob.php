<?php

namespace App\Jobs\Pmks;

use App\Models\PmksCategory;
use App\Models\PmksImport;
use App\Models\PmksSubmission;
use App\Models\Resident;
use Carbon\Carbon;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PmksImportChunkJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;
    public int $tries   = 3;

    private const DISABILITY_CODES = ['PMKS-05', 'PMKS-09'];
    private const VALID_DISABILITY_TYPES = ['fisik', 'intelektual', 'mental', 'sensorik'];

    public function __construct(
        public readonly int   $importId,
        public readonly array $rows,
        public readonly int   $startRow = 2,
    ) {}

    public function handle(): void
    {
        if ($this->batch()?->cancelled()) return;

        $import    = PmksImport::findOrFail($this->importId);
        $batch     = $import->submissionBatch;
        $villageId = $batch->village_id;
        $importedBy = $import->created_by;
        $batchId   = $import->submission_batch_id;

        $success = 0;
        $failed  = 0;
        $errors  = [];

        foreach ($this->rows as $index => $row) {
            $rowNum = $this->startRow + $index;

            try {
                // Pastikan minimal 5 kolom (nik, nama, tgl_lahir, jenis_kelamin, kode_kategori)
                if (count($row) < 5) {
                    $failed++;
                    $errors[] = "Baris {$rowNum}: Format tidak lengkap (minimal 5 kolom)";
                    continue;
                }

                $nik            = trim($row[0] ?? '');
                $nama           = trim($row[1] ?? '');
                $tglLahir       = trim($row[2] ?? '');
                $jenisKelamin   = strtoupper(trim($row[3] ?? ''));
                $kodeKategori   = strtoupper(trim($row[4] ?? ''));
                $catatan        = trim($row[5] ?? '') ?: null;
                $rawDisabilitas = strtolower(trim($row[6] ?? ''));

                // ── Validasi NIK ──────────────────────────────────────────
                if (strlen($nik) !== 16 || !ctype_digit($nik)) {
                    $failed++;
                    $errors[] = "Baris {$rowNum}: NIK tidak valid — '{$nik}' (harus 16 digit angka)";
                    continue;
                }

                // ── Validasi nama ─────────────────────────────────────────
                if (empty($nama)) {
                    $failed++;
                    $errors[] = "Baris {$rowNum} (NIK: {$nik}): Nama tidak boleh kosong";
                    continue;
                }

                // ── Validasi jenis kelamin ────────────────────────────────
                if (!in_array($jenisKelamin, ['L', 'P'])) {
                    $failed++;
                    $errors[] = "Baris {$rowNum} (NIK: {$nik}): Jenis kelamin harus L atau P, ditemukan '{$jenisKelamin}'";
                    continue;
                }

                // ── Validasi tanggal lahir ────────────────────────────────
                $birthDate = null;
                if (!empty($tglLahir)) {
                    try {
                        $birthDate = Carbon::createFromFormat('d-m-Y', $tglLahir)->startOfDay();
                    } catch (\Exception) {
                        $failed++;
                        $errors[] = "Baris {$rowNum} (NIK: {$nik}): Format tanggal lahir salah '{$tglLahir}' — gunakan dd-mm-yyyy";
                        continue;
                    }
                }

                // ── Validasi kode kategori ────────────────────────────────
                $category = PmksCategory::active()->where('code', $kodeKategori)->first();
                if (!$category) {
                    $failed++;
                    $errors[] = "Baris {$rowNum} (NIK: {$nik}): Kode kategori '{$kodeKategori}' tidak ditemukan atau tidak aktif";
                    continue;
                }

                // ── Validasi jenis disabilitas ────────────────────────────
                $disabilityArr = null;
                if (in_array($category->code, self::DISABILITY_CODES)) {
                    if (empty($rawDisabilitas)) {
                        $failed++;
                        $errors[] = "Baris {$rowNum} (NIK: {$nik}): Jenis disabilitas wajib diisi untuk kategori {$category->name}";
                        continue;
                    }

                    $parsed  = array_values(array_filter(array_map('trim', explode('|', $rawDisabilitas))));
                    $invalid = array_diff($parsed, self::VALID_DISABILITY_TYPES);

                    if (!empty($invalid)) {
                        $failed++;
                        $errors[] = "Baris {$rowNum} (NIK: {$nik}): Jenis disabilitas tidak valid: " . implode(', ', $invalid)
                            . " (pilihan: fisik, intelektual, mental, sensorik)";
                        continue;
                    }

                    $disabilityArr = $parsed;
                }

                // ── Cari atau buat Resident ───────────────────────────────
                $resident = Resident::where('nik', $nik)->first();

                if (!$resident) {
                    $resident = Resident::create([
                        'village_id'  => $villageId,
                        'nik'         => $nik,
                        'name'        => $nama,
                        'birth_place' => '-',
                        'birth_date'  => $birthDate,
                        'gender'      => $jenisKelamin,
                        'is_active'   => true,
                    ]);
                }

                // ── Validasi usia ─────────────────────────────────────────
                if ($category->hasAgeRestriction() && $resident->birth_date) {
                    $age = $resident->birth_date->age;

                    $tooYoung = $category->min_age !== null && $age < $category->min_age;
                    $tooOld   = $category->max_age !== null && $age > $category->max_age;

                    if ($tooYoung || $tooOld) {
                        $failed++;
                        $errors[] = "Baris {$rowNum} (NIK: {$nik}): Usia tidak sesuai — kategori {$category->name} untuk {$category->ageLabel()}, penduduk ini berusia {$age} tahun";
                        continue;
                    }
                }

                // ── Validasi gender ───────────────────────────────────────
                if ($category->hasGenderRestriction() && $resident->gender !== $category->gender_restriction) {
                    $genderLabel = $resident->gender === 'L' ? 'Laki-laki' : 'Perempuan';
                    $failed++;
                    $errors[] = "Baris {$rowNum} (NIK: {$nik}): Jenis kelamin tidak sesuai — kategori {$category->name} hanya untuk {$category->genderLabel()}, penduduk ini {$genderLabel}";
                    continue;
                }

                // ── Cek duplikat dalam batch ──────────────────────────────
                $exists = PmksSubmission::where('batch_id', $batchId)
                    ->where('resident_id', $resident->id)
                    ->where('category_id', $category->id)
                    ->exists();

                if ($exists) {
                    $failed++;
                    $errors[] = "Baris {$rowNum} (NIK: {$nik}): Sudah terdaftar dengan kategori {$category->name} di batch ini — dilewati";
                    continue;
                }

                // ── Simpan submission ─────────────────────────────────────
                PmksSubmission::create([
                    'batch_id'         => $batchId,
                    'village_id'       => $villageId,
                    'resident_id'      => $resident->id,
                    'category_id'      => $category->id,
                    'notes'            => $catatan,
                    'disability_types' => $disabilityArr,
                    'input_by'         => $importedBy,
                ]);

                $success++;

            } catch (\Throwable $e) {
                $failed++;
                $errors[] = "Baris {$rowNum}: Error tidak terduga — " . $e->getMessage();
                Log::warning('PmksImportChunkJob row error', [
                    'import_id' => $this->importId,
                    'row'       => $rowNum,
                    'error'     => $e->getMessage(),
                ]);
            }
        }

        // ── Update statistik import ───────────────────────────────────────
        DB::table('pmks_imports')
            ->where('id', $this->importId)
            ->update([
                'success_rows' => DB::raw("success_rows + {$success}"),
                'failed_rows'  => DB::raw("failed_rows + {$failed}"),
            ]);

        if (!empty($errors)) {
            $existing = $import->fresh()->error_summary ?? [];
            $import->update([
                'error_summary' => array_merge($existing, $errors),
            ]);
        }
    }
}
