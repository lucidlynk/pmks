<?php

use App\Models\PsksCategory;
use App\Models\User;

it('admin dapat membuka halaman list kategori psks', function () {
    $user = User::factory()->adminDinsos()->create();

    $this->actingAs($user)
         ->get('/admin/psks-categories')
         ->assertOk();
});

it('dapat membuat kategori psks individu', function () {
    $user = User::factory()->adminDinsos()->create();
    $this->actingAs($user);

    \Livewire\Livewire::test(\App\Filament\Resources\PsksCategories\Pages\CreatePsksCategory::class)
        ->fillForm([
            'code'         => 'PSKS-J-99',
            'name'         => 'Test Kategori',
            'subject_type' => 'person',
            'is_active'    => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(PsksCategory::where('code', 'PSKS-J-99')->exists())->toBeTrue();
});

it('subject_type hanya boleh person atau institution', function () {
    $category = PsksCategory::create([
        'code'         => 'PSKS-J-01',
        'name'         => 'Test',
        'subject_type' => 'person',
    ]);

    expect($category->subject_type)->toBeIn(['person', 'institution']);
});
