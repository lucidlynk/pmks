<?php

namespace App\Filament\Resources\PmksSubmissions\Schemas;

use App\Enums\BatchStatus;
use App\Models\FamilyCard;
use App\Models\Kecamatan;
use App\Models\PmksCategory;
use App\Models\Resident;
use App\Models\SubmissionBatch;
use App\Models\Village;
use App\Rules\DisabilityTypesRule;
use App\Rules\PmksAgeRule;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class PmksSubmissionForm
{
    private const DISABILITY_CATEGORY_IDS = [5, 9];

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
                ->afterStateUpdated(fn (callable $set) => $set('category_id', null))
                // ── SHORTCUT: tambah penduduk baru langsung dari form ──
                ->createOptionForm(function (callable $get) {
                    $user      = Auth::user();
                    $villageId = $user?->isOperatorDesa()
                        ? $user->village_id
                        : $get('village_id');

                    return [
                        TextInput::make('nik')
                            ->label('NIK')
                            ->required()
                            ->maxLength(16)
                            ->minLength(16)
                            ->placeholder('16 digit NIK')
                            ->unique('residents', 'nik')
                            ->validationMessages([
                                'unique' => 'NIK ini sudah terdaftar dalam sistem.',
                            ]),

                        TextInput::make('name')
                            ->label('Nama Lengkap')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('birth_place')
                            ->label('Tempat Lahir')
                            ->required()
                            ->maxLength(255),

                        DatePicker::make('birth_date')
                            ->label('Tanggal Lahir')
                            ->required()
                            ->maxDate(now()),

                        Select::make('gender')
                            ->label('Jenis Kelamin')
                            ->options(['L' => 'Laki-laki', 'P' => 'Perempuan'])
                            ->required(),

                        Select::make('family_card_id')
                            ->label('Kartu Keluarga (No. KK)')
                            ->searchable()
                            ->nullable()
                            ->options(function () use ($villageId) {
                                if (!$villageId) return [];
                                return FamilyCard::active()
                                    ->where('village_id', $villageId)
                                    ->pluck('no_kk', 'id');
                            })
                            ->placeholder('Pilih KK (opsional)'),

                        Select::make('status_hubungan')
                            ->label('Status Hubungan dalam KK')
                            ->options([
                                'kepala_keluarga' => 'Kepala Keluarga',
                                'istri'           => 'Istri',
                                'anak'            => 'Anak',
                                'orang_tua'       => 'Orang Tua',
                                'lainnya'         => 'Lainnya',
                            ])
                            ->nullable(),

                        TextInput::make('phone')
                            ->label('Nomor HP')
                            ->nullable()
                            ->maxLength(20)
                            ->tel(),
                    ];
                })
                ->createOptionUsing(function (array $data, callable $get) {
                    $user      = Auth::user();
                    $villageId = $user?->isOperatorDesa()
                        ? $user->village_id
                        : $get('village_id');

                    $resident = Resident::create([
                        'village_id'       => $villageId,
                        'nik'              => $data['nik'],
                        'name'             => $data['name'],
                        'birth_place'      => $data['birth_place'],
                        'birth_date'       => $data['birth_date'],
                        'gender'           => $data['gender'],
                        'family_card_id'   => $data['family_card_id'] ?? null,
                        'status_hubungan'  => $data['status_hubungan'] ?? null,
                        'phone'            => $data['phone'] ?? null,
                        'is_active'        => true,
                    ]);

                    return $resident->id;
                })
                ->createOptionModalHeading('Tambah Penduduk Baru'),

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

                    $hints = [];
                    if ($category->hasAgeRestriction()) {
                        $hints[] = "Usia: {$category->ageLabel()}";
                    }
                    if ($category->hasGenderRestriction()) {
                        $hints[] = "Gender: {$category->genderLabel()}";
                    }

                    return count($hints) ? implode(' · ', $hints) : null;
                }),

            CheckboxList::make('disability_types')
                ->label('Jenis Disabilitas')
                ->options([
                    'fisik'       => 'Fisik',
                    'intelektual' => 'Intelektual',
                    'mental'      => 'Mental',
                    'sensorik'    => 'Sensorik',
                ])
                ->columns(2)
                ->gridDirection('row')
                ->rules(fn (callable $get) => [
                    new DisabilityTypesRule(categoryId: $get('category_id')),
                ])
                ->visible(fn (callable $get) => in_array($get('category_id'), self::DISABILITY_CATEGORY_IDS)),

            Textarea::make('notes')
                ->label('Catatan')
                ->nullable()
                ->rows(3),
        ]);
    }
}
