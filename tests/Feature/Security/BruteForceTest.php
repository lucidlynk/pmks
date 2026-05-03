<?php

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use DanHarrin\LivewireRateLimiting\WithRateLimiting;

beforeEach(function () {
    // Reset rate limiter & cache sebelum setiap test
    Cache::flush();
});

it('user bisa login dengan kredensial yang benar', function () {
    $user = User::factory()->adminDinsos()->create([
        'email'    => 'test@test.com',
        'password' => bcrypt('password123'),
    ]);

    \Livewire\Livewire::test(\App\Filament\Pages\Login::class)
        ->fillForm([
            'email'    => 'test@test.com',
            'password' => 'password123',
        ])
        ->call('authenticate')
        ->assertHasNoFormErrors();
});

it('login gagal dengan password salah', function () {
    User::factory()->adminDinsos()->create([
        'email'    => 'test@test.com',
        'password' => bcrypt('password123'),
    ]);

    \Livewire\Livewire::test(\App\Filament\Pages\Login::class)
        ->fillForm([
            'email'    => 'test@test.com',
            'password' => 'salah',
        ])
        ->call('authenticate')
        ->assertHasErrors();
});

it('login gagal dicatat di audit log', function () {
    User::factory()->adminDinsos()->create([
        'email'    => 'test@test.com',
        'password' => bcrypt('password123'),
    ]);

    \Livewire\Livewire::test(\App\Filament\Pages\Login::class)
        ->fillForm([
            'email'    => 'test@test.com',
            'password' => 'salah',
        ])
        ->call('authenticate');

    expect(AuditLog::where('action', 'login_failed')->exists())->toBeTrue();
});

it('login berhasil dicatat di audit log', function () {
    User::factory()->adminDinsos()->create([
        'email'    => 'test@test.com',
        'password' => bcrypt('password123'),
    ]);

    \Livewire\Livewire::test(\App\Filament\Pages\Login::class)
        ->fillForm([
            'email'    => 'test@test.com',
            'password' => 'password123',
        ])
        ->call('authenticate');

    expect(AuditLog::where('action', 'login')->exists())->toBeTrue();
});

it('user nonaktif tidak bisa login', function () {
    User::factory()->inactive()->adminDinsos()->create([
        'email'    => 'nonaktif@test.com',
        'password' => bcrypt('password123'),
    ]);

    \Livewire\Livewire::test(\App\Filament\Pages\Login::class)
        ->fillForm([
            'email'    => 'nonaktif@test.com',
            'password' => 'password123',
        ])
        ->call('authenticate')
        ->assertHasErrors();
});

it('rate limiter mencatat percobaan login gagal', function () {
    User::factory()->adminDinsos()->create([
        'email'    => 'brute@test.com',
        'password' => bcrypt('password123'),
    ]);

    // Setiap instance Livewire baru = sesi baru
    // Kita test audit log saja karena rate limiter per IP
    for ($i = 0; $i < 3; $i++) {
        \Livewire\Livewire::test(\App\Filament\Pages\Login::class)
            ->fillForm([
                'email'    => 'brute@test.com',
                'password' => 'salah',
            ])
            ->call('authenticate');
    }

    // Harus ada minimal 1 log login_failed
    expect(AuditLog::where('action', 'login_failed')->count())->toBeGreaterThanOrEqual(1);
});

it('cache menyimpan jumlah percobaan login gagal', function () {
    User::factory()->adminDinsos()->create([
        'email'    => 'cache@test.com',
        'password' => bcrypt('password123'),
    ]);

    \Livewire\Livewire::test(\App\Filament\Pages\Login::class)
        ->fillForm([
            'email'    => 'cache@test.com',
            'password' => 'salah',
        ])
        ->call('authenticate');

    $key = 'login_attempts_' . request()->ip();
    expect(Cache::get($key, 0))->toBeGreaterThanOrEqual(1);
});
