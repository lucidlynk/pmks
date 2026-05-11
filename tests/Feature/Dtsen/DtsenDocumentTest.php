<?php

use App\Models\DtsenDocument;
use App\Models\DtsenRequest;
use App\Models\User;
use App\Models\Village;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

// ─── File Size Formatting ─────────────────────────────────────────────────────

it('formats file size in bytes', function (): void {
    $doc = new DtsenDocument(['file_size' => 512]);
    expect($doc->getFileSizeForHumans())->toBe('512 B');
});

it('formats file size in kilobytes', function (): void {
    $doc = new DtsenDocument(['file_size' => 2048]);
    expect($doc->getFileSizeForHumans())->toBe('2 KB');
});

it('formats file size in megabytes', function (): void {
    $doc = new DtsenDocument(['file_size' => 2_097_152]);
    expect($doc->getFileSizeForHumans())->toBe('2 MB');
});

// ─── existsOnDisk ─────────────────────────────────────────────────────────────

it('existsOnDisk returns true when file exists', function (): void {
    Storage::fake('private');
    Storage::disk('private')->put('dtsen/test.pdf', 'dummy');

    $doc = new DtsenDocument(['file_path' => 'dtsen/test.pdf']);

    expect($doc->existsOnDisk())->toBeTrue();
});

it('existsOnDisk returns false when file missing', function (): void {
    Storage::fake('private');

    $doc = new DtsenDocument(['file_path' => 'dtsen/missing.pdf']);

    expect($doc->existsOnDisk())->toBeFalse();
});

// ─── is_current flag ─────────────────────────────────────────────────────────

it('only one document is current per request', function (): void {
    $village  = Village::factory()->create();
    $user     = User::factory()->create(['village_id' => $village->id]);
    $user->assignRole('operator_desa');

    $request = DtsenRequest::factory()->create([
        'village_id' => $village->id,
        'user_id'    => $user->id,
    ]);

    $doc1 = $request->documents()->create([
        'file_path'         => 'dtsen/v1.pdf',
        'original_filename' => 'v1.pdf',
        'file_size'         => 1024,
        'is_current'        => true,
        'uploaded_by'       => $user->id,
    ]);

    // Simulasi upload baru: set lama jadi false
    $request->documents()->where('is_current', true)->update(['is_current' => false]);

    $doc2 = $request->documents()->create([
        'file_path'         => 'dtsen/v2.pdf',
        'original_filename' => 'v2.pdf',
        'file_size'         => 2048,
        'is_current'        => true,
        'uploaded_by'       => $user->id,
    ]);

    expect($request->documents()->where('is_current', true)->count())->toBe(1);
    expect($request->documents()->where('is_current', true)->first()->id)->toBe($doc2->id);
});
