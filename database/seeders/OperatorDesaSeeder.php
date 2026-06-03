<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use App\Models\Village;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Buat akun operator_desa untuk seluruh 148 desa/kelurahan Buleleng.
 *
 * Email    : operator.<kode_desa>@buleleng.go.id
 * Password : pmks@<kode_desa>
 * Contoh   : operator.5108010001@buleleng.go.id / pmks@5108010001
 *
 * Idempotent — aman dijalankan berulang kali.
 */
class OperatorDesaSeeder extends Seeder
{
    public function run(): void
    {
        $villages = Village::with('kecamatan')->where('is_active', true)->get();

        if ($villages->isEmpty()) {
            $this->command->error('Tidak ada desa aktif. Pastikan VillageSeeder sudah jalan.');
            return;
        }

        $created  = 0;
        $skipped  = 0;
        $restored = 0;

        foreach ($villages as $village) {
            $email    = 'operator.' . $village->code . '@buleleng.go.id';
            $password = 'pmks@' . $village->code;
            $name     = 'Operator ' . $village->name
                      . ' (' . ($village->kecamatan?->name ?? '-') . ')';

            $existing = User::withTrashed()->where('email', $email)->first();

            if ($existing) {
                if ($existing->trashed()) {
                    $existing->restore();
                    $existing->syncRoles([UserRole::OPERATOR_DESA->value]);
                    $restored++;
                    $this->command->warn("  Restored : {$email}");
                } else {
                    $skipped++;
                }
                continue;
            }

            $user = User::create([
                'name'       => $name,
                'email'      => $email,
                'password'   => Hash::make($password),
                'village_id' => $village->id,
                'is_active'  => true,
            ]);

            $user->syncRoles([UserRole::OPERATOR_DESA->value]);
            $created++;
            $this->command->line("  Dibuat   : {$email}");
        }

        $this->command->newLine();
        $this->command->info("Selesai!");
        $this->command->line("  Dibuat   : {$created} akun baru");
        $this->command->line("  Restored : {$restored} akun");
        $this->command->line("  Dilewati : {$skipped} akun (sudah ada)");
        $this->command->newLine();
        $this->command->line("Format login:");
        $this->command->line("  Email    : operator.<kode_desa>@buleleng.go.id");
        $this->command->line("  Password : pmks@<kode_desa>");
        $this->command->newLine();
        $this->command->warn("Ingatkan operator desa ganti password setelah login pertama!");
    }
}
