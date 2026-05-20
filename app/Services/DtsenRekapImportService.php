<?php

namespace App\Services;

use App\Models\DtsenRekap;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DtsenRekapImportService
{
    /**
     * Parse file CSV/Excel yang sudah diupload dan simpan ke tabel detail.
     *
     * @param DtsenRekap $rekap Record induk yang sudah tersimpan
     * @return array{success: bool, message: string, rows_imported: int}
     */
    public function import(DtsenRekap $rekap): array
    {
        $filePath = Storage::disk('local')->path($rekap->file_path);

        if (! file_exists($filePath)) {
            return [
                'success'       => false,
                'message'       => 'File tidak ditemukan di storage.',
                'rows_imported' => 0,
            ];
        }

        try {
            $rows = $this->parseFile($filePath);

            if (empty($rows)) {
                return [
                    'success'       => false,
                    'message'       => 'File kosong atau format tidak dikenali.',
                    'rows_imported' => 0,
                ];
            }

            DB::transaction(function () use ($rekap, $rows): void {
                // Hapus detail lama jika ada (untuk re-import)
                $rekap->details()->delete();

                // Batch insert untuk performa
                foreach (array_chunk($rows, 100) as $chunk) {
                    $rekap->details()->createMany($chunk);
                }
            });

            return [
                'success'       => true,
                'message'       => 'Import berhasil.',
                'rows_imported' => count($rows),
            ];
        } catch (\Exception $e) {
            Log::error('DtsenRekapImportService error', [
                'rekap_id' => $rekap->id,
                'error'    => $e->getMessage(),
                'trace'    => $e->getTraceAsString(),
            ]);

            return [
                'success'       => false,
                'message'       => 'Gagal import: ' . $e->getMessage(),
                'rows_imported' => 0,
            ];
        }
    }

    /**
     * Tentukan metode parse berdasarkan ekstensi file.
     */
    protected function parseFile(string $filePath): array
    {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        if (in_array($extension, ['csv', 'txt'])) {
            return $this->parseCsv($filePath);
        }

        if (in_array($extension, ['xlsx', 'xls'])) {
            return $this->parseExcel($filePath);
        }

        return [];
    }

    /**
     * Parse file CSV dengan auto-detect separator (semicolon/comma).
     */
    protected function parseCsv(string $filePath): array
    {
        $rows   = [];
        $handle = fopen($filePath, 'r');

        if ($handle === false) {
            return [];
        }

        $firstLine = fgets($handle);
        rewind($handle);

        $separator = (substr_count($firstLine, ';') > substr_count($firstLine, ','))
            ? ';'
            : ',';

        $lineNumber = 0;
        $hasHeader  = $this->detectHeader($firstLine, $separator);

        while (($line = fgetcsv($handle, 0, $separator)) !== false) {
            $lineNumber++;

            if ($lineNumber === 1 && $hasHeader) {
                continue;
            }

            if (empty(array_filter($line))) {
                continue;
            }

            if (count($line) < 20) {
                continue;
            }

            $rows[] = $this->mapRowToColumns($line);
        }

        fclose($handle);

        return $rows;
    }

    /**
     * Parse file Excel menggunakan PhpSpreadsheet.
     */
    protected function parseExcel(string $filePath): array
    {
        $rows = [];

        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
            $worksheet   = $spreadsheet->getActiveSheet();
            $highestRow  = $worksheet->getHighestRow();

            $startRow   = 1;
            $firstCell  = $worksheet->getCell('A1')->getValue();
            if ($this->isHeaderValue((string) $firstCell)) {
                $startRow = 2;
            }

            for ($row = $startRow; $row <= $highestRow; $row++) {
                $line = [];
                for ($col = 1; $col <= 20; $col++) {
                    $line[] = $worksheet->getCellByColumnAndRow($col, $row)->getValue() ?? '';
                }

                if (empty(array_filter($line))) {
                    continue;
                }

                $rows[] = $this->mapRowToColumns($line);
            }
        } catch (\Exception $e) {
            Log::error('Excel parse error: ' . $e->getMessage());
        }

        return $rows;
    }

    /**
     * Map array baris (index 0-19) ke associative array kolom database.
     */
    protected function mapRowToColumns(array $line): array
    {
        return [
            'kecamatan'                => trim((string) ($line[0] ?? '')),
            'kelurahan'                => trim((string) ($line[1] ?? '')),
            'jumlah_keluarga'          => $this->toInt($line[2] ?? 0),
            'jumlah_individu'          => $this->toInt($line[3] ?? 0),
            'desil1_keluarga'          => $this->toInt($line[4] ?? 0),
            'desil1_individu'          => $this->toInt($line[5] ?? 0),
            'desil2_keluarga'          => $this->toInt($line[6] ?? 0),
            'desil2_individu'          => $this->toInt($line[7] ?? 0),
            'desil3_keluarga'          => $this->toInt($line[8] ?? 0),
            'desil3_individu'          => $this->toInt($line[9] ?? 0),
            'desil4_keluarga'          => $this->toInt($line[10] ?? 0),
            'desil4_individu'          => $this->toInt($line[11] ?? 0),
            'desil5_keluarga'          => $this->toInt($line[12] ?? 0),
            'desil5_individu'          => $this->toInt($line[13] ?? 0),
            'desil6_10_keluarga'       => $this->toInt($line[14] ?? 0),
            'desil6_10_individu'       => $this->toInt($line[15] ?? 0),
            'belum_peringkat_keluarga' => $this->toInt($line[16] ?? 0),
            'belum_peringkat_individu' => $this->toInt($line[17] ?? 0),
            'nonaktif_keluarga'        => $this->toInt($line[18] ?? 0),
            'nonaktif_individu'        => $this->toInt($line[19] ?? 0),
        ];
    }

    /**
     * Deteksi apakah baris pertama adalah header.
     */
    protected function detectHeader(string $line, string $separator): bool
    {
        $parts      = explode($separator, $line);
        $firstValue = strtolower(trim($parts[0] ?? ''));

        return $this->isHeaderValue($firstValue);
    }

    protected function isHeaderValue(?string $value): bool
    {
        if ($value === null) {
            return false;
        }

        $headerKeywords = ['kecamatan', 'kec', 'district', 'no', 'nama'];
        $lower          = strtolower(trim($value));

        foreach ($headerKeywords as $keyword) {
            if (str_contains($lower, $keyword)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Convert value ke integer. Handle string dengan separator ribuan.
     */
    protected function toInt(mixed $value): int
    {
        if (is_numeric($value)) {
            return (int) $value;
        }

        $cleaned = preg_replace('/[^0-9]/', '', (string) $value);

        return (int) $cleaned;
    }
}
