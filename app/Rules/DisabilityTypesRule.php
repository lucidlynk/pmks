<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class DisabilityTypesRule implements ValidationRule
{
    private const DISABILITY_CATEGORY_IDS = [5, 9];

    public function __construct(private readonly ?int $categoryId) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!in_array($this->categoryId, self::DISABILITY_CATEGORY_IDS)) {
            return; // Bukan kategori disabilitas, skip validasi
        }

        if (empty($value)) {
            $fail('Jenis disabilitas wajib dipilih untuk kategori ini.');
        }
    }
}
