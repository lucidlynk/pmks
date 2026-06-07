<?php

namespace App\Filament\Resources\PsksSubmissions\Schemas;

use App\Enums\BatchStatus;
use App\Models\FamilyCard;
use App\Models\Institution;
use App\Models\Kecamatan;
use App\Models\PsksCategory;
use App\Models\Resident;
use App\Models\SubmissionBatch;
use App\Models\Village;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
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
                ->placeholder('Pilih kategori dan desa dulu')
                ->createOptionForm(function (callable $get) {
                    $subjectType = $get('subject_type');
                    $user        = Auth::user();
                    $villageId   = $user?->isOperatorDesa()
                        ? $user->village_id
                        : $get('village_id');

                    if ($subjectType === 'person') {
                        return [
                            TextInput::make('nik')
                                ->label('NIK')
                                ->required()
                                ->length(16)
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

                            TextInput::make('phone')
                                ->label('Nomor HP')
                                ->nullable()
                                ->maxLength(20)
                                ->tel(),
                        ];
                    }

                    // subject_type = 'institution'
                    return [
                        TextInput::make('name')
                            ->label('Nama Lembaga')
                            ->required()
                            ->maxLength(255),

                        Select::make('type')
                            ->label('Tipe Lembaga')
                            ->options([
                                'karang_taruna' => 'Karang Taruna',
                                'pkk'           => 'PKK',
                                'lks'           => 'LKS',
                                'lainnya'       => 'Lainnya',
                            ])
                            ->required(),

                        TextInput::make('registration_number')
                            ->label('Nomor Registrasi')
                            ->nullable()
                            ->maxLength(100),

                        TextInput::make('address')
                            ->label('Alamat')
                            ->nullable()
                            ->maxLength(500),

                        TextInput::make('contact_person')
                            ->label('Nama Kontak')
                            ->nullable()
                            ->maxLength(255),

                        TextInput::make('phone')
                            ->label('Nomor HP')
                            ->nullable()
                            ->maxLength(20)
                            ->tel(),
                    ];
                })
                ->createOptionUsing(function (array $data, callable $get) {
                    $subjectType = $get('subject_type');
                    $user        = Auth::user();
                    $villageId   = $user?->isOperatorDesa()
                        ? $user->village_id
                        : $get('village_id');

                    if ($subjectType === 'person') {
                        $record = Resident::create([
                            'village_id'     => $villageId,
                            'nik'            => $data['nik'],
                            'name'           => $data['name'],
                            'birth_place'    => $data['birth_place'],
                            'birth_date'     => $data['birth_date'],
                            'gender'         => $data['gender'],
                            'family_card_id' => $data['family_card_id'] ?? null,
                            'phone'          => $data['phone'] ?? null,
                            'is_active'      => true,
                        ]);
                        return $record->id;
                    }

                    // institution
                    $record = Institution::create([
                        'village_id'          => $villageId,
                        'name'                => $data['name'],
                        'type'                => $data['type'],
                        'registration_number' => $data['registration_number'] ?? null,
                        'address'             => $data['address'] ?? null,
                        'contact_person'      => $data['contact_person'] ?? null,
                        'phone'               => $data['phone'] ?? null,
                        'is_active'           => true,
                    ]);
                    return $record->id;
                })
                ->createOptionModalHeading(function (callable $get) {
                    return $get('subject_type') === 'person'
                        ? 'Tambah Penduduk Baru'
                        : 'Tambah Lembaga Baru';
                }),

            Textarea::make('notes')
                ->label('Catatan')
                ->nullable()
                ->rows(3),
        ]);
    }
}
