<?php

namespace App\Rules;

use App\Models\PmksCategory;
use App\Models\Resident;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PmksAgeRule implements ValidationRule
{
    public function __construct(
        private readonly ?int $residentId,
        private readonly ?int $categoryId,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$this->residentId || !$this->categoryId) return;

        $resident = Resident::find($this->residentId);
        $category = PmksCategory::find($this->categoryId);

        if (!$resident || !$category) return;

        // Validasi usia — baca dari DB
        if ($category->hasAgeRestriction()) {
            $age = $resident->birth_date->age;

            $tooYoung = $category->min_age !== null && $age < $category->min_age;
            $tooOld   = $category->max_age !== null && $age > $category->max_age;

            if ($tooYoung || $tooOld) {
                $fail("Kategori {$category->name} hanya untuk penduduk usia {$category->ageLabel()}. Penduduk ini berusia {$age} tahun.");
                return;
            }
        }

        // Validasi gender — baca dari DB
        if ($category->hasGenderRestriction()) {
            if ($resident->gender !== $category->gender_restriction) {
                $genderLabel = $resident->gender === 'L' ? 'Laki-laki' : 'Perempuan';
                $fail("Kategori {$category->name} hanya untuk {$category->genderLabel()}. Penduduk ini berjenis kelamin {$genderLabel}.");
            }
        }
    }
}
