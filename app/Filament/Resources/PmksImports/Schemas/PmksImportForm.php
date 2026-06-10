<?php

namespace App\Filament\Resources\PmksImports\Schemas;

use App\Enums\BatchStatus;
use App\Models\SubmissionBatch;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class PmksImportForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

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
                ]),

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
                        ->helperText(
                            'Format kolom (separator titik koma ;): ' .
                            'nik ; nama ; tgl_lahir (dd-mm-yyyy) ; jenis_kelamin (L/P) ; kode_kategori (PMKS-01 s.d. PMKS-26) ; catatan ; jenis_disabilitas (fisik|intelektual|mental|sensorik — wajib untuk PMKS-05 dan PMKS-09). ' .
                            'Baris pertama adalah header dan akan dilewati. Maks 10MB.'
                        )
                        ->columnSpanFull(),
                ]),

        ]);
    }
}
