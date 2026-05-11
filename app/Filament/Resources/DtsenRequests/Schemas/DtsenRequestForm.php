<?php

namespace App\Filament\Resources\DtsenRequests\Schemas;

use App\Models\Resident;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class DtsenRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        $user      = Auth::user();
        $villageId = $user->village_id;

        return $schema->components([
            Placeholder::make('village_info')
                ->label('Desa')
                ->content(fn () => $user->village?->name ?? '-'),

            Select::make('residents')
                ->label('Warga')
                ->relationship(
                    name: 'residents',
                    titleAttribute: 'name',
                    modifyQueryUsing: fn ($query) => $query
                        ->where('village_id', $villageId)
                        ->where('is_active', true)
                )
                ->multiple()
                ->searchable()
                ->preload(false)
                ->getSearchResultsUsing(
                    fn (string $search) => Resident::query()
                        ->where('village_id', $villageId)
                        ->where('is_active', true)
                        ->where(
                            fn ($q) => $q
                                ->where('name', 'like', "%{$search}%")
                                ->orWhere('nik', 'like', "%{$search}%")
                        )
                        ->limit(20)
                        ->get()
                        ->mapWithKeys(fn ($r) => [$r->id => "{$r->name} — {$r->nik}"])
                )
                ->getOptionLabelUsing(
                    fn ($value) => optional(Resident::find($value))->name
                        . ' — '
                        . optional(Resident::find($value))->nik
                )
                ->required()
                ->helperText('Cari berdasarkan NIK atau nama warga'),

            Textarea::make('purpose')
                ->label('Keperluan')
                ->required()
                ->rows(3)
                ->maxLength(500)
                ->helperText('Jelaskan keperluan permohonan surat DTSEN'),

            Textarea::make('notes')
                ->label('Catatan Tambahan')
                ->rows(2)
                ->nullable(),
        ]);
    }
}
