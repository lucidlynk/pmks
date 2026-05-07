<?php

namespace App\Rules;

use App\Models\FamilyCard;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UniqueKepalakeluarga implements ValidationRule
{
    public function __construct(
        private readonly ?int $ignoreId = null
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $query = FamilyCard::where('status_hubungan_kepala', 'kepala_keluarga')
            ->where('no_kk', $value);

        if ($this->ignoreId) {
            $query->where('id', '!=', $this->ignoreId);
        }

        // Cek apakah di KK ini sudah ada kepala keluarga
        if (FamilyCard::where('no_kk', $value)
            ->when($this->ignoreId, fn($q) => $q->where('id', '!=', $this->ignoreId))
            ->exists()) {
            $fail('Nomor KK ini sudah terdaftar. Satu nomor KK hanya boleh ada satu data.');
        }
    }
}
