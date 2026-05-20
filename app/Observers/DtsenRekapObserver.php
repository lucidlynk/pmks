<?php

namespace App\Observers;

use App\Models\DtsenRekap;
use Illuminate\Support\Facades\Storage;

class DtsenRekapObserver
{
    /**
     * Saat record di-force-delete, hapus file fisik dari storage.
     */
    public function forceDeleted(DtsenRekap $rekap): void
    {
        if ($rekap->file_path && Storage::disk('local')->exists($rekap->file_path)) {
            Storage::disk('local')->delete($rekap->file_path);
        }
    }
}
