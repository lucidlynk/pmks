<?php

namespace App\Filament\Resources\PmksImports\Schemas;

use App\Enums\BatchStatus;
use App\Models\SubmissionBatch;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class PmksImportForm
{
    public static function configure(Schema $schema): Schema
    {
        $isAdmin = Auth::user()?->isAdminDinsos();

        return $schema->components([

            Section::make('Mode Import')
                ->description('Pilih cakupan wilayah import.')
                ->schema([
                    Radio::make('import_mode')
                        ->label('Cakupan Import')
                        ->options([
                            'per_desa'   => 'Per Desa — pilih satu batch desa',
                            'kabupaten'  => 'Seluruh Kabupaten — satu CSV untuk semua desa',
                        ])
                        ->default('per_desa')
                        ->live()
                        ->inline(),
                ])
                ->visible($isAdmin),

            Section::make('Batch Pengajuan')
                ->description('Pilih batch PMKS yang akan diisi datanya via import.')
                ->schema([
                    Select::make('submission_batch_id')
                        ->label('Batch Pengajuan')
                        ->required()
                        ->searchable()
                        ->native(false)
                        ->options(function () {
                            $user  = Auth::user();
                            $query = SubmissionBatch::whereIn('status', [
                                BatchStatus::DRAFT->value,
                                BatchStatus::REVISED->value,
                            ])->with('village.kecamatan');

                            if ($user?->isOperatorDesa() && $user->village_id) {
                                $query->where('village_id', $user->village_id);
                            }

                            return $query->get()->mapWithKeys(fn ($batch) => [
                                $batch->id => "{$batch->village?->kecamatan?->name} › {$batch->village?->name} — {$batch->period_year}",
                            ]);
                        })
                        ->helperText('Hanya batch berstatus Draft atau Sudah Direvisi yang dapat diimport.'),
                ])
                ->visible(fn ($get) => ($get('import_mode') ?? 'per_desa') !== 'kabupaten'),

            Section::make('Periode Import')
                ->description('Sistem akan mencari batch Draft/Direvisi untuk setiap desa pada tahun yang dipilih.')
                ->schema([
                    Select::make('period_year')
                        ->label('Tahun Periode')
                        ->required()
                        ->native(false)
                        ->options(function () {
                            $year = now()->year;
                            return collect(range($year - 1, $year + 1))
                                ->mapWithKeys(fn ($y) => [$y => (string) $y]);
                        })
                        ->default(now()->year)
                        ->helperText('Import akan diarahkan ke batch desa yang berstatus Draft atau Sudah Direvisi pada tahun ini.'),
                ])
                ->visible(fn ($get) => $get('import_mode') === 'kabupaten'),

            Section::make('File CSV')
                ->description('Upload file CSV sesuai format template.')
                ->schema([
                    FileUpload::make('file_path')
                        ->label('File CSV PMKS')
                        ->required()
                        ->disk('local')
                        ->directory('pmks-imports')
                        ->visibility('private')
                        ->acceptedFileTypes(['text/csv', 'text/plain', 'application/csv', 'application/vnd.ms-excel'])
                        ->maxSize(10240)
                        ->storeFileNamesIn('original_filename')
                        ->helperText(function ($get) {
                            if ($get('import_mode') === 'kabupaten') {
                                return 'Format kolom mode Kabupaten (separator titik koma ;): ' .
                                    'kode_desa ; nik ; nama ; tgl_lahir (dd-mm-yyyy) ; jenis_kelamin (L/P) ; kode_kategori (PMKS-01 s.d. PMKS-26) ; catatan ; jenis_disabilitas (fisik|intelektual|mental|sensorik — wajib untuk PMKS-05 dan PMKS-09). ' .
                                    'kode_desa diisi dengan kode desa sesuai master data. Baris pertama adalah header dan akan dilewati. Maks 10MB.';
                            }
                            return 'Format kolom (separator titik koma ;): ' .
                                'nik ; nama ; tgl_lahir (dd-mm-yyyy) ; jenis_kelamin (L/P) ; kode_kategori (PMKS-01 s.d. PMKS-26) ; catatan ; jenis_disabilitas (fisik|intelektual|mental|sensorik — wajib untuk PMKS-05 dan PMKS-09). ' .
                                'Baris pertama adalah header dan akan dilewati. Maks 10MB.';
                        })
                        ->columnSpanFull(),
                ]),

        ]);
    }
}
