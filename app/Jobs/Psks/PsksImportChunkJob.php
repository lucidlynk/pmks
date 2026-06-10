<?php

namespace App\Jobs\Psks;

use App\Models\Institution;
use App\Models\PsksCategory;
use App\Models\PsksImport;
use App\Models\PsksSubmission;
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

class PsksImportChunkJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;
    public int $tries   = 3;

    private const VALID_INSTITUTION_TYPES = [
        'karang_taruna', 'pkk', 'lks', 'lainnya',
    ];

    public function __construct(
        public readonly int   $importId,
        public readonly array $rows,
        public readonly int   $startRow = 2,
    ) {}

    public function handle(): void
    {
        if ($this->batch()?->cancelled()) return;

        $import     = PsksImport::findOrFail($this->importId);
        $batch      = $import->submissionBatch;
        $villageId  = $batch->village_id;
        $importedBy = $import->created_by;
        $batchId    = $import->submission_batch_id;

        $success = 0;
        $failed  = 0;
        $errors  = [];

        foreach ($this->rows as $index => $row) {
            $rowNum = $this->startRow + $index;

            try {
                if (count($row) < 4) {
                    $failed++;
                    $errors[] = "Baris {$rowNum}: Format tidak lengkap (minimal 4 kolom)";
                    continue;
                }

                $kodeKategori = strtoupper(trim($row[0] ?? ''));
                $nik          = trim($row[1] ?? '');
                $nama         = trim($row[2] ?? '');
                $tglLahir     = trim($row[3] ?? '');
                $jenisKelamin = strtoupper(trim($row[4] ?? ''));
                $tipeLembaga  = strtolower(trim($row[5] ?? ''));
                $noReg        = trim($row[6] ?? '') ?: null;
                $catatan      = trim($row[7] ?? '') ?: null;

                // ── Validasi kode kategori ────────────────────────────────
                $category = PsksCategory::active()->where('code', $kodeKategori)->first();
                if (!$category) {
                    $failed++;
                    $errors[] = "Baris {$rowNum}: Kode kategori '{$kodeKategori}' tidak ditemukan atau tidak aktif";
                    continue;
                }

                // ── Proses berdasarkan subject_type ───────────────────────
                if ($category->subject_type === 'person') {
                    [$subjectType, $subject, $errMsg] = $this->resolveResident(
                        $rowNum, $nik, $nama, $tglLahir, $jenisKelamin, $villageId
                    );
                } else {
                    [$subjectType, $subject, $errMsg] = $this->resolveInstitution(
                        $rowNum, $nama, $tipeLembaga, $noReg, $villageId
                    );
                }

                if ($errMsg !== null) {
                    $failed++;
                    $errors[] = $errMsg;
                    continue;
                }

                // ── Cek duplikat dalam batch ──────────────────────────────
                $exists = PsksSubmission::where('batch_id', $batchId)
                    ->where('subject_type', $subjectType)
                    ->where('subject_id', $subject->id)
                    ->where('category_id', $category->id)
                    ->exists();

                if ($exists) {
                    $identifier = $subjectType === 'person' ? "NIK: {$nik}" : "Lembaga: {$nama}";
                    $failed++;
                    $errors[] = "Baris {$rowNum} ({$identifier}): Sudah terdaftar dengan kategori {$category->name} di batch ini — dilewati";
                    continue;
                }

                // ── Simpan submission ─────────────────────────────────────
                PsksSubmission::create([
                    'batch_id'     => $batchId,
                    'village_id'   => $villageId,
                    'category_id'  => $category->id,
                    'subject_type' => $subjectType,
                    'subject_id'   => $subject->id,
                    'notes'        => $catatan,
                    'input_by'     => $importedBy,
                ]);

                $success++;

            } catch (\Throwable $e) {
                $failed++;
                $errors[] = "Baris {$rowNum}: Error tidak terduga — " . $e->getMessage();
                Log::warning('PsksImportChunkJob row error', [
                    'import_id' => $this->importId,
                    'row'       => $rowNum,
                    'error'     => $e->getMessage(),
                ]);
            }
        }

        // ── Update statistik import ───────────────────────────────────────
        DB::table('psks_imports')
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

    // ── Resolve Resident (person) ─────────────────────────────────────────

    private function resolveResident(
        int $rowNum, string $nik, string $nama,
        string $tglLahir, string $jenisKelamin, int $villageId
    ): array {
        if (strlen($nik) !== 16 || !ctype_digit($nik)) {
            return ['person', null, "Baris {$rowNum}: NIK tidak valid — '{$nik}' (harus 16 digit angka)"];
        }

        if (empty($nama)) {
            return ['person', null, "Baris {$rowNum} (NIK: {$nik}): Nama tidak boleh kosong"];
        }

        $birthDate = null;
        if (!empty($tglLahir)) {
            try {
                $birthDate = Carbon::createFromFormat('d-m-Y', $tglLahir)->startOfDay();
            } catch (\Exception) {
                return ['person', null, "Baris {$rowNum} (NIK: {$nik}): Format tanggal lahir salah '{$tglLahir}' — gunakan dd-mm-yyyy"];
            }
        }

        if (!empty($jenisKelamin) && !in_array($jenisKelamin, ['L', 'P'])) {
            return ['person', null, "Baris {$rowNum} (NIK: {$nik}): Jenis kelamin harus L atau P, ditemukan '{$jenisKelamin}'"];
        }

        $resident = Resident::where('nik', $nik)->first();

        if (!$resident) {
            $resident = Resident::create([
                'village_id'  => $villageId,
                'nik'         => $nik,
                'name'        => $nama,
                'birth_place' => '-',
                'birth_date'  => $birthDate,
                'gender'      => $jenisKelamin ?: null,
                'is_active'   => true,
            ]);
        }

        return ['person', $resident, null];
    }

    // ── Resolve Institution (lembaga) ─────────────────────────────────────

    private function resolveInstitution(
        int $rowNum, string $nama, string $tipeLembaga,
        ?string $noReg, int $villageId
    ): array {
        if (empty($nama)) {
            return ['institution', null, "Baris {$rowNum}: Nama lembaga tidak boleh kosong"];
        }

        if (!in_array($tipeLembaga, self::VALID_INSTITUTION_TYPES)) {
            return ['institution', null,
                "Baris {$rowNum} (Lembaga: {$nama}): Tipe lembaga tidak valid '{$tipeLembaga}' " .
                '(pilihan: karang_taruna, pkk, lks, lainnya)'
            ];
        }

        // Cari lembaga by nama + village (case-insensitive)
        $institution = Institution::where('village_id', $villageId)
            ->whereRaw('LOWER(name) = ?', [strtolower($nama)])
            ->first();

        if (!$institution) {
            $institution = Institution::create([
                'village_id'          => $villageId,
                'name'                => $nama,
                'type'                => $tipeLembaga,
                'registration_number' => $noReg,
                'is_active'           => true,
            ]);
        }

        return ['institution', $institution, null];
    }
}
