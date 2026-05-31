<?php

namespace App\Console\Commands;

use App\Jobs\Kis\KisPbiApbdParserJob;
use App\Models\KisPbiApbdImport;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class DetectStuckImports extends Command
{
    protected $signature   = 'kis:detect-stuck-imports';
    protected $description = 'Deteksi import KIS PBI APBD yang stuck dan kirim notifikasi ke admin';

    public function handle(): void
    {
        // Import yang stuck: status processing lebih dari 45 menit
        $stuckImports = KisPbiApbdImport::where('status', 'processing')
            ->where('started_at', '<=', now()->subMinutes(45))
            ->whereNull('finished_at')
            ->get();

        if ($stuckImports->isEmpty()) {
            $this->info('Tidak ada import yang stuck.');
            return;
        }

        foreach ($stuckImports as $import) {
            $this->warn("Import #{$import->id} stuck sejak {$import->started_at} — periode {$import->periode_label}");

            // Update status ke failed
            $import->update([
                'status'        => 'failed',
                'finished_at'   => now(),
                'error_summary' => ['message' => 'Import stuck lebih dari 45 menit. Kemungkinan server restart atau worker mati. Silakan gunakan tombol "Proses Ulang".'],
            ]);

            // Kirim notifikasi ke semua Admin Dinsos
            $admins = User::role('admin_dinsos')->get();

            foreach ($admins as $admin) {
                Notification::make()
                    ->warning()
                    ->title('Import KIS PBI APBD Gagal')
                    ->body("Import periode {$import->periode_label} ({$import->original_filename}) terdeteksi stuck dan ditandai gagal. Silakan buka halaman import dan klik \"Proses Ulang\".")
                    ->sendToDatabase($admin);
            }

            $this->info("Import #{$import->id} ditandai failed dan notifikasi dikirim ke " . $admins->count() . " admin.");
        }

        $this->info("Selesai. {$stuckImports->count()} import ditangani.");
    }
}
