<?php

namespace App\Filament\Resources\PmksSubmissions\Schemas;

use App\Enums\BatchStatus;
use App\Models\Kecamatan;
use App\Models\PmksCategory;
use App\Models\Resident;
use App\Models\SubmissionBatch;
use App\Models\Village;
use App\Rules\PmksAgeRule;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\CheckboxList;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class PmksSubmissionForm
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
                    ])->with('village.kecamatan');

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
                    $set('resident_id', null);
                }),

            Select::make('kecamatan_id')
                ->label('Kecamatan')
                ->options(Kecamatan::active()->pluck('name', 'id'))
                ->searchable()
                ->live()
                ->afterStateUpdated(fn (callable $set) => $set('resident_id', null))
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
                ->afterStateUpdated(fn (callable $set) => $set('resident_id', null))
                ->dehydrated(false)
                ->hidden(fn () => Auth::user()?->isOperatorDesa()),

            Placeholder::make('village_info')
                ->label('Desa')
                ->content(fn () => Auth::user()?->village?->name ?? '-')
                ->visible(fn () => Auth::user()?->isOperatorDesa()),

            Select::make('resident_id')
                ->label('Penduduk (NIK / Nama)')
                ->required()
                ->searchable()
                ->live()
                ->options(function (callable $get) {
                    $user      = Auth::user();
                    $villageId = $user?->isOperatorDesa()
                        ? $user->village_id
                        : $get('village_id');

                    if (!$villageId) return [];

                    return Resident::active()
                        ->where('village_id', $villageId)
                        ->get()
                        ->mapWithKeys(fn ($r) => [
                            $r->id => "{$r->nik} - {$r->name}"
                        ]);
                })
                ->disabled(function (callable $get) {
                    $user = Auth::user();
                    if ($user?->isOperatorDesa()) return false;
                    return !$get('village_id');
                })
                ->placeholder('Cari NIK atau nama penduduk')
                ->afterStateUpdated(fn (callable $set) => $set('category_id', null)),

            Select::make('category_id')
                ->label('Kategori PMKS')
                ->options(PmksCategory::active()->pluck('name', 'id'))
                ->required()
                ->searchable()
                ->live()
                ->rules(function (callable $get) {
                    return [
                        new PmksAgeRule(
                            residentId: $get('resident_id'),
                            categoryId: $get('category_id'),
                        ),
                    ];
                })
                ->helperText(function (callable $get) {
                    $categoryId = $get('category_id');
                    if (!$categoryId) return null;

                    $category = PmksCategory::find($categoryId);
                    if (!$category) return null;

                    $rule = \App\Rules\PmksAgeRule::getRulesForCategory($category->code);
                    if (!$rule) return null;

                    return "Kategori ini hanya untuk usia {$rule['label']}";
                }),

            // INPUT BARU: Muncul hanya untuk ID 5 (Anak Disabilitas) dan 9 (Penyandang Disabilitas)
            CheckboxList::make('disability_types')
                ->label('Jenis Disabilitas')
                ->options([
                    'fisik' => 'Fisik',
                    'intelektual' => 'Intelektual',
                    'mental' => 'Mental',
                    'sensorik' => 'Sensorik',
                ])
                ->columns(2)
                ->gridDirection('row')
                ->required()
                ->visible(fn (callable $get) => in_array($get('category_id'), [5, 9])),

            Textarea::make('notes')
                ->label('Catatan')
                ->nullable()
                ->rows(3),
        ]);
    }
}
