<?php

namespace App\Filament\Resources\PsksImports\Schemas;

use App\Enums\BatchStatus;
use App\Models\SubmissionBatch;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class PsksImportForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            Section::make('Batch Pengajuan')
                ->description('Pilih batch PSKS yang akan diisi datanya via import.')
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
                ->description('Upload file CSV sesuai format template. Satu file dapat memuat baris individu (PSKS-J-*) dan lembaga (PSKS-L-*) sekaligus.')
                ->schema([
                    FileUpload::make('file_path')
                        ->label('File CSV PSKS')
                        ->required()
                        ->disk('local')
                        ->directory('psks-imports')
                        ->visibility('private')
                        ->acceptedFileTypes(['text/csv', 'text/plain', 'application/csv', 'application/vnd.ms-excel'])
                        ->maxSize(10240)
                        ->storeFileNamesIn('original_filename')
                        ->helperText(
                            'Format kolom (separator titik koma ;): ' .
                            'kode_kategori ; nik ; nama ; tgl_lahir (dd-mm-yyyy) ; jenis_kelamin (L/P) ; tipe_lembaga (karang_taruna/pkk/lks/lainnya) ; nomor_registrasi ; catatan. ' .
                            'Untuk baris individu (PSKS-J-*): isi nik, nama, tgl_lahir, jenis_kelamin — kosongkan kolom lembaga. ' .
                            'Untuk baris lembaga (PSKS-L-*): isi nama, tipe_lembaga — kosongkan kolom individu. ' .
                            'Maks 10MB.'
                        )
                        ->columnSpanFull(),
                ]),

        ]);
    }
}
