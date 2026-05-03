<?php

namespace App\Filament\Resources\PsksSubmissions\Schemas;

use App\Enums\BatchStatus;
use App\Models\Institution;
use App\Models\Kecamatan;
use App\Models\PsksCategory;
use App\Models\Resident;
use App\Models\SubmissionBatch;
use App\Models\Village;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class PsksSubmissionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('batch_id')
                ->label('Batch Pengajuan')
                ->options(function () {
                    $user  = Auth::user();
                    $query = SubmissionBatch::whereIn('status', [
                        BatchStatus::DRAFT->value,
                        BatchStatus::REVISED->value,
                    ])->with('village');

                    if ($user?->isOperatorDesa() && $user->village_id) {
                        $query->where('village_id', $user->village_id);
                    }

                    return $query->get()->mapWithKeys(fn ($batch) =>
                        [$batch->id => "{$batch->village->name} - {$batch->period_year}"]
                    );
                })
                ->required()
                ->searchable()
                ->live()
                ->afterStateUpdated(function (callable $set, $state) {
                    if ($state) {
                        $batch = SubmissionBatch::find($state);
                        $set('village_id', $batch?->village_id);
                        $set('kecamatan_id', $batch?->village?->kecamatan_id);
                    }
                    $set('subject_id', null);
                }),

            Select::make('category_id')
                ->label('Kategori PSKS')
                ->options(PsksCategory::active()->pluck('name', 'id'))
                ->required()
                ->searchable()
                ->live()
                ->afterStateUpdated(function (callable $set, $state) {
                    if ($state) {
                        $category = PsksCategory::find($state);
                        $set('subject_type', $category?->subject_type);
                    }
                    $set('subject_id', null);
                }),

            Select::make('subject_type')
                ->label('Jenis Subjek')
                ->options([
                    'person'      => 'Individu / Jiwa',
                    'institution' => 'Lembaga',
                ])
                ->required()
                ->live()
                ->afterStateUpdated(fn (callable $set) => $set('subject_id', null)),

            Select::make('kecamatan_id')
                ->label('Kecamatan')
                ->options(Kecamatan::active()->pluck('name', 'id'))
                ->searchable()
                ->live()
                ->afterStateUpdated(fn (callable $set) => $set('subject_id', null))
                ->dehydrated(false)
                ->hidden(fn () => Auth::user()?->isOperatorDesa()),

            Select::make('village_id')
                ->label('Desa / Kelurahan')
                ->searchable()
                ->live()
                ->options(function (callable $get) {
                    $user = Auth::user();
                    if ($user?->isOperatorDesa() && $user->village_id) {
                        return Village::where('id', $user->village_id)->pluck('name', 'id');
                    }
                    $kecamatanId = $get('kecamatan_id');
                    if (!$kecamatanId) return [];
                    return Village::active()
                        ->where('kecamatan_id', $kecamatanId)
                        ->pluck('name', 'id');
                })
                ->afterStateUpdated(fn (callable $set) => $set('subject_id', null))
                ->dehydrated(false)
                ->hidden(fn () => Auth::user()?->isOperatorDesa()),

            Placeholder::make('village_info')
                ->label('Desa')
                ->content(fn () => Auth::user()?->village?->name ?? '-')
                ->visible(fn () => Auth::user()?->isOperatorDesa()),

            Select::make('subject_id')
                ->label('Pilih Subjek')
                ->required()
                ->searchable()
                ->options(function (callable $get) {
                    $user        = Auth::user();
                    $subjectType = $get('subject_type');
                    $villageId   = $user?->isOperatorDesa()
                        ? $user->village_id
                        : $get('village_id');

                    if (!$subjectType || !$villageId) return [];

                    if ($subjectType === 'person') {
                        return Resident::active()
                            ->where('village_id', $villageId)
                            ->get()
                            ->mapWithKeys(fn ($r) => [
                                $r->id => "{$r->nik} - {$r->name}"
                            ]);
                    }

                    return Institution::active()
                        ->where('village_id', $villageId)
                        ->pluck('name', 'id');
                })
                ->disabled(function (callable $get) {
                    $user = Auth::user();
                    if (!$get('subject_type')) return true;
                    if ($user?->isOperatorDesa()) return false;
                    return !$get('village_id');
                })
                ->placeholder('Pilih kategori dan desa dulu'),

            Textarea::make('notes')
                ->label('Catatan')
                ->nullable()
                ->rows(3),
        ]);
    }
}
